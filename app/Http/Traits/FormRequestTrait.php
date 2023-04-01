<?php

namespace App\Http\Traits;

trait FormRequestTrait
{
    public function getRules($keys, $sub)
    {
        $attributes = [];
        for($i=0; $i<count($keys); $i++)
        {
            $attributes[$keys[$i]] = '';
        }
        return array_merge($attributes, $sub);
    }
    public function getAttributes($keys)
    {
        $attributes = [];
        for($i=0; $i<count($keys); $i++)
        {
            $attributes[$keys[$i]] = __('validation.attributes.'.$keys[$i]);
        }
        return $attributes;
    }
    public function getDocsParameters($keys)
    {
        $attributes = [];
        for($i=0; $i<count($keys); $i++)
        {
            $attributes[$keys[$i]] = [
                'description'   => __('validation.attributes.'.$keys[$i]),
            ];
        }
        return $attributes;
    }
}
