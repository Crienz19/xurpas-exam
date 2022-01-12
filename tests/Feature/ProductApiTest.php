<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Factories\ProductFactory;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{    
    /** @test */
    public function can_seed_products_to_the_database()
    {
        $this->seed(
            ProductSeeder::class
        );

        $this->assertDatabaseCount('products', 3);
    }

    /** @test */
    public function successfully_created_an_order_and_deducted_to_stocks()
    {
        $request_body = [
            'product_id'    =>  '1',
            'quantity'      =>  '2'
        ];

        Sanctum::actingAs(
            User::factory()->create()
        );

        $currentStocks = Product::where('id', $request_body['product_id'])->first()->available_stock;

        $response = $this->post('/api/order', $request_body);

        $newStocks = Product::where('id', $request_body['product_id'])->first()->available_stock;

        $response->assertStatus(201);

        $this->assertEquals($response->json(), ['message' => 'You have successfully ordered this product.']);

        $this->assertEquals($newStocks, $currentStocks - $request_body['quantity']);
    }

    /** @test */
    public function unsuccessful_order_due_to_insufficient_stock_of_a_product()
    {
        $request_body = [
            'product_id'    =>  '2',
            'quantity'      =>  '9999'
        ];

        Sanctum::actingAs(
            User::factory()->create()
        );

        $response = $this->post('/api/order', $request_body);

        $response->assertStatus(400);

        $this->assertEquals($response->json(), ['message' => 'Failed to order this product due to unavailability of the stock']);
    }
}
