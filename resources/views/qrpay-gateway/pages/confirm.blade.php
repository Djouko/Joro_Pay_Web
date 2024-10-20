@extends('qrpay-gateway.layouts.master')

@push('css')
<style>
    .account.payment-confirm .account-wrapper{
        width: 800px;
    }
    @media only screen and (max-width: 991px){
    .account.payment-confirm .account-wrapper {
        width: 100%;
        padding: 15px
    }
    }
</style>
@endpush

@section('content')
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Payment-preview
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

    <section class="checkout-section bg_img" data-background="{{ asset('public/frontend/') }}/images/checkout/checkout-bg-1.jpg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="checkout-wrapper">
                        <div class="checkout-top-area">
                            <div class="profile-area">
                                <img src="{{ get_image($user->image,'user-profile','profile') }}" alt="client">
                            </div>
                            <div class="logo-area">
                                <img src="{{ get_fav($basic_settings) }}" alt="logo">
                            </div>
                            <span class="price-area">{{ get_amount($request_record->amount,$request_record->currency,3)  }}</span>
                        </div>
                        <div class="card-wrapper d-none" ></div>

                        <form class="checkout-form" method="POST" action="{{ $submit_form_url }}" id="payment-form">
                            @csrf
                            <input type="hidden" name="trx_type">
                            <div class="account-info-wrapper">
                                <h5 class="title">{{ @$user->fullname }}</h5>
                                @php
                                    $address = (object)$user->address;
                                @endphp
                                <span>{{  @$address->address  }}</span>
                            </div>
                            <div class="switch-wrapper">
                                <h4 class="title">{{ __("Pay with") }}</h4>
                            </div>
                            <div class="radio-wrapper">
                                @if(@$gateway_setting->wallet_status == true)
                                <div class="radio-item">
                                    <div class="radio-item-wrapper">
                                        <input type="radio" name="checkout" checked id="wallet">
                                        <label for="wallet">
                                          <div class="icon"><i class="fas fa-wallet"></i></div>
                                          <div class="content">
                                            <h6 class="title">{{ __("Wallet Balance") }}</h6>
                                            <span>{{ __("Pay hassle-free using your available Wallet Balance.") }}</span>
                                          </div>
                                        </label>
                                    </div>
                                    @php
                                        $balance = (object)$user->wallet;
                                    @endphp
                                    <span class="amount-area">{{ get_amount($balance->balance,$request_record->currency,3)  }}</span>
                                </div>
                                @endif
                                @if(@$gateway_setting->virtual_card_status == true)
                                <div class="radio-item">
                                    <div class="radio-item-wrapper">
                                        <input type="radio" name="checkout" id="virtual_card">
                                        <label for="virtual_card">
                                          <div class="icon"><i class="fas fa-credit-card"></i></div>
                                          <div class="content">
                                            <h6 class="title">{{ __("Virtual Card") }}</h6>
                                            <span>{{ __("Pay hassle-free using your available Virtual Card Balance.") }}</span>
                                          </div>
                                        </label>
                                    </div>
                                    @if(isset($user->payment_type))
                                        @php
                                            $card_balance = (object)$user->virtual_card;
                                            $card_balance = $card_balance->amount??0;
                                        @endphp
                                        <span class="amount-area">{{ get_amount(@$card_balance,$request_record->currency,3)  }}</span>
                                    @else
                                        @php
                                            if(virtual_card_system('flutterwave')){
                                                $card_balance = (object)$user->virtual_card;
                                            }elseif(virtual_card_system('sudo')){
                                                $card_balance = (object)$user->virtual_card_sudo;
                                            }elseif(virtual_card_system('stripe')){
                                                $card_balance = (object)$user->virtual_card_stripe;
                                            }

                                            $card_balance = $card_balance->amount??0;
                                        @endphp
                                        <span class="amount-area">{{ get_amount(@$card_balance,$request_record->currency,3)  }}</span>
                                    @endif


                                </div>
                                @endif
                                @if(@$gateway_setting->master_visa_status == true)
                                <div class="radio-item">
                                    <div class="radio-item-wrapper">
                                        <input type="radio" name="checkout" id="title3" value="title3">
                                        <label for="title3">
                                          <div class="icon"><i class="fab fa-cc-visa"></i></div>
                                          <div class="content">
                                            <h6 class="title">{{ __("Master / Visa Card") }}</h6>
                                            <span>{{ __("Enjoy the flexibility of paying with it.") }}</span>
                                          </div>
                                        </label>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="input-wrapper" id="title3-fields">
                                <div class="row mb-20-none">
                                    <div class="col-lg-6 form-group">
                                        <input type="text" class="form--control custom-input" placeholder="Cardholder Name" name="name" autocomplete="off" autofocus>
                                    </div>
                                    <div class="col-lg-6 form-group card-number">
                                        <input type="tel" class="form--control custom-input" placeholder="Card number" name="cardNumber" autocomplete="off" autofocus>
                                        <i class="las la-credit-card"></i>
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <input type="tel" id="expiryDateInput" class="form--control custom-input" placeholder="Expiry Date" name="cardExpiry" autocomplete="off" >
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <input type="password" class="form--control custom-input" placeholder="CVV"  name="cardCVC" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            @if(@$gateway_setting->wallet_status === false && @$gateway_setting->virtual_card_status === false && @$gateway_setting->master_visa_status === false)
                            <div class="btn-area">
                                <button type="button" class="btn--base w-100"><i class="las la-angle-left ms-1"></i>{{ __("All Payment Option Diabled") }}</button>
                            </div>
                            @else
                            <div class="btn-area">
                                <button type="submit" class="btn--base w-100">{{ __("Continue to Review Order") }}<i class="las la-angle-right ms-1"></i></button>
                            </div>
                            @endif
                            <a href="{{ url($request_record->data->cancel_url . "?token=".$token) }}" class="home-btn">{{ __("Cancel and return to Home") }}</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @php
            $lang = selectedLang();
            $footer_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::FOOTER_SECTION);
            $footer = App\Models\Admin\SiteSections::getData( $footer_slug)->first();
            $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
            $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('status',1)->get();
        @endphp
        <div class="bottom-area">
            <div class="bottom-wrapper">
                <p>{{ __(@$footer->value->language->$lang->footer_text) }} <a class="fw-bold" href="{{ setRoute('index') }}"> {{ $basic_settings->site_name }}</a></p>
                <ul class="bottom-area-list">
                    @foreach ($policies ?? [] as $key=> $data)
                    <li><a href="{{ setRoute('useful.link',$data->slug) }}" target="_blank">{{ @$data->title->language->$lang->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        End Payment-preview
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@endsection


@push('script')
<script src="{{ asset('public/frontend/') }}/js/card.js"></script>
<script>
    (function ($) {
    "use strict";
    var card = new Card({
        form: '#payment-form',
        container: '.card-wrapper',
        formSelectors: {
            numberInput: 'input[name="cardNumber"]',
            expiryInput: 'input[name="cardExpiry"]',
            cvcInput: 'input[name="cardCVC"]',
            nameInput: 'input[name="name"]'
        }
    });
    })(jQuery);
</script>
 <script>
    $(".radio-item").click(function() {
    var inputRadio = $(this).find("input[type=radio]");
    $(".radio-wrapper").find("input[type=radio]").attr("checked",false);
    inputRadio.attr("checked",true);
    var radioValue = inputRadio.val();
    $(".input-wrapper").hide();
    $(".input-wrapper").removeClass("active");
    // $(".input-wrapper").slideUp();
    var makeId = radioValue + "-fields";
    $("#"+makeId).show(200);
    $("#"+makeId).addClass("active");
    // $("#"+makeId).slideDown();
    });
    //get trx type
    $(document).ready(function(){
        $("input[name=trx_type]").val('{{ payment_gateway_const()::WALLET }}')
    });
   $("#wallet").click(function(){
        $("input[name=trx_type]").val('{{ payment_gateway_const()::WALLET }}')
    });
    $("#virtual_card").click(function(){
        $("input[name=trx_type]").val('{{ payment_gateway_const()::VIRTUAL }}')
    });
    $("#title3").click(function(){
        $("input[name=trx_type]").val('{{ payment_gateway_const()::MASTER }}')
    });


 </script>
@endpush
