<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'category' => $this->faker->randomElement(Skill::CATEGORIES),
            'url' => $this->faker->url(),
            'sort_order' => 0,
        ];
    }
}
