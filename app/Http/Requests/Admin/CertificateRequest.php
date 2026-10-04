<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'link' => ['nullable', 'url:http,https', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:120'],
            'issued_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:300'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
