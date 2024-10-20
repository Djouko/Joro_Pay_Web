@php
    $lang = selectedLang();
    $testimonial_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::TESTIMONIAL_SECTION);
    $testimonial = App\Models\Admin\SiteSections::getData( $testimonial_slug)->first();

@endphp
<section class="testimonial-section ptb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 text-center">
                <div class="section-header">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i>{{ __(@$testimonial->value->language->$lang->title) }}</span>
                    <h2 class="section-title">{{ __(@$testimonial->value->language->$lang->heading) }}</h2>
                    <p>{{ __(@$testimonial->value->language->$lang->sub_heading) }}</p>
                </div>
            </div>
        </div>
        <div class="testimonial-slider-wrapper">
            <div class="testimonial-slider">
                <div class="swiper-wrapper">
                    @if(isset($testimonial->value->items))
                    @foreach($testimonial->value->items ?? [] as $key => $item)
                    <div class="swiper-slide">
                        <div class="testimonial-item">
                            <div class="testimonial-user-area">
                                <div class="user-area">
                                    <img src="{{ get_image(@$item->image ,'site-section') }}" alt="user">
                                </div>
                                <div class="title-area">
                                    <h5>{{ __(@$item->language->$lang->name )}}</h5>
                                    <p>{{ __(@$item->language->$lang->designation )}}</p>
                                </div>
                            </div>
                            <h4 class="testimonial-title">{{ __(@$item->language->$lang->header )}}</h4>
                            <p>{{ __(@$item->language->$lang->details )}}</p>
                            @php
                                $rating = $item->language->$lang->rating??"5";
                            @endphp
                            <ul class="testimonial-icon-list">
                                @for($i = 0; $i <  $rating ; $i++)
                                <li><i class="fas fa-star"></i></li>
                                @endfor
                            </ul>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
                <div class="slider-nav-area">
                    <div class="slider-prev slider-nav">
                        <i class="las la-angle-left"></i>
                    </div>
                    <div class="slider-next slider-nav">
                        <i class="las la-angle-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
