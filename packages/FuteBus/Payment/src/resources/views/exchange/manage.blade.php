@extends('Dashboard::layouts.admin')
@section('title', __('Payment::exchange.title'))
@section('content')
    @include('Payment::exchange.manage-content')
@endsection
