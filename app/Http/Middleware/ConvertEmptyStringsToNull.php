<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class ConvertEmptyStringsToNull extends TransformsRequest
{
    /**
     * The attributes that should not be converted.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        // 변경한 부분
        return is_string($value) && $value === '' ? $value : $value;
    }
}
