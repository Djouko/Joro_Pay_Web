@extends('user.layouts.user_auth')

@php
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('slug',"terms-and-conditions")->where('status',1)->first();
@endphp

@section('content')
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account kyc">
    <div id="body-overlay" class="body-overlay"></div>
    <div class="account-area">
        <div class="account-wrapper kyc">
            <div class="account-logo text-center">
                <a href="{{ setRoute('index') }}" class="site-logo">
                    <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                    data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                        alt="site-logo">
                </a>
            </div>
            <h5 class="title">{{ __("KYC Form") }}</h5>
            <p>{{ __("Please input all the fild for login to your account to get access to your dashboard.") }}</p>
            <form class="account-form" action="{{ setRoute('user.register.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row ml-b-20">
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                            'name'          => "firstname",
                          'placeholder'   => __("first Name"),
                            'value'         => old("firstname"),
                        ])
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                                    'name'          => "lastname",
                                    'placeholder'   => __("last Name"),
                                    'value'         => old("lastname"),
                        ])
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        <select name="country" class="form--control country-select select2-basic" > </select>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext">@</span>
                            </div>
                            <input type="email" name="email" class="form--control" placeholder="Email" value="{{ old('email',@$email) }}" readonly>

                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                            'name'          => "city",
                            'placeholder'   =>__( "city"),
                            'value'         => old("city"),
                        ])
                    </select>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                                    'name'          => "zip_code",
                                 'placeholder'   =>__( "enter Zip Code"),
                                    'value'         => old('zip_code',auth()->user()->address->zip ?? "")
                                ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="input-group">
                            <div class="input-group-text phone-code">+</div>
                            <input class="phone-code" type="hidden" name="phone_code" value="" />
                            <input type="text" class="form--control" placeholder="{{ __("enter Phone Number") }}" name="phone" value="">
                        </div>
                    </div>
                    @if($basic_settings->kyc_verification)
                        @include('user.components.register-kyc',compact("kyc_fields"))
                    @endif
                    <div class="col-lg-6 col-md-4 form-group show_hide_password" id="">
                        <input type="password" class="form--control" name="password"placeholder="{{ __('enter Password') }}" required>
                        <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>


                    </div>
                    <div class="col-lg-6 col-md-4 form-group show_hide_password-2" id="">
                        <input type="password" class="form--control" name="password_confirmation"placeholder="{{ __('confirm Password') }}" required>
                        <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>

                    </div>
                    @if($basic_settings->agree_policy)
                    <div class="col-lg-12 form-group">
                        <div class="custom-check-group">
                            <div class="custom-check-group mb-0">
                                <input type="checkbox" id="level-1" name="agree">
                                <label for="level-1" class="mb-0">{{ __("I have agreed with") }} <a href="{{  $policies != null? setRoute('useful.link',$policies->slug):"javascript:void(0)" }}">{{__("Terms Of Use & Privacy Policy")}}</a></label>
                            </div>

                        </div>
                    </div>
                    @endif
                    <div class="col-lg-12 form-group text-center">
                        <button type="submit" class="btn--base w-100 btn-loading">{{ __("Register") }} <i class="fas fa-arrow-alt-circle-right ms-1"></i></button>
                    </div>
                    <div class="or-area">
                        <span class="or-line"></span>
                        <span class="or-title">Or</span>
                        <span class="or-line"></span>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label>{{ __("already Have An Account") }} <a href="{{ setRoute('user.login') }}" class="account-control-btn">{{ __("Login Now") }}</a></label>
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
      getAllCountries("{{ setRoute('global.countries') }}");
        $(document).ready(function(){
            $("select[name=country]").on('change',function(){
                var phoneCode = $("select[name=country] :selected").attr("data-mobile-code");
                placePhoneCode(phoneCode);
            });
            countrySelect(".country-select",$(".country-select").siblings(".select2"));


        });
</script>

@endpush
