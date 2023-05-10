<?php

namespace App\Http\Traits;

trait AuthTrait
{
    public function loginInfo($level, $brand)
    {
        $auths = $this->getAuthority($level, $brand);
        $this->level = $level;
        $token = $this->createToken($this->user_name, $auths)->plainTextToken;
        return ['access_token'=>$token, 'user'=>$this];
    }

    public function getAuthority($level, $brand)
    {
        $authoritys = [];
        $mbr_type   = $brand['mbr_type'] == 0 ? "mbr_only_mcht" : "mbr_all";
        $guide_type = $brand['guide_type'] == 0 ? "guide_friendly" : "guide_normal";

        array_push($authoritys, $mbr_type);
        array_push($authoritys, $guide_type);
        if($level >= 0)
            array_push($authoritys, 0);     // normal
        if($level >= 10)
            array_push($authoritys, 10);    // merchandise
        if($level >= 15)
            array_push($authoritys, 15);    // agency
        if($level >= 20)
            array_push($authoritys, 20);    // dist
        if($level >= 30)
            array_push($authoritys, 30);    // branch
        if($level >= 35)
            array_push($authoritys, 35);    // franchise staff
        if($level >= 40)
            array_push($authoritys, 40);    // franchise
        if($level >= 35)
            array_push($authoritys, 45);    // partner
        if($level >= 50)
            array_push($authoritys, 50);    // developer
        return $authoritys;
    }
}
