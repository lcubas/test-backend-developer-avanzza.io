<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_must_enter_email()
    {
        $response = $this->postJson(route('api.login'));
        $response->assertUnprocessable(422);
        $response->assertJsonValidationErrorFor('email');
        $this->assertSame(
            __('validation.required', ['attribute' => 'email']),
            $response->json('errors.email.0')
        );
    }

    public function test_must_enter_password()
    {
        $response = $this->postJson(route('api.login'));
        $response->assertUnprocessable(422);
        $response->assertJsonValidationErrorFor('password');
        $this->assertSame(
            __('validation.required', ['attribute' => 'password']),
            $response->json('errors.password.0')
        );
    }

    public function test_successful_login()
    {
        User::factory()->create(['email' => 'sample@test.com']);

        $credentials = ['email' => 'sample@test.com', 'password' => '123'];

        $response = $this->postJson(route('api.login'), $credentials);

        $response->assertOk();
        $response->assertJsonStructure([
            "message",
            "token",
        ]);
        $this->assertAuthenticated();
    }
}
