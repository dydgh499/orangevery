<?php
namespace App\Http\Traits;
use Illuminate\Support\Facades\DB;

trait StoresTrait
{
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
