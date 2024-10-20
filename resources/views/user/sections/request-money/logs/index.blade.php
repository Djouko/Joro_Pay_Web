@extends('user.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
<div class="table-area mt-10">
    <div class="table-wrapper">
        <div class="table-responsive">
            @include('user.sections.request-money.logs.table',[
                'data'  => $transactions
            ])

        </div>
    </div>
    {{ get_paginate($transactions) }}
</div>
</div>
@endsection

@push('script')
    <script>

    </script>
@endpush
