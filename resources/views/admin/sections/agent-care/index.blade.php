@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' => __('Agent Care'),
    ])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("All Agent") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'agent_search',
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.agent-table',compact('agents'))
            </div>
        </div>
        {{ get_paginate($agents) }}
    </div>
@endsection

@push('script')
    <script>
        itemSearch($("input[name=agent_search]"),$(".agent-search-table"),"{{ setRoute('admin.agents.search') }}");
    </script>
@endpush
