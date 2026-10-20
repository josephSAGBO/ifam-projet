<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->lexify('???')).fake()->numerify('###'),
            'description' => fake()->paragraph(),
            'coefficient' => fake()->randomFloat(2, 1, 5),
            'duree_mois' => fake()->optional(0.3)->numberBetween(1, 12),
            'duree_semaines' => fake()->optional(0.4)->numberBetween(1, 10),
            'duree_heures' => fake()->optional(0.7)->numberBetween(1, 40),
            'duree_minutes' => fake()->optional(0.2)->randomElement([15, 30, 45]),
        ];
    }
}
