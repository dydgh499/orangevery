<?php

namespace App\Http\Traits;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

trait AuthTrait
{
    public function loginInfo($level)
    {   // login expire 1 hour
        $auths = $this->getAuthority($level);
        $this->level = $level;
        $token = $this->createToken($this->user_name, $auths)->plainTextToken;
        return ['access_token' => $token, 'user' => $this];
    }

    public function loginAPIResponse($result, $level)
    {
        $data = $result['user']->loginAPI($level);
        $data['user'] = [
            'id' => $data['user']->id,
            'user_name' => $data['user']->user_name,
            'level' => $level,
        ];
        return $data;
    }

    public function loginAPI($level)
    {   //login expire 30 hours
        $auths = $this->getAuthority($level);
        $this->level = $level;
        $token_object = $this->createToken($this->user_name, $auths);

        $bearer_token = $token_object->plainTextToken;
        $access_token = PersonalAccessToken::findToken($bearer_token);
        if($access_token)
            PersonalAccessToken::where('id', $access_token->id)->update(['created_at' => Carbon::now()->addHours(30)->format('Y-m-d H:i:s')]);

        return ['access_token'=>$bearer_token, 'user'=>$this];
    }

    public function getAuthority($level)
    {
        $authoritys = [];
        if($level >= 10)
            array_push($authoritys, 10);    // merchandise
        if($level >= 11)
            array_push($authoritys, 11);    // GMID        
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
    
    public function getResidentNumFrontAttribute()
    {
        $resident_num_front = '';
        if ($this->resident_num) {
            $resident_num = str_replace('-', '', $this->resident_num);            
            if (strlen($resident_num) >= 6)
                $resident_num_front = substr($resident_num, 0, 6);
            else
                $resident_num_front = $resident_num;
        }
        return $resident_num_front;
    }

    public function getResidentNumBackAttribute()
    {
        $resident_num_back = '';
        if ($this->resident_num) {
            $resident_num = str_replace('-', '', $this->resident_num);
            if (strlen($resident_num) > 6)
                $resident_num_back = substr($resident_num, 6);
        }
        return $resident_num_back;
    }
}
