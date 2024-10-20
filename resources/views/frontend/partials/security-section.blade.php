@php
    $lang = selectedLang();
    $security_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::SECURITY_SECTION);
    $security = App\Models\Admin\SiteSections::getData( $security_slug)->first();
@endphp

<section class="security-section pt-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-12 text-center">
                <div class="section-header">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i> {{ __(@$security->value->language->$lang->heading) }}</span>
                    <h2 class="section-title">{{ __(@$security->value->language->$lang->sub_heading) }}</h2>
                    <p>{{ __(@$security->value->language->$lang->details) }}</p>
                </div>
            </div>
        </div>
        <div class="row mb-30-none justify-content-center">

                @if(isset($security->value->items))
                    @foreach($security->value->items ?? [] as $key => $item)
                    <div class="col-lg-4 col-md-6 mb-30">
                        <div class="security-item">
                            <span class="icon"><i class="{{ __( @$item->language->$lang->icon) }}"></i></span>
                            <div class="security-content">
                                <h4 class="title">{{ __( @$item->language->$lang->title) }}</h4>
                                <p>{{ __( @$item->language->$lang->sub_title) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif

        </div>
    </div>
</section>
