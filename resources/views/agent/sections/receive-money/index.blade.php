@extends('agent.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
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
    <div class="row mb-30-none justify-content-center">
        <div class="col-xl-8 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __("Money Receive") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="card-body">
                            <form class="card-form">
                                <div class="row">
                                    <div class="col-xl-12 col-lg-12 form-group">
                                        <label>{{ __("qr Address") }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form--control" value="{{ @$uniqueCode }}" readonly id="referralURL">
                                            <div class="input-group-text copytext" id="copyBoard"><i class="las la-copy"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-xl-12 col-lg-12 form-group">
                                        <div class="qr-code-thumb text-center">
                                            <img class="mx-auto" src="{{ @$qrCode }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12">
                                    {{-- <a href="whatsapp://send?text=Your%20QR%20Code%20Image%20Link%20Here">Send via WhatsApp</a>

                                    <br>
                                    <a href="fb-messenger://share/?link=Your%20QR%20Code%20Image%20Link%20Here">Send via Messenger</a>
                                <br>
                                <a href="https://web.telegram.org/#/im?p=@username&text=Your%20QR%20Code%20Image%20Link%20Here">Send via Telegram</a> --}}

                                    <button type="button" class="btn--base w-100" data-bs-toggle="modal" data-bs-target="#exampleModal">{{ __("Share") }} <i class="fas fa-share-square ms-1"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">{{ __("Share Qr Code") }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="footer-download d-flex justify-content-center">
                @php
                    $whatsappLink = 'whatsapp://send?text=' . urlencode($qrCode);

                @endphp

        <p style="margin-right:10px; font-size:18px;" >Share By: </p>
        <a href="{{ $whatsappLink }}" target="_blank" class="ml-3">
            <i class="fab fa-whatsapp text-success" style=" font-size:22px; margin-top:4px"></i>
        </a>
            </div>
        </div>
      </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function($){
        "use strict";

        $('.copytext').on('click',function(){
            var copyText = document.getElementById("referralURL");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            throwMessage('success',["Copied: " + copyText.value]);
        });
    })(jQuery);
</script>
@endpush
