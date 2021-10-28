<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductCategoryRequest extends FormRequest
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
            'name'  =>  'required',
            'parent'    =>  'required',
            'sort'    =>  'required',
            'status'    =>  'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' =>  '名称不能为空',
            'parent.required' =>  '父类ID不能为空',
            'sort.required' =>  '排序不能为空',
            'status.required' =>  '状态不能为空',
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
