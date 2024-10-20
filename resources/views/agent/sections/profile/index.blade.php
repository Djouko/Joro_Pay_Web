@extends('agent.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
        ]
    ], 'active' => __("profile")])
@endsection

@section('content')

    <div class="body-wrapper">
        <div class="row mb-20-none">
            <div class="col-xl-6 col-lg-6 mb-20">
                <div class="custom-card mt-10">
                    <div class="dashboard-header-wrapper">
                        <h4 class="title">{{ __('Profile Settings') }}</h4>
                        <a href="javascript:void(0)" class="btn--base btn btn-sm delete-btn">{{ __("delete Account") }}</a>
                    </div>
                    <div class="card-body profile-body-wrapper">
                        <form class="card-form" method="POST" action="{{ setRoute('agent.profile.update') }} " enctype="multipart/form-data">
                            @csrf
                            @method("PUT")
                            <div class="profile-settings-wrapper">
                                <div class="preview-thumb profile-wallpaper">
                                    <div class="avatar-preview">
                                        <div class="profilePicPreview agent bg_img" data-background="{{ asset('public/frontend/') }}/images/element/virtual-card.png"></div>
                                    </div>
                                </div>
                                <div class="profile-thumb-content">
                                    <div class="preview-thumb profile-thumb">
                                        <div class="avatar-preview">
                                            <div class="profilePicPreview bg_img" data-background="{{ auth()->user()->agentImage }}"></div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input type='file' class="profilePicUpload" name="image" id="profilePicUpload2"
                                                accept=".png, .jpg, .jpeg" />
                                            <label for="profilePicUpload2"><i class="las la-upload"></i></label>
                                        </div>
                                    </div>
                                    <div class="profile-content">
                                        <h6 class="username">{{ auth()->user()->username }}</h6>
                                        <ul class="user-info-list mt-md-2">
                                            <li><i class="las la-envelope"></i>{{ auth()->user()->email }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-form-area">
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 form-group">
                                        @include('admin.components.form.input',[
                                             'label'         => __('first Name')."<span>*</span>",
                                            'name'          => "firstname",
                                           'placeholder'   => __("enter First Name"),
                                            'value'         => old('firstname',auth()->user()->firstname)
                                        ])
                                    </div>
                                    <div class="col-xl-4 col-lg-4 form-group">
                                        @include('admin.components.form.input',[
                                            'label'         => __('last Name')."<span>*</span>",
                                            'name'          => "lastname",
                                           'placeholder'   => __("enter Last Name"),
                                            'value'         => old('lastname',auth()->user()->lastname)
                                        ])
                                    </div>
                                    <div class="col-xl-4 col-lg-4 form-group">
                                        @include('admin.components.form.input',[
                                            'label'         => __("appLStoreName")."<span>*</span>",
                                            'name'          => "store_name",
                                            'placeholder'   => __("appLEnterStoreName"),
                                            'value'         => old('store_name',auth()->user()->store_name)
                                        ])
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("country") }}<span>*</span></label>
                                        <select name="country" class="form--control select2-auto-tokenize country-select" data-placeholder="{{ __('select Country') }}" data-old="{{ old('country',auth()->user()->address->country ?? "") }}">
                                        </select>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Phone") }}<span>*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-text phone-code">+{{ auth()->user()->mobile_code }}</div>
                                            <input class="phone-code" type="hidden" name="phone_code" value="{{ auth()->user()->mobile_code }}" />
                                            <input type="text" class="form--control" placeholder="{{ __("enter Phone Number") }}" name="phone" value="{{ old('phone',auth()->user()->mobile) }}">
                                        </div>
                                        @error("phone")
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        @include('admin.components.form.input',[
                                          'label'         => __("address"),
                                            'name'          => "address",
                                             'placeholder'   => __("enter Address"),
                                            'value'         => old('address',auth()->user()->address->address ?? "")
                                        ])
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        @php
                                        $old_city = old('city',auth()->user()->address->city ?? "");
                                        @endphp
                                        @include('admin.components.form.input',[
                                          'label'         => __("city"),
                                            'name'          => "city",
                                           'placeholder'   =>__('enter City'),
                                            'value'         => old('city', $old_city)
                                        ])

                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        @php
                                        $old_state = old('state',auth()->user()->address->state ?? "");
                                    @endphp

                                    @include('admin.components.form.input',[
                                       'label'         => __("state"),
                                        'name'          => "state",
                                       'placeholder'   => __('enter State'),
                                        'value'         => old('state', $old_state)
                                    ])
                                    </div>
                                    <div class="col-xl-6 col-lg-6 form-group">
                                        @include('admin.components.form.input',[
                                            'label'         => __("zip Code"),
                                            'name'          => "zip_code",
                                            'placeholder'   => __('enter Zip Code'),
                                            'value'         => old('zip_code',auth()->user()->address->zip ?? "")
                                        ])
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading">{{ __("update") }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 mb-20">
                <div class="custom-card mt-10">
                    <div class="dashboard-header-wrapper">
                        <h4 class="title">{{ __("Change Password") }}</h4>
                    </div>
                    <div class="card-body">
                        <form class="card-form" action="{{ setRoute('agent.profile.password.update') }}" method="POST">
                            @csrf
                            @method("PUT")
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group show_hide_password">
                                    @include('admin.components.form.input',[
                                   'label'     => __("Current Password")."<span>*</span>",
                                    'name'      => "current_password",
                                    'type'      => "password",
                                    'placeholder'   => __("enter Password"),
                                ])
                                <a href="javascript:void(0)" class="show-pass profile"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group show_hide_password-2">
                                    @include('admin.components.form.input',[
                                      'label'     => __("new Password")."<span>*</span>",
                                    'name'      => "password",
                                    'type'      => "password",
                                    'placeholder'   => __("enter Password"),
                                ])
                                <a href="javascript:void(0)" class="show-pass profile"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group show_hide_password-3">
                                    @include('admin.components.form.input',[
                                       'label'     => __("confirm Password")."<span>*</span>",
                                        'name'      => "password_confirmation",
                                        'type'      => "password",
                                        'placeholder'   => __("enter Password"),
                                    ])
                                    <a href="javascript:void(0)" class="show-pass profile"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12">
                                <button type="submit" class="btn--base w-100 btn-loading">{{ __("Change") }}</button>
                            </div>
                        </form>
                    </div>
                </div>
                @include('agent.components.profile.kyc',compact("kyc_data"))
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        getAllCountries("{{ setRoute('global.countries.agent') }}");
        $(document).ready(function(){
            $("select[name=country]").change(function(){
                var phoneCode = $("select[name=country] :selected").attr("data-mobile-code");
                placePhoneCode(phoneCode);
            });

            countrySelect(".country-select",$(".country-select").siblings(".select2"));
            stateSelect(".state-select",$(".state-select").siblings(".select2"));

        });
        $(".delete-btn").click(function(){
            var actionRoute =  "{{ setRoute('agent.delete.account') }}";
            var target      = 1;
            var btnText = '{{ __("delete Account") }}';
            var projectName = "{{ @$basic_settings->site_name }}";
            var name = $(this).data('name');
            var deleteSureText = '{{ __("Are you sure to") }}';
            var firstText = '{{ __("If you do not think you will use") }}';
            var fullText = '{{ __("body_text_web") }}'

            var message     = `${deleteSureText} <strong> ${btnText}</strong>?<br>${firstText} “<strong>${projectName}</strong>”  ${fullText}, click “${btnText}”.?`;
            openAlertModal(actionRoute,target,message,btnText,"DELETE");
        });
    </script>
@endpush
