
@extends('agent.layouts.user_auth')

@php
    $lang = selectedLang();
    $auth_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AUTH_SECTION);
    $auth_text = App\Models\Admin\SiteSections::getData( $auth_slug)->first();
@endphp


@section('content')

<section class="account">

    <div class="account-area">
        <div class="account-wrapper">
            <div class="account-logo text-center">
               <a class="site-logo" href="{{ setRoute('index') }}">
                <img src="{{ get_logo_agent($basic_settings) }}"  data-white_img="{{ get_logo_agent($basic_settings,'white') }}"
                data-dark_img="{{ get_logo_agent($basic_settings,'dark') }}"
                    alt="site-logo">
               </a>
            </div>
            <h5 class="title">{{ __("Reset Your Forgotten Password") }}</h5>
            <p>{{ __(@$auth_text->value->language->$lang->forget_text) }}</p>
            <form class="account-form" action="{{ setRoute('agent.password.forgot.send.code') }}" method="POST">
                @csrf
                <div class="row ml-b-20">

                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext"><span>{{ __("Email") }}</span></span>
                            </div>
                            <input type="email" name="credentials" class="form--control checkUser mobile" placeholder="{{ __('enter Email Address') }}">

                        </div>
                        <small class="text-danger exits"></small>
                    </div>

                    <div class="col-lg-12 form-group text-center">
                        <button type="submit"  class="btn--base w-100 btn-loading">{{ __("Continue") }} </button>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label>{{ __("already Have An Account") }} <a href="{{ setRoute('agent.login') }}" class="account-control-btn">{{ __("Login Now") }}</a></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<ul class="bg-bubbles">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>
@endsection

@push('script')
@endpush
