<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AdminAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_one_verified_admin_who_can_sign_in(): void
    {
        config()->set('seed.admin.name', 'Quản trị FUTA');
        config()->set('seed.admin.email', 'admin@example.test');
        config()->set('seed.admin.password', 'strong-test-password-2026');

        $this->seed(AdminAccountSeeder::class);
        $this->seed(AdminAccountSeeder::class);

        $admin = User::where('email', 'admin@example.test')->sole();

        $this->assertTrue($admin->isAdmin());
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('strong-test-password-2026', $admin->password));
        $this->assertSame(1, DB::table('role_user')->where('user_id', $admin->id)->count());

        $this->post(route('login.store'), [
            'email'    => 'admin@example.test',
            'password' => 'strong-test-password-2026',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_seeder_skips_admin_when_no_password_is_configured(): void
    {
        config()->set('seed.admin.password', null);

        $this->seed(AdminAccountSeeder::class);

        $this->assertSame(0, User::count());
    }

    public function test_seeder_does_not_promote_an_existing_customer_account(): void
    {
        $customer = User::factory()->create(['email' => 'admin@example.test']);
        $roleId = DB::table('roles')->insertGetId(['name' => 'Customer', 'slug' => 'customer']);
        DB::table('role_user')->insert(['user_id' => $customer->id, 'role_id' => $roleId]);
        config()->set('seed.admin.email', 'admin@example.test');
        config()->set('seed.admin.password', 'strong-test-password-2026');

        $this->expectException(LogicException::class);
        $this->seed(AdminAccountSeeder::class);
    }

    public function test_seeder_does_not_promote_an_existing_account_without_a_role(): void
    {
        User::factory()->create(['email' => 'admin@example.test']);
        config()->set('seed.admin.email', 'admin@example.test');
        config()->set('seed.admin.password', 'strong-test-password-2026');

        $this->expectException(LogicException::class);
        $this->seed(AdminAccountSeeder::class);
    }
}
