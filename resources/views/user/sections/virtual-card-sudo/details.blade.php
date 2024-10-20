@extends('user.layouts.master')

@push('css')
<style>
.btn-ring {
    position: absolute;
   top: 0px;
    right: 0px;
}
.toggle-container .switch-toggles {
    position: relative;
    width: 235px;
    height: 35px;
    border-radius: 15px;
    background-color: #ffffff;
}
.cardNumber iframe{
    display: block;
    width: 152px;
    height: 20px;
}
#cvv iframe{
    display: block;
    width: 26px;
    height: 20px;
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

    </div>
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="exchange-area-wrapper text-center">
                            <div class="exchange-area mb-20">
                                <code class="d-block text-center"><span>{{ __("Current Balance") }}</span>
                                    @if($api_mode == global_const()::SANDBOX)
                                       <span class="text--warning">{{ __("TEST MODE") }}</span>
                                    @elseif ($api_mode == global_const()::LIVE)
                                        {{ getAmount(@$myCard->amount,2) }} {{ get_default_currency_code() }}
                                    @endif

                                </code>
                            </div>
                        </div>
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hourglass-end"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Account") }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="preview-list-right">
                                    <span class="text--warning">{{ __((ucwords(@$myCard->account->accountName))) }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-credit-card"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("card Holder") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$myCard->name }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hand-holding-heart"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("currency") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$myCard->currency }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hand-holding-heart"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("brand") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ @$myCard->brand }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hand-holding-heart"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("type")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{ ucwords(@$myCard->type) }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-user-tag"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("card Pan") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                  <span class="cardNumber">{{ $myCard->maskedPan }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-business-time"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("expiry Date") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span>{{$myCard->expiryMonth }} / {{$myCard->expiryYear }}</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-hourglass-start"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Cvv") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span id="cvv">{{ __("***") }}</span>
                                </div>
                            </div>

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Status") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <div class="toggle-container">
                                        @include('admin.components.form.switcher',[

                                            'name'          => 'status',
                                            'value'         => old('status',@$myCard->status ),
                                            'options'       => [__('unblock') => 1,__('block') => 0],
                                            'onload'        => true,
                                            'data_target'   =>@$myCard->id,
                                        ])
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-eye"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("reveal Details") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <div class="toggle-containers">
                                        <input type="checkbox" id="toggle-switch" onchange="handleToggleChange(this)" style="display: none">
                                        <label for="toggle-switch" class="card-eye-btn" style="cursor: pointer;margin-bottom:0"><i class="las la-eye"></i></label>
                                    </div>
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
@if($api_mode == global_const()::SANDBOX)
    <script type="text/javascript" src="https://js.verygoodvault.com/vgs-show/1.5/ACiWvWF9tYAez4BitGpSE68f.js"></script>
@elseif ($api_mode == global_const()::LIVE)
    <script type="text/javascript" src="https://js.verygoodvault.com/vgs-show/1.5/ACcHSbXEBmKzyoAT5fzzyLTu.js"></script>
@endif

<script>
     $(document).ready(function(){
        switcherAjax("{{ setRoute('user.sudo.virtual.card.change.status') }}");
    })
</script>
<script>
   function handleToggleChange(toggle) {
        const selectedValue = toggle.checked ? 1 : 0;
        if (selectedValue === 1) {
            $(toggle).parent().find("i").removeClass('la-eye').addClass("la-eye-slash")
            getSecureData();
        } else {
            var card_pan = "{{ $myCard->maskedPan }}";
            $(toggle).parent().find("i").removeClass('la-eye-slash').addClass("la-eye")
            $('.cardNumber').text(card_pan);
            $('#cvv').text('***')
        }

    }
    function getSecureData(){
            var show = VGSShow.create('{{ $api_vault_id }}');
            var cardToken = "{{ $cardToken }}";
            var cvviframe = show.request({
                    name: 'cvv-text',
                    method: 'GET',
                    path: '/cards/' + "{{ $myCard->card_id }}" + '/secure-data/cvv',
                    headers: {
                        "Authorization": "Bearer " + cardToken
                    },
                    htmlWrapper: 'text',
                    jsonPathSelector: 'data.cvv'
                });
                $('#cvv').text('')
            cvviframe.render('#cvv');
            var cardNumberIframe = show.request({
                    name: 'pan-text',
                    method: 'GET',
                    path: '/cards/' +  "{{ $myCard->card_id }}" + '/secure-data/number',
                    headers: {
                        "Authorization": "Bearer " + cardToken
                    },
                    htmlWrapper: 'text',
                    jsonPathSelector: 'data.number'
                });
                $('.cardNumber').text('')
            cardNumberIframe.render('.cardNumber');
    }
</script>
@endpush
