<?php

declare(strict_types=1);

namespace FuteBus\Notification\Services;

use App\Models\User;
use FuteBus\BusCompanyManagement\Models\BusCompany as BusCompanyRecord;
use FuteBus\Notification\Notifications\SystemNotice;
use Illuminate\Support\Facades\Log;

class CompanyNotificationService
{
    public function notifyManagers(int $companyId, array $data, ?User $actor = null): void
    {
        try {
            $company = BusCompanyRecord::query()->toBase()->where('id', $companyId)->first(['email', 'code']);

            $recipients = User::query()->where('is_active', true)
                ->where(function ($query) use ($companyId, $company): void {
                    $query->where('bus_company_id', $companyId);

                    if (is_string($company?->email) && $company->email !== '') {
                        $query->orWhereRaw('LOWER(email) = ?', [mb_strtolower($company->email)]);
                    }

                    if ($company?->code === 'FUTA') {
                        $query->orWhereHas('roles', fn ($roles) => $roles->where('roles.slug', 'admin'));
                    }
                })
                ->get()
                ->keyBy('id');

            if ($actor?->exists && $actor->is_active) {
                $recipients->put($actor->getKey(), $actor);
            }

            $recipients->each(function (User $recipient) use ($data): void {
                $recipient->notify(new SystemNotice($data));
            });
        } catch (\Throwable $exception) {
            Log::warning('Could not store a bus company notification.', ['exception' => $exception::class]);
        }
    }
}
