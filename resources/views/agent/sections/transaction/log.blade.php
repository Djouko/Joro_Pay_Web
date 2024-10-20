@extends('agent.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
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
            @include('agent.sections.transaction.table',[
                'data'  => $profits
            ])

        </div>
    </div>
    {{ get_paginate($profits) }}
</div>
</div>

@endsection

@push('script')

@endpush
