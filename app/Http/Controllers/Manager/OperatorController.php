<?php

namespace App\Http\Controllers\Manager;

use App\Models\Operator;
use App\Http\Controllers\Ablilty\Ablilty;

use App\Enums\AuthLoginCode;
use App\Http\Controllers\Auth\AuthPhoneNum;
use App\Http\Controllers\Auth\AuthPasswordChange;
use App\Http\Controllers\Ablilty\AbnormalConnection;
use App\Http\Controllers\Message\MessageController;
use App\Http\Controllers\Manager\Service\BrandInfo;

use App\Models\Service\OperatorIP;
use App\Http\Traits\StoresTrait;
use App\Http\Traits\ManagerTrait;
use App\Http\Traits\ExtendResponseTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use App\Http\Requests\Manager\OperatorReqeust;
use App\Http\Requests\Manager\IndexRequest;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\HistoryType;

/**
 * @group Operator API
 *
 * 오퍼레이터 관리 메뉴에서 사용될 API 입니다. 본사 이상권한이 요구됩니다.
 */
class OperatorController extends Controller
{
    use ManagerTrait, ExtendResponseTrait, StoresTrait, EncryptDataTrait;
    protected $operators;
    protected $target;
    protected $imgs;

    public function __construct(Operator $operators)
    {
        $this->operators = $operators;
        $this->target = '운영자';
        $this->imgs = [
            'params'    => [
                 'profile_file',
            ],
            'cols'  => [
                'profile_img',
            ],
            'folders'   => [
                'profile',
            ],
            'sizes'     => [
               500
            ],
        ];
    }

    /**
     * 목록출력
     *
     * 가맹점 이상 가능
     */
    public function index(IndexRequest $request)
    {
        $search = $request->search;
        $query = $this->operators
            ->where('brand_id', $request->user()->brand_id)
            ->where('is_delete', false)
            ->where(function ($query) use ($search) {
                return $query->where('user_name', 'like', "%$search%")
                    ->orWhere('nick_name', 'like', "%$search%");
            });
        if($request->is_lock)
            $query = $query->where('is_lock', 1);
        if($request->user()->level < 40)
            $query = $query->where('id', $request->user()->id);

        $data = $this->getIndexData($request, $query);
        return $this->response(0, $data);
    }

    /**
     * 추가
     *
     * 대리점 이상 가능
     *
     */
    public function store(OperatorReqeust $request)
    {
        $current = date("Y-m-d H:i:s");
        $validated = $request->validate(['user_pw'=>'required']);
        if($request->user()->level > 35)
        {
            if(Ablilty::isEditAbleTime() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
            if($this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $user = $request->data();
                [$result, $msg] = AuthPasswordChange::registerValidate($user['user_name'], $request->input('user_pw'));
                if($result === false)
                    return $this->extendResponse(954, $msg, []);
                else
                {
                    [$result, $msg, $datas] = $this->employeePhoneValidate($request);
                    if($result !== AuthLoginCode::SUCCESS->value)
                        return $this->extendResponse($result, $msg, $datas);

                    $user = $this->saveImages($request, $user, $this->imgs);
                    $user['user_pw'] = Hash::make($request->input('user_pw').$current);
                    $user['created_at'] = $current;
                    $res = $this->operators->create($user);

                    operLogging(HistoryType::CREATE, $this->target, [], ['msg'=>'운영자는 보안상 이력이 남지 않습니다.'], $user['nick_name']);
                    return $this->response($res ? 1 : 990, ['id'=>$res->id]);        
                }
            }
        }
        else
            return $this->response(951);
    }

    /**
     * 단일조회
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function show(Request $request, int $id)
    {
        // level이 직원인데 본인이 아닌 유저가 조회하려할 때 실패
        if($request->user()->level === 35 && $request->user()->id !== $id)
        {
            AbnormalConnection::tryParameterModulationApproach();
            return $this->response(951);
        }
        else
        {
            $user = $this->operators->where('id', $id)->first();
            if(Ablilty::isBrandCheck($request, $user->brand_id) === false)
                return $this->response(951);
            else
                return $user ? $this->response(0, $user) : $this->response(1000);    
        }
    }

    private function employeePhoneValidate($request)
    {
        $result = AuthLoginCode::SUCCESS->value;
        $msg    = '';
        $data   = [];
        
        $brand = BrandInfo::getBrandById($request->user()->brand_id);
        if($brand['pv_options']['paid']['use_head_office_withdraw'])
        {
            $result = AuthPhoneNum::validate((string)$request->token);
            if($result === AuthLoginCode::REQUIRE_PHONE_AUTH->value)
            {
                [$code, $msg] = resolve(MessageController::class)->mobileCodeSend($request->user()->brand_id, $request->user()->phone_num, -1);
                if($code === 0)
                {
                    $msg = '직원 휴대폰번호 변경은 본사 휴대폰번호 인증이 필요합니다.<br>'.$request->user()->nick_name.'님에게 인증번호를 보냈습니다.';
                    $data = [
                        'phone_num' => $request->user()->phone_num,
                        'nick_name' => $request->user()->nick_name
                    ];
                }
                else
                    $result = $code;
            }
            else if($result === AuthLoginCode::WRONG_ACCESS->value)
                $msg = '잘못된 접근입니다.';
        }
        return [$result, $msg, $data];
        
    }

    /**
     * 업데이트
     *
     * 가맹점 이상 가능
     *
     * @urlParam id integer required 유저 PK
     */
    public function update(OperatorReqeust $request, int $id)
    {
        if($request->user()->level > 35)
        {
            $data = $request->data();
            $data = $this->saveImages($request, $data, $this->imgs);
            $query = $this->operators->where('id', $id);
            $user = $query->first();

            if(Ablilty::isBrandCheck($request, $user->brand_id) === false)
                return $this->response(951);
            if(Ablilty::isEditAbleTime() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
    
            if((int)$data['level'] === 40)
            {   // 본사는 전화번호 변경 불가
                unset($data['phone_num']);
            }
            else if((int)$data['level'] === 35)
            {
                [$result, $msg, $datas] = $this->employeePhoneValidate($request);
                if($result !== AuthLoginCode::SUCCESS->value)
                    return $this->extendResponse($result, $msg, $datas);
            }

            if($user->user_name !== $request->user_name && $this->isExistUserName($request->user()->brand_id, $request->user_name))
                return $this->extendResponse(1001, __("validation.already_exsit", ['attribute'=>'아이디']));
            else
            {
                $res = $query->update($data);
                operLogging(HistoryType::UPDATE, $this->target, [], ['msg'=>'운영자는 보안상 이력이 남지 않습니다.'], $data['nick_name']);
                return $this->response($res ? 1 : 990);    
            }
        }
        else
            return $this->response(951);
    }

    /**
     * 단일삭제
     *
     * @urlParam id integer required 유저 PK
     */
    public function destroy(Request $request, int $id)
    {
        $user = $this->operators->where('id', $id)->first();
        if(Ablilty::isBrandCheck($request, $user->brand_id) === false)
            return $this->response(951);
        else
        {
            if(Ablilty::isEditAbleTime() === false)
                return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');

            $res = $this->delete($this->operators->where('id', $id));
            operLogging(HistoryType::DELETE, $this->target, [], ['msg'=>'운영자는 보안상 이력이 남지 않습니다.'], $user->nick_name);
            return $this->response($res ? 1 : 990, ['id'=>$id]);
        }
    }

    /**
     * 패스워드 변경
     */
    public function passwordChange(Request $request, int $id)
    {
        if(Ablilty::isEditAbleTime() === false)
            return $this->extendResponse(1500, '지금은 작업할 수 없습니다.');
        if(Ablilty::isOperator($request))
            return $this->_passwordChange($this->operators->where('id', $id), $request);
        else
            return $this->response(951);
    }

    /**
     * 계정잠금해제
     */
    public function unlockAccount(Request $request, int $id)
    {
        if(Ablilty::isOperator($request))
            return $this->_unlockAccount($this->operators->where('id', $id));
        else
            return $this->response(951);
    }

    public function create2FAQRLink(Request $request, int $id)
    {
        if(Ablilty::isMyOperator($request, $id))
            return $this->_create2FAQRLink($request, $id);
        else
            return $this->response(951);
    }

    public function vertify2FAQRLink(Request $request, int $id)
    {
        if(Ablilty::isMyOperator($request, $id))
             return $this->_vertify2FAQRLink($request, $this->operators->where('id', $id));   
        else
            return $this->response(951);
    }
}
