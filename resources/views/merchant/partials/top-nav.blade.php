
<nav class="navbar-wrapper">
    <div class="dashboard-title-part">
        <div class="left">
            <div class="icon">
                <button class="sidebar-menu-bar">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>
            @yield('breadcrumb')
        </div>
        <div class="right">
            <div class="header-push-wrapper">
                <button class="push-icon">
                    <i class="las la-bell"></i>
                </button>
                <div class="push-wrapper">
                    <div class="push-header">
                        <h5 class="title">{{ __("Notification") }}</h5>
                    </div>
                    <ul class="push-list">
                        @foreach (get_user_notifications() ?? [] as $item)
                        <li>
                            <div class="thumb">
                                <img src="{{ auth()->user()->userImage }}" alt="user" />
                            </div>
                            <div class="content">
                                <div class="title-area">
                                    <h5 class="title">{{ $item->message->title }}</h5>
                                    <span class="time">{{ $item->created_at->diffForHumans() }}</span>
                                </div>
                                <span class="sub-title">{{ $item->message->message ?? "" }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>

                </div>
            </div>
            <div class="header-user-wrapper">
                <div class="header-user-thumb">
                    <a href="{{ setRoute('merchant.profile.index') }}"><img src="{{ auth()->user()->userImage }}" alt="client" /></a>
                </div>
            </div>
        </div>
    </div>
</nav>
