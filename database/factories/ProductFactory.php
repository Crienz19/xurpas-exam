<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'              =>  'product_' . (rand(1, 10) * 10),
            'available_stock'   =>  100
        ];
    }
}
