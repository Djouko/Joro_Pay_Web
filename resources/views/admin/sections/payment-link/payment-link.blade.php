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
        'active' => __($page_title),
    ])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ $page_title }}</h5>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __('User Email') }}</th>
                            <th>{{ __("titleS") }}</th>
                            <th>{{ __('type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payment_links as $item)
                            <tr>
                                <td>{{ $item->creator->email }}</td>
                                <td>{{ $item->title }}</td>
                                <td>{{ $item->linkType }}</td>
                                <td>{{ $item->amountCalculation }}</td>
                                <td><span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span></td>
                                <td>{{ dateFormat('d M Y , h:i:s A', $item->created_at) }}</td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 7])
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ get_paginate($payment_links) }}
        </div>
    </div>
@endsection

@push('script')
@endpush
