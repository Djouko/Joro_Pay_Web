
<!-- jquery -->
<script src="{{ asset('public/frontend/') }}/js/jquery-3.5.1.min.js"></script>
<!-- bootstrap js -->
<script src="{{ asset('public/frontend/') }}/js/bootstrap.bundle.min.js"></script>
<!-- swipper js -->
<script src="{{ asset('public/frontend/') }}/js/swiper.min.js"></script>
<!-- wow js file -->
{{-- <script src="{{ asset('public/frontend/') }}/js/wow.min.js"></script> --}}
<!-- smooth scroll js -->
<script src="{{ asset('public/frontend/') }}/js/smoothscroll.min.js"></script>
<!-- main -->
<!-- nice select js -->
<script src="{{ asset('public/frontend/js/jquery.nice-select.js') }}"></script>
<script src="{{ asset('public/backend/js/select2.min.js') }}"></script>

<script src="{{ asset('public/frontend/') }}/js/odometer.min.js"></script>
<!-- viewport js -->
<script src="{{ asset('public/frontend/') }}/js/viewport.jquery.js"></script>

<script src="{{ asset('public/frontend/') }}/js/main.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}

<script>
     $(".langSel").on("change", function() {
        window.location.href = "{{route('index')}}/change/"+$(this).val();
    });
</script>

<script>
    function getAllCountries(hitUrl,targetElement = $(".country-select"),errorElement = $(".country-select").siblings(".select2")) {
    if(targetElement.length == 0) {
      return false;
    }
    var CSRF = $("meta[name=csrf-token]").attr("content");
    var data = {
      _token      : CSRF,
    };
    $.post(hitUrl,data,function() {
      // success
      $(errorElement).removeClass("is-invalid");
      $(targetElement).siblings(".invalid-feedback").remove();
    }).done(function(response){
      // Place States to States Field
      var options = "<option selected disabled>{{ __('select Country') }}</option>";
      var selected_old_data = "";
      if($(targetElement).attr("data-old") != null) {
          selected_old_data = $(targetElement).attr("data-old");
      }
      $.each(response,function(index,item) {
          options += `<option value="${item.name}" data-id="${item.id}" data-iso2="${item.iso2}" data-mobile-code="${item.mobile_code}" ${selected_old_data == item.name ? "selected" : ""}>${item.name}</option>`;
      });

      allCountries = response;

      $(targetElement).html(options);
    }).fail(function(response) {
      var faildMessage = "Something went worng! Please try again.";
      var faildElement = `<span class="invalid-feedback" role="alert">
                              <strong>${faildMessage}</strong>
                          </span>`;
      $(errorElement).addClass("is-invalid");
      if($(targetElement).siblings(".invalid-feedback").length != 0) {
          $(targetElement).siblings(".invalid-feedback").text(faildMessage);
      }else {
        errorElement.after(faildElement);
      }
    });
}
</script>

@include('admin.partials.notify')
