<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LakeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'            => 'required|string',
            'desc'            => 'nullable|string',
            'hint'            => 'nullable|string',
            'fishing_rights'  => 'nullable|string',
            'ticket_sales'    => 'nullable|string',
            'states'          => 'required|array',
            'states.*'        => 'exists:states,id',
            'fish'            => 'nullable|array',
            'fish.*'          => 'exists:fish,id',
        ];
    }
}
