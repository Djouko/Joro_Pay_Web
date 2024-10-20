@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Response Codes") }}</h1>
        <p>{{ __(@$basic_settings->site_name) }} {{__("API responses include standard HTTP status codes to indicate the success or failure of a request. Successful responses will have a status code of")}} <strong>{{ __("200 OK") }}</strong>,  {{ __("while various error conditions will be represented by different status codes along with error messages in the response body.") }}</p>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute("developer.check.status.payment") }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Check Payment Status") }}</a>
            <a href="{{ setRoute("developer.error.handling") }}" class="right">{{ __("Error Handling") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
