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
            <h3>{{ __($page_title) }}</h3>
            <a href="{{ setRoute('agent.sender.recipient.add') }}" class="btn--base">{{ __("Add") }} <i class="fas fa-plus-circle ms-2"></i></a>
        </div>
    </div>
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-list-wrapper">
            @forelse ($recipients ?? []  as $data)
            <div class="dashboard-list-item-wrapper">
                <div class="dashboard-list-item sent">
                    <div class="dashboard-list-left">
                        <div class="dashboard-list-user-wrapper">
                            <div class="dashboard-list-user-icon">
                                <i class="las la-arrow-up"></i>
                            </div>
                            <div class="dashboard-list-user-content">

                                <h4 class="title">{{ @$data->fullname }}
                                    @if($data->type == "wallet-to-wallet-transfer")
                                        <span class="text-success">( {{@$basic_settings->site_name}} {{__("rWallet")}} )</span>
                                    @elseif($data->type == "cash-pickup")
                                        <span class="text-success">( {{ __("r".@$data->type)}} )</span>
                                    @else
                                        <span class="text-success">( {{ __(@$data->type)}} )</span>
                                    @endif </h4>
                                <span class="sub-title text--warning">{{ @$data->email }}</span>

                            </div>
                        </div>
                    </div>
                    <div class="dashboard-list-right">
                        <div class="dashboard-list-right-btn-area">
                            <a href="{{ setRoute('agent.sender.recipient.send.remittance',$data->id) }}" class="btn--base"><i class="fas fa-paper-plane"></i></a>
                            <a href="{{ setRoute('agent.sender.recipient.edit',$data->id) }}" class="btn--base"><i class="fas fa-edit"></i></a>
                            <button type="button" class="btn--base delete-btn " data-id="{{ $data->id }}" data-name="{{ $data->fullname }}"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>

            </div>
            @empty
            <div class="alert alert-primary text-center">
                {{ __("No Sender Recipient Found!") }}
            </div>
            @endforelse

        </div>
    </div>
    <nav>
        <ul class="pagination">
            {{ get_paginate($recipients) }}
        </ul>
    </nav>
</div>
@endsection

@push('script')
<script>
     $(".delete-btn").click(function(){
            var actionRoute =  "{{ setRoute('agent.sender.recipient.delete') }}";
            var target      = $(this).data('id');
            var btnText = "Delete";
            var name = $(this).data('name');
            var message     = `Are you sure to delete <strong>${name}</strong>?`;
            openAlertModal(actionRoute,target,message,btnText,"DELETE");
        });
</script>
@endpush
