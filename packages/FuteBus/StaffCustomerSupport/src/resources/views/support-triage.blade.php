@extends('core::layouts.home')
@section('title', __('StaffCustomerSupport::support.triage'))
@section('content')
    <main class="mx-auto max-w-5xl space-y-5 px-4 py-8">
        <header><h1 class="text-2xl font-bold text-futa-orange">{{ __('StaffCustomerSupport::support.triage') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('StaffCustomerSupport::support.triage_intro') }}</p></header>
        @if(session('status'))<p role="status" class="rounded-lg bg-green-50 p-4 text-green-800">{{ session('status') }}</p>@endif
        @foreach($errors->all() as $error)<p role="alert" class="text-sm text-red-700">{{ $error }}</p>@endforeach
        @forelse($reports as $item)
            <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="font-bold">PA-{{ str_pad((string) $item->id, 6, '0', STR_PAD_LEFT) }} · {{ $item->subject }}</h2>
                <p class="mt-2 whitespace-pre-wrap wrap-break-word text-sm text-gray-600">{{ $item->message }}</p>
                <p class="mt-3 text-sm">{{ $item->booking_code ?? __('StaffCustomerSupport::support.no_booking') }} · {{ __('Profile::feedback.status.'.$item->status) }}</p>
                <p class="mt-1 text-sm">{{ __('StaffCustomerSupport::support.company') }}: {{ $item->company_name ?? __('StaffCustomerSupport::support.unassigned') }}</p>
                @if($item->booking_id !== null && ($item->trip_company !== $item->route_company || $item->trip_company !== $item->bus_company))
                    <p class="mt-3 text-sm text-red-700">{{ __('StaffCustomerSupport::support.company_mismatch') }}</p>
                @endif
                @if($item->status !== 'resolved')
                    <form action="{{ route('staff.support-triage.assign', ['report' => $item->id]) }}" method="POST" class="mt-4 flex flex-wrap gap-3">
                        @csrf @method('PUT')
                        <input type="hidden" name="version" value="{{ $item->version }}">
                        <label class="text-sm font-semibold">{{ __('StaffCustomerSupport::support.company') }}
                            <select name="bus_company_id" required class="ml-2 h-10 rounded-lg border border-gray-200 bg-white px-3 font-normal">
                                <option value="">{{ __('StaffCustomerSupport::support.choose') }}</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" @selected((int) $item->assigned_bus_company_id === $company->id)>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="rounded-lg bg-futa-orange px-4 py-2 font-semibold text-white">{{ __('StaffCustomerSupport::support.assign') }}</button>
                    </form>
                @endif
            </article>
        @empty
            <x-core::empty-state class="py-10 text-center text-gray-500" :title="__('StaffCustomerSupport::support.empty')" compact />
        @endforelse
        <x-core::data-pagination :paginator="$reports" />
    </main>
@endsection
