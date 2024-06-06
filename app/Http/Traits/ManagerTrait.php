<?php
namespace App\Http\Traits;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;


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
        $path = $img->storePubliclyAs($folder, $name, 's3'); //storeAs -> private
        return env('AWS_URL')."/".$path;
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
        
                if(env('DISK_CONNECTION') == 's3')
                    $data[$cols[$i]] = $this->ToS3($folders[$i], $img, $name);
                else if(env('DISK_CONNECTION') == 'cloudinary')
                    $data[$cols[$i]] = $this->ToCloudinary($folders[$i], $img, $name);
                else
                    $data[$cols[$i]] = $this->ToLocal($folders[$i], $img, $name);
            }
        }
        return $data;
    }
    public function delete($query, $imgs=[])
    {
        $data = $query->first();
        if($data)
        {
            $data = $data->toArray();
            for($i=0; $i<count($imgs); $i++)
            {
                if(Str::contains($data[$imgs[$i]], env('APP_URL')))
                {
                    $path = str_replace(env('APP_URL').'/storage/', '', $data[$imgs[$i]]);
                    if(Storage::disk('public')->exists($path))
                        Storage::disk('public')->delete($path);
                }
            }
            $res = $query->update(['is_delete' => true]);
            return $res ? 1 : 990;
        }
        else
            return 1000;
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

    public function _passwordChange($query, $request)
    {
        $validated = $request->validate(['user_pw'=>'required', 'id' => 'required|numeric']);
        if($this->authCheck($request->user(), $request->id, 35))
        {
            $res = $query->update([
                'user_pw' => Hash::make($request->user_pw),
                'password_change_at' => date('Y-m-d H:i:s'),
            ]);
            return $this->response($res ? 1 : 990);    
        }
        else
            return $this->response(951);
    }

    public function _unlockAccount($query, $request)
    {
        $validated = $request->validate(['id' => 'required|numeric']);
        if($this->authCheck($request->user(), $request->id, 35))
        {
            $res = $query->update(['is_lock' => 0]);
            return $this->response($res ? 1 : 990);    
        }
        else
            return $this->response(951);
    }
}
