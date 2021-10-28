<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MerchantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'chinese_name' =>  'required|between:3,25',
            'phone'    =>  'required|min:11',
            'email'    =>  'required|email:rfc,dns',
        ];
    }

    public function messages()
    {
        return [
            'chinese_name.required' =>  '中文名不能为空',
            'chinese_name.between' =>  '中文名长度3-25',
            'supplier_id.required' =>  '供应商不能为空',
            'phone.required' =>  '手机不能为空',
            'phone.min' =>  '手机最少11位数',
            'email.required' =>  '邮箱不能为空',
            'email.email'   =>  '邮箱错误',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw (new HttpResponseException(response()->json([
            'code' => 400,
            'message' => $validator->errors()->first(),
        ], 200)));
    }


}
