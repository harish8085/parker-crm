<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterCodeRequest extends FormRequest
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
            'code' => 'required|max:15|min:5',            
        ];
    }

    public function messages()
    {
        return [
            'code.required'=> 'Please enter the master code',
            'code.max' => 'Please enter the master code less than 15 characters',
            'code.min' => 'Please enter the master code greater than 5 characters'            
        ];
    }
}
