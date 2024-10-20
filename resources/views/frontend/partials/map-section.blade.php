
@php
    $lang = selectedLang();
    $overview_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::OVERVIEW_SECTION);
    $overview = App\Models\Admin\SiteSections::getData( $overview_slug)->first();
    $currencies = App\Models\Admin\currency::count();
    $payment_gateways = App\Models\Admin\PaymentGateway::where('slug','add-money')->count();
    $send_remittamce = App\Models\Transaction::where('type','REMITTANCE')->where('attribute','SEND')->count();
@endphp
<div class="map-section pt-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 text-center">
                <div class="section-header">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i> {{ __(@$overview->value->language->$lang->title) }}</span>
                    <h2 class="section-title">{{ __(@$overview->value->language->$lang->heading) }}</h2>
                    <p>{{ __(@$overview->value->language->$lang->sub_heading) }}</p>
                </div>
            </div>
        </div>
        <div class="map-wrapper">
            <div class="thumb">
                <img src="{{ get_image(@$overview->value->images->map_image,'site-section') }}" alt="map">
            </div>
        </div>
        <div class="map-content">
            <div class="map-statistics-wrapper">
                <div class="statistics-item">
                    <div class="statistics-content">
                        <div class="odo-area">
                            <h3 class="odo-title odometer" data-odometer-final="{{ @$payment_gateways }}">0</h3>
                            <h3 class="title">+</h3>
                        </div>
                        <p>{{ __("Payment Gateway") }}</p>
                    </div>
                </div>
                <div class="statistics-item">
                    <div class="statistics-content">
                        <div class="odo-area">
                            <h3 class="odo-title odometer" data-odometer-final="{{ __( @$currencies) }}">0</h3>
                            <h3 class="title">+</h3>
                        </div>
                        <p>{{ __("Currencies") }}</p>
                    </div>
                </div>
                <div class="statistics-item">
                    <div class="statistics-content">
                        <div class="odo-area">
                            <h3 class="odo-title odometer" data-odometer-final="{{ @$send_remittamce }}">0</h3>
                            <h3 class="title">+</h3>
                        </div>
                        <p>{{ __("Send Remittance") }}</p>
                    </div>
                </div>
            </div>
            <div class="content-bottom">
                <p> {{ __(@$overview->value->language->$lang->botton_text) }}</p>
                <a href="{{ url('/').'/'. @$overview->value->language->$lang->button_link}}"> {{ __(@$overview->value->language->$lang->button_name) }} <i class="las la-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>
