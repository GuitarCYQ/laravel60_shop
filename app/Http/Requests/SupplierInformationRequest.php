<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SupplierInformationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'supplier'  =>  'required|integer',
            'business_license'  =>  'required',
            'id_imgs'  =>  'required|',
            'id_number'  =>  'required|integer',
            'id_type'  =>  'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'supplier.required' =>  '供应商不能为空',
            'business_license.required' =>  '营业执照连接图不能为空',
            'id_imgs.required' =>  '身份证连接图不能为空',
            'id_number.required' =>  '身份证号不能为空',
            'id_type.required' =>  '性别不能为空',
            'supplier.integer' =>  '供应商只能是int类型',
            'id_number.integer' =>  '身份证号只能是int类型',
            'id_type.integer' =>  '性别只能是int类型',
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
