@extends('user.layouts.master')

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
                        <form class="card-form" action="{{ setRoute('user.mobile.topup.automatic.pay') }}" method="POST">
                            @csrf
                            <input type="hidden" name="country_code">
                            <input type="hidden" name="phone_code">
                            <input type="hidden" name="exchange_rate">
                            <input type="hidden" name="operator">
                            <input type="hidden" name="operator_id">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span class="fees-show">--</span></code>
                                        <code class="d-block text-center limit-show"></code>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <label>{{ __("Mobile Number") }}<span>*</span></label>
                                    <div class="input-group mobile-code">
                                        <select class="form--control nice-select" name="mobile_code">
                                            @foreach(freedom_countries(global_const()::USER) ?? [] as $key => $code)
                                                <option value="{{ $code->iso2 }}"
                                                    data-mobile-code="{{ remove_speacial_char($code->mobile_code) }}"
                                                    {{ $code->name === auth()->user()->address->country ? 'selected' :'' }}
                                                    >
                                                    {{ $code->name." (+".remove_speacial_char($code->mobile_code).")" }}
                                                </option>
                                            @endforeach

                                        </select>
                                        <input type="text" class="form--control number-input" name="mobile_number" placeholder="{{ __("enter Mobile Number") }}" value="{{ old('mobile_number') }}">
                                        <span class="btn-ring-input"></span>
                                    </div>

                                </div>
                                <div  class="add_item">

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
                                            <span>{{ __("Operator Name") }}</span>
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
                                            <i class="las la-hand-holding-usd"></i>
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
                                            <i class="las la-exchange-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Exchange Rate") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="rate-show">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hand-holding-usd"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Conversion Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--info conversion-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hand-holding-usd"></i>
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
                                            <i class="las la-hand-holding-usd"></i>
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
    $('.mobileTopupBtn').attr('disabled',true);
    $("select[name=mobile_code]").change(function(){
        if(acceptVar().mobileNumber != '' ){
            checkOperator();
        }
    });
    $("input[name=mobile_number]").focusout(function(){
        checkOperator();
    });
    $(document).on("click",".radio_amount",function(){
        preview();
    });
    $(document).on("focusout","input[name=amount]",function(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var denominationType = operator.denominationType;
        if(denominationType === "RANGE"){
            enterLimit();
        }
        preview();
    });
    $(document).on("keyup","input[name=amount]",function(){
        preview();
    });
    function acceptVar() {
        var selectedMobileCode = $("select[name=mobile_code] :selected");
        var mobileNumber = $("input[name=mobile_number]").val();
        var currencyCode = defualCurrency;
        var currencyRate = defualCurrencyRate;
        return {
            selectedMobileCode:selectedMobileCode,
            mobileNumber:mobileNumber,
            currencyCode:currencyCode,
            currencyRate:currencyRate,

        };
    }
    function checkOperator() {
        var url = '{{ route('user.mobile.topup.automatic.check.operator') }}';
        var mobile_code = acceptVar().selectedMobileCode.data('mobile-code');
        var phone = acceptVar().mobileNumber;
        var iso = acceptVar().selectedMobileCode.val();
        var token = '{{ csrf_token() }}';

        var data = {_token: token, mobile_code: mobile_code, phone: phone, iso: iso};

        $.post(url, data, function(response) {
            $('.btn-ring-input').show();
            if(response.status === true){
                var response_data = response.data;
                var destination_currency_code = response_data.destinationCurrencyCode;
                var destination_currency_symbol = response_data.destinationCurrencySymbol;
                var denominationType = response_data.denominationType;
                var destination_exchange_rate = response_data.fx.rate;
                $('.add_item').empty();
                $('.limit-show').empty();
                if(denominationType === "RANGE"){
                    var minAmount = response_data.minAmount
                    var maxAmount = response_data.maxAmount
                    // Append the HTML code to the .add_item div for RANGE
                    $('.add_item').html(`
                        <div class="col-xxl-12 col-xl-12 col-lg-12 form-group">
                            <label>{{ __("Amount") }}<span>*</span></label>
                            <div class="input-group">
                                <input type="text" class="form--control number-input" required placeholder="{{__('enter Amount')}}" name="amount" value="{{ old("amount") }}">
                                <select class="form--control nice-select currency" name="currency">
                                    <option value="${destination_currency_code}">${destination_currency_code}</option>
                                </select>
                            </div>
                        </div>
                    `);
                    $("select[name=currency]").niceSelect();

                    $('.limit-show').html(`
                        <span class="limit-show">{{ __("limit") }}: ${minAmount+" "+destination_currency_code+" - "+maxAmount+" "+destination_currency_code}</span>
                    `);
                }else if(denominationType === "FIXED"){
                    var fixedAmounts = response_data.fixedAmounts;
                    // Multiply each value in fixedAmounts array by destination_exchange_rate
                    var multipliedAmounts = fixedAmounts.map(function(amount) {
                        return (amount * destination_exchange_rate).toFixed(2); // Set precision to two decimal places
                    });
                    // Generate radio input fields for each multiplied amount
                    var radioInputs = '';
                    $.each(multipliedAmounts, function(index, amount) {
                        // Check the first radio button by default
                        var checked = index === 0 ? 'checked' : '';
                        radioInputs += `
                            <div class="gift-card-radio-item">
                                <input type="radio" id="level-${index}" name="amount" value="${amount}" onclick="handleRadioClick(this)" class="radio_amount" ${checked}>
                                <label for="level-${index}">${amount} ${destination_currency_code}</label>
                            </div>
                        `;
                    });
                    // Append the HTML code to the .add_item div for FIXED with radio input fields
                    $('.add_item').html(`
                        <div class="col-xl-12 mb-20">
                            <label>{{ __("Amount") }}<span>*</span></label>
                            <div class="gift-card-radio-wrapper">
                                ${radioInputs}
                            </div>
                        </div>
                    `);

                }
                $("input[name=operator]").val(JSON.stringify(response_data));
                getFee();
                getExchangeRate();
                // preview();
                if(denominationType === "FIXED"){
                    var firstRadio = $('input[type="radio"]:first');
                    firstRadio.prop('checked', true);
                    handleRadioClick(firstRadio[0]);
                }
                $('.mobileTopupBtn').attr('disabled',false);
                setTimeout(function() {
                    $('.btn-ring-input').hide();
                },1000);
            }else if(response.status === false && response.from === "error"){
                $('.add_item, .limit-show').empty();
                $('.fees-show, .rate-show, .topup-type, .mobile-number, .request-amount, .conversion-amount, .fees, .payable-total').html('--');
                $('input[name=phone_code], input[name=country_code],input[name=operator],input[name=operator_id],input[name=exchange_rate]').val('');
                $('.mobileTopupBtn').attr('disabled',true);
                setTimeout(function() {
                    $('.btn-ring-input').hide();
                    throwMessage('error',[response.message]);
                },1000);
                return false;
            }
        });
    }
    function getFee(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var destination_currency_code = operator.destinationCurrencyCode;
        var destination_exchange_rate = operator.fx.rate;
        var destination_fixed = operator.fees.international;
        var destination_percent = operator.fees.internationalPercentage;
        $(".fees-show").html("{{ __('TopUp Fee') }}: " + parseFloat(destination_fixed).toFixed(2) + " " + destination_currency_code + " + " + parseFloat(destination_percent).toFixed(2) + "%  ");

    }
    function getExchangeRate() {
            var walletCurrencyCode = acceptVar().currencyCode;
            var walletCurrencyRate = acceptVar().currencyRate;
            var operator =  JSON.parse($("input[name=operator]").val());
            var destination_currency_code = operator.destinationCurrencyCode;
            $.ajax({
                type:'get',
                    url:"{{ route('global.receiver.wallet.currency') }}",
                    data:{code:destination_currency_code},
                    success:function(data){
                        var receiverCurrencyCode = data.currency_code;
                        var receiverCurrencyRate = data.rate;
                        var exchangeRate = (walletCurrencyRate/receiverCurrencyRate);
                        $("input[name=exchange_rate]").val(exchangeRate);
                        $('.rate-show').html("1 " +receiverCurrencyCode + " = " + parseFloat(exchangeRate).toFixed(4) + " " + walletCurrencyCode);
                        preview();
                    }
            });

    }
    function handleRadioClick(radio) {
            if (radio.checked) {
                amount = parseFloat(radio.value);
                $('.mobileTopupBtn').attr('disabled',false);

            }
        }
    function preview(){
        var sender_currency = acceptVar().currencyCode;
        var operator =  JSON.parse($("input[name=operator]").val());
        var destination_currency_code = operator.destinationCurrencyCode;
        var destination_fixed = operator.fees.international;
        var destination_percent = operator.fees.internationalPercentage;
        var exchangeRate =  parseFloat($("input[name=exchange_rate]").val());
        var senderAmount = parseFloat(get_amount());
        senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

        var conversion_amount = parseFloat(senderAmount) * parseFloat(exchangeRate);
        var phone_code = acceptVar().selectedMobileCode.data('mobile-code');
        var phone = "+"+phone_code+acceptVar().mobileNumber;
        var total_charge = (parseFloat(destination_fixed) + parseFloat(destination_percent) * exchangeRate);
        var payable = conversion_amount + total_charge

        $('.topup-type').text(operator.name);
        $('.mobile-number').text(phone);
        $('.request-amount').text(parseFloat(senderAmount).toFixed(2) + " " + destination_currency_code);
        $('.conversion-amount').text(parseFloat(conversion_amount).toFixed(2) + " " + sender_currency);
        $('.fees').text(parseFloat(total_charge).toFixed(2) + " " + sender_currency);
        $('.payable-total').text(parseFloat(payable).toFixed(2) + " " + sender_currency);
        //hidden filed fullups
        $('input[name=phone_code]').val(phone_code);
        $('input[name=country_code]').val(acceptVar().selectedMobileCode.val());
        $('input[name=operator_id]').val(operator.operatorId);

    }
    var amount = 0;
    function get_amount(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var denominationType = operator.denominationType;
        if(denominationType === "RANGE"){
            amount =  amount = parseFloat($("input[name=amount]").val());
            if (!($.isNumeric(amount))) {
                amount = 0;
            }else{
                amount = amount;
            }
        }else{
            amount = amount;
        }
        return amount;
    }
    function enterLimit(){
        var operator =  JSON.parse($("input[name=operator]").val());
        var minAmount = operator.minAmount
        var maxAmount = operator.maxAmount
        var min_limit = parseFloat(minAmount);
        var max_limit = parseFloat(maxAmount);
        var senderAmount = parseFloat(get_amount());

        senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

        if( senderAmount < min_limit ){
            throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else if(senderAmount > max_limit){
            throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
            $('.mobileTopupBtn').attr('disabled',true)
        }else{
            $('.mobileTopupBtn').attr('disabled',false)
        }

    }

</script>

@endpush
