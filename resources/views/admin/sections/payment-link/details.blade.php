@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' =>  __($page_title),
    ])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{  __($page_title) }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form">
                <div class="row align-items-center mb-10-none">
                    <div class="col-xl-4 col-lg-4 form-group">
                        <ul class="user-profile-list-two">
                            <li class="one">{{ __("Date") }}: <span>{{ dateFormat('d M y h:i:s A', $data->created_at) }}</span></li>
                            <li class="two">{{ __("web_trx_id") }}: <span>{{ @$data->trx_id }}</span></li>
                            <li class="three">{{ __("Email") }}: <span>
                                @if($data->user_id != null)
                                    <a href="{{ setRoute('admin.users.details',$data->creator->username) }}">{{ $data->creator->email }} ({{ __("USER") }})</a>
                                @elseif($data->merchant_id != null)
                                    <a href="{{ setRoute('admin.merchants.details',$data->creator->username) }}">{{ $data->creator->email }} ({{ __("MERCHANT") }})</a>
                                @endif
                                </span></li>
                            <li class="four">{{ __("Method") }}: <span>{{ @$data->type }}</span></li>
                            <li class="five">{{ __("Amount") }}: <span>{{ get_amount(@$data->request_amount,null,4) }} {{ @$data->details->charge_calculation->sender_cur_code }}</span></li>
                        </ul>
                    </div>
                    <div class="col-xl-4 col-lg-4 form-group">
                        <div class="user-profile-thumb">
                            <img src="{{ @$data->creator->userImage }}" alt="payment">
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 form-group">
                        <ul class="user-profile-list two">
                            <li class="one">{{ __("Charge") }}: <span>{{ get_amount(@$data->charge->total_charge,null,4) }} {{ @$data->details->charge_calculation->sender_cur_code }}</span></li>
                            <li class="two">{{ __("After Charge") }}: <span>{{ get_amount(@$data->payable,null,4) }} {{ @$data->details->charge_calculation->sender_cur_code }}</span></li>
                            <li class="four">{{ __("Conversion Payable") }}: <span>{{ get_amount(@$data->details->charge_calculation->conversion_payable,@$data->details->charge_calculation->receiver_currency_code,4) }}</span></li>
                            <li class="three">{{ __("Rate") }}: <span>1 {{ get_default_currency_code() }} = {{ get_amount(@$data->details->sender_currency->rate,@$data->details->sender_currency->currency_code,4) }} {{ @$data->currency->currency_code }}</span></li>
                            <li class="five">{{__("Status") }}:  <span class="{{ @$data->stringStatus->class }}">{{ __(@$data->stringStatus->value) }}</span></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="custom-card mt-15">
        <div class="card-header">
            <h6 class="title text-bold">{{ __("Payment Information") }}</h6>
            <h6 class="title ">{{ __("Payment Type") }}: <span class="text-bold fw-bold text-success"> {{ ucwords(str_replace('_',' ',$data->details->payment_type??__("Card Payment")) )}} </span></h6>
        </div>
        <div class="card-body">

            <ul class="product-sales-info">
                @if(isset($data->details->payment_type) && $data->details->payment_type == payment_gateway_const()::TYPE_CARD_PAYMENT)
                    <li>
                        <span class="kyc-title">{{ __("Sender Email") }}:</span>
                        <span>{{ @$data->details->email }}</span>
                    </li>
                    <li>
                        <span class="kyc-title">{{ __("Card Holder Name") }}:</span>
                        <span>{{ @$data->details->card_name }}</span>
                    </li>
                    <li>
                        <span class="kyc-title">{{ __("Sender Card Number") }}:</span>
                        <span>**** **** **** {{ @$data->details->last4_card }}</span>
                    </li>

                @elseif(isset($data->details->payment_type) && $data->details->payment_type == payment_gateway_const()::TYPE_GATEWAY_PAYMENT)
                <li>
                    <span class="kyc-title">{{ __("Payment Gateway") }}:</span>
                    <span>{{ $data->details->currency->name }}</span>
                </li>

                @elseif(isset($data->details->payment_type) && $data->details->payment_type == payment_gateway_const()::TYPE_WALLET_SYSTEM)
                <li>
                    <span class="kyc-title">{{ __("User Type") }}:</span>
                    <span>{{ "USER"}}</span>
                </li>
                <li>
                    <span class="kyc-title">{{ __("Full Name") }}:</span>
                    <a href="{{ setRoute('admin.users.details',$data->details->sender->username) }}">
                        <span>{{ $data->details->sender->fullname }}</span>
                    </a>
                </li>
                <li>
                    <span class="kyc-title">{{ __("Sender Username") }}:</span>
                    <a href="{{ setRoute('admin.users.details',$data->details->sender->username) }}">
                        <span>{{ $data->details->sender->username }}</span>
                    </a>
                </li>
                <li>
                    <span class="kyc-title">{{ __("Sender Email") }}:</span>
                    <a href="{{ setRoute('admin.users.details',$data->details->sender->username) }}">
                        <span>{{ $data->details->sender->email }}</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>

@endsection
@push('script')
@endpush
