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
            'type' => 'required|max:45',
            'address1' => 'required|max:255',
            'address2' => 'required|max:255',
            'city' => 'required|max:255',
            'zipcode' => 'required|max:45',
            'state' => 'nullable|max:45',
            'country_code' => 'required|max:3',
        ];
    }
    public function messages()
    {
        return [
            'required' => ':attribute is a required field',
            'max' => 'Trường :attribute phải ít hơn :max ký tự',
        ];
    }
}
