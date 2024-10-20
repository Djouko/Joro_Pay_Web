@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')

<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Authentication") }}</h1>
        <p>{{ __("To access the") }} {{ __(@$basic_settings->site_name) }} {{ __("Developer API, you’ll need an API key. You can obtain your API key by logging in to your") }} {{ __(@$basic_settings->site_name) }} {{ __("merchant account and navigating to the API section. Collect") }} <strong class="fst-italic">{{ __("Client/Primary Key") }}</strong> & <strong class="fst-italic">{{ __("secret Key") }}</strong> {{ __("Carefully. Keep your API key confidential and do not share it publicly.") }}</p>
        <div class="text-center">
            <span class="alert--custom">{{ __("If you don't have any merchant account please") }} <a href="{{ setRoute('merchant.register') }}" class="highlight text--base">{{ __("Register") }}</a> {{ __("to continue") }}</span>
        </div>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.prerequisites') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Prerequisites") }}</a>
            <a href="{{ setRoute('developer.base.url') }}" class="right">{{ __("Base URL") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>

    </div>
</div>
@endsection


@push("script")

@endpush
