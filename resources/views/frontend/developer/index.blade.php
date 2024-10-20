@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
    $intro_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::DEVELOPER_INTRO);
    $intro = App\Models\Admin\SiteSections::getData( $intro_slug)->first();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20 text-center">{{ __(@$intro->value->language->$lang->heading) }}</h1>
        <div class="image-area text-center">
            <img class="w-75" src="{{ get_image(@$intro->value->images->intro_image,'site-section') }}" alt="image">
        </div>
        <p>{{ __(@$intro->value->language->$lang->details) }}</p>
        <h1 class="heading-title mb-20 mt-40">1. {{__("Introduction") }}</h1>
        <p>{{ __(@$intro->value->language->$lang->intro_details) }}</p>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute('developer.prerequisites') }}" class="right">{{ __("Prerequisites") }} <i class="las la-arrow-right ms-1"></i></a>
        </div>

    </div>
</div>
@endsection


@push("script")

@endpush
