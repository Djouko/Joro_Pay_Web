@php
    $lang = selectedLang();
    $choose_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CHOOSE_SECTION);
    $choose = App\Models\Admin\SiteSections::getData( $choose_slug)->first();
@endphp
<section class="choose-us-section pt-120">
    <div class="container">
        <div class="choose-us-main-wrapper">
            <div class="row justify-content-center">
                <div class="col-xl-7 text-center">
                    <div class="section-header">
                        <span class="section-sub-titel"><i class="fas fa-qrcode"></i>{{ __(@$choose->value->language->$lang->heading) }}</span>
                        <h2 class="section-title">{{ __(@$choose->value->language->$lang->sub_heading) }}</h2>
                        <p>{{ __(@$choose->value->language->$lang->details) }}</p>
                    </div>
                </div>
                <div class="row mb-60-none justify-content-center">
                @if(isset($choose->value->items))
                @php
                    $num = 0;
                @endphp
                    @foreach($choose->value->items ?? [] as $key => $item)
                    @php
                        $num += 1;
                    @endphp
                    <div class="col-lg-4 col-md-6 mb-60">
                        <div class="choose-us-item">
                            <div class="icon-wrapper">
                                <div class="icon-area">
                                    <i class="{{ __( @$item->language->$lang->icon) }}"></i>
                                    <span class="choose-badge">{{'0'.@$num }}</span>
                                </div>
                            </div>
                            <h3 class="title">{{ __( @$item->language->$lang->title) }}</h3>
                            <p>{{ __( @$item->language->$lang->sub_title) }}</p>
                        </div>
                    </div>
                    @endforeach
                @endif
                </div>
            </div>
        </div>
    </div>
</section>
