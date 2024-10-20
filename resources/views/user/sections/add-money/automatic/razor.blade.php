@extends('user.layouts.master')

@push('css')
<style>
    .razorpay-payment-button{
        display: none;
    }
  </style>
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Razorpay Payment")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none justify-content-center">
        <div class="col-lg-6">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{_("Payment Information")}}</h5>
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
                                    <span class="request-amount">{{ get_amount(@$output->data->amount->requested_amount,@$output->data->amount->default_currency,4 )}}</span>
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
                                    <span class="request-amount">{{ __("1") }} {{ get_default_currency_code() }} =  {{ get_amount(@$output->data->amount->sender_cur_rate, @$output->data->amount->sender_cur_code,4 )}}</span>
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
                                    <span class="fees">{{ get_amount(@$output->data->amount->total_charge,@$output->data->amount->sender_cur_code,4 )}} </span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="lab la-get-pocket"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Will Get") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="will-get">{{ get_amount(@$output->data->amount->requested_amount,@$output->data->amount->default_currency,4 )}}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="last">{{ __("Total Payable Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--warning last pay-in-total">{{ get_amount(@$output->data->amount->total_amount,@$output->data->amount->sender_cur_code,4 )}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form action="{{ route('user.add.money.razor.callback') }}" method="GET">
                <script
                    src="https://checkout.razorpay.com/v1/checkout.js"
                    data-key="{{ $data->public_key}}"
                    data-amount="{{ intval($output->data->amount->total_amount) * 100}}"
                    data-currency={{ @$output->data->amount->sender_cur_code??"INR" }}
                    data-name="{{ $basic_settings->site_name }}"
                    data-description="Add Money"
                    data-image={{ get_fav($basic_settings) }}
                    data-prefill.name="{{ auth()->user()->username }}"
                    data-prefill.email="{{ auth()->user()->email }}"
                    data-theme.color="#F37254"
                ></script>
                <input type="hidden" value="{{ $orderId }}" name="razorpay_order_id">
                <input type="hidden" value="{{ @$output->data->amount->sender_cur_code??"INR" }}" name="razorpay_currency">
                <input type="hidden" value="{{ intval($output->data->amount->total_amount) * 100 }}" name="razorpay_amount">
                <input type="hidden" value="{{ $basic_settings->site_name }}" name="razorpay_merchant_name">
                <input type="hidden" value="Payment for Order ID: {{ $orderId }}" name="razorpay_description">
                <input type="hidden" value="{{ setRoute('user.add.money.razor.cancel',$orderId) }}" name="razorpay_cancel_url">
                <button type="submit" class="btn--base mt-20 w-100">{{ __("Pay Now") }}</button>
            </form>
        </div>

    </div>
</div>
@endsection

@push('script')

@endpush
