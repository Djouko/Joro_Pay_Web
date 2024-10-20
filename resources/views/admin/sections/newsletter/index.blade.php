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
    ], 'active' => __("Newsletter Section")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            @includeUnless($default_currency,'admin.components.alerts.warning',['message' => __("There is no default currency in your system.")])
            <div class="table-header">
                <h5 class="title">{{ __("Newsletters") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'newslatter_search',
                    ])
                    @include('admin.components.link.send-default',[
                        'text'          => __("Send Email"),
                        'href'          => "#send-email",
                        'class'         => "modal-btn",
                        'permission'    => "admin.newsletter.send.email",
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.newsletter-table',[
                    'data'  => $data
                ])
            </div>
        </div>
        {{ get_paginate($data) }}
    </div>



    {{-- Currency Add Modal --}}
    @include('admin.components.modals.send-mail-newslatter')

@endsection

@push('script')
    <script>

        $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));

            var actionRoute =  "{{ setRoute('admin.newsletter.delete') }}";
            var target      = oldData.id;
            var message     = `Are you sure to delete <strong>${oldData.name}</strong> Newsletter?`;

            openDeleteModal(actionRoute,target,message);
        });

        itemSearch($("input[name=newslatter_search]"),$(".newletter-search-table"),"{{ setRoute('admin.newsletter.search') }}",1);
    </script>
@endpush
