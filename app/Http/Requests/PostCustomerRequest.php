<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostCustomerRequest extends FormRequest
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
            'id' => 'required',
            'status' => 'required',
            'shippingAddress' => 'required',
            'billingAddress' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'id.required' => 'id is a required field',
            'status.required' => 'status is a required field',
            'shippingAddress.required' => 'shippingAddress is a required field',
            'billingAddress.required' => 'billingAddress is a required field',
        ];
    }
}
