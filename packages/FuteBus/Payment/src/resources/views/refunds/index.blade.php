@extends('Dashboard::layouts.admin')
@section('title', __('Payment::refunds.title'))
@section('content')
    @include('Payment::refunds.list')
@endsection
