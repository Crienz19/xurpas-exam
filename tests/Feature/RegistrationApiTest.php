<?php

namespace Tests\Feature;

use App\Events\NewlyCreatedUserEvent;
use App\Mail\WelcomeUserMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function successful_registration()
    {
        $request = $this->post('/api/register', $this->request_body());

        // asserting response header http code is 201
        $request->assertCreated();

        // asserting response message
        $this->assertEquals($request->json(), ['message' => 'User successfully registered.']);
    }

    /** @test */
    public function a_welcome_email_is_queued()
    {
        Mail::fake();
        
        $this->post('/api/register', $this->request_body());
        
        Mail::assertQueued(WelcomeUserMail::class);
    }

    /** @test */
    public function unsuccessful_registration_due_to_email_is_already_taken()
    {
        $request = $this->post('/api/register', $this->request_body());

        $request = $this->post('/api/register', $this->request_body());

        // checking if response header http code is 400
        $request->assertStatus(400);

        // checking response message
        $this->assertEquals($request->json(), ['message' => 'Email already taken']);
    }

    private function request_body()
    {
        return [
            'email'     =>  'backend@multicorp.com',
            'password'  =>  'test123'
        ];
    }
}
