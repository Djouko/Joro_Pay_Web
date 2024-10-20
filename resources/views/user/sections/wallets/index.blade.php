@extends('user.layouts.master')

@push('css')
    
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __("Wallets")])
@endsection

@section('content')
    <div class="dashboard-area mt-10">
        @include('user.components.wallets.fiat',compact("fiat_wallets"))
    </div>
    <div class="dashboard-area mt-20">
        @include('user.components.wallets.crypto',compact("crypto_wallets"))
    </div>
@endsection

@push('script')

@endpush