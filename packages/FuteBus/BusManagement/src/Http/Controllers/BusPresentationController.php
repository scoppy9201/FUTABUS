<?php

declare(strict_types=1);

namespace FuteBus\BusManagement\Http\Controllers;

use FuteBus\BusManagement\Http\Requests\BusPresentationRequest;
use FuteBus\BusManagement\Services\BusPresentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class BusPresentationController extends Controller
{
    public function update(BusPresentationRequest $request, BusPresentationService $service, int $id): RedirectResponse
    {
        $service->update($id, $request->validated());

        return redirect()->route('bus-management.buses.index')->with('success', __('BusManagement::app.presentation_saved'));
    }
}
