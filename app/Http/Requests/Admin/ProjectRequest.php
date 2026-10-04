<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
        $projectId = $this->route('project')?->getKey();

        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', Rule::unique('projects', 'slug')->ignore($projectId)],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:20000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048', 'dimensions:min_width=320,min_height=180,max_width=4000,max_height=4000'],
            // portfolio.project_types is a flat list of the *values* the form submits, so
            // Rule::in() must receive those values. array_keys() here would validate against
            // 0,1,2,3,4 and reject every real project type.
            'project_type' => ['nullable', 'string', 'max:80', Rule::in(config('portfolio.project_types', []))],
            'tech_stack' => ['nullable', 'array', 'max:30'],
            'tech_stack.*' => ['string', 'max:60'],
            'live_url' => ['nullable', 'url:http,https', 'max:255'],
            'link' => ['nullable', 'string', 'max:255'],
            'github_url' => ['nullable', 'url:http,https', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in([Project::STATUS_LIVE, Project::STATUS_IN_PROGRESS, Project::STATUS_ARCHIVED])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'problem' => ['nullable', 'string', 'max:10000'],
            'solution' => ['nullable', 'string', 'max:10000'],
            'highlights' => ['nullable', 'array', 'max:20'],
            'highlights.*' => ['string', 'max:200'],
            'challenges' => ['nullable', 'string', 'max:10000'],
            'outcome' => ['nullable', 'string', 'max:10000'],
            'role' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'screenshots' => ['nullable', 'array', 'max:12'],
            'screenshots.*' => ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'short_description' => 'short description',
            'github_url' => 'GitHub URL',
            'live_url' => 'live URL',
            'tech_stack' => 'tech stack',
            'tech_stack.*' => 'tech stack item',
            'highlights.*' => 'highlight',
            'screenshots.*' => 'screenshot',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => trim((string) $this->input('slug'))]);
        }
    }
}
