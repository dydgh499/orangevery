<?php
namespace App\Http\Traits;
use App\Models\Brand;

trait DNSTrait 
{
    static public function getDNSInformation()
    {
        $brand = Brand::where('dns', request()->getHost())->first();
        if($brand == NULL)
            abort('404');
        else
            return $brand;
    }
    /**
     * DNS 검증
     *
     * @unauthenticated
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function DNSValidate()
    {
        $brand = Brand::where('dns', request()->getHost())->first();
        return $this->response($brand ? 0 : 1000, $brand);
    }
}
