<div class="dashboard-path">
    @foreach ($breadcrumbs as $item)
        <span class="main-path"><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></span>
        @if(request()->routeIs('merchant.dashboard'))
        @else
        <i class="las la-angle-right" ></i>
        @endif

    @endforeach
    @if(request()->routeIs('merchant.dashboard'))
    @else
      <span class="active-path ">{{ $active ?? "" }}</span>
    @endif
</div>


