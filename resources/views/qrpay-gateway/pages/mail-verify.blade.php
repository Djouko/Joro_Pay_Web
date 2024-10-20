@extends('qrpay-gateway.layouts.master')

@push('css')

@endpush

@section('content')
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        Start Account
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


    <section class="account">
        <div class="account-area">
            <div class="account-wrapper">
                <div class="account-logo text-center">
                    {{-- @if ($payment_gateway_image)
                        <a class="site-logo" href="javascript:void(0)"><img src="{{ $payment_gateway_image }}" alt="logo"></a>
                    @endif --}}
                    @if ($payment_gateway_image)
                        <a class="site-logo" href="javascript:void(0)"><img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                            data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                                alt="site-logo"></a>
                    @endif
                </div>
                <h4 class="title">{{ __("Account verification") }}</h4>
                <p>{{ __("Please check your email inbox to get verification code") }}</p>
                <form action="{{ $form_submit_url }}" class="account-form bounce-safe" method="POST">
                    @csrf
                    <div class="row ml-b-20">
                        <div class="col-lg-12 form-group">
                            <input class="otp" name="code[]" type="text" oninput='digitValidate(this)' onkeyup='tabChange(1)'
                            maxlength=1 required>
                        <input class="otp" name="code[]" type="text" oninput='digitValidate(this)' onkeyup='tabChange(2)'
                            maxlength=1 required>
                        <input class="otp" name="code[]" type="text" oninput='digitValidate(this)' onkeyup='tabChange(3)'
                            maxlength=1 required>
                        <input class="otp" name="code[]" type="text" oninput='digitValidate(this)' onkeyup='tabChange(4)'
                            maxlength=1 required>
                        <input class="otp" name="code[]" type="text" oninput='digitValidate(this)' onkeyup='tabChange(5)'
                            maxlength=1 required>
                        <input class="otp" name="code[]" type="text" oninput='digitValidate(this)' onkeyup='tabChange(6)'
                            maxlength=1 required>
                        </div>

                        <div class="col-lg-12 form-group text-center">
                            <button type="submit" class="btn--base w-100 btn-loading">{{ __("Verify") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <ul class="bg-bubbles">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        End Account
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection


@push('script')
<script>
    let digitValidate = function (ele) {
        ele.value = ele.value.replace(/[^0-9]/g, '');
    }

    let tabChange = function (val) {
        let ele = document.querySelectorAll('.otp');
        if (ele[val - 1].value != '') {
            ele[val].focus()
        } else if (ele[val - 1].value == '') {
            ele[val - 2].focus()
        }
    }
</script>

<script>
    $(".otp").parents("form").find("input[type=submit],button[type=submit]").click(function(e){
        // e.preventDefault();
        var otps = $(this).parents("form").find(".otp");
        var result = true;
        $.each(otps,function(index,item){
            if($(item).val() == "" || $(item).val() == null) {
                result = false;
            }
        });

        if(result == false) {
            $(this).parents("form").find(".otp").addClass("required");
        }else {
            $(this).parents("form").find(".otp").removeClass("required");
            $(this).parents("form").submit();
        }
    });
</script>
@endpush
