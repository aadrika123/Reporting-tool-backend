<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class Request extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'templateCode' => 'required',
            'searchGroupId' => 'required',
            'templateName' => 'required',
            'paperSizeEnum' => 'required',
            'detailLayout' => 'required',
            'headerHeight' => 'required',
            'headerHeightPage2' => 'required',
            'footerHeight' => 'required',
            'detailLineSpacing' => 'required',
            'layoutSql' => 'required',
            'detailSql' => 'required',
            'footerSql' => 'required'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));

    }

    public function messages()
    {

        return [

            'templateCode.required' => 'required',
            'searchGroupId.required' => 'required',
            'templateName.required' => 'required',
            'paperSizeEnum.required' => 'required',
            'detailLayout.required' => 'required',
            'headerHeight.required' => 'required',
            'headerHeightPage2.required' => 'required',
            'footerHeight.required' => 'required',
            'detailLineSpacing.required' => 'required',
            'layoutSql.required' => 'required',
            'detailSql.required' => 'required',
            'footerSql.required' => 'required'

        ];
    }


}
