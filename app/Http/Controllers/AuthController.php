<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\LoginForm;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

/**
 * @group Auth API
 * @unauthenticated
 *
 * 인증관련 API 입니다.
 */
class AuthController extends Controller
{
    use ManagerTrait, ExtendResponseTrait;
    protected $users;

    public function __construct(User $users)
    {
        $this->users = $users;
    }


    /**
     * 로그인
     *
     * @bodyParam email string required 유저 이메일
     * @bodyParam password string required 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(LoginForm $request)
    {
        $user = $this->users->where('email', $request->email)->first();
        if($user)
        {
            if(Hash::check($request->password, $user->password))
            {
                $auths = $user->getAbilities($user->level);
                $token = $user->createToken($user->email, $auths)->plainTextToken;
                return $this->clearResponse(['accessToken'=>$token, 'userData'=>$user->getAuthData(), 'userAbilities'=>$auths]);
            }
            else
                return $this->errorResponse(['email'=>__('auth.failed')]);
        }
        else
            return $this->errorResponse(['email'=>__('auth.failed')]);
    }

    /**
     * 회원가입
     *
     * 일반유저 등급으로 회원가입 됩니다.
     *
     * @bodyParam email string required 유저 아이디
     * @bodyParam password string required 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUp(LoginForm $request)
    {
        $user = $this->users->where('email', $request->email)->first();
        if(!$user)
        {
            $new_user = $request->data();
            $new_user['password'] = Hash::make($new_user['password']);
            $res = $this->users->create($new_user);
            if($res)
            {
                $user = $this->users->where('id', $res->id)->first();
                $auths = $user->getAbilities($user->level);
                $token = $user->createToken($user->email, $auths)->plainTextToken;
                return $this->clearResponse(['accessToken'=>$token, 'userData'=>$user->getAuthData(), 'userAbilities'=>$auths]);
            }
            else
                return $this->errorResponse(['email'=>__('validation.system_error')]);
        }
        else
            return $this->response(['email'=>__('validation.unique', ['unique'=>'email'])]);
    }
    /**
     * 로그아웃
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOut(Request $request)
    {
        $request->user()->tokens();
        return $this->clearResponse([]);
    }
}
