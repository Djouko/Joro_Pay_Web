@extends('user.layouts.master')

@push('css')
<link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/virtual-card.css">
<style>
.btn-ring {
    position: absolute;
   top: 0px;
    right: 0px;

}
.cardNumber iframe{
    display: block;
    width: 163px;
    height: 28px;
    background: #fff;
    padding: 5px;
    border-radius: 3px;
}
#cvv iframe {
    display: block;
    width: 37px;
    height: 28px;
    background: #fff;
    padding: 5px;
    border-radius: 3px;
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
<div class="body-wrapper ptb-40">
    <div class="row">
        <div class="col-xl-10">
            <div class="card-prevew pt-2">
                <div class="preview-list-wrapper">
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-credit-card"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("cardI d") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ @$myCard->card_id }}</span>
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
                            <span>{{Str::upper( @$myCard->currency) }}</span>
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
                            <span>{{ $myCard->expiryMonth > 9 ?'':'0' }}{{$myCard->expiryMonth }}/{{$myCard->expiryYear }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-hourglass-start"></i>
                                </div>
                                <div class="preview-list-user-content " >
                                    <span>{{ __("Cvv") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right text-white">
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
                                    'options'       => [__('active') => 1,__('Inactive') => 0],
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
@endsection

@push('script')
<script>
    $(document).ready(function(){
       switcherAjax("{{ setRoute('user.stripe.virtual.card.change.status') }}");
   })

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
        var card_id = "{{ $myCard->card_id }}";
        $.ajax({
            url: "{{route('user.stripe.virtual.card.sensitive.data')}}",
            type: "POST",
            data: {
                card_id: card_id,
                _token: '{{csrf_token()}}'
            },
            dataType: 'json',
            success: function (res) {
                var data = res.result;
                if(data.status == true){
                    $('.cardNumber').text(data.number);
                    $('#cvv').text(data.cvc)
                }else{
                    $('.cardNumber').text("{{ $myCard->maskedPan }}");
                    $('#cvv').text("***")
                    throwMessage('error',[data.message]);
                }

            }
        });

   }

</script>

@endpush
