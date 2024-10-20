@extends('frontend.layouts.master')

@php
    $lang = selectedLang();
    $banner_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BANNER_SECTION);
    $banner = App\Models\Admin\SiteSections::getData( $banner_slug)->first();
    $banner_floting_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BANNER_FLOTING);
    $banner_floting = App\Models\Admin\SiteSections::getData( $banner_floting_slug)->first();
    $service_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::SERVICE_SECTION);
    $service = App\Models\Admin\SiteSections::getData( $service_slug)->first();
    $blog_section_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BLOG_SECTION);
    $blog_section = App\Models\Admin\SiteSections::getData( $blog_section_slug)->first();
@endphp
@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<section class="banner-section bg_img" data-background="{{ asset('public/frontend/') }}/images/banner/bg-1.jpg">
    <div class="container home-container">
        <div class="row mb-30-none">
            <div class="col-lg-6 col-md-6 mb-30">
                <div class="banner-thumb-area text-center">
                    <img src="{{ get_image(@$banner->value->images->banner_image,'site-section') }}" alt="banner">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 mb-30">
                <div class="banner-content">
                    <span class="banner-sub-titel"><i class="fas fa-qrcode"></i>{{ __(@$banner->value->language->$lang->title) }}</span>
                    <h1 class="banner-title">{{ __(@$banner->value->language->$lang->heading) }}</h1>
                    <p>{{ __(@$banner->value->language->$lang->sub_heading) }}</p>
                    <div class="app-btn-area">
                        <a href="{{ @$app_urls->android_url }}" class="app-btn" target="_blank">
                            <div class="icon">
                                <img src="{{ asset('public/frontend/') }}/images/app/play-store.png" alt="play-store">
                            </div>
                            <div class="content">
                                <span class="sub-title">{{ __("Get It On") }}</span>
                                <h5 class="title">{{ __("google Play") }}</h5>
                            </div>
                        </a>
                        <a href="{{ @$app_urls->iso_url }}" class="app-btn" target="_blank">
                            <div class="icon">
                                <img src="{{ asset('public/frontend/') }}/images/app/apple-store.png" alt="play-store">
                            </div>
                            <div class="content">
                                <span class="sub-title">{{ __("Download On The") }}</span>
                                <h5 class="title">{{ __("Apple Store") }}</h5>
                            </div>
                        </a>
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
    Start Banner floting section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="banner-floting-section">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="banner-floting-right-area">
                    <ul class="banner-floting-right-list">
                        @if(isset($banner_floting->value->items))
                            @foreach($banner_floting->value->items ?? [] as $key => $item)
                            <li><i class="fas fa-check"></i>{{ @$item->language->$lang->name }}</li>
                            @endforeach
                        @endif
                    </ul>
                    <div class="banner-floting-right-content">
                        <h3 class="title">{{ __(@$banner_floting->value->language->$lang->title) }}</h3>
                        <p>{{ __(@$banner_floting->value->language->$lang->sub_title) }}</p>
                        <a href="{{url('/').'/'.@$banner_floting->value->language->$lang->button_link}}" class="link-area">{{ __(@$banner_floting->value->language->$lang->button_name) }} <i class="fas fa-long-arrow-alt-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Banner floting section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start how it's works section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.how-work')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End how it's works section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.about')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Security section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End service section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@include('frontend.partials.security-section')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start map section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.map-section')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End map section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start why choose us section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.choose-section')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End why choose us section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start testimonial
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.testimonials')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End testimonial
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Brand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.brand-section')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Brand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@endsection


@push("script")

@endpush
