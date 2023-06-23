<?php

namespace App\Http\Traits;

trait AuthTrait
{
    public function loginInfo($level)
    {
        $auths = $this->getAuthority($level);
        $this->level = $level;
        $token = $this->createToken($this->user_name, $auths)->plainTextToken;
        return ['access_token'=>$token, 'user'=>$this];
    }

    public function getAuthority($level)
    {
        $authoritys = [];
        if($level >= 10)
            array_push($authoritys, 10);    // merchandise
        if($level >= 13)
            array_push($authoritys, 13);    // under agency
        if($level >= 15)
            array_push($authoritys, 15);    // agency
        if($level >= 17)
            array_push($authoritys, 17);    // under dist
        if($level >= 20)
            array_push($authoritys, 20);    // dist
        if($level >= 25)
            array_push($authoritys, 25);    // under branch
        if($level >= 30)
            array_push($authoritys, 30);    // branch
        if($level >= 35)
            array_push($authoritys, 35);    // franchise staff
        if($level >= 40)
            array_push($authoritys, 40);    // franchise
        if($level >= 45)
            array_push($authoritys, 45);    // partner
        if($level >= 50)
            array_push($authoritys, 50);    // developer
        return $authoritys;
    }
}
