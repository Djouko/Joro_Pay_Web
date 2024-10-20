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
    ], 'active' => __("withdraw Log")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ $page_title }}</h5>
            @if(count($transactions) > 0)
            <div class="table-btn-area">
                <a href="{{ setRoute('admin.money.out.export.data') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
            </div>
        @endif
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("web_trx_id") }}</th>
                        <th>{{ __("Full Name") }}</th>
                        <th>{{ __("Email") }}</th>
                        <th>{{ __("User Type") }}</th>
                        <th>{{ __("request Amount") }}</th>
                        <th>{{ __("Method") }}</th>
                        <th>{{ __(("Status")) }}</th>
                        <th>{{ __("Time") }}</th>
                        <th>{{__("action")}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions  as $key => $item)

                        <tr>
                            <td>{{ $item->trx_id }}</td>
                            <td>
                                @if($item->user_id != null)
                                <a href="{{ setRoute('admin.users.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @elseif($item->agent_id != null)
                                <a href="{{ setRoute('admin.agents.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @elseif($item->merchant_id != null)
                                <a href="{{ setRoute('admin.merchants.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @endif
                            </td>
                            <td>
                               {{ $item->creator->email ?? '' }}
                            </td>
                            <td>
                                @if($item->user_id != null)
                                     {{ __("USER") }}
                                @elseif($item->agent_id != null)
                                     {{ __("AGENT") }}
                                @elseif($item->merchant_id != null)
                                     {{ __("MERCHANT") }}
                                @endif

                            </td>

                            <td>{{ number_format($item->request_amount,2) }} {{ get_default_currency_code() }}</td>
                            <td><span class="text--info">{{ @$item->currency->name }}</span></td>
                            <td>
                                <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>
                            <td>
                                @include('admin.components.link.info-default',[
                                    'href'          => setRoute('admin.money.out.details', $item->id),
                                    'permission'    => "admin.money.out.details",
                                ])

                            </td>
                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 9])
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ get_paginate($transactions) }}
    </div>
</div>
@endsection

@push('script')

@endpush
