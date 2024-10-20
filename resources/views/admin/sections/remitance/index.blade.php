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
    ], 'active' => __("Bill Pay Logs")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ $page_title }}</h5>
            @if(count($transactions) > 0)
            <div class="table-btn-area">
                <a href="{{ setRoute('admin.remitance.export.data') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
            </div>
        @endif
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("web_trx_id") }}</th>
                        <th>{{ __("sender") }}</th>
                        <th>{{ __("Receiver") }}</th>
                        <th>{{ __("Remittance Type") }}</th>
                        <th>{{ __(("Send Amount")) }}</th>
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
                                @if($item->attribute == "SEND")
                                    @if($item->user_id != null)
                                    <a href="{{ setRoute('admin.users.details',$item->user->username) }}">{{ $item->user->fullname }}</a>
                                    @elseif($item->agent_id != null)
                                    <a href="{{ setRoute('admin.agents.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                    @elseif($item->merchant_id != null)
                                    <a href="{{ setRoute('admin.merchants.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                    @endif
                                @else
                                <span>{{ $item->details->sender->fullname }}</span>
                                @endif

                            </td>
                            <td>
                                @if($item->attribute == "RECEIVED")
                                    @if($item->user_id != null)
                                        <a href="{{ setRoute('admin.users.details',$item->user->username) }}">{{ $item->user->fullname }}</a>
                                    @elseif($item->agent_id != null)
                                        <a href="{{ setRoute('admin.agents.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                    @elseif($item->merchant_id != null)
                                        <a href="{{ setRoute('admin.merchants.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                    @endif
                                @else

                                    @if($item->user_id != null)
                                        <span>{{ @$item->details->receiver->firstname }} {{ @$item->details->receiver->lastname }}</span>
                                    @elseif($item->agent_id != null)
                                    <span>{{ @$item->details->receiver_recipient->firstname }} {{ @$item->details->receiver_recipient->lastname }}</span>
                                    @endif
                                @endif

                            </td>
                            <td >
                                @if( @$item->details->remitance_type == "wallet-to-wallet-transfer")
                                    <span class="fw-bold"> {{@$basic_settings->site_name}} {{__("Wallet")}} </span>
                                    @else
                                    <span class="fw-bold"> {{ ucwords(str_replace('-', ' ', @$item->details->remitance_type))}} </span>

                                @endif
                               </td>
                            {{-- <td ><span class="fw-bold">{{ @$item->details->bill_number }}</span></td> --}}
                            <td>{{ number_format($item->request_amount,2) }} {{ get_default_currency_code() }}</td>
                            <td>
                                <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>
                            <td>
                                @include('admin.components.link.info-default',[
                                    'href'          => setRoute('admin.remitance.details', $item->id),
                                    'permission'    => "admin.remitance.details",
                                ])

                            </td>
                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 8])
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
