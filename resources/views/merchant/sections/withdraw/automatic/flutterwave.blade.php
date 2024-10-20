@extends('merchant.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('merchant.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("merchant.dashboard"),
        ]
    ], 'active' => __("Withdraw")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none justify-content-center">
        <div class="col-lg-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>

                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute("merchant.withdraw.confirm.automatic") }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="gateway_name" value="{{ strtolower($gateway->name) }}">
                            <div class="row">
                                <div class="col-lg-12 form-group">
                                    <label for="bank_name">{{ __("select Bank") }} <span class="text-danger">*</span></label>
                                    <select name="bank_name" class="form--control select2-basic" required data-placeholder="{{ __("select Bank") }}" >
                                          <option disabled selected value="">{{ __("select Bank") }}</option>
                                        @foreach ($allBanks ??[] as $bank)
                                            <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label for="account_number">{{ __("account Number") }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form--control check_bank number-input" id="account_number"  name="account_number" value="{{ old('account_number') }}" placeholder="{{ __("account Number") }}">
                                    <label class="exist text-start"></label>
                                </div>
                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading withdraw"> {{ __("confirm") }}

                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{__("Withdraw Money Information!")}}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Entered Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="request-amount">{{ number_format(@$moneyOutData->amount,2 )}} {{ get_default_currency_code() }}</span>
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
                                    <span class="request-amount">{{ __("1") }} {{ get_default_currency_code() }} =  {{ number_format(@$moneyOutData->gateway_rate,2 )}} {{ @$moneyOutData->gateway_currency }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="lab la-get-pocket"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Conversion Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="conversion">{{ number_format(@$moneyOutData->conversion_amount,2 )}} {{ @$moneyOutData->gateway_currency }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Fees & Charges") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">{{ number_format(@$moneyOutData->gateway_charge,2 )}} {{ @$moneyOutData->gateway_currency }}</span>
                                </div>
                            </div>

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="">{{ __("Will Get") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--success ">{{ number_format(@$moneyOutData->will_get,2 )}} {{ @$moneyOutData->gateway_currency }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="last">{{ __("Total Payable") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--warning last">{{ number_format(@$moneyOutData->payable,2 )}} {{ get_default_currency_code() }}</span>
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
{{-- <script>
      $('.check_bank').on('focusout',function(e){
            var url = '{{ route('merchant.withdraw.check.flutterwave.bank') }}';
            var account_number = $(this).val();
            var bank_code = $("select[name=bank_name] :selected").val();
            var token = '{{ csrf_token() }}';
            if ($(this).attr('name') == 'account_number') {
                var data = {
                             account_number:account_number,
                             bank_code:bank_code,
                             _token:token
                        }

            }
            $.post(url,data,function(response) {
                if(response.data.status == "success"){
                    var name = "Account Holder Name : <strong>"+response.data.data.account_name+"</strong>";
                    if($('.exist').hasClass('text--danger')){
                        $('.exist').removeClass('text--danger');
                    }
                    $('.exist').html(name).addClass('text-success');
                    $('.withdraw').attr('disabled',false)
                } else {
                    if($('.exist').hasClass('text-success')){
                        $('.exist').removeClass('text-success');
                    }
                    $('.exist').text('Bank account doesn\'t  exists.').addClass('text--danger');
                    $('.withdraw').attr('disabled',true)
                    return false
                }

            });
        });
</script> --}}
@endpush
