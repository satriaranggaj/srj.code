<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->words(4, true)),
            'link' => $this->faker->url(),
            'issuer' => $this->faker->company(),
            'issued_at' => $this->faker->dateTimeBetween('-4 years'),
            'description' => $this->faker->sentence(10),
            'sort_order' => 0,
        ];
    }
}
