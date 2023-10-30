<?php

namespace App\Http\Traits\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use DateTimeInterface;
use Carbon\Carbon;

trait AttributeTrait
{
    protected function dateAttribute()
    {
        return Attribute::make(
            get: fn ($value) => $value != null ? Carbon::parse($value)->format('Y-m-d') : $value,
        );
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }
    protected function getAes256Key()
    {
        $aes_key    = env('AES_256_KEY', 'pu3pleve3yf2gh2ngUnt1l10B1ll1on');
        $iv         = env('AES_256_IV', '1234567890123456');
        return [$aes_key, $iv];
    }

    protected function aes256_encode($value)
    {
        [$aes_key, $iv] = $this->getAes256Key();
        return base64_encode(openssl_encrypt($value, 'AES-256-CBC', $aes_key, true, $iv));
    }

    protected function aes256_decode($value)
    {
        [$aes_key, $iv] = $this->getAes256Key();
        return openssl_decrypt(base64_decode($value), 'AES-256-CBC', $aes_key, true, $iv);
    }
}
