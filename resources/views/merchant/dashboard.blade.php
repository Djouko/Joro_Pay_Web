
@extends('merchant.layouts.master')

@section('breadcrumb')
    @include('merchant.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("merchant.dashboard"),
        ]
    ], 'active' => __("Dashboard")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{ __("Overview") }}</h3>
        </div>
        <div class="dashboard-item-area">
            <div class="row mb-20-none">
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-20">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <span class="sub-title">{{__("balance")}}</span>
                            <h3 class="title">{{ authWalletBalance() }} <span class="text--base">{{ @$baseCurrency->code }}</span></h3>
                        </div>

                        <div class="dashboard-icon">
                            <img src="{{  @$baseCurrency->currencyImage }}" alt="flag" />
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-20">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <span class="sub-title">{{__("Total Withdraw")}}</span>
                            <h3 class="title">{{ getAmount($data['money_out_amount']) }} <span class="text--base">{{ @$baseCurrency->code }}</span></h3>
                        </div>
                        <div class="dashboard-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-20">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <span class="sub-title">{{ __("total Received") }}</span>
                            <h3 class="title">{{ getAmount($data['receive_money']) }} <span class="text--base">{{ @$baseCurrency->code }}</span></h3>
                        </div>
                        <div class="dashboard-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-20">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <span class="sub-title">{{ __("Total Transactions") }}</span>
                            <h3 class="title">{{ getAmount($data['total_transaction']) }} <span class="text--base"></span></h3>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @if (auth()->user()->developerApi && auth()->user()->developerApi->mode == payment_gateway_const()::ENV_SANDBOX) --}}
    @if (count($data['sandbox_fiat_wallets']) > 0 )
        <div class="dashboard-area mt-10">
            <div class="dashboard-header-wrapper">
                <h4 class="title">{{ __("My Wallet (Sandbox)") }}</h4>

            </div>
            @include('merchant.components.wallets.fiat',['fiat_wallets' => $data['sandbox_fiat_wallets']])
        </div>

    @endif
    {{-- @endif --}}
    <div class="chart-area mt-30">
        <div class="row mb-20-none">
            <div class="col-xxl-12 col-xl-12 col-lg-12 mb-20">
                <div class="chart-wrapper">
                    <div class="dashboard-header-wrapper">
                        <h4 class="title">{{ __("Withdraw Money Chart") }}</h4>
                    </div>
                    <div class="chart-container">
                        <div id="chart1"  data-chart_one_data="{{ json_encode($chartData['chart_one_data']) }}" data-month_day="{{ json_encode($chartData['month_day']) }}" class="chart"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title">{{ __("Latest Transactions") }}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('merchant.transactions.index') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('merchant.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    var chart1 = $('#chart1');
    var chart_one_data = chart1.data('chart_one_data');
    var month_day = chart1.data('month_day');
    var options = {
        series: [
            {
            name: "{{ __('Pending') }}",
            color: "#0C56DB",
            data: chart_one_data.pending_data
            }, {
            name: "{{ __('Completed') }}",
            color: "rgba(0, 227, 150, 0.85)",
            data: chart_one_data.success_data
            }, {
            name: "{{ __('Canceled') }}",
            color: "#dc3545",
            data: chart_one_data.canceled_data
            }, {
            name: "{{ __('Hold') }}",
            color: "#ded7e9",
            data: chart_one_data.hold_data
            }
        ],
        chart: {
            height: 350,
            type: "area",
            toolbar: {
                show: false,
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
        },
        xaxis: {
            type: "datetime",
            categories:month_day,
        },
        tooltip: {
            x: {
                format: "dd/MM/yy HH:mm",
            },
        },
    };

    var chart = new ApexCharts(document.querySelector("#chart1"), options);
    chart.render();


</script>
@endpush
