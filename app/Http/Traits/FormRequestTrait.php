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

    protected function convertToBoolean($data)
    {
        if (is_array($data)) 
        {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertToBoolean($value);
            }
        } 
        else 
        {
            if ($data === 'true')
                $data = true;
            else if ($data === 'false')
                $data = false;
        }
        return $data;
    }
    
    protected function getParmasBaseKey($base='')
    {
        $data = [];
        for ($i=0; $i < count($this->keys) ; $i++)
        {
            $key = $this->keys[$i];
            $data[$key] = $this->input($key, $base);
        }
        return $data;
    }

    protected function getParmasBaseKeyV2($keys, $base='')
    {
        $data = [];
        for ($i=0; $i < count($keys) ; $i++)
        {
            $key = $keys[$i];
            $data[$key] = $this[$key] ? $this[$key] : $base;
        }
        return $data;
    }

    protected function getParmasBaseKeyV3($parent, $keys, $base='')
    {
        $data = [];
        for ($i=0; $i < count($keys) ; $i++)
        {
            $key = $keys[$i];
            if(isset($parent[$key]))
                $data[$key] = $parent[$key] ? $parent[$key] : $base;
            else
                $data[$key] = $base;
        }
        return $data;
    }

    protected function getParamsBaseFile($keys)
    {
        $data = [];
        if(env('FILESYSTEM_DISK') === 's3')
        {
            foreach($keys as $key)
            {
                if(isset($this[$key]))
                {
                    if(env('FILESYSTEM_DISK') === 's3' && strpos($this[$key], '?X-Amz-Content-Sha256') !== false)
                    {
                        $idx = strpos($this[$key], '?X-Amz-Content-Sha256');
                        $data[$key] = substr($this[$key], 0, $idx);    
                    }
                    else
                        $data[$key] = $this[$key];
                }
                else
                    $data[$key] = null;
            }    
        }
        return $data;
    }
}
