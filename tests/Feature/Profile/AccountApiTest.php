<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bearer_token_can_read_and_update_own_profile(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->getJson(route('api.v1.me.show'))->assertUnauthorized();

        $this->withToken($user->createToken('profile test', ['api'])->plainTextToken)
            ->getJson(route('api.v1.me.show'))
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password');

        $this->putJson(route('api.v1.me.update'), [
            'name'  => 'Updated Customer',
            'phone' => '0912345678',
        ])->assertOk()->assertJsonPath('data.name', 'Updated Customer');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Updated Customer',
            'phone' => '+84912345678',
        ]);
    }

    public function test_session_without_bearer_token_cannot_use_account_api(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user)->getJson(route('api.v1.me.show'))->assertForbidden();
    }

    public function test_disabled_account_cannot_use_account_api(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('profile test', ['api'])->plainTextToken;
        $user->update(['is_active' => false]);
        $this->withToken($token)->getJson(route('api.v1.me.show'))->assertForbidden();
    }
}
