@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Base URL") }}</h1>
        <p>{{ __("The base URL for API requests is") }}:</p>
        <div class="mb-10">
            <span>{{ __("For PRODUCTION Mode") }}: </span>
            <code  class="highlight">{{ url('/') }}/pay/api/v1</code>
        </div>
        <div>
            <span>{{ __("For SANDBOX Mode") }}: </span>
            <code class="highlight">{{ url('/') }}/pay/sandbox/api/v1</code>
        </div>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.authentication') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Authentication") }}</a>
            <a href="{{ setRoute('developer.access.token') }}" class="right">{{ __("Access Token") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
