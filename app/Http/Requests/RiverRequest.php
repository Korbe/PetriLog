<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RiverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string',
            'desc'            => 'nullable|string',
            'hint'            => 'nullable|string',
            'fishing_rights'  => 'nullable|string',
            'ticket_sales'    => 'nullable|string',
            'states'          => 'required|array',
            'states.*'        => 'exists:states,id',
            'fish'            => 'array',
            'fish.*'          => 'exists:fish,id',
        ];
    }
}
