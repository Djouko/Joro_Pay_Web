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
    ], 'active' => __("Dashboard")])
@endsection

@section('content')
    <div class="dashboard-area">
        <div class="dashboard-item-area">
            <div class="row">
                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{__("Add Money Balance")}}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ get_default_currency_symbol() }}{{ get_amount($data['add_money_total_balance']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Completed") }} {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['completed_add_money']) }}</span>
                                    <span class="badge badge--warning">{{ __("Pending") }} {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['pending_add_money']) }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart7" data-percent="{{ $data['add_money_percent'] }}"><span>{{ round($data['add_money_percent']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Money Out Balance") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ get_default_currency_symbol() }}{{ get_amount($data['total_money_out']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Completed") }} {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['completed_money_out']) }}</span>
                                    <span class="badge badge--warning">{{ __("Pending") }} {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['pending_money_out']) }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart8" data-percent="{{ $data['money_out_percent'] }}"><span>{{ round($data['money_out_percent']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Profit") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ get_default_currency_symbol() }}{{ get_amount($data['total_profits']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("This Month") }} {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['this_month_profits']) }}</span>
                                    <span class="badge badge--warning">{{ __("Last Month") }}  {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['last_month_profits']) }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart9" data-percent="{{ $data['profit_percent'] }}"><span>{{ round($data['profit_percent']) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __('Total Virtual Cards') }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ $data['total_cards'] }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--success">{{ __("active") }} {{ $data['active_cards'] }}</span>
                                    <span class="badge badge--warning">{{ __("Inactive") }} {{ $data['inactive_cards'] }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart10" data-percent="{{ $data['card_perchant'] }}"><span>{{ $data['card_perchant'] }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Remittance Balance") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ get_default_currency_symbol() }}{{ get_amount($data['total_remittance']) }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("Completed") }} {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['completed_remittance']) }}</span>
                                    <span class="badge badge--warning">{{ __("Pending") }} {{ get_default_currency_symbol() }}{{ formatNumberInKNotation($data['pending_remittance']) }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart11" data-percent="{{ $data['remittance_percent'] }}"><span>{{ round($data['remittance_percent'],2) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                 <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Users") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ $data['total_users'] }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("active") }} {{  $data['active_users'] }}</span>
                                    <span class="badge badge--warning">{{ __("Unverified") }} {{ $data['unverified_users'] }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart12" data-percent="{{ $data['user_perchant'] }}"><span>{{ round($data['user_perchant'],2) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Merchants") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ $data['total_merchants'] }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("active") }} {{  $data['active_merchants'] }}</span>
                                    <span class="badge badge--warning">{{ __("Unverified") }} {{ $data['unverified_merchants'] }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart14" data-percent="{{ $data['merchant_perchant'] }}"><span>{{ round($data['merchant_perchant'],2) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Total Supports Tickets") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ $data['total_tickets'] }}</h2>
                                </div>
                                <div class="user-badge">
                                    <span class="badge badge--info">{{ __("active") }} {{  $data['active_tickets'] }}</span>
                                    <span class="badge badge--warning">{{ __("Pending") }} {{ $data['pending_tickets'] }}</span>
                                </div>
                            </div>
                            <div class="right">
                                <div class="chart" id="chart15" data-percent="{{ $data['ticket_perchant'] }}"><span>{{ round($data['ticket_perchant'],2) }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">

                            @if(virtual_card_system('flutterwave'))
                            <div class="left">
                                <h6 class="title">{{ __("Flutterwave Balance") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{  get_default_currency_symbol() }} {{ get_amount(flutterwaveBalance()['balance']) }}</h2>
                                </div>
                            </div>
                            @elseif(virtual_card_system('stripe'))
                            <div class="left">
                                <h6 class="title">{{ __("Stripe Issuing Balance") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{ get_default_currency_symbol() }}{{ getAmount(getIssueBalance()['amount'],2) }}</h2>
                                </div>
                            </div>
                            @elseif(virtual_card_system('sudo'))
                            <div class="left">
                                <h6 class="title">{{ __("Sudo Balance") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{  get_default_currency_symbol() }} {{getSudoBalance()['amount'] }}</h2>
                                </div>
                            </div>
                            @endif

                            <div class="right">
                                <div class="chart" id="chart16" data-percent="100"><span>100%</span></div>
                            </div>
                        </div>
                        @if(virtual_card_system('sudo'))
                        <div class="user-badge">
                            @if(getSudoBalance()['status'] == true)
                                <span class="badge badge--success">{{getSudoBalance()['message'] }}</span>
                            @else
                            <span class="badge badge--danger">{{getSudoBalance()['message'] }}</span>
                            @endif
                        </div>
                        @elseif(virtual_card_system('flutterwave'))
                        <div class="user-badge">
                            @if(flutterwaveBalance()['status'] == true)
                                <span class="badge badge--success">{{flutterwaveBalance()['message'] }}</span>
                            @else
                            <span class="badge badge--danger">{{flutterwaveBalance()['message'] }}</span>
                            @endif
                        </div>

                        @elseif(virtual_card_system('stripe'))
                        <div class="user-badge">
                            @if(getIssueBalance()['status'] == true)
                                <span class="badge badge--success">{{getIssueBalance()['message'] }}</span>
                            @else
                            <span class="badge badge--danger">{{getIssueBalance()['message'] }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-xxxl-4 col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-15">
                    <div class="dashbord-item">
                        <div class="dashboard-content">
                            <div class="left">
                                <h6 class="title">{{ __("Admin Profits") }}</h6>
                                <div class="user-info">
                                    <h2 class="user-count">{{  get_default_currency_symbol() }}{{getAmount(totalAdminProfits(),2) }}</h2>
                                </div>
                            </div>

                            <div class="right">
                                <div class="chart" id="chart17" data-percent="100"><span>100%</span></div>
                            </div>
                        </div>
                        <div class="user-badge">

                                <span class="badge badge--success">{{__("LIVE TIME SUPERADMIN PROFITS BALANCE")}}</span>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="chart-area mt-15">
        <div class="row mb-15-none">
            <div class="col-xxl-6 col-xl-6 col-lg-6 mb-15">
                <div class="chart-wrapper">
                    <div class="chart-area-header">
                        <h5 class="title">
                           {{__("Monthly Add Money Chart")}}
                        </h5>
                        <a href="{{ setRoute('admin.add.money.index') }}" class="btn--base "> {{__("View")}}</a>
                    </div>
                    <div class="chart-container">
                        <div id="chart1" data-chart_one_data="{{ json_encode($data['chart_one_data']) }}" data-month_day="{{ json_encode($data['month_day']) }}" class="sales-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6 mb-15">
                <div class="chart-wrapper">
                    <div class="chart-area-header">
                        <h5 class="title">{{ __("Monthly Virtual Card Chart") }}</h5>


                    </div>
                    <div class="chart-container">
                        <div id="chart2" data-chart_two_data="{{ json_encode($data['chart_two_data']) }}" class="revenue-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-15">
                <div class="chart-wrapper">
                    <div class="chart-area-header">
                        <h5 class="title">
                           {{__("Monthly Money Out Chart")}}
                        </h5>
                        <a href="{{ setRoute('admin.money.out.index') }}" class="btn--base "> {{__("View")}}</a>
                    </div>
                    <div class="chart-container">
                        <div id="chart3" data-chart_three_data="{{ json_encode($data['chart_three_data']) }}" data-month_day="{{ json_encode($data['month_day']) }}" class="sales-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-15">
                <div class="chart-wrapper">
                    <div class="chart-area-header">
                        <h5 class="title">{{ __("User Analytics") }}</h5>
                    </div>
                    <div class="chart-container">
                        <div id="chart4" data-chart_four_data="{{ json_encode($data['chart_four_data']) }}" class="balance-chart"></div>
                    </div>
                    <div class="chart-area-footer">
                        <div class="chart-btn">
                            <a href="{{ setRoute('admin.users.index') }}" class="btn--base w-100">{{__("View Users")}}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-15">
                <div class="chart-wrapper">
                    <div class="chart-area-header">
                        <h5 class="title">{{ __("Merchant Analytics") }}</h5>
                    </div>
                    <div class="chart-container">
                        <div id="merchants" data-chart_merchant="{{ json_encode($data['chart_merchant']) }}" class="balance-chart"></div>
                    </div>
                    <div class="chart-area-footer">
                        <div class="chart-btn">
                            <a href="{{ setRoute('admin.merchants.index') }}" class="btn--base w-100">{{__("View Merchants")}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-area  mt-15">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("Latest Add Money") }}</h5>
                <a href="{{ setRoute('admin.add.money.index') }}" class="btn--base">{{__("Add Money")}}</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("web_trx_id") }}</th>
                            <th>{{ __("Full Name") }}</th>
                            <th>{{ __("User Type") }}</th>
                            <th>{{ __("Phone") }}</th>
                            <th>{{ __("Amount") }}</th>
                            <th>{{ __("Method") }}</th>
                            <th>{{ __(("Status")) }}</th>
                            <th>{{ __("Time") }}</th>
                            <th>{{__("action")}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['transactions'] ??[]  as $key => $item)

                        <tr>
                            <td>{{ $item->trx_id }}</td>
                            <td>
                                @if($item->user_id != null)
                                <a href="{{ setRoute('admin.users.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @elseif($item->agent_id != null)
                                <a href="{{ setRoute('admin.agents.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @endif

                            <td>
                                @if($item->user_id != null)
                                     {{ __("USER") }}
                                @elseif($item->agent_id != null)
                                     {{ __("AGENT") }}
                                @elseif($item->merchant_id != null)
                                     {{ __("MERCHANT") }}
                                @endif

                            </td>
                            <td>
                                {{ $item->creator->full_mobile ?? '' }}
                            </td>

                            <td>{{ number_format($item->request_amount,2) }} {{ get_default_currency_code() }}</td>
                            <td><span class="text--info">{{ @$item->currency->name }}</span></td>
                            <td>
                                <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>
                            <td>
                                @include('admin.components.link.info-default',[
                                    'href'          => setRoute('admin.add.money.details', $item->id),
                                    'permission'    => "admin.add.money.details",
                                ])

                            </td>
                        </tr>
                        @empty
                            <div class="alert alert-primary">{{ __('empty Status') }}</div>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@push('script')
<script>
var chart1 = $('#chart1');
var chart_one_data = chart1.data('chart_one_data');
var month_day = chart1.data('month_day');
// apex-chart
var options = {
  series: [{
  name: "{{ __('Pending') }}",
  color: "#5A5278",
  data: chart_one_data.pending_data
}, {
  name: "{{ __('Completed') }}",
  color: "#6F6593",
  data: chart_one_data.success_data
}, {
  name: '{{ __("Canceled") }}',
  color: "#8075AA",
  data: chart_one_data.canceled_data
}, {
  name: '{{ __("Hold") }}',
  color: "#A192D9",
  data: chart_one_data.hold_data
}],
  chart: {
  type: 'bar',
  height: 350,
  stacked: true,
  toolbar: {
    show: false
  },
  zoom: {
    enabled: true
  }
},
responsive: [{
  breakpoint: 480,
  options: {
    legend: {
      position: 'bottom',
      offsetX: -10,
      offsetY: 0
    }
  }
}],
plotOptions: {
  bar: {
    horizontal: false,
    borderRadius: 10
  },
},
xaxis: {
  type: 'datetime',
  categories: month_day,
},
legend: {
  position: 'bottom',
  offsetX: 40
},
fill: {
  opacity: 1
}
};

var chart = new ApexCharts(document.querySelector("#chart1"), options);
chart.render()

var chart2 = $('#chart2');
var chart_two_data = chart2.data('chart_two_data');
var options = {
  series: [{
  name: "{{ __('Pending') }}",
  color: "#5A5278",
  data: chart_two_data.pending_data
}, {
  name: "{{ __('Completed') }}",
  color: "#6F6593",
  data: chart_two_data.success_data
}, {
  name: '{{ __("Canceled") }}',
  color: "#8075AA",
  data: chart_two_data.canceled_data
}, {
  name: '{{ __("Hold") }}',
  color: "#A192D9",
  data: chart_two_data.hold_data
}],
  chart: {
  type: 'bar',
  height: 350,
  stacked: true,
  toolbar: {
    show: false
  },
  zoom: {
    enabled: true
  }
},
responsive: [{
  breakpoint: 480,
  options: {
    legend: {
      position: 'bottom',
      offsetX: -10,
      offsetY: 0
    }
  }
}],
plotOptions: {
  bar: {
    horizontal: true,
    borderRadius: 10
  },
},
yaxis: {
  type: 'datetime',
  labels: {
    format: 'dd/MMM',
  },
  categories: month_day,
},
legend: {
  position: 'bottom',
  offsetX: 40
},
fill: {
  opacity: 1
}
};

var chart = new ApexCharts(document.querySelector("#chart2"), options);
chart.render();



var chart3 = $('#chart3');
var chart_three_data = chart3.data('chart_three_data');
var month_day = chart3.data('month_day');
// apex-chart
var options = {
  series: [{
  name: "{{ __('Pending') }}",
  color: "#5A5278",
  data: chart_three_data.pending_data
}, {
  name: "{{ __('Completed') }}",
  color: "#6F6593",
  data: chart_three_data.success_data
}, {
  name: '{{ __("Canceled") }}',
  color: "#8075AA",
  data: chart_three_data.canceled_data
}, {
  name: '{{ __("Hold") }}',
  color: "#A192D9",
  data: chart_three_data.hold_data
}],
  chart: {
  type: 'bar',
  height: 350,
  stacked: true,
  toolbar: {
    show: false
  },
  zoom: {
    enabled: true
  }
},
responsive: [{
  breakpoint: 480,
  options: {
    legend: {
      position: 'bottom',
      offsetX: -10,
      offsetY: 0
    }
  }
}],
plotOptions: {
  bar: {
    horizontal: false,
    borderRadius: 10
  },
},
xaxis: {
  type: 'datetime',
  categories: month_day,
},
legend: {
  position: 'bottom',
  offsetX: 40
},
fill: {
  opacity: 1
}
};

var chart = new ApexCharts(document.querySelector("#chart3"), options);
chart.render()


var chart4 = $('#chart4');
var chart_four_data = chart4.data('chart_four_data');

var options = {
  series: chart_four_data,
  chart: {
  width: 350,
  type: 'pie'
},
colors: ['#10c469', '#f03d30', '#ff9f43', '#A192D9'],
labels: ['{{ __("active") }}', '{{ __("banned") }}','{{ __("Unverified") }}', '{{ __("All") }}'],
responsive: [{
  breakpoint: 1480,
  options: {
    chart: {
      width: 280
    },
    legend: {
      position: 'bottom'
    }
  },
  breakpoint: 1199,
  options: {
    chart: {
      width: 380
    },
    legend: {
      position: 'bottom'
    }
  },
  breakpoint: 575,
  options: {
    chart: {
      width: 280
    },
    legend: {
      position: 'bottom'
    }
  }
}],
legend: {
  position: 'bottom'
},
};

var chart = new ApexCharts(document.querySelector("#chart4"), options);
chart.render();

var merchants = $('#merchants');
var chart_merchant = merchants.data('chart_merchant');

var options = {
  series: chart_merchant,
  chart: {
  width: 350,
  type: 'pie'
},
colors: ['#10c469', '#f03d30', '#ff9f43', '#A192D9'],
labels: ['{{ __("active") }}', '{{ __("banned") }}','{{ __("Unverified") }}', '{{ __("All") }}'],
responsive: [{
  breakpoint: 1480,
  options: {
    chart: {
      width: 280
    },
    legend: {
      position: 'bottom'
    }
  },
  breakpoint: 1199,
  options: {
    chart: {
      width: 380
    },
    legend: {
      position: 'bottom'
    }
  },
  breakpoint: 575,
  options: {
    chart: {
      width: 280
    },
    legend: {
      position: 'bottom'
    }
  }
}],
legend: {
  position: 'bottom'
},
};

var chart = new ApexCharts(document.querySelector("#merchants"), options);
chart.render();


// pie-chart
$(function() {
  $('#chart6').easyPieChart({
      size: 80,
      barColor: '#f05050',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#f050505a',
      lineCap: 'circle',
      animate: 3000
  });
});

$(function() {
  $('#chart7').easyPieChart({
      size: 80,
      barColor: '#10c469',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#10c4695a',
      lineCap: 'circle',
      animate: 3000
  });
});

$(function() {
  $('#chart8').easyPieChart({
      size: 80,
      barColor: '#ffbd4a',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#ffbd4a5a',
      lineCap: 'circle',
      animate: 3000
  });
});

$(function() {
  $('#chart9').easyPieChart({
      size: 80,
      barColor: '#ff8acc',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#ff8acc5a',
      lineCap: 'circle',
      animate: 3000
  });
});

$(function() {
  $('#chart10').easyPieChart({
      size: 80,
      barColor: '#7367f0',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#7367f05a',
      lineCap: 'circle',
      animate: 3000
  });
});

$(function() {
  $('#chart11').easyPieChart({
      size: 80,
      barColor: '#1e9ff2',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#1e9ff25a',
      lineCap: 'circle',
      animate: 3000
  });
});

$(function() {
  $('#chart12').easyPieChart({
      size: 80,
      barColor: '#5a5278',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#5a52785a',
      lineCap: 'circle',
      animate: 3000
  });
});

$(function() {
  $('#chart13').easyPieChart({
      size: 80,
      barColor: '#ADDDD0',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#ADDDD05a',
      lineCap: 'circle',
      animate: 3000
  });
});
$(function() {
  $('#chart14').easyPieChart({
      size: 80,
      barColor: '#ADDDD0',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#ADDDD05a',
      lineCap: 'circle',
      animate: 3000
  });
});
$(function() {
  $('#chart15').easyPieChart({
      size: 80,
      barColor: '#ADDDD0',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#ADDDD05a',
      lineCap: 'circle',
      animate: 3000
  });
});
$(function() {
  $('#chart16').easyPieChart({
      size: 80,
      barColor: '#ADDDD0',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#ADDDD05a',
      lineCap: 'circle',
      animate: 3000
  });
});
$(function() {
  $('#chart17').easyPieChart({
      size: 80,
      barColor: '#ADDDD0',
      scaleColor: false,
      lineWidth: 5,
      trackColor: '#ADDDD05a',
      lineCap: 'circle',
      animate: 3000
  });
});
</script>
@endpush
