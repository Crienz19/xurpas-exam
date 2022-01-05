<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function successful_login()
    {        
        $this->post('/api/register', $this->request_body());

        $request = $this->post('/api/login', $this->request_body());

        // asserting response header http code 201
        $request->assertCreated();

        // assert if response is correct token
        $this->assertEquals($request->json(), ['access_token' => $request->json()['access_token']]);
    }

    /** @test */
    public function unsuccessful_login_due_to_invalid_credentials()
    {
        $invalidCredential = [
            'email'     =>  'backend123123@multicorp.com',
            'password'  =>  'test123123'
        ];

        $this->post('/api/register', $this->request_body());

        $response = $this->post('/api/login', $invalidCredential);

        $response->assertStatus(401);
    }

    /** @test */
    public function account_lockout_for_5_mins_after_5_tries()
    {        
        $invalidCredential = [
            'email'     =>  'backend123123@multicorp.com',
            'password'  =>  'test123123'
        ];

        $this->post('/api/register', $this->request_body());
        
        for ($i=0; $i < 6; $i++) { 
            $response = $this->post('/api/login', $invalidCredential);
        }

        $response->assertStatus(302);
    }

    private function request_body()
    {
        return [
            'email'     =>  'backend@multicorp.com',
            'password'  =>  'test123'
        ];
    }
}
