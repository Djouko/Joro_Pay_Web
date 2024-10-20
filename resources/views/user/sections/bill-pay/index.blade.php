@extends('user.layouts.master')

@push('css')

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
                        <h5 class="title">{{ __("Bill Pay Form") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('user.bill.pay.confirm') }}" method="POST">
                            @csrf
                            <input type="hidden" name="exchange_rate">
                            <input type="hidden" name="biller_item_type">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span class="fees-show">--</span> <span class="limit-show">--</span></code>
                                    </div>
                                </div>
                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("bill Type") }} <span class="text--base">*</span></label>
                                    <select class="form--control" name="bill_type">
                                        @forelse ($billType ??[] as $type)
                                            @php
                                                $type = (object) $type;
                                            @endphp
                                            <option value="{{ $type->id }}" data-item-type ="{{ $type->item_type }}" data-item="{{ json_encode($type) }}" data-name="{{ $type->name }}">{{ $type->name }} {{ $type->item_type === "MANUAL" ? "(Manual)" : "" }}</option>
                                        @empty
                                            <option  disabled selected value="null" >{{ __('No Items Available') }}</option>
                                        @endforelse

                                    </select>
                                </div>
                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Bill Month") }} <span class="text--base">*</span></label>
                                    <select class="form--control" name="bill_month">
                                        <option value="{{ "January".'-'.date("Y") }}">{{ "January ".date("Y") }}</option>
                                        <option value="{{ "February".'-'.date("Y") }}">{{ "February ".date("Y") }}</option>
                                        <option value="{{ "March".'-'.date("Y") }}">{{ "March ".date("Y") }}</option>
                                        <option value="{{ "April".'-'.date("Y") }}">{{ "April ".date("Y") }}</option>
                                        <option value="{{ "May".'-'.date("Y") }}">{{ "May ".date("Y") }}</option>
                                        <option value="{{ "June".'-'.date("Y") }}">{{ "June ".date("Y") }}</option>
                                        <option value="{{ "July".'-'.date("Y") }}">{{ "July ".date("Y") }}</option>
                                        <option value="{{ "August".'-'.date("Y") }}">{{ "August ".date("Y") }}</option>
                                        <option value="{{ "September".'-'.date("Y") }}">{{ "September ".date("Y") }}</option>
                                        <option value="{{ "October".'-'.date("Y") }}">{{ "October ".date("Y") }}</option>
                                        <option value="{{ "November".'-'.date("Y") }}">{{ "November ".date("Y") }}</option>
                                        <option value="{{ "December".'-'.date("Y") }}">{{ "December ".date("Y") }}</option>
                                    </select>
                                </div>
                                <div class="col-xxl-6 col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Bill Number") }} <span class="text--base">*</span></label>
                                    <input type="text" class="form--control number-input btn-loading" required name="bill_number" placeholder="{{ __("enter Bill Number") }}" value="{{ old('bill_number') }}">

                                </div>

                                <div class="col-xxl-6 col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" placeholder="{{__('enter Amount')}}" name="amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select currency" name="currency" id="currency-select">
                                            <option value="">--</option>
                                        </select>
                                    </div>

                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="note-area">
                                        <code class="d-block fw-bold">{{ __("Available Balance") }}: {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                                    </div>
                                </div>

                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading billPayBtn">{{ __("pay Bill") }} <i class="fas fa-coins ms-1"></i></button>
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
                                            <span>{{ __("Bill Pay") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="bill-type">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-list-ol"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Bill Month") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="bill-month">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-list-ol"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Bill Number") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="bill-number">--</span>
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
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("Bill Pay Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('user.transactions.index','bill-pay') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('user.components.transaction-log',compact("transactions"))
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
        getExchangeRate();
        getFees();
        senderCurrency();
        activeItems();
       });
       $("input[name=amount]").keyup(function(){
            getFees();
            getLimit();
            senderCurrency();
            activeItems();
       });
       $("input[name=amount]").focusout(function(){
            enterLimit();
       });
       $("input[name=bill_number]").keyup(function(){
            getFees();
            getLimit();
            senderCurrency();
            activeItems();
       });
       $("select[name=bill_type]").change(function(){
            getFees();
            getLimit();
            getExchangeRate();
            senderCurrency();
            activeItems();
       });
       $("select[name=bill_month]").change(function(){
            getFees();
            getLimit();
            getExchangeRate();
            senderCurrency();
            activeItems();
       });
       function getLimit() {
            if(acceptVar().billType.val() === "null"){
                return false;
            }
            if(acceptVar().billType.data('item-type') === "AUTOMATIC"){
                var item = acceptVar().billType.data('item');
                $('.limit-show').html("{{ __('limit') }} " + parseFloat(item.minLocalTransactionAmount).toFixed(4) + " " + item.localTransactionCurrencyCode + " - " + parseFloat(item.maxLocalTransactionAmount).toFixed(4) + " " + item.localTransactionCurrencyCode);
            }else{
                var currencyCode = acceptVar().currencyCode;
                var currencyRate = acceptVar().currencyRate;

                var min_limit = acceptVar().currencyMinAmount;
                var max_limit =acceptVar().currencyMaxAmount;
                if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
                    var min_limit_calc = parseFloat(min_limit/currencyRate).toFixed(4);
                    var max_limit_clac = parseFloat(max_limit/currencyRate).toFixed(4);
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

       }
        function getExchangeRate() {
            var walletCurrencyCode = acceptVar().currencyCode;
            var walletCurrencyRate = acceptVar().currencyRate;
            var sender_amount = $("input[name=amount]").val();

            sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

            if (acceptVar().billType.data('item-type') === "AUTOMATIC") {
                    var item = acceptVar().billType.data('item');
                    var receiver_currency_code = item.localTransactionCurrencyCode;
            } else {
                var receiver_currency_code = acceptVar().currencyCode;
            }
            $.ajax({
            type:'get',
                url:"{{ route('global.receiver.wallet.currency') }}",
                data:{code:receiver_currency_code},
                success:function(data){
                    var receiverCurrencyCode = data.currency_code;
                    var receiverCurrencyRate = data.rate;
                    var exchangeRate = (walletCurrencyRate/receiverCurrencyRate);
                    $("input[name=exchange_rate]").val(exchangeRate);
                    $('.rate-show').html("1 " +receiverCurrencyCode + " = " + parseFloat(exchangeRate).toFixed(8) + " " + walletCurrencyCode);
                    activeItems();
                }
            });
        }
       function acceptVar() {
           var selectedVal = $("select[name=currency] :selected");
        //    var currencyCode = $("select[name=currency] :selected").val();
           var currencyCode = defualCurrency;
           var currencyRate = defualCurrencyRate;
           var currencyMinAmount ="{{getAmount($billPayCharge->min_limit)}}";
           var currencyMaxAmount = "{{getAmount($billPayCharge->max_limit)}}";
           var currencyFixedCharge = "{{getAmount($billPayCharge->fixed_charge)}}";
           var currencyPercentCharge = "{{getAmount($billPayCharge->percent_charge)}}";
           var billType = $("select[name=bill_type] :selected");
           var billName = $("select[name=bill_type] :selected").data("name");
           var billMonth = $("select[name=bill_month] :selected").val();
           var billNumber = $("input[name=bill_number]").val();

           return {
               currencyCode:currencyCode,
               currencyRate:currencyRate,
               currencyMinAmount:currencyMinAmount,
               currencyMaxAmount:currencyMaxAmount,
               currencyFixedCharge:currencyFixedCharge,
               currencyPercentCharge:currencyPercentCharge,
               billName:billName,
               billNumber:billNumber,
               billMonth:billMonth,
               billType:billType,
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
               total_charge = parseFloat(total_charge).toFixed(4);
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
        if(acceptVar().billType.val() === "null"){
            return false;
        }
        if(acceptVar().billType.data('item-type') === "AUTOMATIC"){
            var item = acceptVar().billType.data('item');
            $(".fees-show").html("{{ __('Bill Pay Charges') }}: " + parseFloat(item.localTransactionFee).toFixed(4) + " " + item.localTransactionCurrencyCode);
        }else{
            var currencyCode = acceptVar().currencyCode;
            var percent = acceptVar().currencyPercentCharge;
            var charges = feesCalculation();
            if (charges == false) {
                    return false;
            }
            $(".fees-show").html("{{ __('Bill Pay Charges') }}: " + parseFloat(charges.fixed).toFixed(4) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(4) + "%  ");
        }

       }
       function activeItems(){
            var billType = acceptVar().billType.val();
            if(billType === undefined || billType === '' || billType === null){
                return false;
            }else{
                return getPreview();
            }
       }
       function getPreview() {
            if(acceptVar().billType.val() === "null"){
                return false;
            }
            var senderAmount = $("input[name=amount]").val();
            var wallet_currency = acceptVar().currencyCode;
            var billName = acceptVar().billName;
            var billNumber = acceptVar().billNumber;
            var exchangeRate =  parseFloat($("input[name=exchange_rate]").val());
            senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
            var conversion_amount = parseFloat(senderAmount) * parseFloat(exchangeRate);
            //fillup hidden fields
            $("input[name=biller_item_type]").val(acceptVar().billType.data('item-type'));
            //fillup hidden fields

            if (acceptVar().billType.data('item-type') === "AUTOMATIC") {
                var item = acceptVar().billType.data('item');
                var sender_currency_code = item.localTransactionCurrencyCode;
                var final_charge = item.localTransactionFee * exchangeRate;
            } else {
                var sender_currency_code = acceptVar().currencyCode;
                var charges = feesCalculation();
                var final_charge = charges.total;
            }

            // Sending Amount
            $('.request-amount').text(parseFloat(senderAmount).toFixed(4) + " " + sender_currency_code);
            $('.conversion-amount').text(parseFloat(conversion_amount).toFixed(4) + " " + wallet_currency);
            //bill type
            $('.bill-type').text(billName);
            $('.bill-month').text(acceptVar().billMonth);
            // Fees
            //bill number
            if(billNumber == '' || billNumber == 0){
                $('.bill-number').text("Ex: 1234567891");
            }else{
                $('.bill-number').text(billNumber);
            }
            // Fees
            var total_charge = 0;
            if(senderAmount == 0){
                total_charge = 0;
            }else{
                total_charge = final_charge;
            }

            $('.fees').text(parseFloat(total_charge).toFixed(4) + " " + wallet_currency);
            // Pay In Total
            var totalPay = parseFloat(conversion_amount) + parseFloat(total_charge)
            var pay_in_total = 0;
            if(senderAmount == 0){
                pay_in_total = 0;
            }else{
                pay_in_total =  parseFloat(totalPay);
            }
            $('.payable-total').text(parseFloat(pay_in_total).toFixed(4) + " " + wallet_currency);

       }
       function senderCurrency() {
            var selectElement = document.getElementById("currency-select");
            selectElement.innerHTML = "";
            if (acceptVar().billType.data('item-type') === "AUTOMATIC") {
                var item = acceptVar().billType.data('item');
                var currencyCode = item.localTransactionCurrencyCode;
            } else {
                var currencyCode = acceptVar().currencyCode;
            }
            var optionElement = document.createElement("option");
            optionElement.value = currencyCode;
            optionElement.textContent = currencyCode;
            selectElement.appendChild(optionElement);
            $(selectElement).niceSelect('update');
        }
       function enterLimit(){
            if (acceptVar().billType.data('item-type') === "AUTOMATIC") {
                var item = acceptVar().billType.data('item');
                var min_limit = item.minLocalTransactionAmount;
                var max_limit = item.maxLocalTransactionAmount;
            } else {
                var min_limit = parseFloat("{{getAmount($billPayCharge->min_limit)}}");
                var max_limit =parseFloat("{{getAmount($billPayCharge->max_limit)}}");
            }
            var sender_amount = parseFloat($("input[name=amount]").val());
            if( sender_amount < min_limit ){
                throwMessage('error',['{{ __("Please follow the mimimum limit") }}']);
                $('.billPayBtn').attr('disabled',true)
            }else if(sender_amount > max_limit){
                throwMessage('error',['{{ __("Please follow the maximum limit") }}']);
                $('.billPayBtn').attr('disabled',true)
            }else{
                $('.billPayBtn').attr('disabled',false)
        }

       }

</script>

@endpush
