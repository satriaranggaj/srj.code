<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListViewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['min:0'],
            'search' => ['min:0'],
            'limit' => ['min:0'],
            'exact' => ['min:0'],
            'order' => ['min:0', 'required_with:direction'],
            'dir' => ['min:0', 'required_with:order'],
        ];
    }
}
