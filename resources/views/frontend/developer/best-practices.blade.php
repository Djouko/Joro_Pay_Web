@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Best Practices") }}</h1>
        <p class="pb-10">{{ __("To ensure a smooth integration process and optimal performance, follow these best practices") }}:</p>
        <ol>
            <li>{{ __("Use secure HTTPS connections for all API requests.") }}</li>
            <li>{{ __("Implement robust error handling to handle potential issues gracefully.") }}</li>
            <li>{{ __("Regularly update your integration to stay current with any API changes or enhancements.") }}</li>
        </ol>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute("developer.error.handling") }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Error Handling") }}</a>
            <a href="{{ setRoute('developer.examples') }}" class="right">{{ __("Examples") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
