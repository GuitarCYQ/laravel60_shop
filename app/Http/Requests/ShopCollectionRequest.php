<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShopCollectionRequest extends FormRequest
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
            'name' =>  'required',
            'code'    =>  'required',
            'shop'    =>  'required|integer',
            'type'    =>  'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' =>  '名称不能为空',
            'code.required' =>  '账号不能为空',
            'shop.required' =>  '店铺不能为空',
            'type.required' =>  '类型不能为空',
            'shop.integer' =>  '店铺在哪是int类型',

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
