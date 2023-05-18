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
            'logo_img',
            'favicon_img',
            'passbook_img',
            'contract_img',
            'id_img',
            'og_img',
            'og_description',
            'map_marker_img',
            'ceo_nm',
            'addr',
            'phone_num',
            'fax_num',
            'theme_css',
            'company_nm',
            'pvcy_rep_nm',
            'business_num',
            'fax_num',
            'stamp_flag',
            'point_flag',
            'stamp_max_size',
            'stamp_save_count',
            'coupon_model_id',
            'point_rate',
            'point_min_amount',
            'mbr_type',
            'guide_type',
            'options',
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
            'logo_img'    => 'file|mimes:jpg,bmp,png,jpeg,webp,svg',
            'favicon_img' => 'file|mimes:jpg,bmp,png,jpeg,webp,ico,svg',
            'passbook_img'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'contract_img'  => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'id_img'        => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'og_img'        => 'file|mimes:jpg,bmp,png,jpeg,webp',
            'map_marker_img' => 'file|mimes:jpg,bmp,png,jpeg,webp,svg',
            'options'       => 'string|required',
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
        $params['coupon_model_id']['description'] .= '(기본 0)';
        $params['logo_img']['description']      .= '(max-width:256px 이상은 리사이징)';
        $params['favicon_img']['description']   .= '(max-width:32px 이상은 리사이징)';
        $params['passbook_img']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['contract_img']['description']  .= '(max-width:500px 이상은 리사이징)';
        $params['stamp_flag']['description']    .= '(1=사용, 0=미사용)';
        $params['point_flag']['description']    .= '(1=사용, 0=미사용)';
        $params['id_img']['description']    .= '(max-width:500px 이상은 리사이징)';
        $params['og_img']['description']    .= '(max-width:1200px 이상은 리사이징)';
        $params['map_marker_img']['description'] .= '(max-width:60px 이상은 리사이징)';
        $params['theme_css']['description'] .= '(테마 CSS 내용 작성)';
        $params['name']['description']      .= '(브랜드명)';
        $params['mbr_type']['description']  .= '(1=모든 가맹점에서 사용, 0=유입된 가맹점에서만 사용)';
        $params['mbr_type']['example']  = '0';
        $params['guide_type']['description'] .= '(1=일반형, 0=친화적)';
        $params['guide_type']['example']  = '0';
        return $params;
    }

    public function data()
    {
        $data = [
            'dns'       => $this->input('dns'),
            'name'      => $this->input('name'),
            'theme_css'     => $this->input('theme_css', ''),
            'company_nm'    => $this->input('company_nm', ''),
            'pvcy_rep_nm'   => $this->input('pvcy_rep_nm', ''),
            'og_description'   => $this->input('og_description', ''),
            'ceo_nm'    => $this->input('ceo_nm', ''),
            'addr'      => $this->input('addr', ''),
            'fax_num'       => $this->input('fax_num', ''),
            'business_num'  => $this->input('business_num', ''),
            'phone_num'     => $this->input('phone_num', ''),
            'fax_num'       => $this->input('fax_num', ''),
            'stamp_flag'     => (boolean)$this->input('stamp_flag', 0),
            'point_flag'     => (boolean)$this->input('point_flag', 0),
            'stamp_max_size' => (int)$this->input('stamp_max_size', 10),
            'stamp_save_count'  => (int)$this->input('stamp_save_count', 0),
            'coupon_model_id'   => (int)$this->input('coupon_model_id', 0),
            'point_rate'        => (int)$this->input('point_rate', 0),
            'point_min_amount'  => (int)$this->input('point_min_amount', 0),
            'mbr_type'  => (int)$this->input('mbr_type', 0),
            'guide_type'=> (int)$this->input('guide_type', 0),
            'options'   => $this->input('options', '[]'),
        ];
        return $data;
    }
}
