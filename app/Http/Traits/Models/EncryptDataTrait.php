<?php

namespace App\Http\Traits\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait EncryptDataTrait
{
    protected function get_aes256_key()
    {
        $aes_key    = env('AES_256_KEY', '20f15aa3337ca06f2ff29b2afade36fc');
        $iv         = env('AES_256_IV', '6a62f6085a2361c1');
        return [$aes_key, $iv];
    }

    protected function aes256_encode($value)
    {
        [$aes_key, $iv] = $this->get_aes256_key();
        return base64_encode(openssl_encrypt($value, 'AES-256-CBC', $aes_key, true, $iv));
    }

    protected function aes256_decode($value)
    {
        [$aes_key, $iv] = $this->get_aes256_key();
        return openssl_decrypt(base64_decode($value), 'AES-256-CBC', $aes_key, true, $iv);
    }
}
