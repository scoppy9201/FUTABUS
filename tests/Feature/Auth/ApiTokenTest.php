<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_active_user_can_issue_and_revoke_a_bearer_token(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->postJson(route('api.v1.tokens.store'), [
            'email'       => $user->email,
            'password'    => 'password',
            'device_name' => 'API test client',
        ])->assertCreated()->assertJsonPath('token_type', 'Bearer');

        $token = $response->json('access_token');
        $this->assertNotEmpty($token);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)->deleteJson(route('api.v1.tokens.destroy'))->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_inactive_and_unverified_accounts_cannot_issue_tokens(): void
    {
        $inactive = User::factory()->create(['is_active' => false]);
        $unverified = User::factory()->unverified()->create(['is_active' => true]);

        foreach ([
            [$inactive->email, 'password'],
            [$unverified->email, 'password'],
            [$unverified->email, 'incorrect'],
        ] as [$email, $password]) {
            $this->postJson(route('api.v1.tokens.store'), [
                'email'       => $email,
                'password'    => $password,
                'device_name' => 'API test client',
            ])->assertUnprocessable()->assertJsonValidationErrors('email');
        }

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->deleteJson(route('api.v1.tokens.destroy'))->assertUnauthorized();
    }
}
