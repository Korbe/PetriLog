<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stateId = $this->route('state')?->id ?? null;

        return [
            'name'  => 'required|string|unique:states,name,' . $stateId,
            'desc'  => 'nullable|string',
            'photo' => 'nullable|image|max:102400',
        ];
    }
}
