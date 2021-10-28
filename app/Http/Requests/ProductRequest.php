<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
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
            'sku'  =>  'required|integer',
            'chinese_name'  =>  'required',
            'supplier'  =>  'required',
            'brand'  =>  'required',
            'attribute'  =>  'required',
            'imgs'  =>  'required',
            'category'  =>  'required',
        ];
    }

    public function messages()
    {
        return [
            'supplier.required' =>  '供应商不能为空',
            'category.required' =>  '分类不能为空',
            'sku.required' =>  'sku不能为空',
            'chinese_name.required' =>  '中文名不能为空',
            'brand.required' =>  '品牌不能为空',
            'attribute.required' =>  '属性不能为空',
            'imgs.required' =>  '主图路径不能为空',
            'sku.integer' =>  'SKU只能是int类型',
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
