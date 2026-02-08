<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFishRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $fishId = $this->route('fish')->id;

        return [
            'name'  => 'required|string|unique:fish,name,' . $fishId,
            'desc'  => 'nullable|string',
            'photo' => 'nullable|image|max:102400',
        ];
    }
}
