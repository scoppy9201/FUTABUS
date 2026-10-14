<?php

declare(strict_types=1);

namespace FuteBus\StaffCustomerSupport\Services;

use App\Models\User;
use FuteBus\Profile\Services\FeedbackService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use stdClass;

class FeedbackProcessingService
{
    public function visible(User $actor): Builder
    {
        $actor->refresh();
        abort_unless($actor->is_active && $actor->email_verified_at !== null, 403);
        abort_unless($actor->isAdmin() || ($actor->isStaff() && $actor->bus_company_id && $actor->hasPermissionTo('support.view')), 403);
        $query = DB::table('customer_feedback_reports as feedback')
            ->leftJoin('bookings', 'bookings.id', '=', 'feedback.booking_id')
            ->leftJoin('trips', 'trips.id', '=', 'bookings.trip_id')
            ->leftJoin('routes', 'routes.id', '=', 'trips.route_id')
            ->leftJoin('buses', 'buses.id', '=', 'trips.bus_id')
            ->leftJoin('roles as department', 'department.id', '=', 'feedback.department_role_id')
            ->leftJoin('users as handler', 'handler.id', '=', 'feedback.assigned_staff_id')
            ->leftJoin('bus_companies as company', 'company.id', '=', 'feedback.assigned_bus_company_id')
            ->select('feedback.*', 'routes.origin_city', 'routes.destination_city', 'trips.departure_time',
                'department.name as department_name', 'handler.name as handler_name', 'company.name as company_name', 'trips.bus_company_id as trip_company',
                'routes.bus_company_id as route_company', 'buses.bus_company_id as bus_company');
        if (! $actor->isAdmin()) {
            $companyId = $actor->bus_company_id;
            $query->where(function (Builder $scope) use ($companyId): void {
                $scope->where(function (Builder $unlinked) use ($companyId): void {
                    $unlinked->whereNull('feedback.booking_id')->where('feedback.assigned_bus_company_id', $companyId);
                })->orWhere(function (Builder $linked) use ($companyId): void {
                    $linked->whereNotNull('feedback.booking_id')->where('trips.bus_company_id', $companyId)
                        ->where('routes.bus_company_id', $companyId)->where('buses.bus_company_id', $companyId)
                        ->where(fn (Builder $assignment) => $assignment->whereNull('feedback.assigned_bus_company_id')
                            ->orWhere('feedback.assigned_bus_company_id', $companyId));
                });
            });
        }

        return $query;
    }

    public function history(int $report, bool $internal = false): Collection
    {
        return DB::table('customer_support_events')->where('report_id', $report)->orderBy('version')
            ->when(! $internal, fn (Builder $query) => $query->whereNotNull('response'))
            ->get($internal ? ['id', 'actor_name', 'kind', 'previous_status', 'status', 'response', 'internal_note', 'department_name', 'version', 'created_at'] : ['id', 'actor_name', 'kind', 'previous_status', 'status', 'response', 'version', 'created_at']);
    }

    public function claim(User $actor, int $id, int $version): stdClass
    {
        $this->authorizeResponse($actor);

        return DB::transaction(function () use ($actor, $id, $version): stdClass {
            $report = $this->lockedVisible($actor, $id);
            if ((int) $report->assigned_staff_id === $actor->id && $report->status === 'reviewing') {
                return $report;
            }
            abort_unless($report->status !== 'resolved' && $report->assigned_staff_id === null, 409, __('StaffCustomerSupport::support.claimed'));
            $this->checkVersion($report, $version);
            if ($report->department_role_id !== null) {
                abort_unless(DB::table('role_user')->where('user_id', $actor->id)->where('role_id', $report->department_role_id)->exists(), 403);
            }

            return $this->record($actor, $report, [
                'assigned_staff_id' => $actor->id, 'status' => 'reviewing',
                'reviewing_at'      => $report->reviewing_at ?? now(),
            ], 'claim');
        });
    }

    public function respond(User $actor, int $id, array $input): stdClass
    {
        $this->authorizeResponse($actor);
        if (! in_array($input['status'], ['reviewing', 'resolved'], true) || trim($input['response']) === '' || mb_strlen($input['response']) > 3000) {
            throw ValidationException::withMessages(['response' => __('StaffCustomerSupport::support.error')]);
        }

        return DB::transaction(function () use ($actor, $id, $input): stdClass {
            $report = $this->lockedVisible($actor, $id);
            $response = trim($input['response']);
            $hash = hash('sha256', json_encode([$input['status'], $response], JSON_THROW_ON_ERROR));
            $existing = DB::table('customer_support_events')->where('report_id', $id)
                ->where('idempotency_key', $input['idempotency_key'])->first();
            if ($existing !== null) {
                abort_unless((int) $existing->actor_id === $actor->id && hash_equals($existing->request_hash, $hash), 409, __('StaffCustomerSupport::support.retry_conflict'));

                return $existing;
            }
            $this->checkVersion($report, (int) $input['version']);
            abort_unless((int) $report->assigned_staff_id === $actor->id && $report->status === 'reviewing', 409, __('StaffCustomerSupport::support.claim_first'));
            $changes = ['status' => $input['status'], 'response' => $response];
            if ($input['status'] === 'resolved') {
                $changes['resolved_at'] = now();
            }
            $updated = $this->record($actor, $report, $changes, 'response', [
                'response' => $response, 'idempotency_key' => $input['idempotency_key'], 'request_hash' => $hash,
            ]);

            $this->notifyCustomer($report, $response);

            return DB::table('customer_support_events')->where('report_id', $id)->where('version', $updated->version)->first();
        });
    }

    public function assign(User $actor, int $id, int $companyId, int $version): stdClass
    {
        $actor->refresh();
        abort_unless($actor->isAdmin() && $actor->is_active && $actor->email_verified_at !== null && $actor->hasPermissionTo('support.assign'), 403);

        return DB::transaction(function () use ($actor, $id, $companyId, $version): stdClass {
            $report = $this->lockedVisible($actor, $id);
            abort_unless(DB::table('bus_companies')->where('id', $companyId)->where('is_active', true)->exists(), 422);
            if ($report->booking_id !== null) {
                $linked = $this->visible($actor)->where('feedback.id', $id)->first();
                abort_unless((int) $linked->trip_company === $companyId && (int) $linked->route_company === $companyId
                    && (int) $linked->bus_company === $companyId, 422, __('StaffCustomerSupport::support.company_mismatch'));
            }
            if ((int) $report->assigned_bus_company_id === $companyId) {
                return $report;
            }
            $this->checkVersion($report, $version);
            abort_if($report->status === 'resolved', 409, __('StaffCustomerSupport::support.closed'));

            return $this->record($actor, $report, ['assigned_bus_company_id' => $companyId, 'assigned_staff_id' => null, 'department_role_id' => null], 'assignment', [
                'previous_bus_company_id' => $report->assigned_bus_company_id, 'bus_company_id' => $companyId,
            ]);
        });
    }

    public function updateAsAdmin(User $actor, int $id, string $status, ?string $response): stdClass
    {
        abort_unless($actor->isAdmin(), 403);
        if (! in_array($status, FeedbackService::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => __('Profile::feedback.validation.status_invalid')]);
        }
        $response = $response === null ? null : trim($response);
        if ($response !== null && mb_strlen($response) > 3000) {
            throw ValidationException::withMessages(['response' => __('Profile::feedback.validation.response_length')]);
        }

        return DB::transaction(function () use ($actor, $id, $status, $response): stdClass {
            $report = $this->lockedVisible($actor, $id);
            $current = array_search($report->status, FeedbackService::STATUSES, true);
            $next = array_search($status, FeedbackService::STATUSES, true);
            if ($current === false || $next < $current || $next > $current + 1) {
                throw ValidationException::withMessages(['status' => __('Profile::feedback.validation.status_transition')]);
            }
            $changes = ['status' => $status, 'response' => $response];
            if ($status !== $report->status) {
                $changes[$status === 'reviewing' ? 'reviewing_at' : 'resolved_at'] = now();
            }

            return $this->record($actor, $report, $changes, 'admin_update', ['response' => $response]);
        });
    }

    public function departments(User $actor): Collection
    {
        return DB::table('roles')->where('roles.bus_company_id', $actor->bus_company_id)
            ->whereExists(function (Builder $query): void {
                $query->selectRaw('1')->from('permission_role')->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->whereColumn('permission_role.role_id', 'roles.id')->where('permissions.slug', 'support.respond')->where('permissions.is_active', true);
            })->whereExists(function (Builder $query): void {
                $query->selectRaw('1')->from('permission_role')->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->whereColumn('permission_role.role_id', 'roles.id')->where('permissions.slug', 'support.view')->where('permissions.is_active', true);
            })->whereExists(function (Builder $query) use ($actor): void {
                $query->selectRaw('1')->from('role_user')->join('users', 'users.id', '=', 'role_user.user_id')
                    ->whereColumn('role_user.role_id', 'roles.id')->where('users.bus_company_id', $actor->bus_company_id)
                    ->where('users.is_active', true)->whereNotNull('users.email_verified_at')
                    ->whereExists(function (Builder $staff): void {
                        $staff->selectRaw('1')->from('role_user as staff_membership')->join('roles as staff_role', 'staff_role.id', '=', 'staff_membership.role_id')
                            ->whereColumn('staff_membership.user_id', 'users.id')->where('staff_role.slug', 'staff');
                    });
            })->orderBy('roles.name')->get(['roles.id', 'roles.name']);
    }

    public function action(User $actor, int $id, string $kind, array $input): stdClass
    {
        $this->authorizeResponse($actor);

        return DB::transaction(function () use ($actor, $id, $kind, $input): stdClass {
            $report = $this->lockedVisible($actor, $id);
            $hash = hash('sha256', json_encode([$kind, $input['content'], $input['department_role_id'] ?? null], JSON_THROW_ON_ERROR));
            $existing = DB::table('customer_support_events')->where('report_id', $id)->where('idempotency_key', $input['idempotency_key'])->first();
            if ($existing !== null) {
                abort_unless((int) $existing->actor_id === $actor->id && hash_equals($existing->request_hash, $hash), 409, __('StaffCustomerSupport::support.retry_conflict'));

                return $existing;
            }
            $this->checkVersion($report, (int) $input['version']);
            abort_unless($report->status === 'reviewing' && (int) $report->assigned_staff_id === $actor->id, 409, __('StaffCustomerSupport::support.claim_first'));
            $changes = [];
            $event = ['internal_note' => $input['content'], 'idempotency_key' => $input['idempotency_key'], 'request_hash' => $hash];
            if ($kind === 'transfer') {
                $department = $this->departments($actor)->firstWhere('id', (int) $input['department_role_id']);
                abort_unless($department !== null && (int) $report->department_role_id !== $department->id, 422, __('StaffCustomerSupport::support.invalid_department'));
                $changes = ['status'          => 'waiting', 'assigned_staff_id' => null, 'department_role_id' => $department->id,
                    'assigned_bus_company_id' => $actor->bus_company_id, 'transferred_at' => now()];
                $event['department_name'] = $department->name;
                $event['response'] = __('StaffCustomerSupport::support.transfer_customer');
            }
            $updated = $this->record($actor, $report, $changes, $kind, $event);
            if ($kind === 'transfer') {
                $this->notifyCustomer($report, $event['response']);
            }

            return DB::table('customer_support_events')->where('report_id', $id)->where('version', $updated->version)->first();
        });
    }

    private function notifyCustomer(stdClass $report, string $message): void
    {
        DB::table('notifications')->insert([
            'id'              => (string) Str::uuid(), 'type' => 'customer-support-response',
            'notifiable_type' => User::class, 'notifiable_id' => $report->user_id,
            'data'            => json_encode(['title' => __('StaffCustomerSupport::support.notification_title'), 'message' => $message,
                'url'                                 => route('profile.feedback.show', $report->id), 'report_id' => $report->id], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function authorizeResponse(User $actor): void
    {
        abort_unless($actor->hasPermissionTo('support.respond'), 403);
    }

    private function lockedVisible(User $actor, int $id): stdClass
    {
        $report = DB::table('customer_feedback_reports')->where('id', $id)->lockForUpdate()->first();
        abort_if($report === null || ! $this->visible($actor)->where('feedback.id', $id)->exists(), 404);

        return $report;
    }

    private function checkVersion(stdClass $report, int $version): void
    {
        abort_unless((int) $report->version === $version, 409, __('StaffCustomerSupport::support.stale'));
    }

    private function record(User $actor, stdClass $report, array $changes, string $kind, array $event = []): stdClass
    {
        $version = (int) $report->version + 1;
        DB::table('customer_feedback_reports')->where('id', $report->id)->update([
            ...$changes, 'version' => $version, 'updated_at' => now(),
        ]);
        DB::table('customer_support_events')->insert([
            'report_id' => $report->id, 'actor_id' => $actor->id, 'actor_name' => mb_substr($actor->name, 0, 120),
            'kind'      => $kind, 'previous_status' => $report->status, 'status' => $changes['status'] ?? $report->status,
            'version'   => $version, 'created_at' => now(), ...$event,
        ]);

        return DB::table('customer_feedback_reports')->where('id', $report->id)->first();
    }
}
