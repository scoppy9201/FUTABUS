@extends('Dashboard::layouts.admin')
@section('title', __('BookingTracking::bookings.title'))
@section('content')
    @include('BookingTracking::bookings.list')
@endsection
