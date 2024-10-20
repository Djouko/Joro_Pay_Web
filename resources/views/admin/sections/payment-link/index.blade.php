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
        'active' =>__($page_title),
    ])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __($page_title) }}</h5>
                @if(count($transactions) > 0)
                    <div class="table-btn-area">
                        <a href="{{ setRoute('admin.payment.link.export.data') }}" class="btn--base py-2 px-4"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                    </div>
                @endif

            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("web_trx_id") }}</th>
                            <th>{{__("type")}}</th>
                            <th>{{ __("User Type") }}</th>
                            <th>{{ __("Email") }}</th>
                            <th>{{ __("Payment Type") }}</th>
                            <th>{{ __("Amount") }}</th>
                            <th>{{ __("Payable") }}</th>
                            <th>{{ __("Conversion Payable") }}</th>
                            <th>{{__("Status") }}</th>
                            <th>{{ __("Time") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions  as $key => $item)
                            <tr>
                                <td>{{ $item->trx_id }}</td>
                                <td><span class="text--info">{{ $item->type }}</span></td>
                                <td>
                                    @if($item->user_id != null)
                                         {{ __("USER") }}
                                    @elseif($item->merchant_id != null)
                                         {{ __("MERCHANT") }}
                                    @endif

                                </td>
                                <td>
                                    @if($item->user_id != null)
                                    <a href="{{ setRoute('admin.users.details',$item->creator->username) }}">{{ $item->creator->email??"N/A" }}</a>
                                    @elseif($item->merchant_id != null)
                                    <a href="{{ setRoute('admin.merchants.details',$item->creator->username) }}">{{ $item->creator->email??"N/A" }}</a>
                                    @endif
                                </td>

                                <td>{{  ucwords(str_replace('_',' ',$item->details->payment_type??__('Card Payment')) ) }}</td>
                                <td>{{ get_amount(@$item->request_amount, @$item->details->charge_calculation->sender_cur_code) }}</td>
                                <td>{{ get_amount(@$item->payable, @$item->details->charge_calculation->sender_cur_code) }}</td>
                                <td>{{ get_amount(@$item->details->charge_calculation->conversion_payable, @$item->details->charge_calculation->receiver_currency_code) }}</td>
                                <td>
                                    <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                                </td>
                                <td>{{ dateFormat('d M y h:i:s A', $item->created_at) }}</td>
                                <td>
                                    @if ($item->status == 0)
                                        <button type="button" class="btn btn--base bg--success"><i
                                                class="las la-check-circle"></i></button>
                                        <button type="button" class="btn btn--base bg--danger"><i
                                                class="las la-times-circle"></i></button>
                                        <a href="add-logs-edit.html" class="btn btn--base"><i class="las la-expand"></i></a>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->type == 'PAY-INVOICE')
                                        @include('admin.components.link.info-default',[
                                            'href'          => setRoute('admin.invoice.details', $item->id),
                                            'permission'    => "admin.invoice.details",
                                        ])
                                    @else
                                        @include('admin.components.link.info-default',[
                                            'href'          => setRoute('admin.payment.link.details', $item->id),
                                            'permission'    => "admin.payment.link.details",
                                        ])
                                    @endif

                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 10])
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
