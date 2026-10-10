<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function createTestUser(array $attributes = []): User
    {
        /** @var User $user */
        $user = User::factory()->createOne($attributes);

        return $user;
    }
}
