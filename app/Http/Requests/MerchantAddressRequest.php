<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MerchantAddressRequest extends FormRequest
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
            'merchant'  =>  'required|integer',
            'name'  =>  'required',
            'phone'  =>  'required|min:11',
            'country'  =>  'required|integer',
            'province'  =>  'required|integer',
            'state_city'  =>  'required|integer',
            'county_district'  =>  'required|integer',
            'address'  =>  'required',
            'type'  =>  'required',
        ];
    }

    public function messages()
    {
        return [
            'merchant.required' =>  '供应商不能为空',
            'name.required' =>  '中文名不能为空',
            'country.required' =>  '国家不能为空',
            'province.required' =>  '省份不能为空',
            'state_city.required' =>  '州或市不能为空',
            'county_district.required' =>  '县或区不能为空',
            'address.required' =>  '详细地址不能为空',
            'merchant.integer' =>  '供应商只能是int类型',
            'country.integer' =>  '国家只能是int类型',
            'province.integer' =>  '省份只能是int类型',
            'state_city.integer' =>  '州或市只能是int类型',
            'county_district.integer' =>  '县或区只能是int类型',
            'phone.required' =>  '手机不能为空',
            'phone.min' =>  '手机最少11位数',
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
