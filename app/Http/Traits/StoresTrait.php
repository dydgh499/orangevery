<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

trait StoresTrait
{
    /*
        존재 여부 확인
     */
    public function existenceValidate($items, $jsons, $json_col, $item_col, $require=true)
    {
        $fails = [];
        for($i=0; $i<count($jsons); $i++)
        {
            if($require || $jsons[$i][$json_col] != "")
            {
                $idx = array_search($jsons[$i][$json_col], array_column($items, $item_col));
                if($idx === false)
                    array_push($fails, $i);
            }
        }
        return count($fails) ? [$json_col => $fails] : [];
    }

    /*
        미존재 여부 확인
     */
    public function existenceNotValidate($items, $jsons, $json_col, $item_col, $require=true)
    {
        $fails = [];
        for($i=0; $i<count($jsons); $i++)
        {
            if($require || $jsons[$i][$json_col] != "")
            {
                $idx = array_search($jsons[$i][$json_col], array_column($items, $item_col));
                if($idx !== false)
                    array_push($fails, $i);
            }
        }
        return count($fails) ? [$json_col => $fails] : [];
    }

    public function manyInsert($orm, $datas)
    {
        $pieces = [];
        $piece = [];
        for($i=0; $i<count($datas); $i++)
        {
            array_push($piece, $datas[$i]);
            if(count($piece)%900 == 0)
            {
                array_push($pieces, $piece);
                $piece = [];
            }
        }
        if(count($piece) > 0)
            array_push($pieces, $piece);

        return DB::transaction(function () use($orm, $pieces) {
            for($i=0; $i<count($pieces); $i++)
            {
                $res = $orm->insert($pieces[$i]);
                if(!$res)
                    return false;
            }
            return true;
        }, 3);
    }
}
