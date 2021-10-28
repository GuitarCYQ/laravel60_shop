<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductBarcodeRequest extends FormRequest
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
            'type'  =>  'required',
            'weight'    =>  'required',
            'length'    =>  'required',
            'width'    =>  'required',
            'height'    =>  'required',
            'company'    =>  'required',
            'specifications'    =>  'required',
            'purchase'    =>  'required',
        ];
    }

    public function messages()
    {
        return [
            'type.required' =>  '类型不能为空',
            'weight.required' =>  '重量不能为空',
            'length.required' =>  '长不能为空',
            'width.required' =>  '宽不能为空',
            'height.required' =>  '高不能为空',
            'code.required' =>  '条码不能为空',
            'company.required' =>  '单位不能为空',
            'specifications.required' =>  '规格不能为空',
            'purchase.required' =>  '供货价不能为空',

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
