@extends('admin.layouts.master')

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
    ], 'active' => __("Setup Bill Pay Method")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{$page_title}}</h5>
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'category_search',
                    ])
                    @include('admin.components.link.add-default',[
                        'text'          => __("Add Method"),
                        'href'          => "#category-add",
                        'class'         => "modal-btn",
                        'permission'    => "admin.setup-sections.section.item.store",
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.bill-category-table',[
                    'data'  => $allCategory
                ])
            </div>
        </div>
        {{ get_paginate($allCategory) }}
    </div>

    {{-- Currency Edit Modal --}}
    @include('admin.components.modals.edit-bill-category')

    {{-- Currency Add Modal --}}
    @include('admin.components.modals.bill-category-add')

@endsection

@push('script')
    <script>
        function keyPressCurrencyView(select) {
            var selectedValue = $(select);
            selectedValue.parents("form").find("input[name=code],input[name=currency_code]").keyup(function(){
                selectedValue.parents("form").find(".selcted-currency").text($(this).val());
            });
        }

        $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
            var actionRoute =  "{{ setRoute('admin.bill.pay.method.manual.category.delete') }}";
            var target      = oldData.id;
            var delete_text = "{{ __('delete_text') }}"
            var message     = `${delete_text} <strong>${oldData.name}</strong>?`;
            openDeleteModal(actionRoute,target,message);
        });

        itemSearch($("input[name=category_search]"),$(".category-search-table"),"{{ setRoute('admin.bill.pay.method.manual.category.search') }}",1);
    </script>
@endpush
