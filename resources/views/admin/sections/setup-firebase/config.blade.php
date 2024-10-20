@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("Setup Email")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("Firebase Api") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" method="POST" action="{{ setRoute('admin.setup.firebase.config.update') }}">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12">
                        <div class="row align-items-end">
                            <div class="col-xl-10 col-lg-10 form-group">
                                <label>{{ __("Name*") }}</label>
                                <select class="form--control nice-select" name="name">
                                    <option disabled selected>Select Name</option>
                                    <option value="{{ $firebase_config->name??'firebase' }}" @if (isset($firebase_config->name) && $firebase_config->name == "firebase")
                                        @selected(true)
                                    @endif>{{ ucwords($firebase_config->name??'Firebase') }}</option>
                                </select>
                                @error("name")
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-xl-2 col-lg-2 form-group">
                                <!-- Open Modal For Test code Send -->
                                @include('admin.components.link.custom',[
                                    'class'         => "btn--base modal-btn w-100",
                                    'href'          => "#test-mail",
                                    'text'          => "Send Test Code",
                                    'permission'    => "admin.setup.email.test.mail.send",
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-5 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "Api Key*",
                            'name'      => 'api_key',
                            'type'      => 'text',
                            'value'     => old('api_key',$firebase_config->api_key ?? ""),
                        ])
                    </div>
                    <div class="col-xl-5 col-lg-5 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "Auth Domain*",
                            'name'      => 'auth_domain',
                            'type'      => 'text',
                            'value'     => old('auth_domain',$firebase_config->auth_domain ?? ""),
                        ])
                    </div>
                    <div class="col-xl-5 col-lg-5 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "Project Id*",
                            'name'      => 'project_id',
                            'type'      => 'text',
                            'value'     => old('project_id',$firebase_config->project_id ?? ""),
                        ])
                    </div>
                    <div class="col-xl-5 col-lg-5 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "Storage Bucket*",
                            'name'      => 'storage_bucket',
                            'type'      => 'text',
                            'value'     => old('storage_bucket',$firebase_config->storage_bucket ?? ""),
                        ])
                    </div>
                    <div class="col-xl-5 col-lg-5 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "Messaging SenderId*",
                            'name'      => 'messaging_senderId',
                            'type'      => 'text',
                            'value'     => old('messaging_senderId',$firebase_config->messaging_senderId ?? ""),
                        ])
                    </div>
                    <div class="col-xl-5 col-lg-5 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "App Id*",
                            'name'      => 'app_id',
                            'type'      => 'text',
                            'value'     => old('app_id',$firebase_config->app_id ?? ""),
                        ])
                    </div>
                    <div class="col-xl-5 col-lg-5 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "Measurement Id*",
                            'name'      => 'measurement_id',
                            'type'      => 'text',
                            'value'     => old('measurement_id',$firebase_config->measurement_id ?? ""),
                        ])
                    </div>

                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => "Update",
                            'permission'    => "admin.setup.firebase.config.update",
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Test mail send modal --}}
    @include('admin.components.modals.send-text-mail')

@endsection

@push('script')

@endpush
