@extends('admin.layouts.master')

@push('css')
    <style>
        .fileholder {
            min-height: 448px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 404px !important;
        }
    </style>
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
    ], 'active' => __("Onboard Settings")])
@endsection

@section('content')
<div class="custom-card">
    <div class="card-header">
        <h6 class="title">{{ __($page_title) }}</h6>
    </div>

</div>
<div class="custom-card mt-5">
    <div class="card-body">
        <div class="dashboard-area">
            <div class="dashboard-item-area">
                <div class="row">
                        <div class="col-lg-4 col-md-6 mb-15">
                            <a href="{{ setRoute('admin.app.settings.onboard.screens',"USER") }}" class="d-block">
                                <div class="dashbord-item border">
                                    <div class="dashboard-content">
                                        <div class="left">
                                            <h6 class="title">{{ __("Active Onboards (USER)") }}</h6>
                                            <div class="user-info">
                                                <h2 class="user-count">{{ $onboard_screens_user }}</h2>
                                            </div>
                                        </div>
                                        <div class="right">
                                            <div class="chart" id="user_onboard" data-percent="100"><span>100%</span></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-15">
                            <a href="{{ setRoute('admin.app.settings.onboard.screens',"AGENT") }}" class="d-block">
                                <div class="dashbord-item border">
                                    <div class="dashboard-content">
                                        <div class="left">
                                            <h6 class="title">{{ __("Active Onboards (Agent)") }}</h6>
                                            <div class="user-info">
                                                <h2 class="user-count">{{ $onboard_screens_agent }}</h2>
                                            </div>
                                        </div>
                                        <div class="right">
                                            <div class="chart" id="agent_onboard" data-percent="100"><span>100%</span></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-15">
                            <a href="{{ setRoute('admin.app.settings.onboard.screens',"MERCHANT") }}" class="d-block">
                                <div class="dashbord-item border">
                                    <div class="dashboard-content">
                                        <div class="left">
                                            <h6 class="title">{{ __("Active Onboards (Merchant)") }}</h6>
                                            <div class="user-info">
                                                <h2 class="user-count">{{ $onboard_screens_merchant }}</h2>
                                            </div>
                                        </div>
                                        <div class="right">
                                            <div class="chart" id="merchant_onboard" data-percent="100"><span>100%</span></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
// pie-chart
<script>
    $(function() {
        $('#user_onboard').easyPieChart({
            size: 80,
            barColor: '#10c469',
            scaleColor: false,
            lineWidth: 5,
            trackColor: '#10c4695a',
            lineCap: 'circle',
            animate: 3000
        });
        $('#agent_onboard').easyPieChart({
            size: 80,
            barColor: '#10c469',
            scaleColor: false,
            lineWidth: 5,
            trackColor: '#10c4695a',
            lineCap: 'circle',
            animate: 3000
        });
        $('#merchant_onboard').easyPieChart({
            size: 80,
            barColor: '#10c469',
            scaleColor: false,
            lineWidth: 5,
            trackColor: '#10c4695a',
            lineCap: 'circle',
            animate: 3000
        });
    });
</script>
@endpush
