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
                <div class="error-wrapper">
                    <div class="error-header text-center">
                        @if ($data['logo'] && $data['logo'] != "")
                            <a class="site-logo" href="javascript:void(0)"><img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                                data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                                    alt="site-logo"></a>
                        @endif
                        <h4 class="title">{{ $data['title'] ?? "" }}</h4>
                        <p>{{ $data['subtitle'] ?? "" }}</p>
                    </div>
                    <div class="return-btn text-center">
                        <a href="{{ $data['link'] ?? "javascript:void(0)" }}" class="btn--base">{{ __($data['button_text'] ?? "") }}</a>
                    </div>
                </div>
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

@endpush
