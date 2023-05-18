<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class MerchandiseRequest extends FormRequest
{
    use FormRequestTrait;

    public function __construct()
    {
        $this->keys = [
            'user_name','nick_name','birth_date',
            'group_id','mcht_name','addr',
            'stamp_flag','point_flag','point_rate',
            'stamp_save_count','profile_img',
            'phone_num',
        ];
    }

    public function authorize()
    {
        return $this->user()->tokenCan(10) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'user_name' => 'required',
            'birth_date'=> 'required|date_format:Y-m-d',
            'group_id'  => 'numeric',
            'mcht_name' => 'required',
            'addr'      => 'required',
            'stamp_flag'=> 'boolean|required',
            'point_flag'=> 'boolean|required',
            'point_rate'=> 'numeric|required|max:100',
            'stamp_save_count' => 'required|numeric',
            'profile_img' => 'mimes:jpg,bmp,png,jpeg,webp',
        ];
        return $this->getRules($this->keys, $sub);
    }
    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    public function bodyParameters()
    {
        $params = $this->getDocsParameters($this->keys);
        $params['point_flag']['description']    .= '(1=사용, 0=미사용)';
        $params['stamp_flag']['description']    .= '(1=사용, 0=미사용)';
        $params['profile_img']['description']   .= '(max-width: 120px, 이상은 리사이징)';
        return $params;
    }
    public function data()
    {
        return [
            'brand_id'  => $this->user()->brand_id,
            'user_name' => $this->input('user_name'),
            'nick_name' => $this->input('nick_name', ''),
            'birth_date'=> $this->input('birth_date', ''),
            'group_id'  => $this->input('group_id', 0),
            'mcht_name' => $this->input('mcht_name', ''),
            'addr'      => $this->input('addr', ''),
            'stamp_flag'=> $this->input('stamp_flag', 0),
            'point_flag'=> $this->input('point_flag', 0),
            'point_rate'=> $this->input('point_rate', 0),
            'stamp_save_count'=> $this->input('stamp_save_count', 0),
            'phone_num' => $this->input('phone_num', null),
        ];
    }
}
