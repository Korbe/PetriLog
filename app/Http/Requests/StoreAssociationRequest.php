<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssociationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:associations,name',
            'desc' => 'nullable|string',
            'link' => 'nullable|string',
            'state_id' => 'required|exists:states,id',
        ];
    }
}
