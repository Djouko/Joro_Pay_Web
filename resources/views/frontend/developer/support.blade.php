@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">{{ __("Support") }}</h1>
        <p class="pb-10">{{ __("If you encounter any issues or need assistance, please reach out to our dedicated developer support team") }} <a href="{{ setRoute('contact') }}" class="text-decoration-underline fw-bold">{{ __("Contact Us") }}</a></p>
        <p>{{ __("Thank you for choosing") }} {{ __( @$basic_settings->site_name) }} {{ __("Payment Gateway Solutions! We look forward to seeing your integration thrive and provide a seamless payment experience for your valued customers.") }}</p>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.faq') }}" class="left"><i class="las la-arrow-left me-1"></i> {{ __("FAQ") }}</a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
