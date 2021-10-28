<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShopProductStockRequest extends FormRequest
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
            'code'  =>  'required',
            'stock'  =>  'required|integer',
            'blocked_stock'  =>  'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'code.required' =>  '产品条码不能为空',
            'stock.required' =>  '可用库存不能为空',
            'blocked_stock.required' =>  '冻结库存不能为空',
            'key.required' =>  'key不能为空',
            'stock.integer' =>  '可用库存只能是int类型',
            'blocked_stock.integer' =>  '冻结库存只能是int类型',
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
