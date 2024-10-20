@extends('admin.layouts.master')
@php
    $app_local = get_default_language_code();
    $system_default    = $default_language_code;
@endphp
@push('css')
    <style>
        .fileholder {
            min-height: 194px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 150px !important;
        }
    </style>
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
    ], 'active' => __("Setup Category Type")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("Categories") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.link.add-default',[
                        'text'          => "Add Category",
                        'href'          => "#category-add",
                        'class'         => "modal-btn",
                        'permission'    => "admin.setup-sections.section.item.store",
                    ])
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("name") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th>{{ __("Created At") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allCategory ?? [] as $item)


                            <tr data-item="{{ json_encode($item) }}">
                                <td>{{ $item->data?->language?->$app_local?->name ?? $item->data?->language?->$system_default?->name}}</td>
                                <td>
                                    @include('admin.components.form.switcher',[
                                        'name'          => 'category_status',
                                        'value'         => $item->status,
                                        'options'       => [__('active') => 1,__('Deactive') => 0],
                                        'onload'        => true,
                                        'data_target'   => $item->id,
                                        'permission'    => "admin.setup.sections.category.status.update",
                                    ])
                                </td>
                                <td>{{ $item->created_at->format("d-m-y h:i:s") }}</td>
                                <td>
                                    @include('admin.components.link.edit-default',[
                                        'href'          => "javascript:void(0)",
                                        'class'         => "edit-modal-button",
                                        'permission'    => "admin.setup.sections.category.update",
                                    ])
                                    @include('admin.components.link.delete-default',[
                                        'href'          => "javascript:void(0)",
                                        'class'         => "delete-modal-button",
                                        'permission'    => "admin.setup.sections.category.delete",
                                    ])
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 7])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- Store --}}
    @if (admin_permission_by_name("admin.setup.sections.category.store"))
        <div id="category-add" class="mfp-hide large">
            <div class="modal-data">
                <div class="modal-header px-0">
                    <h5 class="modal-title">{{ __("Add New Category") }}</h5>
                </div>
                <div class="modal-form-data">
                    <form class="modal-form" method="POST" action="{{ setRoute('admin.setup.sections.category.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-10-none">
                            <div class="col-xl-12 col-lg-12">
                                <div class="product-tab">
                                    <nav>
                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                            @foreach ($languages as $item)
                                                <button class="nav-link @if (get_default_language_code() == $item->code) active @endif" id="{{$item->name}}-tab" data-bs-toggle="tab" data-bs-target="#{{$item->name}}" type="button" role="tab" aria-controls="{{ $item->name }}" aria-selected="true">{{ $item->name }}</button>
                                            @endforeach
                                        </div>
                                    </nav>
                                    <div class="tab-content" id="nav-tabContent">
                                        @foreach ($languages as $item)
                                            @php
                                                $lang_code = $item->code;
                                            @endphp
                                            <div class="tab-pane @if (get_default_language_code() == $item->code) fade show active @endif" id="{{ $item->name }}" role="tabpanel" aria-labelledby="english-tab">
                                                <div class="col-xl-12 col-lg-12 form-group">
                                                    @include('admin.components.form.input',[
                                                        'label'         => __('name'),
                                                        'label_after'   => '*',
                                                        'name'          => $item->code . "_name",
                                                        'value'         => old($item->code . "_name")
                                                    ])
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                                <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                                <button type="submit" class="btn btn--base">{{ __("Add") }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Update --}}
    @if (admin_permission_by_name("admin.setup.sections.category.update"))
        <div id="category-update" class="mfp-hide large">
            <div class="modal-data">
                <div class="modal-header px-0">
                    <h5 class="modal-title">{{ __("Edit Category") }}</h5>
                </div>
                <div class="modal-form-data">
                    <form class="modal-form" method="POST" action="{{ setRoute('admin.setup.sections.category.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method("PUT")
                        <input type="hidden" name="target" value="{{ old('target') }}">
                        <div class="row mb-10-none">
                            <div class="col-xl-12 col-lg-12">
                                <div class="product-tab">
                                    <nav>
                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                            @foreach ($languages as $item)
                                                <button class="nav-link @if (get_default_language_code() == $item->code) active @endif" id="{{$item->name}}-tab" data-bs-toggle="tab" data-bs-target="#{{$item->name}}" type="button" role="tab" aria-controls="{{ $item->name }}" aria-selected="true">{{ $item->name }}</button>
                                            @endforeach
                                        </div>
                                    </nav>
                                    <div class="tab-content" id="nav-tabContent">
                                        @foreach ($languages as $item)
                                            @php
                                                $lang_code = $item->code;
                                            @endphp
                                            <div class="tab-pane @if (get_default_language_code() == $item->code) fade show active @endif" id="{{ $item->name }}" role="tabpanel" aria-labelledby="english-tab">
                                                <div class="col-xl-12 col-lg-12 form-group">
                                                    @include('admin.components.form.input',[
                                                        'label'         => __('name'),
                                                        'label_after'   => '*',
                                                        'name'          => $item->code . "_name_edit",
                                                        'value'         => old($item->code . "_name_edit")
                                                    ])
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                                <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                                <button type="submit" class="btn btn--base">{{ __("Update") }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('script')
    <script>
        var languages = "{{ $__languages }}";
        languages = JSON.parse(languages.replace(/&quot;/g,'"'));
        var appLocal = "{{ $app_local }}";

        function keyPressCurrencyView(select) {
            var selectedValue = $(select);
            selectedValue.parents("form").find("input[name=code],input[name=currency_code]").keyup(function(){
                selectedValue.parents("form").find(".selcted-currency").text($(this).val());
            });
        }
        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.setup.sections.category.status.update') }}");
        })

        $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
            var actionRoute =  "{{ setRoute('admin.setup.sections.category.delete') }}";
            var target      = oldData.id;
            var message     = `Are you sure to delete <strong>${oldData.name}</strong> Category?`;
            openDeleteModal(actionRoute,target,message);
        });

        $(document).on("click",".edit-modal-button",function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
            var editModal = $("#category-update");

            editModal.find(".invalid-feedback").remove();
            editModal.find(".form--control").removeClass("is-invalid");

            editModal.find("form").first().find("input[name=target]").val(oldData.id);

            $.each(languages,function(index,item) {
                editModal.find("input[name="+item.code+"_name_edit]").val(oldData?.data.language[item.code]?.name);
            });

            openModalBySelector("#category-update");
        });

    </script>
@endpush
