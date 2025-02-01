<?php

namespace App\Http\Controllers\Ablilty;

use App\Http\Controllers\Manager\CodeGenerator\GeneratorInterface;
use App\Models\Merchandise\ShoppingMall\ShoppingMall;
use App\Models\Merchandise\ShoppingMall\Category;
use App\Models\Merchandise\ShoppingMall\Product;
use App\Models\Merchandise;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ShoppingMallWindowInterface implements GeneratorInterface
{
    CONST REDIS_KEEP_SEC = 60;
    static public function publishCode($window_code, $length)
    {
        return $window_code.strtoupper(Str::random($length - strlen($window_code)));
    }

    static public function create($generate_code, $length=10)
    {
        do {
            $window_code = self::publishCode($generate_code, $length);
        }
        while(ShoppingMall::where('window_code', $window_code)->exists());
        return $window_code;
    }

    static public function bulkCreate($generate_code, $create_count)
    {
        // 사용안함
    }

    static public function renew($mcht_id)
    {
        $data = [
            'mcht_id' => $mcht_id,
            'window_code' => self::create(''),
        ];
        ShoppingMall::create($data);
        return $data;
    }

    static public function getShopInfo($window_code)
    {
        $key_name = "shop-info:".$window_code;
        $data = Redis::get($key_name);
        if($data !== null)
            return json_decode($data, true);
        else
        {
            $mcht = Merchandise::join('shopping_malls', 'merchandises.id', '=', 'shopping_malls.mcht_id')
                ->where('shopping_malls.window_code', $window_code)
                ->first([
                    'merchandises.profile_img',
                    'merchandises.mcht_name',
                    'merchandises.contact_num',
                    'merchandises.business_num',
                    'merchandises.nick_name',
                    'merchandises.addr',
                ]);
            if($mcht)
            {
                $categories = Category::join('shopping_malls', 'categories.mcht_id', '=', 'shopping_malls.mcht_id')
                    ->where('shopping_malls.window_code', $window_code)
                    ->with(['products'])
                    ->get(['categories.*']);
                $data = [
                    'merchandise' => [
                        'profile_img' => $mcht->profile_img,
                        'mcht_name' => $mcht->mcht_name,
                        'contact_num' => $mcht->contact_num,
                        'business_num' => $mcht->business_num,
                        'nick_name' => $mcht->nick_name,
                        'addr' => $mcht->addr,
                    ],
                    'categories' => $categories,
                ];
                Redis::set($key_name, json_encode($data), 'EX', self::REDIS_KEEP_SEC);
                return $data;
            }
            else
                return [];
        }
    }

    static public function initShopInfo($window_code)
    {
        $key_name = "shop-info:".$window_code;
        Redis::set($key_name, null, 'EX', 1);
    }

    static public function getProductInfo($window_code, $product_id)
    {
        $key_name = "product-info:".$product_id;
        $data = Redis::get($key_name);
        if($data !== null)
            return json_decode($data, true);
        else
        {
            $data = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->join('shopping_malls', 'categories.mcht_id', '=', 'shopping_malls.mcht_id')
                ->join('payment_windows', 'products.pmod_id', '=', 'payment_windows.pmod_id')
                ->where('shopping_malls.window_code', $window_code)
                ->where('products.id', $product_id)
                ->with(['productOptionGroups.productOptions'])
                ->first([
                    'products.*', 
                    'payment_windows.window_code',
                ]);
            Redis::set($key_name, json_encode($data), 'EX', self::REDIS_KEEP_SEC);
            return $data;
        }
    }

    static public function initProductInfo($product_id)
    {
        $key_name = "product-info:".$product_id;
        Redis::set($key_name, null, 'EX', 1);
    }

    static public function getPayWindowProductInfo($shop_window, $product_id)
    {
        $product = self::getProductInfo($shop_window, $product_id);
        if($product)
        {
            if(isset($product['productOptionGroups']))
                $product_option_groups = $product['productOptionGroups'];
            else if(isset($product['product_option_groups']))
                $product_option_groups = $product['product_option_groups'];
            else
                $product_option_groups = null;

            return [
                'amount'    => $product['product_amount'],
                'item_img'  => $product['product_img'],
                'item_name' => $product['product_name'],
                'buyer_name' => '',
                'buyer_phone' => '',
                'product_option_groups' => $product_option_groups,
            ];
        }
        else
            return null;
    }
}
