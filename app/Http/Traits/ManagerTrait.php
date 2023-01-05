<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait ManagerTrait 
{
    public function getIndexData($request, $query, $index_col='id', $cols=[])
    {
        $page      = $request->input('page');
        $page_size = $request->input('page_size');
        $sp = ($page - 1) * $page_size;
        $ep = $page * $page_size;
        $min = $query->min($index_col);
        $res = ['page'=>$page, 'page_size'=>$page_size];
        if($min != NULL)
        {
            $con_query = $query->where($index_col, '>=', $min)->orderBy($index_col, 'desc')->offset($sp)->limit($ep);
            $res['total']   = $query->count();
            $res['content'] = count($cols) ? $con_query->get($cols) : $con_query->get();
        }
        else
        {
            $res['total'] = 0;
            $res['content'] = [];
        }
        return $res;
    }
    public function saveImages($request, $data, $images, $folders, $max_width=[])
    {
        for($i=0; $i<count($images); $i++)
        {
            if($request->hasFile($images[$i]))
            {
                $img    = $request->file($images[$i]);
                $folder = public_path('images/'.$folders[$i]);
                $name   = time().md5(pathinfo($img, PATHINFO_FILENAME)).".webp";
                //JPG, jpeg, PNG, GIF, BMP or WebP
                if(Storage::disk('public')->exists($img))
                    Storage::disk('public')->delete($img);
                                
                if(!file_exists($folder))
                    mkdir($folder, 755, true);

                $image = Image::make($img)->encode('webp', 90)
                    ->resize(200, 250)
                    ->save("$folder/$name");
                $data[$images[$i]] = env('APP_URL').'/images/'.$folders[$i].'/'.$name;
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
                if(Storage::disk('public')->exists($data[$imgs[$i]]))
                    Storage::disk('public')->delete($data[$imgs[$i]]);
            }
            $res = $query->delete();
            return $res ? 4 : 990;
        }
        else
            return 1000;
    }
}
