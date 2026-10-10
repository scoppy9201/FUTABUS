<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_collections_return_json_and_reject_invalid_news_filter(): void
    {
        $this->getJson(route('api.v1.news.index'))->assertOk()->assertJsonStructure(['data']);
        $this->getJson(route('api.v1.schedules.index'))->assertOk()->assertJsonPath('data', []);
        $this->getJson(route('api.v1.news.index', ['q' => str_repeat('x', 101)]))
            ->assertUnprocessable()->assertJsonValidationErrors('q');
    }

    public function test_faq_and_branch_endpoints_only_expose_active_localized_records(): void
    {
        DB::table('faq_categories')->insert([
            ['slug' => 'active', 'name' => json_encode(['vi' => 'Hoạt động']), 'is_active' => true],
            ['slug' => 'inactive', 'name' => json_encode(['vi' => 'Đã ẩn']), 'is_active' => false],
        ]);
        $regionId = DB::table('branch_regions')->insertGetId([
            'slug' => 'south', 'name' => json_encode(['vi' => 'Miền Nam']), 'is_active' => true,
        ]);
        DB::table('branch_offices')->insert([
            'branch_region_id' => $regionId,
            'name'             => json_encode(['vi' => 'Văn phòng Cần Thơ']),
            'address'          => json_encode(['vi' => 'Cần Thơ']),
            'is_active'        => true,
        ]);

        $this->getJson(route('api.v1.faq-categories.index'))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Hoạt động');
        $this->getJson(route('api.v1.faq-categories.show', 'inactive'))->assertNotFound();
        $this->getJson(route('api.v1.branches.index'))
            ->assertOk()->assertJsonPath('data.0.offices.0.name', 'Văn phòng Cần Thơ');
    }

    public function test_contact_api_validates_and_persists_a_message(): void
    {
        $this->postJson(route('api.v1.contact-messages.store'), [])
            ->assertUnprocessable()->assertJsonValidationErrors([
                'department', 'name', 'email', 'phone', 'subject', 'message',
            ]);

        $this->postJson(route('api.v1.contact-messages.store'), [
            'department' => 'futabus',
            'name'       => 'Test Customer',
            'email'      => 'customer@example.com',
            'phone'      => '0912345678',
            'subject'    => 'Bus service',
            'message'    => 'Please contact me about my trip.',
        ])->assertCreated()->assertJsonStructure(['data' => ['id']]);

        $this->assertDatabaseHas('contact_messages', [
            'email'   => 'customer@example.com',
            'subject' => 'Bus service',
        ]);
    }
}
