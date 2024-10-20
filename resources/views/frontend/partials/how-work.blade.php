@php
    $lang = selectedLang();
    $work_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::WORK_SECTION);
    $work = App\Models\Admin\SiteSections::getData( $work_slug)->first();
@endphp
<section class="how-it-work-section pt-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-12 text-center">
                <div class="section-header">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i> {{ __(@$work->value->language->$lang->title) }}</span>
                    <h2 class="section-title">{{ __(@$work->value->language->$lang->heading) }}</h2>
                    <p>{{ __(@$work->value->language->$lang->sub_heading) }}</p>
                </div>
            </div>
        </div>
        <div class="how-it-works-wrapper">
            <div class="row justify-content-center mb-30-none">
                @if(isset($work->value->items))
                @php
                    $num =0
                @endphp
                @foreach($work->value->items ?? [] as $key => $item)
                @php
                    $num += 1;
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-30">
                    <div class="how-it-works-item">
                        <div class="how-it-works-icon-wrapper">
                            <div class="how-it-works-icon">
                                <i class="{{ @$item->language->$lang->icon }}"></i>
                            </div>
                        </div>
                        <div class="how-it-works-content">
                            <span  class="sub-title">{{ __("Step") }} {{ __($num)}}</span>
                            <h4 class="title">{{ @$item->language->$lang->name }}</h4>
                        </div>
                        @if($num != 4)
                        <span class="process-devider"></span>
                        @endif

                    </div>
                </div>
                @endforeach
                @endif

            </div>
        </div>
    </div>
</section>
