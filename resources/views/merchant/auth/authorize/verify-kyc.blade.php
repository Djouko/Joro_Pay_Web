@extends('merchant.layouts.user_auth')

@push('css')

@endpush

@section('content')


    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account">
    <div class="account-area">
        <div class="account-wrapper">
            <div class="account-logo text-center">
                <a href="{{ setRoute('index') }}" class="site-logo">
                    <img src="{{ get_logo_merchant($basic_settings) }}"  data-white_img="{{ get_logo_merchant($basic_settings,'white') }}"
                            data-dark_img="{{ get_logo_merchant($basic_settings,'dark') }}"
                                alt="site-logo">
                </a>
            </div>
            <h3 class="title">{{ __("KYC Verification") }}</h3>
            <p>{{ __("Please submit your KYC information with valid data.") }}</p>

            <form action="{{ setRoute('merchant.authorize.kyc.submit') }}" class="account-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row ml-b-20">

                    @include('merchant.components.generate-kyc-fields',['fields' => $kyc_fields])

                    <div class="col-lg-12 form-group">
                        <div class="forgot-item">
                            <label>{{ __("Back to") }} <a href="{{ setRoute('merchant.dashboard') }}" class="text--base">{{ __("Dashboard") }}</a></label>
                        </div>
                    </div>
                    <div class="col-lg-12 form-group text-center">
                        <button type="submit" class="btn--base w-100">{{ __("Submit") }}</button>
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
