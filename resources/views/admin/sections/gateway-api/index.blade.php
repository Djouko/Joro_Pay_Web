@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("PayLink Api")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("PayLink Api") }}</h6>
        </div>
        <div class="card-body">

            <div class="row mb-30-none">
                <div class="col-xxl-4 col-xl-6 col-md-6 mb-20">
                    <div class="gateway-item">
                        <div class="gateway-item-wrapper">
                            <div class="content">
                                <h4 class="title">{{ __("Wallet System") }}</h4>
                                <span><i class="las la-exclamation-circle"></i>{{ __("enable Or Disable This Features") }}</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" name="wallet_status" type="checkbox" id="walletStatusCheckbox" {{ @$api->wallet_status == 1 ?'checked' :'' }} >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-6 col-md-6 mb-20">
                    <div class="gateway-item">
                        <div class="gateway-item-wrapper">
                            <div class="content">
                                <h4 class="title">{{ __("Payment Gateway") }}</h4>
                                <span><i class="las la-exclamation-circle"></i>{{ __("enable Or Disable This Features") }}</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="paymentGatewaySystem" {{ @$api->payment_gateway_status == 1 ?'checked' :'' }}>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-6 col-md-6 mb-20">
                    <div class="gateway-item">
                        <div class="gateway-item-wrapper">
                            <div class="content">
                                <h4 class="title">{{ __("Master / Visa Card") }} <small class="text--base">({{ __("Stripe") }})</small></h4>
                                <span><i class="las la-exclamation-circle"></i>{{ __("enable Or Disable This Features") }}</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input master-visa-switch" type="checkbox" id="masterVisaStatusCheckBox" {{ @$api->card_status == 1 ?'checked' :'' }}>
                            </div>
                        </div>
                        <form class="master-visa-api-form mt-20 {{ @$api->card_status == 1 ?'active' :'' }}" id="master" action="{{ setRoute('admin.gateway.api.update.card.credentials') }}" method="POST">
                            @csrf
                            <div class="api-input-wrapper mb-20-none">
                                <div class="form-group">
                                    <label>{{ __("Public Key") }} <span class="text--base">*</span></label>
                                    <input type="text" name="public_key" placeholder="{{ __("Public Key") }}" class="form--control" value="{{ @$api->public_key }}" required value="{{ old('public_key') }}">
                                </div>
                                <div class="form-group">
                                    <label>{{ __("secret Key") }} <span class="text--base">*</span></label>
                                    <input type="text" name="secret_key" placeholder="{{ __("secret Key") }}" class="form--control" value="{{ @$api->secret_key }}" required value="{{ old('secret_key') }}">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn--base w-100 btn-loading">{{ __("Save & Change") }} <i class="fas fa-check-circle ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
<script>
    var app_mode = "{{ env('APP_MODE') }}"
    $(document).ready(function() {
        //wallet system
        $('#walletStatusCheckbox').on('change', function() {
            var status = this.checked ? 1 : 0;
            if(app_mode == "demo"){
                throwMessage('error',["Can't change anything for demo application."]);
                setTimeout(function wait(){
                    location.reload();
                },2000);
                return false;
            }

            $.ajax({
                url: '{{ setRoute("admin.gateway.api.update.wallet.status") }}',
                method: 'POST',
                data: {
                    status: status,
                    _token: '{{csrf_token()}}'
                 },
                success: function(response) {
                    console.log(response);
                    var updated_status = response.status;
                    if(updated_status == 1){
                        throwMessage('success',["{{ __('EWallet System Enabled Successfully') }}"]);
                    }else{
                        throwMessage('success',["{{ __('Wallet System Disabled  Successfully') }}"]);
                    }
                },
                error: function(xhr, status, error) {

                    throwMessage('error',["Something is wrong!, Please try again later"]);
                }
            });
        });
        //virtual card
        $('#paymentGatewaySystem').on('change', function() {
            var status = this.checked ? 1 : 0;
            if(app_mode == "demo"){
                throwMessage('error',["Can't change anything for demo application."]);
                setTimeout(function wait(){
                    location.reload();
                },2000);
                return false;
            }
            $.ajax({
                url: '{{ setRoute("admin.gateway.api.update.payment.gateway.status") }}',
                method: 'POST',
                data: {
                    status: status,
                    _token: '{{csrf_token()}}'
                 },
                success: function(response) {
                    var updated_status = response.status;
                    if(updated_status == 1){
                        throwMessage('success',["{{ __('EPayment Gateway System Enabled Successfully') }}"]);
                    }else{
                        throwMessage('success',["{{ __('Payment Gateway System Disabled Successfully') }}"]);
                    }
                },
                error: function(xhr, status, error) {
                    throwMessage('error',["Something is wrong!, Please try again later"]);
                }
            });
        });
        //Master/Visa card
        $('#masterVisaStatusCheckBox').on('change', function() {

            var status = this.checked ? 1 : 0;
            if(app_mode == "demo"){
                throwMessage('error',["Can't change anything for demo application."]);
                setTimeout(function wait(){
                    location.reload();
                },2000);
                return false;
            }
            if(status == 1){
                $('#master').addClass('active');
            }else{
                $('#master').removeClass('active');
            }
                $.ajax({
                url: '{{ setRoute("admin.gateway.api.update.card.status") }}',
                method: 'POST',
                data: {
                    status: status,
                    _token: '{{csrf_token()}}'
                 },
                success: function(response) {
                    console.log(response);
                   var updated_status = response.status;
                   if(updated_status == 1){
                    throwMessage('success',["{{ __('Master/Visa Card System Enabled Successfully') }}"]);
                   }else{
                    throwMessage('success',["{{ __('DMaster/Visa Card System Disabled Successfully') }}"]);
                   }

                },
                error: function(xhr, status, error) {
                    throwMessage('error',["Something is wrong!, Please try again later"]);
                }
            });


        });
    });
</script>
@endpush
