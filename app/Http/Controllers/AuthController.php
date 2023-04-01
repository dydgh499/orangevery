<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Requests\LoginForm;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
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

    public function __construct()
    {
        
    }

    /**
     * 로그인
     *
     * @bodyParam user_name string required 유저 이메일
     * @bodyParam password string required 유저 패스워드
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(LoginForm $request)
    {

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
    }
    /**
     * 로그아웃
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOut(Request $request)
    {

    }
}
