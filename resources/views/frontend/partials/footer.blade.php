@php
    $lang = selectedLang();
    $footer_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::FOOTER_SECTION);
    $footer = App\Models\Admin\SiteSections::getData( $footer_slug)->first();
    $contact_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CONTACT_SECTION);
    $contact = App\Models\Admin\SiteSections::getData( $contact_slug)->first();
    $app_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::APP_SECTION);
    $appInfo = App\Models\Admin\SiteSections::getData( $app_slug)->first();
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('status',1)->get();
    $system_default    = $default_language_code;

@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start footer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<footer class="footer-section pt-60 bg_img" data-background="{{ get_image(@$footer->value->images->bg_image,'site-section') }}">
    <div class="container">
        <div class="footer-wrapper">
            <div class="row mb-30-none">
                <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-6 mb-30">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <a class="site-logo site-title" href="{{route('index')}}">
                                <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                                data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                                    alt="site-logo">
                            </a>
                        </div>
                        <div class="footer-content">
                            <p>{{ __(@$footer->value->language->$lang->details) }}</p>
                        </div>
                        <div class="footer-content-bottom">
                            <ul class="footer-list logo">
                                <li><a href="tel:{{ __(@$contact->value->language->$lang->mobile) }}"><i class="las la-phone-volume me-1"></i> +{{ __(@$contact->value->language->$lang->mobile) }}</a></li>
                                <li><a href="mailto:{{ __(@$contact->value->language->$lang->email) }}"><i class="las la-envelope me-1"></i>{{ __(@$contact->value->language->$lang->email) }}</a></li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-2 col-lg-2 col-md-6 col-sm-6 mb-30">
                    <div class="footer-widget">
                        <h4 class="widget-title">{{__("Useful LInks")}}</h4>
                        <ul class="footer-list">
                            @foreach ($policies ?? [] as $key=> $data)
                            <li><a href="{{ setRoute('useful.link',$data->slug) }}">{{ @$data->title->language->$lang->title??@$data->title->language->$system_default->title }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-xxl-2 col-xl-3 col-lg-2 col-md-6 col-sm-6 mb-30">
                    <div class="footer-widget">
                        <h4 class="widget-title">{{ __("Download App") }}</h4>
                        <p>{{ __(@$footer->value->language->$lang->app_text) }}</p>
                        <ul class="footer-list two">
                            <li><a href="{{@$appInfo->value->language->$lang->google_link }}"  target="_blank" class="app-img"><img src="{{ get_image(@$appInfo->value->images->google_play,'site-section') }}" alt="app"></a></li>
                            <li> <a href="{{@$appInfo->value->language->$lang->apple_link }}"  target="_blank" class="app-img"><img src="{{ get_image(@$appInfo->value->images->appple_store,'site-section') }}" alt="app"></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-6 col-sm-6 mb-30">
                    <div class="footer-widget">
                        <h4 class="widget-title">{{ __("Newsletter") }}</h4>
                        <p>{{ __(@$footer->value->language->$lang->newsltter_details) }}</p>
                        <ul class="footer-list two">
                            <form action="{{ setRoute('newsletter.submit') }}" method="POST">
                                @csrf
                            <li>
                                <input type="text" name="fullname" placeholder="{{ __("name") }}" class="form--control">
                                <span class="input-icon"><i class="las la-user"></i></span>
                                @error('fullname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </li>
                            <li>
                                <input type="email" name="email" placeholder="{{ __("enter Email Address") }}" class="form--control">
                                <span class="input-icon"><i class="las la-envelope"></i></span>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </li>
                            <li>
                                <button type="submit" class="btn--base sub-btn btn-loading">{{ __("Subscribe") }}<i class="las la-arrow-right ms-1"></i></button>
                            </li>
                           </form>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<div class="copyright-area mt-30 ptb-10">
    <div class="container">
        <div class="copyright-wrapper">
            <p>{{ __(@$footer->value->language->$lang->footer_text) }} <a class="fw-bold" href="{{ setRoute('index') }}"> {{ $basic_settings->site_name }}</a></p>
            <ul class="footer-social">
                @if(isset($footer->value->items))
                    @foreach($footer->value->items ?? [] as $key => $item)
                    <li><a href="{{ @$item->language->$lang->link }}" target="_blank"><i class="{{ @$item->language->$lang->social_icon }}"></i></a></li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End footer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
