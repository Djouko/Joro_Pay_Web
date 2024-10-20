@extends('qrpay-gateway.layouts.master')

@push('css')

@endpush

@section('content')
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        Start Account
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

    <section class="account">
        <div class="account-area">
            <div class="account-wrapper">
                <div class="account-logo text-center">
                    @if ($payment_gateway_image)
                    {{-- <a class="site-logo" href="javascript:void(0)"><img src="{{ $payment_gateway_image }}" alt="logo"></a> --}}
                    <a class="site-logo" href="javascript:void(0)"><img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                        data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                            alt="site-logo"></a>
                @endif
                </div>
                <h5 class="title">{{ __("Pay With") }} {{ $basic_settings->site_name }}</h5>
                <p>{{ __("With a QrPay account, you're eligible for Purchase, Protection and Rewards") }}</p>
                <form class="account-form bounce-safe" action="{{ $auth_form_submit }}" method="POST">
                    @csrf
                    <div class="row ml-b-20">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text copytext"><span>{{ __("Email") }}</span></span>
                                </div>
                                 <input type="email" name="email" class="form--control" placeholder="{{ __("enter Email Address") }}" required value="{{old('email')}}">
                            </div>
                        </div>
                        <div class="col-lg-12 form-group" id="show_hide_password">
                            <input type="password" required class="form-control form--control" name="password"placeholder="{{ __('enter Password') }}">
                            <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                        </div>

                        <div class="col-lg-12 form-group text-center">
                            <button type="submit" class="btn--base w-100 btn-loading">{{ __("Login Now") }} <i class="las la-arrow-right"></i></button>
                        </div>


                    </div>
                </form>
            </div>
        </div>
    </section>
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        End Account
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
<script>
    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if($('#show_hide_password input').attr("type") == "text"){
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass( "fa-eye-slash" );
                $('#show_hide_password i').removeClass( "fa-eye" );
            }else if($('#show_hide_password input').attr("type") == "password"){
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass( "fa-eye-slash" );
                $('#show_hide_password i').addClass( "fa-eye" );
            }
        });
    });
</script>
@endpush
