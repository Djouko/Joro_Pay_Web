@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __("Mobile Topup Details")])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' => __('Mobile Topup Details'),
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
                        <li class="two">{{ __("Fullname") }}: <span>
                            @if($data->user_id != null)
                            <a href="{{ setRoute('admin.users.details',$data->creator->username) }}">{{ $data->creator->fullname }} ({{ __("USER") }})</a>
                            @elseif($data->agent_id != null)
                            <a href="{{ setRoute('admin.agents.details',$data->creator->username) }}">{{ $data->creator->fullname }} ({{ __("AGENT") }})</a>
                            @endif
                            </span>
                        </li>
                        <li class="three">{{ __("TopUp Type") }}: <span class="fw-bold">{{ @$data->details->topup_type_name }}</span></li>
                        <li class="four">{{ __("Mobile Number") }}: <span class="fw-bold">{{ @$data->details->mobile_number }}</span></li>
                        <li class="five">{{ __("Topup Amount") }}: <span class="fw-bold">{{ get_amount($data->request_amount,topUpCurrency($data)['destination_currency']) }}</span></li>

                    </ul>
                </div>

                <div class="col-xl-4 col-lg-4 form-group">
                    <div class="user-profile-thumb">
                        <img src="{{  @$default_currency->currencyImage }}" alt="payment">
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 form-group">
                    <ul class="user-profile-list two">
                        <li class="one">{{ __("Exchange Rate") }}: <span>{{topUpExchangeRate($data)['exchange_info'] }}</span></li>
                        <li class="two">{{ __("Total Charge") }}: <span>{{ get_amount($data->charge->total_charge,topUpCurrency($data)['wallet_currency']) }}</span></li>
                        <li class="three">{{ __("Payable Amount") }}: <span>{{ get_amount($data->payable,topUpCurrency($data)['wallet_currency']) }}</span></li>
                        <li class="three">{{ __("Remaining Balance") }}: <span>{{ get_amount($data->available_balance,topUpCurrency($data)['wallet_currency']) }}</span></li>
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
                <form class="modal-form" action="{{ setRoute('admin.mobile.topup.approved') }}" method="POST">

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
                <form class="modal-form" action="{{ setRoute('admin.mobile.topup.rejected') }}" method="POST">
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
