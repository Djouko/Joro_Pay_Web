@extends('user.layouts.master')

@php
    $base_code =  getDialCode();
@endphp
@push('css')
    <style>
        .input-group.mobile-code .nice-select{
            border-radius: 5px 0 0 5px !important;
        }
        .input-group.mobile-code .nice-select .list{
            width: auto !important;
        }
        .input-group.mobile-code .nice-select .list::-webkit-scrollbar {
            height: 20px;
            width: 3px;
            background: #F1F1F1;
            border-radius: 10px;
        }

        .input-group.mobile-code .nice-select .list::-webkit-scrollbar-thumb {
            background: #999;
            border-radius: 10px;
        }

        .input-group.mobile-code .nice-select .list::-webkit-scrollbar-corner {
            background: #999;
            border-radius: 10px;
        }
    </style>
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __("Recharge") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('user.mobile.topup.manual.confirm') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span class="fees-show">--</span> <span class="limit-show">--</span></code>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Mobile Topup") }} <span class="text--base">*</span></label>
                                    <select class="form--control" name="topup_type">
                                        @forelse ($topupType ??[] as $type)
                                           <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
                                        @empty
                                           <option  disabled selected value="null">{{ __('No Items Available') }}</option>
                                        @endforelse

                                    </select>
                                </div>

                                <div class="col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Mobile Number") }}<span>*</span></label>
                                    <div class="input-group mobile-code">
                                        <select class="form--control nice-select" name="mobile_code">
                                            @foreach(get_all_countries() ?? [] as $key => $code)
                                                <option value="{{ remove_speacial_char($code->mobile_code) }}"   {{ $code->name === auth()->user()->address->country ? 'selected' :'' }}>+{{ remove_speacial_char($code->mobile_code) }}</option>
                                            @endforeach

                                        </select>
                                        <input type="text" class="form--control number-input" name="mobile_number" placeholder="{{ __("enter Mobile Number") }}" value="{{ old('mobile_number') }}">
                                    </div>

                                </div>
                                <div class="col-xxl-12 col-xl-12 col-lg-12  form-group">
                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select currency" name="currency">
                                            <option value="{{ get_default_currency_code() }}">{{ get_default_currency_code() }}</option>
                                        </select>
                                    </div>

                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="note-area">
                                        <code class="d-block fw-bold">{{ __("Available Balance") }}: {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                                    </div>
                                </div>

                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading mobileTopupBtn">{{ __("Recharge Now") }} <i class="fas fa-mobile ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __("Preview") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-plug"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("TopUp Type") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="topup-type">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-phone-volume"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Mobile Number") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="mobile-number">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-funnel-dollar"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="request-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Charge") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">--</span>
                                </div>
                            </div>

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Payable") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--base last payable-total">--</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";

        $(document).ready(function(){
           getLimit();
           getFees();
           activeItems();
       });
       $("input[name=amount]").keyup(function(){
            getFees();
            activeItems();
       });
       $("input[name=amount]").focusout(function(){
            enterLimit();
       });
       $("input[name=mobile_number]").keyup(function(){
            getFees();
            activeItems();
       });
       $("select[name=topup_type]").change(function(){
            getFees();
            activeItems();
       });
       $("select[name=mobile_code]").change(function(){
            activeItems();
       });
       function getLimit() {
        if(acceptVar().topUp.val() === "null"){
            return false;
        }
           var currencyCode = acceptVar().currencyCode;
           var currencyRate = acceptVar().currencyRate;

           var min_limit = acceptVar().currencyMinAmount;
           var max_limit =acceptVar().currencyMaxAmount;
           if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
               var min_limit_calc = parseFloat(min_limit/currencyRate).toFixed(2);
               var max_limit_clac = parseFloat(max_limit/currencyRate).toFixed(2);
               $('.limit-show').html("{{ __('limit') }} " + min_limit_calc + " " + currencyCode + " - " + max_limit_clac + " " + currencyCode);

               return {
                   minLimit:min_limit_calc,
                   maxLimit:max_limit_clac,
               };
           }else {
               $('.limit-show').html("--");
               return {
                   minLimit:0,
                   maxLimit:0,
               };
           }
       }
       function acceptVar() {
           var selectedVal = $("select[name=currency] :selected");
           var currencyCode = $("select[name=currency] :selected").val();
           var currencyRate = defualCurrencyRate;
           var currencyMinAmount ="{{getAmount($topupCharge->min_limit)}}";
           var currencyMaxAmount = "{{getAmount($topupCharge->max_limit)}}";
           var currencyFixedCharge = "{{getAmount($topupCharge->fixed_charge)}}";
           var currencyPercentCharge = "{{getAmount($topupCharge->percent_charge)}}";
           var topUp = $("select[name=topup_type] :selected");
           var topUpname = $("select[name=topup_type] :selected").data("name");
           var mobileCode = $("select[name=mobile_code] :selected").val();
           var mobileNumber = $("input[name=mobile_number]").val();

           return {
               currencyCode:currencyCode,
               currencyRate:currencyRate,
               currencyMinAmount:currencyMinAmount,
               currencyMaxAmount:currencyMaxAmount,
               currencyFixedCharge:currencyFixedCharge,
               currencyPercentCharge:currencyPercentCharge,
               topUpname:topUpname,
               mobileNumber:mobileNumber,
               mobileCode:mobileCode,
               topUp:topUp,
               selectedVal:selectedVal,

           };
       }
       function feesCalculation() {
           var currencyCode = acceptVar().currencyCode;
           var currencyRate = acceptVar().currencyRate;
           var sender_amount = $("input[name=amount]").val();
           sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

           var fixed_charge = acceptVar().currencyFixedCharge;
           var percent_charge = acceptVar().currencyPercentCharge;
           if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
               // Process Calculation
               var fixed_charge_calc = parseFloat(currencyRate * fixed_charge);
               var percent_charge_calc = parseFloat(currencyRate)*(parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
               var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
               total_charge = parseFloat(total_charge).toFixed(2);
               // return total_charge;
               return {
                   total: total_charge,
                   fixed: fixed_charge_calc,
                   percent: percent_charge,
               };
           } else {
               // return "--";
               return false;
           }
       }
       function getFees() {
            if(acceptVar().topUp.val() === "null"){
                return false;
            }
           var currencyCode = acceptVar().currencyCode;
           var percent = acceptVar().currencyPercentCharge;
           var charges = feesCalculation();
           if (charges == false) {
               return false;
           }
           $(".fees-show").html("{{ __('TopUp Fee') }}: " + parseFloat(charges.fixed).toFixed(2) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(2) + "%  ");
       }
       function activeItems(){
            var topUp = acceptVar().topUp.val();
            if(topUp === undefined || topUp === '' || topUp === null){
                return false;
            }else{
                return getPreview();
            }
       }
       function getPreview() {
               var senderAmount = $("input[name=amount]").val();
               var sender_currency = acceptVar().currencyCode;
               var sender_currency_rate = acceptVar().currencyRate;
               var topup_type = acceptVar().topUpname;
               var mobile_number = acceptVar().mobileNumber;
               var mobile_code = acceptVar().mobileCode;
               senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
               // Sending Amount
               $('.request-amount').text(senderAmount + " " + defualCurrency);
                //topup type
                $('.topup-type').text(topup_type);
               // Fees
                //topup number
                if(mobile_number == '' || mobile_number == 0){
                    $('.mobile-number').text("Ex: +1234567891");
                }else{
                    $('.mobile-number').text("+"+mobile_code+mobile_number);
                }

               // Fees
               var charges = feesCalculation();
               var total_charge = 0;
               if(senderAmount == 0){
                   total_charge = 0;
               }else{
                   total_charge = charges.total;
               }

               $('.fees').text(total_charge + " " + sender_currency);

                // Pay In Total
               var totalPay = parseFloat(senderAmount) * parseFloat(sender_currency_rate)
               var pay_in_total = 0;
               if(senderAmount == 0){
                    pay_in_total = 0;
               }else{
                    pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
               }
               $('.payable-total').text(parseFloat(pay_in_total).toFixed(2) + " " + sender_currency);

       }
       function enterLimit(){
        var min_limit = parseFloat("{{getAmount($topupCharge->min_limit)}}");
        var max_limit =parseFloat("{{getAmount($topupCharge->max_limit)}}");
        var currencyRate = acceptVar().currencyRate;
        var sender_amount = parseFloat($("input[name=amount]").val());

        if( sender_amount < min_limit ){
            throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else if(sender_amount > max_limit){
            throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else{
            $('.mobileTopupBtn').attr('disabled',false)
        }

       }

</script>

@endpush
