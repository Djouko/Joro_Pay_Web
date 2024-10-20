@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Examples") }} </h1>
        <p class="pb-10">{{ __("For code examples and implementation guides, please refer to the “Examples” section on our developer portal.") }} <a href="https://github.com/appdevsx/QRPay-Gateway-Example.git" target="_blank" class="highlight text--base">{{ __("Go to GitHub Repository") }}</a></p>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.best.practices') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("Best Practices") }}</a>
            <a href=" {{ setRoute('developer.faq') }}" class="right">{{ __("FAQ") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
