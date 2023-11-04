<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Models\Options\PvOptions;
use App\Models\Options\ThemeCSS;


class BrandRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'name',
        'dns',
        'og_description',
        'ceo_name',
        'addr',
        'phone_num',
        'fax_num',
        'company_name',
        'pvcy_rep_name',
        'business_num',
        'deposit_day',
        'deposit_amount',
        'extra_deposit_amount',
        'dev_fee',
        'dev_settle_type',
        'fax_num',
        'use_different_settlement',
        'rep_mcht_id',
        'above_pg_type',
    ];
    public $file_keys = [   
        'logo_file',         
        'favicon_file',
        'passbook_file',
        'contract_file',
        'bsin_lic_file',
        'id_file',
        'og_file',
    ];

    public function authorize()
    {
        return $this->user()->tokenCan(40) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $sub = [
            'name'  => 'string|required',
            'dns'   => 'string|required',
            'logo_file'    => 'file|mimes:svg',
            'favicon_file' => 'file|mimes:ico,svg,jpg,bmp,png,jpeg,webp',
            'passbook_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'contract_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'bsin_lic_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'id_file'        => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'og_file'        => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'login_file'     => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'pv_options'    => 'required',
            'ceo_name'        => 'string|required',
            'company_name'    => 'string|required',
            'phone_num'     => 'string|required',
            'addr'          => 'string|required',
            'business_num'  => 'string|required',
            'deposit_day'   => 'required',
            'deposit_amount' => 'required',
            'dev_fee' => 'required|numeric',
            'extra_deposit_amount'=> 'required|numeric',
            'dev_settle_type'=> 'required|numeric',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    protected function prepareForValidation()
    {
        if ($this->has('pv_options')) 
        {
            $pvOptions = $this->input('pv_options');    
            $pvOptions = $this->convertToBoolean($pvOptions);
            $this->merge(['pv_options' => $pvOptions]);
            $this->merge(['use_different_settlement' => $this->convertToBoolean($this->input('use_different_settlement'), false)]);
        }
    }

    public function bodyParameters()
    {
        $params = array_merge($this->getDocsParameters($this->keys), $this->getDocsParameters($this->file_keys));
        $params['logo_file']['description']      .= '(max-width:256px 이상은 리사이징)';
        $params['favicon_file']['description']   .= '(max-width:32px 이상은 리사이징)';
        $params['passbook_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['contract_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['bsin_lic_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['id_file']['description']    .= '(max-width:500px 이상은 리사이징)';
        $params['og_file']['description']    .= '(max-width:1200px 이상은 리사이징)';
        $params['name']['description']      .= '(브랜드명)';
        return $params;
    }

    public function data()
    {
        $data = $this->getParmasBaseKey();
        $data['pv_options'] = json_encode($this->pv_options); 
        $data['theme_css']  = json_encode($this->theme_css);
        $data['dev_fee']    = $this->dev_fee/100;
        $data['above_pg_type'] = $data['above_pg_type'] === '' ? 0 : $data['above_pg_type'];
        if($this->has('login_img'))
            $data['login_img'] = $this->login_img;

        return $data;
    }
}
