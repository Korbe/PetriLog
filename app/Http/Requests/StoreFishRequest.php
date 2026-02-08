<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFishRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'  => 'required|string|unique:fish,name',
            'desc'  => 'nullable|string',
            'photo' => 'nullable|image|max:102400',
        ];
    }
}
