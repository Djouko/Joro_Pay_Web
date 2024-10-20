@php
    $lang = selectedLang();
    $about_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::ABOUT_SECTION);
    $about = App\Models\Admin\SiteSections::getData($about_slug)->first();

@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="about-section pt-120">
    <div class="container">
        <div class="row mb-30-none align-items-center">
            <div class="col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="about-thumb-area">
                    <div class="about-thumb">
                        <img src="{{ get_image(@$about->value->images->image,'site-section') }}" alt="about">
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="about-content-area">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="section-header">
                                <span class="section-sub-titel"><i class="fas fa-qrcode"></i>{{ __(@$about->value->language->$lang->heading) }}</span>
                                <h2 class="section-title">{{ __(@$about->value->language->$lang->sub_heading) }}</h2>
                                <p>{{ __(@$about->value->language->$lang->details) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="about-item-wrapper">
                        @if(isset($about->value->items))
                        @php
                            $numKey = 0;
                        @endphp
                        @foreach($about->value->items ?? [] as $key => $item)
                        @php
                            $numKey += 1;
                        @endphp
                        <div class="about-content-item">
                            <div class="icon-area {{  $numKey == 1 ?'active':'' }}">
                                <i class="{{ @$item->language->$lang->icon }} "></i>
                            </div>
                            <div class="title-area">
                                <h4 class="title">{{ __(@$item->language->$lang->title )}}</h4>
                                <span class="sub-title">{{__( @$item->language->$lang->sub_title) }}</span>
                            </div>
                        </div>
                        @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
