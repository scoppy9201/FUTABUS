<?php

declare(strict_types=1);

namespace Staff\Dashboard\Services;

use App\Models\User;
use FuteBus\Core\Models\FaqQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffDashboardService
{
    public function data(User $staff): array
    {
        $avatar = $staff->avatar;
        $avatarUrl = is_string($avatar) && str_starts_with($avatar, 'avatars/') && ! str_contains($avatar, '..') && Storage::disk('public')->exists($avatar)
            ? Storage::disk('public')->url($avatar) : null;

        $navigation = [];
        foreach (['trips' => ['trip.view', 'trip-management.trips.index'], 'reports' => ['report.view', 'dashboard.section']] as $key => [$permission, $route]) {
            if ($staff->bus_company_id && $staff->hasPermissionTo($permission)) {
                $navigation[] = ['key' => $key, 'url' => route($route, $key === 'reports' ? ['section' => 'reports'] : [])];
            }
        }

        $faq = FaqQuestion::active()->whereHas('category', fn ($query) => $query->active())
            ->with('category')->orderBy('sort_order')->orderBy('id')->limit(4)->get()
            ->map(fn (FaqQuestion $question): array => ['id' => $question->id, 'question' => $question->localizedQuestion(), 'url' => route('faq-category', $question->category->slug)]);

        $notifications = $staff->notifications()->latest()->limit(4)->get()->map(fn ($notification): array => [
            'id'         => $notification->id,
            'title'      => is_string($notification->data['title'] ?? null) ? $notification->data['title'] : '',
            'message'    => is_string($notification->data['message'] ?? null) ? $notification->data['message'] : '',
            'created_at' => $notification->created_at->toIso8601String(),
            'read_at'    => $notification->read_at?->toIso8601String(),
        ]);

        return [
            'employee' => ['id' => $staff->id, 'name' => $staff->name, 'avatar_url' => $avatarUrl],
            'groups'   => [
                'tickets' => [
                    'sale'     => $staff->bus_company_id !== null && $staff->hasPermissionTo('booking.create'),
                    'exchange' => false, 'cancellation' => false,
                    'lookup'   => $staff->bus_company_id !== null && $staff->hasPermissionTo('ticket.view'),
                ],
                'support' => ['intake' => false, 'requests' => false, 'resolution' => false],
            ],
            'navigation'       => $navigation,
            'statistics'       => ['trips_today' => null, 'tickets_today' => $this->ticketsToday($staff), 'support_requests' => null, 'processing_requests' => null],
            'support_requests' => null,
            'notifications'    => $notifications,
            'faq'              => $faq,
        ];
    }

    private function ticketsToday(User $staff): ?int
    {
        if ($staff->bus_company_id === null || (! $staff->hasPermissionTo('ticket.view') && ! $staff->hasPermissionTo('booking.create'))) {
            return null;
        }

        $start = now()->startOfDay();

        return DB::table('tickets')
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('trips', 'trips.id', '=', 'tickets.trip_id')
            ->join('routes', 'routes.id', '=', 'trips.route_id')
            ->join('buses', 'buses.id', '=', 'trips.bus_id')
            ->whereColumn('bookings.trip_id', 'tickets.trip_id')
            ->where('trips.bus_company_id', $staff->bus_company_id)
            ->where('routes.bus_company_id', $staff->bus_company_id)
            ->where('buses.bus_company_id', $staff->bus_company_id)
            ->where('bookings.status', 'confirmed')
            ->whereIn('tickets.status', ['active', 'used'])
            ->where('tickets.created_at', '>=', $start)
            ->where('tickets.created_at', '<', $start->copy()->addDay())
            ->count('tickets.id');
    }
}
