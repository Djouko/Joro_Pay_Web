   @php
    $lang = selectedLang();
    $app_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::APP_SECTION);
    $appInfo = App\Models\Admin\SiteSections::getData( $app_slug)->first();
   @endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    download app Modal
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">{{ __("Download now") }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="footer-download d-flex justify-content-around">
                <a href="{{@$appInfo->value->language->$lang->google_link }}"  target="_blank"><img src="{{ get_image(@$appInfo->value->images->google_play,'site-section') }}" alt="app"></a>
                <a href="{{@$appInfo->value->language->$lang->apple_link }}"  target="_blank"><img src="{{ get_image(@$appInfo->value->images->appple_store,'site-section') }}" alt="app"></a>
            </div>
        </div>
      </div>
    </div>
</div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End download app Modal
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
