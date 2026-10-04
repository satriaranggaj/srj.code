<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst($this->faker->unique()->words(3, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 999999),
            'short_description' => $this->faker->sentence(12),
            'description' => $this->faker->paragraph(3),
            'project_type' => 'Full Stack Application',
            'tech_stack' => ['Laravel', 'Vue', 'MySQL'],
            'live_url' => $this->faker->url(),
            'github_url' => 'https://github.com/satriaranggaj/'.$this->faker->slug(2),
            'featured' => false,
            'status' => Project::STATUS_LIVE,
            'sort_order' => 0,
            'role' => 'Full Stack Developer',
            'year' => (string) $this->faker->numberBetween(2021, 2026),
            'highlights' => [$this->faker->sentence(8), $this->faker->sentence(8)],
            'problem' => $this->faker->paragraph(2),
            'solution' => $this->faker->paragraph(2),
            'outcome' => $this->faker->paragraph(2),
        ];
    }

    public function featured(): self
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function archived(): self
    {
        return $this->state(fn () => ['status' => Project::STATUS_ARCHIVED]);
    }
}
