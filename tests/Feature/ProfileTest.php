<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_or_update_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->put(route('profile.update'), [])->assertRedirect(route('login'));
    }

    public function test_customer_can_view_and_update_own_profile(): void
    {
        $user = $this->createUser(['name' => 'Hung Manh', 'phone' => '+84568503606']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Hung Manh')
            ->assertSee('0568503606')
            ->assertSee('Joyful%20Victory%20Against%20Turquoise%20Wall.png')
            ->assertSee('editing: false', false)
            ->assertSee('x-show="!editing"', false)
            ->assertSee(':disabled="!editing"', false)
            ->assertSee('action="'.route('profile.update').'"', false);

        $this->put(route('profile.update'), [
            'name'          => 'Bui Manh Hung',
            'phone'         => '0568503606',
            'email'         => 'attacker@example.com',
            'gender'        => 'male',
            'date_of_birth' => '2000-01-01',
            'address'       => 'Ho Chi Minh City',
            'occupation'    => 'Developer',
        ])->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertSame('Bui Manh Hung', $user->name);
        $this->assertSame('+84568503606', $user->phone);
        $this->assertSame('male', $user->gender);
        $this->assertSame('2000-01-01', $user->date_of_birth->format('Y-m-d'));
        $this->assertSame('Ho Chi Minh City', $user->address);
        $this->assertSame('Developer', $user->occupation);
        $this->assertNotSame('attacker@example.com', $user->email);
    }

    public function test_profile_rejects_duplicate_phone_and_invalid_avatar(): void
    {
        $user = $this->createUser();
        $other = $this->createUser(['phone' => '+84912345678']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name'   => 'Hung Manh',
            'phone'  => $other->phone,
            'avatar' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors(['phone', 'avatar']);

        $this->assertNotSame('Hung Manh', $user->fresh()->name);
    }

    public function test_customer_can_upload_and_replace_avatar(): void
    {
        Storage::fake('public');
        $user = $this->createUser();
        $this->actingAs($user);

        $details = ['name' => $user->name, 'phone' => $user->phone];
        $this->put(route('profile.update'), $details + ['avatar' => UploadedFile::fake()->image('first.png')])
            ->assertRedirect(route('profile.show'));
        $first = $user->fresh()->avatar;
        $this->assertTrue(Storage::disk('public')->exists($first));
        $this->get(route('profile.avatar'))->assertOk()->assertHeader('content-type', 'image/png');
        $this->get(route('profile.show'))->assertOk()
            ->assertDontSee('Joyful%20Victory%20Against%20Turquoise%20Wall.png');

        $this->put(route('profile.update'), $details + ['avatar' => UploadedFile::fake()->image('second.png')])
            ->assertRedirect(route('profile.show'));
        $this->assertFalse(Storage::disk('public')->exists($first));
        $this->assertTrue(Storage::disk('public')->exists($user->fresh()->avatar));
    }

    public function test_invalid_birth_date_uses_vietnamese_feedback(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->put(route('profile.update').'?lang=vi', [
            'name'          => $user->name,
            'phone'         => $user->phone,
            'date_of_birth' => now()->addDay()->toDateString(),
        ])->assertSessionHasErrors(['date_of_birth' => 'Ngày sinh không thể ở tương lai.']);
    }

    public function test_malformed_phone_input_returns_validation_error(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->put(route('profile.update'), [
            'name'  => $user->name,
            'phone' => ['unexpected'],
        ])->assertSessionHasErrors('phone');

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('editing: true', false);
    }

    public function test_avatar_endpoint_only_serves_profile_uploads(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('other/private.png', 'not an avatar');
        $user = $this->createUser(['avatar' => 'other/private.png']);

        $this->actingAs($user)->get(route('profile.avatar'))->assertNotFound();
    }

    private function createUser(array $attributes = []): User
    {
        /** @var User $user */
        $user = User::factory()->create($attributes);

        return $user;
    }
}
