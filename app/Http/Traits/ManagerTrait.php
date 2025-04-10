<?php
namespace App\Http\Traits;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\AuthGoogleOTP;
use App\Http\Controllers\Auth\AuthAccountLock;
use App\Http\Controllers\Auth\AuthPasswordChange;
use App\Http\Controllers\Ablilty\Ablilty;
use App\Http\Controllers\Ablilty\EditAbleWorkTime;


trait ManagerTrait
{
    public function getIndexData($request, $query, $index_col='id', $cols=[], $date="created_at", $is_date_filter=true)
    {
        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;

        if($is_date_filter)
        {
            if($request->has('s_dt'))
            {
                $s_dt = strlen($request->s_dt) === 10 ? date($request->s_dt." 00:00:00") : $request->s_dt;
                $query = $query->where($date, '>=', $s_dt);
            }
            if($request->has('e_dt'))
            {
                $e_dt = strlen($request->e_dt) === 10 ? date($request->e_dt." 23:59:59") : $request->e_dt;
                $query = $query->where($date, '<=', $e_dt);
            }
        }

        $min    = $query->min($index_col);
        $res    = ['page'=>$page, 'page_size'=>$page_size];
        if($min != NULL)
        {
            $con_query = $query->where($index_col, '>=', $min);
            $res['total']   = $query->count();

            $con_query = $con_query->orderBy($date, 'desc')->offset($sp)->limit($page_size);
            $res['content'] = count($cols) ? $con_query->get($cols) : $con_query->get();
        }
        else
        {
            $res['total'] = 0;
            $res['content'] = [];
        }
        return $res;
    }

    // S3
    public function ToS3($folder, $img, $name)
    {
        $public_folders = ['posts', 'logos', 'favicons', 'logins', 'ogs', 'profiles'];
        $disk = in_array($folder, $public_folders) ? 's3-public' : 's3';
        $path = Storage::disk($disk)->putFileAs($folder, $img, $name);
        $host = $disk === 's3-public' ? env('AWS_PUBLIC_BUCKET_HOST') : env('AWS_PRIVATE_BUCKET_HOST');
        return "{$host}/{$path}";
    }

    // NCloud
    public function ToNCloud($folder, $img, $name)
    {
        $public_folders = ['posts', 'logos', 'favicons', 'logins', 'ogs', 'profiles'];
        $disk = in_array($folder, $public_folders) ? 'n-cloud-public' : 'n-cloud';
        $path = Storage::disk($disk)->putFileAs($folder, $img, $name);
        $host = $disk === 'n-cloud-public' ? env('NCLOUD_PUBLIC_BUCKET') : env('NCLOUD_PRIVATE_BUCKET');
        return "{$host}/{$path}";
    }

    // Cloudinary
    public function ToCloudinary($folder, $img, $name)
    {
        $result = $img->storeOnCloudinaryAs($folder, $name);
        return $result->getSecurePath();
    }

    // local
    public function ToLocal($folder, $img, $name)
    {
        $parent_folder = public_path('storage/images/'.$folder);
        if(Storage::disk('public')->exists($img))
            Storage::disk('public')->delete($img);

        if(!file_exists($parent_folder))
            mkdir($parent_folder, '0755', true);

        $name = $img->store($folder, 'main');
        return env('APP_URL')."/storage/images/$name";
    }

    public function saveImages($request, $data, $imgs)
    {
        $cols    = $imgs['cols'];
        $params  = $imgs['params'];
        $folders = $imgs['folders'];
        $sizes   = $imgs['sizes'];
        for($i=0; $i<count($params); $i++)
        {
            if($request->hasFile($params[$i]))
            {
                $img    = $request->file($params[$i]);
                $ext    = $img->extension();                
		        $name = time().md5(pathinfo($img, PATHINFO_FILENAME)).".$ext";

                if(env('FILESYSTEM_DISK') === 's3')
                    $data[$cols[$i]] = $this->ToS3($folders[$i], $img, $name);
                else if(env('FILESYSTEM_DISK') === 'n-cloud')
                    $data[$cols[$i]] = $this->ToNCloud($folders[$i], $img, $name);
                else if(env('FILESYSTEM_DISK') === 'cloudinary')
                    $data[$cols[$i]] = $this->ToCloudinary($folders[$i], $img, $name);
                else
                    $data[$cols[$i]] = $this->ToLocal($folders[$i], $img, $name);
            }
        }
        return $data;
    }
    public function delete($query, $imgs=[])
    {
        if(env('FILESYSTEM_DISK') === 'local')
        {
            $datas = $query->get($imgs);
            if(count($datas))
            {
                $datas = $datas->toArray();
                foreach($datas as $data)
                {
                    if(Str::contains($data[$imgs[$i]], env('APP_URL')))
                    {
                        $path = str_replace(env('APP_URL').'/storage/', '', $data[$imgs[$i]]);
                        if(Storage::disk('public')->exists($path))
                            Storage::disk('public')->delete($path);
                    }
                }
            }    
        }
        return $query->update(['is_delete' => true]);
    }

    public function authCheck($session, $id, $req_level)
    {
        $auth = false;
        if($session->tokenCan($req_level))
            $auth = true;
        else if($session->id === $id)
            $auth = true;

        return $auth;
    }

    public function _passwordChange($query, $request, $is_me)
    {
        if($is_me)
            $validated = $request->validate(['user_pw'=>'required', 'current_pw'=>'required']);
        else
            $validated = $request->validate(['user_pw'=>'required']);

        $user = $query->first();
        if(Ablilty::isBrandCheck($request, $user->brand_id) === false)
            return $this->response(951);
        else
        {
            if($is_me)
            {
                if(AuthPasswordChange::HashCheck($user, $request->current_pw) === false)
                    return $this->extendResponse(954, '현재 패스워드가 정확하지 않습니다.', []);
            }

            [$result, $msg] = AuthPasswordChange::passwordValidate($user->user_name, $request->user_pw);
            if($result === false)
                return $this->extendResponse(954, $msg, []);
            if(AuthPasswordChange::HashCheck($user, $request->user_pw))
                return $this->extendResponse(954, '기존 패스워드와 달라야합니다.', []);
            else
            {
                $user->user_pw = Hash::make($request->user_pw.$user->created_at);
                $user->password_change_at = date('Y-m-d H:i:s');
                $user->save();
                AuthAccountLock::initPasswordWrongCounter($user);
                return $this->response(1);
            }
        }
    }

    public function _unlockAccount($query)
    {
        AuthAccountLock::setUserUnlock($query->first());
        return $this->response(1);    
    }

    public function _create2FAQRLink($request, int $id)
    {
        $qrcode_url = AuthGoogleOTP::getQrcodeUrl($request);
        return $this->response(1, ['qrcode_url' => $qrcode_url]);
    }

    public function _vertify2FAQRLink($request, $orm)
    {
        $cond_1 = AuthGoogleOTP::createVerify($request, $request->verify_code);
        $cond_2 = AuthPasswordChange::HashCheck($request->user(), $request->user_pw);
        if($cond_1 && $cond_2)
        {
            $data = $this->setEncryptPersonalInfo(['google_2fa_secret_key' => AuthGoogleOTP::getTempSecretKey($request->user())]);
            $orm->update($data);
            return $this->response(1);
        }
        else
            return $this->extendResponse(952, '핀번호 또는 패스워드가 정확하지 않습니다.');
    }

    public function _init2FASecretKey($request, $orm)
    {
        $user = (clone $orm)->first();
        if(Ablilty::isBrandCheck($request, $user->brand_id) === false)
            return $this->response(951);
        else
        {
            if($user->level <= 35)
            {
                AuthGoogleOTP::initSecretKey($user);
                return $this->response(1);        
            }
            else
                return $this->response(951);
        }
    }
}
