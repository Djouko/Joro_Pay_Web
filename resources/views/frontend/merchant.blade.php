@extends('frontend.layouts.master')

@php
    $lang = selectedLang();
    $merchant_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::MERCHANT_SECTION);
    $merchant = App\Models\Admin\SiteSections::getData( $merchant_slug)->first();

    $merchant_app_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::MERCHANT_APP_SECTION);
    $merchant_app = App\Models\Admin\SiteSections::getData( $merchant_app_slug)->first();
@endphp

@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="banner-section bg_img" data-background="{{ asset('public/frontend/') }}/images/banner/bg-3.jpg">
    <div class="container home-container">
        <div class="row mb-30-none">
            <div class="col-lg-6 col-md-6 mb-30">
                <div class="banner-thumb-area text-center">
                    <img src="{{ get_image(@$merchant->value->images->banner_image,'site-section') }}" alt="banner">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 mb-30">
                <div class="banner-content">
                    <span class="banner-sub-titel"><i class="fas fa-qrcode"></i> {{ __(@$merchant->value->language->$lang->heading) }}</span>
                    <h1 class="banner-title">{{ __(@$merchant->value->language->$lang->sub_heading) }}</h1>
                    <p>{{ __(@$merchant->value->language->$lang->details) }}</p>
                    <div class="banner-btn">
                        <a href="{{ setRoute('merchant.register') }}" class="btn--base"><i class="las la-user-plus me-1"></i>{{ __("Register") }}</a>
                        <a href="{{ setRoute('merchant.login') }}" class="btn--base active"><i class="las la-key me-1"></i>{{ __("Login") }}</a>
                        <a href="{{ setRoute('developer.index') }}" class="btn--base active"><i class="las la-code me-1"></i>{{ __("developer API") }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start app section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="merchant-app-section pt-120">
    <div class="container">
        <div class="row mb-30-none justify-content-center align-items-center">
            <div class="col-xxl-2 col-xl-2 col-lg-1 d-md-none"></div>
            <div class="col-xxl-4 col-xl-4 col-lg-5 col-md-6 mb-30">
                <div class="thumb">
                    <img src="{{ get_image(@$merchant_app->value->images->site_image,'site-section') }}" alt="img">
                </div>
            </div>
            <div class="col-xxl-1 col-xl-1 col-lg-1 d-md-none"></div>
            <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-6 mb-30">
                <div class="content text-sm-center">
                    <h1 class="display-2 fw-bolder mb-10">{{ @$merchant_app->value->language->$lang->title }}</h1>
                    <p>{{ @$merchant_app->value->language->$lang->details }}</p>
                    <div class="download-btn-area align-items-center d-flex justify-content-sm-center pt-20 m-8-none">
                            <a href="{{ @$merchant_app->value->language->$lang->apple_link }}" target="_blank" class="m-8"><img src="{{ get_image(@$merchant_app->value->images->appple_store,'site-section') }}" alt="img"></a>
                            <a href="{{ @$merchant_app->value->language->$lang->google_link }}" target="_blank" class="m-8"><img src="{{ get_image(@$merchant_app->value->images->google_play,'site-section') }}" alt="img"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End app section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start service section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.service')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End service section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection


@push("script")

@endpush
