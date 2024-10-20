
@php
    $lang = selectedLang();
    $brand_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BRAND_SECTION);
    $brand = App\Models\Admin\SiteSections::getData( $brand_slug)->first();
@endphp
<section class="brand-section pb-20">
    <div class="container">
        <div class="line-head">
            <h6 class="title">{{ __(@$brand->value->language->$lang->title) }}</h6>
        </div>
        <div class="brand-slider">
            <div class="swiper-wrapper">
                    @if(isset($brand->value->items))
                        @foreach($brand->value->items ?? [] as $key => $item)
                        <div class="swiper-slide">
                            <div class="brand-item">
                                <img src="{{ get_image(@$item->image ,'site-section') }}" alt="brand">
                            </div>
                        </div>
                        @endforeach
                    @endif
            </div>
        </div>
    </div>
</section>
