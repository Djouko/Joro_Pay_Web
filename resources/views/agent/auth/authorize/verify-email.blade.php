@extends('agent.layouts.user_auth')

@push('css')

@endpush

@section('content')
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<section class="account">
    <div class="account-area">
        <div class="account-wrapper">
            <div class="account-logo text-center">
                <a class="site-logo" href="{{ setRoute('index') }}">
                    <img src="{{ get_logo_agent($basic_settings) }}"  data-white_img="{{ get_logo_agent($basic_settings,'white') }}"
                    data-dark_img="{{ get_logo_agent($basic_settings,'dark') }}"
                        alt="site-logo">
                </a>
            </div>
            <h5 class="title text-center">{{ __("Please enter the code") }}</h5>
            <p class="d-block text-center">{{ __("We sent a 6 digit code to your email") }} <strong>{{ @$data->email }}</strong></p>
            <form class="account-form" action="{{ setRoute('agent.verify.code',$token) }}" method="POST">
                @csrf
                <div id="recaptcha-container"></div>
                <div class="row ml-b-20">
                    <div class="col-lg-12 form-group">
                        <input class="otp" required name="code[]"   type="text" oninput='digitValidate(this)' onkeyup='tabChange(1)'
                        maxlength=1 >
                        <input class="otp" required name="code[]"  type="text" oninput='digitValidate(this)' onkeyup='tabChange(2)'
                            maxlength=1 >
                        <input class="otp" required name="code[]"  type="text" oninput='digitValidate(this)' onkeyup='tabChange(3)'
                            maxlength=1 >
                        <input class="otp" required name="code[]"  type="text" oninput='digitValidate(this)' onkeyup='tabChange(4)'
                            maxlength=1 >
                        <input class="otp" required name="code[]"  type="text" oninput='digitValidate(this)' onkeyup='tabChange(5)'
                            maxlength=1 >
                        <input class="otp" required name="code[]"  type="text" oninput='digitValidate(this)' onkeyup='tabChange(6)'
                            maxlength=1 >

                    </div>

                    <div class="col-lg-12 form-group text-end">
                        <div class="time-area">{{ __("You can resend the code after") }} <span id="time"></span></div>
                    </div>
                    <div class="col-lg-12 form-group text-center">
                        <button type="submit"  class="btn--base w-100 btn-loading">{{ __("Submit") }}</button>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label>{{ __("already Have An Account") }} <a href="{{ setRoute('agent.login') }}" class="account-control-btn">{{ __("Login Now") }}</a></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

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
    function resetTime (second = 60) {
        var coundDownSec = second;
        var countDownDate = new Date();
        countDownDate.setMinutes(countDownDate.getMinutes() + 120);
        var x = setInterval(function () {  // Get today's date and time
            var now = new Date().getTime();  // Find the distance between now and the count down date
            var distance = countDownDate - now;  // Time calculations for days, hours, minutes and seconds  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * coundDownSec)) / (1000 * coundDownSec));
            var seconds = Math.floor((distance % (1000 * coundDownSec)) / 1000);  // Output the result in an element with id="time"
            document.getElementById("time").innerHTML =seconds + "s ";  // If the count down is over, write some text

            if (distance < 0 || second < 2 ) {
                // alert();
                clearInterval(x);
                // document.getElementById("time").innerHTML = "RESEND";
                document.querySelector(".time-area").innerHTML = "Didn't get the code? <a href='{{ setRoute('agent.resend.code') }}' class='text--danger'>Resend</a>";
            }

            second--
        }, 1000);
    }

    resetTime();
</script>


@endpush
