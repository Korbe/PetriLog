<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssociationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $associationId = $this->route('association')->id;

        return [
            'name' => 'required|string|unique:associations,name,' . $associationId,
            'desc' => 'nullable|string',
            'link' => 'nullable|string',
            'state_id' => 'required|exists:states,id',
        ];
    }
}
