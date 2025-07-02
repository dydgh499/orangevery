<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;
use App\Models\Options\OvOptions;
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
        'business_num',
        'note',
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
    public $image_keys = [
        'logo_img',         
        'favicon_img',
        'passbook_img',
        'contract_img',
        'bsin_lic_img',
        'id_img',
        'og_img',
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
            'logo_file'    => 'file|mimes:svg,jpg,png,jpeg,webp',
            'favicon_file' => 'file|mimes:ico,svg,jpg,png,jpeg,webp',
            'passbook_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'contract_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'bsin_lic_file'  => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'id_file'        => 'file|mimes:jpg,bmp,png,jpeg,webp,pdf',
            'og_file'        => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'login_file'     => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'ov_options'    => 'required',
            'ceo_name'        => 'string|required',
            'company_name'    => 'string|required',
            'phone_num'     => 'string|required',
            'addr'          => 'string|required',
            'business_num'  => 'string|required',
        ];
        return $this->getRules($this->keys, $sub);
    }

    public function attributes()
    {
        return $this->getAttributes($this->keys);
    }

    protected function prepareForValidation()
    {
        $this->merge(['ov_options' => $this->convertToBoolean($this->input('ov_options'))]);
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
        $data = array_merge($this->getParmasBaseKey(), $this->getParamsBaseFile($this->image_keys));
        $data['ov_options'] = json_encode($this->ov_options); 
        $data['theme_css']  = json_encode($this->theme_css);
        if($this->has('login_img'))
            $data['login_img'] = $this->login_img;

        return $data;
    }
}
