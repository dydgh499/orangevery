<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Traits\FormRequestTrait;

class PostRequest extends FormRequest
{
    use FormRequestTrait;
    public $keys = [
        'parent_id',
        'writer',
        'title',
        'content',
        'type',
        'is_reply',
    ];

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
            'title' => 'required',
            'content' => 'required',
            'is_reply' => 'required',
            'type' => 'required',
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
        return $params;
    }

    protected function prepareForValidation()
    {
        if ($this->has('is_reply')) 
        {
            $this->merge(['is_reply' => $this->convertToBoolean($this->input('is_reply'))]);
        }
    }

    public function data()
    {
        $data = $this->getParmasBaseKey();
        $data['parent_id'] = $data['parent_id'] == '' ? null : $data['parent_id'];
        $data['parent_id'] = $data['parent_id'] == 'NaN' ? null : $data['parent_id'];
        $data['brand_id'] = $this->user()->brand_id;
        return $data;
    }
}
