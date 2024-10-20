@extends('user.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Support Tickets")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="table-area mt-10">
        <div class="table-wrapper">
            <div class="dashboard-header-wrapper d-block d-sm-flex">
                <h4 class="title">{{ __("Support Tickets") }}</h4>
                <div class="dashboard-btn-wrapper mt-3 mt-sm-0">
                    <div class="dashboard-btn">
                        <a href="{{ route('user.support.ticket.create') }}" class="btn--base"><i class="las la-plus me-1"></i>{{ __("Add New") }}</a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("Ticket ID") }}</th>
                            <th>{{ __("Full Name") }}</th>
                            <th>{{ __("Email") }}</th>
                            <th>{{__("Subject")}}</th>
                            <th>{{__("Status") }}</th>
                            <th>{{__("Last Replied")}}</th>
                            <th>{{ __("Details") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($support_tickets as $item)
                            <tr>
                                <td>#{{ $item->token }}</td>
                                <td><span class="text--info">{{ $item->creator->fullname }}</span></td>
                                <td><span class="text--info">{{ $item->creator->email }}</span></td>
                                <td><span class="text--info">{{ $item->subject }}</span></td>
                                <td>
                                    <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                                </td>
                                <td>{{ $item->created_at->format("Y-m-d H:i A") }}</td>
                                <td>
                                    <a href="{{ route('user.support.ticket.conversation',encrypt($item->id)) }}" class="btn btn--base"><i class="las la-comment"></i></a>
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty2',['colspan' => 7])
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
        {{ get_paginate($support_tickets) }}
    </div>
</div>
@endsection

@push('script')
    <script>

    </script>
@endpush
