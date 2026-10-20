<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->city(),
            'ville' => fake()->city(),
            'adresse' => fake()->optional()->address(),
            'telephone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'est_actif' => true,
        ];
    }
}
