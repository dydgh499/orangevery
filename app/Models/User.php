<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function getAbilities($level)
    {
        
        $authoritys = [];
        if($level == 0) // 일반 유저
            array_push($authoritys, ['action'=> 'manage', 'subject'=> 'Auth']);
        else if($level == 10)   // 가맹점
            array_push($authoritys, ['action'=> 'manage', 'subject'=> 'Auth']);
        else if($level == 15)   // 대리점
            array_push($authoritys, ['action'=> 'manage', 'subject'=> 'all']);
        else if($level == 20)   // 총판
            array_push($authoritys, ['action'=> 'manage', 'subject'=> 'all']);
        else if($level == 30)   // 지사
            array_push($authoritys, ['action'=> 'manage', 'subject'=> 'all']);
        else if($level == 35)   // 협력사
            array_push($authoritys, ['action'=> 'read', 'subject'=> 'all']);
        else if($level == 40)   // 본사
            array_push($authoritys, ['action'=> 'manage', 'subject'=> 'all']);
        else if($level == 50)   // 개발사
            array_push($authoritys, ['action'=> 'manage', 'subject'=> 'all']);
        return $authoritys;
    }

    private function getRole()
    {
        if($this->level == 0)
            return __('normal');
        else if($this->level == 10)
            return __('merchandise');
        else if($this->level == 15)
            return __('agency');
        else if($this->level == 20)
            return __('distributor');
        else if($this->level == 30)
            return __('branch');            
        else if($this->level == 35)
            return __('partner');            
        else if($this->level == 40)
            return __('headquarters');
        else if($this->level == 50)
            return __('developer');
        else
            return __('unknown');
    }

    public function getAuthData()
    {
        return [
            'id'=> $this->id,
            'avatar'    => $this->avatar,
            'email'     => $this->email,
            'fullName'  => $this->business_nm,
            'role'      => $this->getRole(),
            'username'  =>  $this->business_nm,
        ];
    }
}
