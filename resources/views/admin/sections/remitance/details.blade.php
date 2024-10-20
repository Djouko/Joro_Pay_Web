@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __("Remittance Details")])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' => __('Remittance Details'),
    ])
@endsection

@section('content')

<div class="custom-card">
    <div class="card-header">
        <h6 class="title">{{ __($page_title) }}</h6>
    </div>
    <div class="card-body">
        <form class="card-form">
            <div class="row align-items-center mb-10-none">
                <div class="col-xl-4 col-lg-4 form-group">
                    <ul class="user-profile-list-two">
                        <li class="one">{{ __("Date") }}: <span>{{ @$data->created_at->format('d-m-y h:i:s A') }}</span></li>
                        @if( @$data->details->remitance_type == "bank-transfer")
                        <li class="two">{{ __("account Number") }}:
                            <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$data->details->bank_account))}}</span>
                        </li>
                        @else
                        <li class="two">{{ __("web_trx_id") }}: <span>{{ @$data->trx_id }}</span></li>
                        @endif
                        <li class="three">{{ __("sender") }}:
                            @if($data->attribute == "SEND")
                                @if($data->user_id != null)
                                <a href="{{ setRoute('admin.users.details',$data->user->username) }}">{{ $data->user->fullname }} {{ "(USER)" }}</a>
                                @elseif($data->agent_id != null)
                                <a href="{{ setRoute('admin.agents.details',$data->creator->username) }}">{{ $data->creator->fullname }} {{ "(AGENT)" }}</a>
                                @elseif($data->merchant_id != null)
                                <a href="{{ setRoute('admin.merchants.details',$data->creator->username) }}">{{ $data->creator->fullname }} {{ ("MERCHANT") }}</a>
                                @endif
                            @else
                            <span>{{ $data->details->sender->fullname }} ({{ @$data->user->full_mobile }})</span>
                            @endif
                        </li>
                        <li class="four">{{ __("Receiver") }}:
                            @if($data->attribute == "RECEIVED")
                                @if($data->user_id != null)
                                <a href="{{ setRoute('admin.users.details',$data->user->username) }}">{{ $data->user->fullname }}</a>
                                @elseif($data->agent_id != null)
                                    <a href="{{ setRoute('admin.agents.details',$data->creator->username) }}">{{ $data->creator->fullname }}</a>
                                @elseif($data->merchant_id != null)
                                    <a href="{{ setRoute('admin.merchants.details',$data->creator->username) }}">{{ $data->creator->fullname }}</a>
                                @endif
                            @else
                                @if($data->user_id != null)
                                    <span>{{ @$data->details->receiver->firstname }} {{ @$data->details->receiver->lastname }}</span>
                                @elseif($data->agent_id != null)
                                    <span>{{ @$data->details->receiver_recipient->firstname }} {{ @$data->details->receiver_recipient->lastname }}</span>
                                @endif
                            @endif
                        </li>
                        <li class="five">{{ __("sending Country") }}: <span class="fw-bold">{{ @$data->details->form_country }}</span></li>
                        <li class="five">{{ __("Receiving Country") }}: <span class="fw-bold">{{ @$data->details->to_country->country }}</span></li>
                        <li class="one">{{ __("Remittance Type") }}:
                            @if( @$data->details->remitance_type == "wallet-to-wallet-transfer")
                                <span class="fw-bold"> {{@$basic_settings->site_name}} {{__("Wallet")}} </span>
                                @else
                                <span class="fw-bold"> {{ ucwords(str_replace('-', ' ', @$data->details->remitance_type))}} </span>

                            @endif

                            </li>

                    </ul>
                </div>

                <div class="col-xl-4 col-lg-4 form-group">
                    <div class="user-profile-thumb">
                        @if($data->user_id != null)
                            <img src="{{  @$data->user->userImage }}" alt="payment">
                        @elseif($data->agent_id != null)
                            <img src="{{  @$data->agent->agentImage }}" alt="payment">
                        @endif

                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 form-group">
                    <ul class="user-profile-list two">
                        @php
                        if ($data->user_id != null) {
                            $alias = @$data->details->receiver->alias;
                        }elseif($data->agent_id != null){
                            $alias = @$data->details->receiver_recipient->alias;
                        }
                        @endphp

                        @if( @$data->details->remitance_type == "bank-transfer")
                        <li class="one">{{ __("bank Name") }}:
                            <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$alias))}}</span>
                        </li>

                        @endif
                        @if( @$data->details->remitance_type == "cash-pickup")
                        <li class="one">{{ __("Pickup Point") }}:
                            <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$alias))}}</span>
                        </li>
                        @endif
                        <li class="three">{{ __("Exchange Rate") }}:
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($data->details->to_country->rate,$data->details->to_country->code) }}
                        </li>
                        <li class="four">{{ __("Send Amount") }}: <span>{{ get_amount($data->request_amount,get_default_currency_code()) }}</span></li>
                        <li class="two">{{ __("Charge") }}: <span>{{ get_amount($data->charge->total_charge,get_default_currency_code()) }}</span></li>
                        <li class="three">{{ __("Payable Amount") }}: <span>{{ get_amount($data->payable,get_default_currency_code()) }}</span></li>
                        <li class="three">{{ __("Receipient Get") }}: <span>{{ number_format(@$data->details->recipient_amount,2)}} {{ $data->details->to_country->code }}</span></li>
                        <li class="four">{{__("Status") }}:  <span class="{{ @$data->stringStatus->class }}">{{ __(@$data->stringStatus->value) }}</span></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

@if(@$data->status == 2)
<div class="custom-card mt-15">
    <div class="card-body">
        <div class="product-sales-btn">
            <button type="button" class="btn btn--base approvedBtn">{{ __("approve") }}</button>
            <button type="button" class="btn btn--danger rejectBtn" >{{ __("reject") }}</button>
        </div>
    </div>
</div>

<div class="modal fade" id="approvedModal" tabindex="-1" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="approvedModalLabel">
                <h5 class="modal-title">{{ __("Approved Confirmation") }} ( <span class="fw-bold text-danger">{{ number_format(@$data->request_amount,2) }} {{ get_default_currency_code() }}</span> )</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.remitance.approved') }}" method="POST">

                    @csrf
                    @method("PUT")
                    <div class="row mb-10-none">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <input type="hidden" name="id" value={{ @$data->id }}>
                           <p>{{ __("Are you sure to approved this request?") }}</p>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                <button type="submit" class="btn btn--base btn-loading ">{{ __("Approved") }}</button>
            </div>
        </form>
        </div>
    </div>
</div>
<div class="modal fade" id="rejectModal" tabindex="-1" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="rejectModalLabel">
                <h5 class="modal-title">{{ __("Rejection Confirmation") }} ( <span class="fw-bold text-danger">{{ number_format(@$data->request_amount,2) }} {{ get_default_currency_code() }}</span> )</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.remitance.rejected') }}" method="POST">
                    @csrf
                    @method("PUT")
                    <div class="row mb-10-none">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <input type="hidden" name="id" value={{ @$data->id }}>
                            @include('admin.components.form.textarea',[
                                'label'         => __("Explain Rejection Reason*"),
                                'name'          => 'reject_reason',
                                'value'         => old('reject_reason')
                            ])
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                <button type="submit" class="btn btn--base">{{ __("confirm") }}</button>
            </div>
        </form>
        </div>
    </div>
</div>
@endif


@endsection


@push('script')
<script>
    $(document).ready(function(){
        @if($errors->any())
        var modal = $('#rejectModal');
        modal.modal('show');
        @endif
    });
</script>
<script>
     (function ($) {
        "use strict";
        $('.approvedBtn').on('click', function () {
            var modal = $('#approvedModal');
            modal.modal('show');
        });
        $('.rejectBtn').on('click', function () {
            var modal = $('#rejectModal');
            modal.modal('show');
        });
    })(jQuery);





</script>
@endpush
