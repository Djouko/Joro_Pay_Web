@extends('merchant.layouts.user_auth')

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
                    <img src="{{ get_logo_merchant($basic_settings) }}"  data-white_img="{{ get_logo_merchant($basic_settings,'white') }}"
                    data-dark_img="{{ get_logo_merchant($basic_settings,'dark') }}"
                        alt="site-logo">
                </a>
            </div>
            <h5 class="title text-center">{{ __("Please enter the code") }}</h5>
            <p class="d-block text-center">{{__("We sent a 6 digit code here")}} <strong>+{{ auth()->user()->full_mobile }}</strong></p>
            <form class="account-form" action="{{ setRoute('merchant.authorize.verify.code') }}" method="POST">
                @csrf
                <div id="recaptcha-container"></div>
                <div class="row ml-b-20">
                    <div class="col-lg-12 form-group">
                        <input class="otp code1" type="text" oninput='digitValidate(this)' onkeyup='tabChange(1)'
                            maxlength=1 required>
                        <input class="otp code2" type="text" oninput='digitValidate(this)' onkeyup='tabChange(2)'
                            maxlength=1 required>
                        <input class="otp code3" type="text" oninput='digitValidate(this)' onkeyup='tabChange(3)'
                            maxlength=1 required>
                        <input class="otp code4" type="text" oninput='digitValidate(this)' onkeyup='tabChange(4)'
                            maxlength=1 required>
                        <input class="otp code5" type="text" oninput='digitValidate(this)' onkeyup='tabChange(5)'
                            maxlength=1 required>
                        <input class="otp code6" type="text" oninput='digitValidate(this)' onkeyup='tabChange(6)'
                            maxlength=1 required>
                    </div>

                    <div class="col-lg-12 form-group text-end">
                        <div class="time-area">{{ __("You can resend the code after") }} <span id="time"></span></div>
                    </div>
                    <div class="col-lg-12 form-group text-center">
                        <button type="button" onclick="verify()" class="btn--base w-100 btn-loading verifyCode">{{__("Verify")}}</button>
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
    function resetTime (second = 20) {
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
                document.querySelector(".time-area").innerHTML = "Didn't get the code? <a href='javascript:void(0)' onclick='resendOtp()' class='text--danger'>Resend</a>";
            }

            second--
        }, 1000);
    }

    resetTime();
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
<script>
    window.onload = function () {
        render();
    };
    function render() {
        // window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container');
        // recaptchaVerifier.render();
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier(
                "recaptcha-container",
                {
                size: "invisible",
                callback: function(response) {
                    submitPhoneNumberAuth();
                }
                }
            );
    }
    function verify() {
            var code1 = $(".code1").val();
            var code2 = $(".code2").val();
            var code3 = $(".code3").val();
            var code4 = $(".code4").val();
            var code5 = $(".code5").val();
            var code6 = $(".code6").val()
            if(code1 == '' || code2 == '' || code3 == '' || code4 == '' || code5 == '' || code6 == ''){
                throwMessage('error',["Please, enter full code "]);
                setTimeout(function () {
                        location.reload();
                },1000);
                return false;

            }
            var code = code1+code2+code3+code4+code5+code6;
            var codeToken = "{{$firbase_token??''}}";
            var credential = firebase.auth.PhoneAuthProvider.credential(codeToken, code);
            firebase.auth().signInWithCredential(credential).then((result)=>{
                // throwMessage('success',["Sms Verification Successfully"]);
                $('.verifyCode').parents("form").submit();
            }).catch((error)=>{
                throwMessage('error',[error.message]);
                setTimeout(function () {
                        location.reload();
                },1000);
                return false;

            })

            // });
        }
    function resendOtp() {
        var number ="{{ $phone??''}}";
            firebase.auth().signInWithPhoneNumber(number, window.recaptchaVerifier).then(function (confirmationResult) {
                window.confirmationResult = confirmationResult;
                coderesult = confirmationResult;
                var firebaseToken = coderesult.verificationId;

            $.ajax({
                headers: { "X-CSRF-Token": $("meta[name=csrf_token]").attr("content") },
                    type:  "GET",
                    dataType: "json",
                    url:'{{ route("merchant.authorize.send.code") }}',
                    data:{'firebaseToken': firebaseToken},
                    success: function(data){
                        throwMessage('success',["Sms Code Send Successfully"]);
                        setTimeout(function(){// wait for 5 secs(2)
                            location.reload(); // then reload the page.(3)
                        },1000);

                    }

                });
            }).catch(function (error) {
                throwMessage('error',[error.message]);
            });
        }
</script>
@endpush
