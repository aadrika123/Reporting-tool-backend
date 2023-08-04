<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidationValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class Resource extends FormRequest
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
            'resource_name' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:1024'
        ];
    }


    // Validation Error Message
    protected function failedValidation(ValidationValidator $validator)
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'status' => false,
                    'message' => 'The given data was invalid',
                    'errors' => $validator->errors()
                ],
                422
            )
        );
    }
}
