<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShopProductRequest extends FormRequest
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
            'shop'  =>  'required|integer',
            'code'  =>  'required',
            'purchase'  =>  'required',
            'selling'  =>  'required',
            'imgs'  =>  'required',
            'describe'  =>  'required',
        ];
    }

    public function messages()
    {
        return [
            'shop.required' =>  '商铺不能为空',
            'code.required' =>  '产品条码不能为空',
            'purchase.required' =>  '采购价不能为空',
            'selling.required' =>  '销售价不能为空',
            'imgs.required' =>  '产品图片路径不能为空',
            'describe.required' =>  '描述不能为空',
            'key.required' =>  'key不能为空',
            'shop.integer' =>  '商铺只能是int类型',
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
