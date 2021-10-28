<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LogisticsRequest extends FormRequest
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
            'code'  =>  'required|string',
            'order_code'  =>  'required|string',
            'order_quantity'  =>  'required|integer',
            'ec'  =>  'required|integer',
            'freight'  =>  'required',
            'status'  =>  'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'code.required' =>  '物流单号不能为空',
            'order_code.required' =>  '订单号不能为空',
            'order_quantity.required' =>  '订单数量不能为空',
            'ec.required' =>  '快递公司不能为空',
            'freight.required' =>  '运费不能为空',
            'status.required' =>  '状态不能为空',
            'code.string' =>  '物流单号只能是字符串类型',
            'order_code.string' =>  '订单号只能是字符串类型',
            'order_quantity.integer' =>  '订单数量只能是整数类型',
            'ec.integer' =>  '快递公司只能是整数类型',
            'status.integer' =>  '状态只能是整数类型',
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
