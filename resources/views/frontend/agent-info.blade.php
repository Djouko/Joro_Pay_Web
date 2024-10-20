@extends('frontend.layouts.master')

@php
    $lang = selectedLang();
    $agent_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AGENT_SECTION);
    $agent = App\Models\Admin\SiteSections::getData( $agent_slug)->first();

    $agent_app_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AGENT_APP_SECTION);
    $agent_app = App\Models\Admin\SiteSections::getData( $agent_app_slug)->first();
@endphp

@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="banner-section bg_img" data-background="{{ asset('public/frontend/') }}/images/banner/agent-bg.jpg">
    <div class="container home-container">
        <div class="row mb-30-none">
            <div class="col-lg-6 col-md-6 mb-30">
                <div class="banner-thumb-area text-center">
                    <img src="{{ get_image(@$agent->value->images->banner_image,'site-section') }}" alt="banner">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 mb-30">
                <div class="banner-content">
                    <span class="banner-sub-titel"><i class="fas fa-qrcode"></i> {{ __(@$agent->value->language->$lang->heading) }}</span>
                    <h1 class="banner-title">{{ __(@$agent->value->language->$lang->sub_heading) }}</h1>
                    <p>{{ __(@$agent->value->language->$lang->details) }}</p>
                    <div class="banner-btn">
                        <a href="{{ setRoute('agent.register') }}" class="btn--base"><i class="las la-user-plus me-1"></i>{{ __("Register") }}</a>
                        <a href="{{ setRoute('agent.login') }}" class="btn--base active"><i class="las la-key me-1"></i>{{ __("Login") }}</a>
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
                    <img src="{{ get_image(@$agent_app->value->images->site_image,'site-section') }}" alt="img">
                </div>
            </div>
            <div class="col-xxl-1 col-xl-1 col-lg-1 d-md-none"></div>
            <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-6 mb-30">
                <div class="content text-sm-center">
                    <h1 class="display-2 fw-bolder mb-10">{{ @$agent_app->value->language->$lang->title }}</h1>
                    <p>{{ @$agent_app->value->language->$lang->details }}</p>
                    <div class="download-btn-area align-items-center d-flex justify-content-sm-center pt-20 m-8-none">
                            <a href="{{ @$agent_app->value->language->$lang->apple_link }}" target="_blank" class="m-8"><img src="{{ get_image(@$agent_app->value->images->appple_store,'site-section') }}" alt="img"></a>
                            <a href="{{ @$agent_app->value->language->$lang->google_link }}" target="_blank" class="m-8"><img src="{{ get_image(@$agent_app->value->images->google_play,'site-section') }}" alt="img"></a>
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
