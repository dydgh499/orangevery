<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;
use App\Http\Traits\Models\EncryptDataTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;

class IdentityAuthInfo extends Model
{
    use HasFactory, AttributeTrait, EncryptDataTrait;
    protected   $primaryKey = 'id';
    protected   $guarded    = [];
    protected   $appends    = [];
    protected   $hidden     = [];

    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }

    protected function subKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }

    protected function encKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->aes256_decode($value),
            set: fn ($value) => $this->aes256_encode($value),
        );
    }

    public function encrypt($data)
    {
        $enc_keys = ['api_key', 'sub_key', 'enc_key'];
        foreach($enc_keys as $enc_key)
        {
            $data[$enc_key] = $this->aes256_encode($data[$enc_key]);
        }
        return $data;
    }
}
