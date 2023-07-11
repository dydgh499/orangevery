<?php
namespace App\Http\Traits;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;

trait ManagerTrait
{
    function sql($query) {
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'".str_replace("'", "\'", $binding)."'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        echo $sql;
    }
    public function getIndexData($request, $query, $index_col='id', $cols=[], $date="created_at", $is_group=false)
    {
        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;

        if($request->has('s_dt'))
        {
            $s_dt = date($request->s_dt." 00:00:00");
            $query = $query->where($date, '>=', $s_dt);
        }
        if($request->has('e_dt'))
        {
            $e_dt = date($request->e_dt." 23:59:59");
            $query = $query->where($date, '<=', $e_dt);
        }

        $min    = $query->min($index_col);
        $res    = ['page'=>$page, 'page_size'=>$page_size];
        if($min != NULL)
        {
            $con_query = $query->where($index_col, '>=', $min);
            if($is_group)
                $res['total']   = $con_query->get([$index_col])->count();
            else
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

    public function imgMaxResize($src_img, $max_width)
    {
        $width = $src_img->width();
        $height= $src_img->height();

        if($max_width < $width)
        {
            $max_height = ($max_width * $height)/$width;
            $per_width  = (int)($width / ($width/$max_width));
            $per_height = (int)($height / ($height/$max_height));
            $src_img = $src_img->resize($per_width, $per_height);
        }
        return $src_img;
    }
    public function saveImage($img, $parent_folder, $img_folder, $max_width)
    {
        $name    = time().md5(pathinfo($img, PATHINFO_FILENAME)).".".$img->getClientOriginalExtension();
        $src_img = $img->store("images/$img_folder/$name", 'public');
        return env('APP_URL').'/storage/images/'.$img_folder.'/'.$name;
    }

    public function saveWebp($img, $parent_folder, $img_folder, $max_width)
    {
        $name    = time().md5(pathinfo($img, PATHINFO_FILENAME)).".webp";
        $src_img = Image::make($img);
        $src_img = $src_img->encode('webp', 90);
        $src_img = $this->imgMaxResize($src_img, $max_width);
        $src_img = $src_img->save("$parent_folder/$name");
        return env('APP_URL').'/storage/images/'.$img_folder.'/'.$name;
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
                $img        = $request->file($params[$i]);
                $ext        = $img->extension();
                //JPG, jpeg, PNG, GIF, BMP or WebP
                $is_webp    = in_array(strtoupper($ext), ['JPG', 'JPEG', 'PNG', 'BMP']);
                $folder     = public_path('storage/images/'.$folders[$i]);

                if(Storage::disk('public')->exists($img))
                    Storage::disk('public')->delete($img);

                if(!file_exists($folder))
                    mkdir($folder, '0755', true);

                $data[$cols[$i]] = $is_webp ? $this->saveWebp($img, $folder, $folders[$i], $sizes[$i]) : $this->saveImage($img, $folder, $folders[$i], $sizes[$i]);
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
            return $res ? 4 : 990;
        }
        else
            return 1000;
    }

    public function authCheck($session, $id, $req_level)
    {
        $auth = false;
        if($session->tokenCan($req_level))
            $auth = true;
        else if($session->id == $id)
            $auth = true;

        return $auth;
    }
}
