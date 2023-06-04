<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;


class BrandRequest extends FormRequest
{
    use FormRequestTrait;
    public function __construct()
    {
        $this->keys = [
            'name',
            'dns',
            'logo_file',
            'favicon_file',
            'passbook_file',
            'contract_file',
            'bsin_lic_file',
            'id_file',
            'og_file',
            'og_description',
            'ceo_nm',
            'addr',
            'phone_num',
            'fax_num',
            'theme_css',
            'company_nm',
            'pvcy_rep_nm',
            'business_num',
            'deposit_day',
            'deposit_amount',
            'fax_num',
            'pv_options',
        ];
    }

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
            'pv_options'    => 'required',
            'ceo_nm'        => 'string|required',
            'company_nm'    => 'string|required',
            'phone_num'     => 'string|required',
            'addr'          => 'string|required',
            'business_num'  => 'string|required',
            'deposit_day'   => 'required',
            'deposit_amount' => 'required',

        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    protected function prepareForValidation()
    {
        if ($this->has('is_deposit')) 
        {
            $pvOptions = $this->input('pv_options');    
            $pvOptions = $this->convertToBoolean($pvOptions);
            $this->merge(['pv_options' => $pvOptions]);
        }
    }

    public function bodyParameters()
    {
        $params = $this->getDocsParameters($this->keys);
        $params['logo_file']['description']      .= '(max-width:256px 이상은 리사이징)';
        $params['favicon_file']['description']   .= '(max-width:32px 이상은 리사이징)';
        $params['passbook_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['contract_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['bsin_lic_file']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['id_file']['description']    .= '(max-width:500px 이상은 리사이징)';
        $params['og_file']['description']    .= '(max-width:1200px 이상은 리사이징)';
        $params['theme_css']['description'] .= '(테마 CSS 내용 작성)';
        $params['name']['description']      .= '(브랜드명)';
        return $params;
    }

    public function data()
    {
        $data = [
            'dns' => $this->dns,
            'name' => $this->name,
            'ceo_nm' => $this->ceo_nm,
            'company_nm'    => $this->company_nm,
            'phone_num'     => $this->phone_num,
            'addr'          => $this->addr,
            'business_num'  => $this->business_num,
            'og_description' => $this->input('og_description', ''),        
            'deposit_day'   => $this->deposit_day,
            'deposit_amount'   => $this->deposit_amount,
            'note'  => $this->input('note', ''),
        ];
        $data['pv_options'] = json_encode($this->pv_options, true);
        $data['theme_css']  = json_encode($this->theme_css, true);
        return $data;
    }
}
