<?php

declare(strict_types=1);

namespace FuteBus\Notification\Http\Middleware;

use App\Models\User;
use Closure;
use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\Notification\Services\CompanyNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CaptureCompanyActivity
{
    private const CAPTURED_ATTRIBUTE = 'futabus.company_activity_notification_recorded';

    public function __construct(private readonly CompanyNotificationService $notifications) {}

    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user('sanctum') ?? $request->user();
        $routeName = $request->route()?->getName();
        if ($request->attributes->get(self::CAPTURED_ATTRIBUTE)
            || ! $actor instanceof User
            || ! $this->isWrite($request)
            || ! $this->isManagementActor($actor)
            || $this->isIgnoredRoute($routeName)) {
            return $next($request);
        }

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->recordOnce($request, $actor, $routeName, false);

            throw $exception;
        }

        $newFlash = $request->hasSession() ? $request->session()->get('_flash.new', []) : [];
        $hasErrorFlash = in_array('errors', $newFlash, true)
            || in_array('warning', $newFlash, true)
            || in_array('error', $newFlash, true);
        $status = $response->getStatusCode();
        $failedStatus = $status >= 400;

        $this->recordOnce($request, $actor, $routeName, ! ($hasErrorFlash || $failedStatus));

        return $response;
    }

    private function notify(User $actor, ?string $routeName, bool $success, Request $request): void
    {
        try {
            $companyId = $actor->bus_company_id;
            if ($companyId === null && ! $actor->isCustomer()) {
                $companyId = BusCompanyRecord::query()->toBase()
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower($actor->email)])
                    ->value('id');
            }
            if ($companyId === null && $actor->isAdmin()) {
                $companyId = BusCompanyRecord::query()->toBase()->where('code', 'FUTA')->value('id');
            }
            if ($companyId === null) {
                return;
            }

            $operation = match ($request->method()) {
                'POST' => 'create', 'PUT', 'PATCH' => 'update', 'DELETE' => 'delete', default => 'manage',
            };
            $area = match (true) {
                str_starts_with((string) $routeName, 'api.v1.admin.')              => 'vehicle',
                str_starts_with((string) $routeName, 'bus-management.')            => 'vehicle',
                str_starts_with((string) $routeName, 'trip-management.schedules.') => 'schedule',
                str_starts_with((string) $routeName, 'trip-management.trips.')     => 'trip',
                str_starts_with((string) $routeName, 'route-management.')          => 'route',
                str_starts_with((string) $routeName, 'staff.refunds.')             => 'refund',
                str_starts_with((string) $routeName, 'refunds.')                   => 'refund',
                str_starts_with((string) $routeName, 'staff.ticket-sales.')        => 'ticket',
                str_starts_with((string) $routeName, 'api.v1.staff.sales.')        => 'ticket',
                str_starts_with((string) $routeName, 'staff-accounts.')            => 'team',
                str_starts_with((string) $routeName, 'access-management.')         => 'team',
                default                                                            => 'company',
            };

            $this->notifications->notifyManagers((int) $companyId, [
                'title'   => __($success ? 'Notification::app.activity_success_title' : 'Notification::app.activity_failure_title'),
                'message' => __($success ? 'Notification::app.activity_success_message' : 'Notification::app.activity_failure_message', [
                    'actor'  => $actor->name,
                    'action' => __('Notification::app.'.$area.'_'.$operation),
                ]),
            ], $actor);
        } catch (Throwable $exception) {
            Log::warning('Could not capture bus company activity.', ['exception' => $exception::class]);
        }
    }

    private function isWrite(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function isManagementActor(User $actor): bool
    {
        return ! $actor->isCustomer()
            && ($actor->bus_company_id !== null
                || $actor->hasRole(['bus-company', 'staff'])
                || $actor->isAdmin());
    }

    private function isIgnoredRoute(?string $routeName): bool
    {
        if ($routeName === null) {
            return false;
        }

        return $routeName === 'logout'
            || str_starts_with($routeName, 'notifications.')
            || str_starts_with($routeName, 'profile.')
            || str_starts_with($routeName, 'api.v1.me.')
            || str_starts_with($routeName, 'api.v1.staff.me.')
            || in_array($routeName, ['api.v1.staff.previews.store', 'api.v1.staff.quotes.store'], true);
    }

    private function recordOnce(Request $request, User $actor, ?string $routeName, bool $success): void
    {
        if ($request->attributes->get(self::CAPTURED_ATTRIBUTE)) {
            return;
        }

        $request->attributes->set(self::CAPTURED_ATTRIBUTE, true);
        $this->notify($actor, $routeName, $success, $request);
    }
}
