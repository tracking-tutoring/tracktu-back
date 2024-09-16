<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
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
        // Liste des valeurs possibles pour le champ name
        $names = [
            'gestion de projet',
            'design',
            'architecture logiciel',
            'cryptographie'
        ];

        // Sélectionner une valeur aléatoire
        $randomName = $this->faker->randomElement($names);

        return [
            'name' => $randomName,
        ];
    }
}
