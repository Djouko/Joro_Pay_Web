@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Prerequisites") }}</h1>
        <p>{{ __("Before you begin integrating the") }} {{ __(@$basic_settings->site_name) }} {{ __("Developer API, make sure you have") }}:</p>
        <ol class="pt-1">

            <li>{{ __("An active") }} {{ __(@$basic_settings->site_name) }} {{ __("merchant account.") }}</li>
            <li>{{ __("Basic knowledge of API integration and web development with PHP & Laravel.") }}</li>
            <li>{{ __("A secure and accessible web server to handle API requests.") }}</li>
        </ol>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.index') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Introduction") }}</a>
            <a href="{{ setRoute('developer.authentication') }}" class="right">{{ __("Authentication") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
