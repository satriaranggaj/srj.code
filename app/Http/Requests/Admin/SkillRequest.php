<?php

namespace App\Http\Requests\Admin;

use App\Models\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SkillRequest extends FormRequest
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
        $skillId = $this->route('skill')?->getKey();

        return [
            'name' => ['required_without:image', 'nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:60', Rule::in(Skill::CATEGORIES)],
            'url' => ['nullable', 'url:http,https', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:512', 'dimensions:min_width=16,min_height=16,max_width=512,max_height=512'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['url' => 'reference URL'];
    }
}
