
@extends('user.layouts.user_auth')

@push('css')

@endpush

@section('content')
    <section class="account">
        <div class="account-area">
            <div class="account-wrapper">
                <div class="account-logo text-center">
                    <a href="{{ setRoute('index') }}" class="site-logo">
                        <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                                data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                                    alt="site-logo">
                    </a>
                </div>
                <h5 class="title">{{ __(@$page_title) }}</h5>
                {{-- <p>{{ __("Reset your password") }}</p> --}}
                <form class="account-form" action="{{ setRoute('user.password.reset',$token) }}" method="POST">
                    @csrf
                    <div class="row ml-b-20">
                        <div class="col-lg-12 form-group show_hide_password" >
                            <input type="password"  class="form-control form--control" name="password" placeholder="New Password">
                            <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                        </div>
                        <div class="col-lg-12 form-group show_hide_password-2" >
                            <input type="password"  class="form-control form--control" name="password_confirmation" placeholder="Confirmed Password">
                            <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                        </div>

                        <div class="col-lg-12 form-group text-center">
                            <button type="submit" class="btn--base w-100 btn-loading">{{ __("Reset Password") }} <i class="las la-arrow-right"></i></button>
                        </div>
                        <div class="or-area">
                            <span class="or-line"></span>
                            <span class="or-title">Or</span>
                            <span class="or-line"></span>
                        </div>
                        <div class="col-lg-12 text-center">
                            <div class="account-item">
                                <label>{{ __("Don't Have An Account?") }} <a href="{{ setRoute('user.register') }}" class="account-control-btn">{{ __("Register Now") }}</a></label>
                            </div>
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
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if($('#show_hide_password input').attr("type") == "text"){
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass( "fa-eye-slash" );
                $('#show_hide_password i').removeClass( "fa-eye" );
            }else if($('#show_hide_password input').attr("type") == "password"){
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass( "fa-eye-slash" );
                $('#show_hide_password i').addClass( "fa-eye" );
            }
        });
    });
</script>
@endpush
