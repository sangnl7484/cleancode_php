<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|max:2000',
            'price' => 'required|numeric',
            'image' => 'image|max:2000',
            'published' => 'boolean',
            'slug' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'Trường :attribute không được để trống',
            'boolean' => 'Trường :attribute phải là dạng boolean',
            'image' => 'Phải là ảnh',
            'numeric' => 'Trường :attribute phải là dạng số',
            'max' => 'Trường :attribute phải ít hơn 2000 ký tự',
            'image.max' => 'Đường dẫn :attribute phải ít hơn :max ký tự'
        ];
    }
}
