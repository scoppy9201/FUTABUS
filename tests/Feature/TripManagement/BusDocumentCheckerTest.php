<?php

declare(strict_types=1);

namespace Tests\Feature\TripManagement;

use FuteBus\TripManagement\Services\BusDocumentChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusDocumentCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_without_expiry_is_valid_but_expiring_documents_need_a_valid_date(): void
    {
        $companyId = DB::table('bus_companies')->insertGetId(['code' => 'FUTA', 'name' => 'FUTA Bus Lines']);
        $busId = DB::table('buses')->insertGetId([
            'bus_company_id' => $companyId, 'license_plate' => '51H-123.45', 'capacity' => 34,
        ]);
        $typeId = DB::table('document_types')->insertGetId([
            'name' => 'Registration', 'is_required' => true, 'has_expiry' => false, 'status' => 'active',
        ]);
        $docId = DB::table('vehicle_documents')->insertGetId([
            'bus_id'      => $busId, 'document_type_id' => $typeId,
            'issued_date' => today()->subYear(), 'expiry_date' => null, 'status' => 'active',
        ]);
        $bus = DB::table('buses')->find($busId);
        $departure = today()->addDay();

        $this->assertSame([], BusDocumentChecker::messages($bus, $departure));

        DB::table('document_types')->where('id', $typeId)->update(['has_expiry' => true]);
        $this->assertNotEmpty(BusDocumentChecker::messages($bus, $departure));

        DB::table('vehicle_documents')->where('id', $docId)->update(['expiry_date' => today()]);
        $this->assertNotEmpty(BusDocumentChecker::messages($bus, $departure));

        DB::table('vehicle_documents')->where('id', $docId)->update(['expiry_date' => $departure]);
        $this->assertSame([], BusDocumentChecker::messages($bus, $departure));

        DB::table('vehicle_documents')->where('id', $docId)->update(['status' => 'inactive']);
        $this->assertNotEmpty(BusDocumentChecker::messages($bus, $departure));
    }
}
