<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShopExpensesRequest extends FormRequest
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
            'price'  =>  'required',
            'favorable_price'  =>  'required',
            'actual_price'  =>  'required',
            'expiration_time'  =>  'required',
            'type'  =>  'required',
        ];
    }

    public function messages()
    {
        return [
            'shop.required' =>  '商铺不能为空',
            'price.required' =>  '价格不能为空',
            'favorable_price.required' =>  '优惠价不能为空',
            'actual_price.required' =>  '实际价不能为空',
            'expiration_time.required' =>  '到期时间不能为空',
            'type.required' =>  '类型不能为空',
            'merchant.integer' =>  '商铺只能是int类型',
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
