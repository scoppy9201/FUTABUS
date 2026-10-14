<?php

namespace Tests\Feature\BusManagement;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VehicleDocumentExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_expiring_vehicle_document_can_be_saved_without_an_expiry_date(): void
    {
        $companyId = DB::table('bus_companies')->insertGetId([
            'code' => 'FUTA',
            'name' => 'FUTA',
        ]);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId,
            'license_plate'  => '51H-123.45',
            'capacity'       => 20,
            'bus_type'       => 'sleeper',
            'status'         => 'active',
        ]);
        $documentTypeId = DB::table('document_types')->insertGetId([
            'name'            => 'Giấy chứng nhận đăng ký xe',
            'is_required'     => true,
            'has_expiry'      => false,
            'blocks_dispatch' => true,
            'warning_days'    => 0,
            'status'          => 'active',
        ]);
        DB::table('document_type_fields')->insert([
            'document_type_id' => $documentTypeId,
            'field_key'        => 'registration_number',
            'label'            => 'Số chứng nhận đăng ký',
            'field_type'       => 'text',
            'is_required'      => true,
            'is_active'        => true,
            'sort_order'       => 0,
        ]);

        $admin = $this->createTestUser();
        $roleId = DB::table('roles')->insertGetId(['slug' => 'admin', 'name' => 'Admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $roleId]);

        $this->actingAs($admin)
            ->post(route('bus-management.vehicle-documents.store'), [
                'bus_id'           => $busId,
                'document_type_id' => $documentTypeId,
                'issued_date'      => today()->subDay()->toDateString(),
                'values'           => ['registration_number' => 'DEMO-DK-0001'],
            ])
            ->assertRedirect(route('bus-management.vehicle-documents.index'));

        $this->assertDatabaseHas('vehicle_documents', [
            'bus_id'           => $busId,
            'document_type_id' => $documentTypeId,
            'expiry_date'      => null,
        ]);
    }
}
