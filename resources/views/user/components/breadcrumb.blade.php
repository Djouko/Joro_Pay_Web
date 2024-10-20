<div class="dashboard-path">
    @foreach ($breadcrumbs as $item)
        <span class="main-path"><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></span>
        @if(request()->routeIs('user.dashboard'))
        @else
        <i class="las la-angle-right" ></i>
        @endif

    @endforeach
    @if(request()->routeIs('user.dashboard'))
    @else
      <span class="active-path ">{{ $active ?? "" }}</span>
    @endif
</div>


