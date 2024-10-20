<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<header class="developer-header">
    <div class="container-fluid">
        <div class="developer-wrapper">
            <div class="developer-logo-area">
                <div class="sidebar-mobile-btn">
                    <button><i class="fas fa-bars"></i></button>
                </div>
                <a class="site-logo site-title" href="{{ setRoute('index') }}">
                    <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                    data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                        alt="site-logo">
                </a>
                <span class="logo-text">{{ __("Developer") }}</span>
            </div>
            <div class="developer-header-content">
                <ul class="developer-header-list">
                    <li>
                        <a href="{{ setRoute('contact') }}">{{ __("Support") }}</a>
                    </li>
                </ul>
                <div class="developer-header-action">
                    @auth("merchant")
                    <a href="{{ setRoute('merchant.dashboard') }}" class="btn--base"><i class="las la-user-edit me-1"></i>{{ __("Merchant Dashboard") }}</a>
                    @else
                    <a href="{{ setRoute('merchant.login') }}" class="btn--base"><i class="las la-user-edit me-1"></i>{{ __("Login in to Dashboard") }}</a>
                    @endauth

                </div>
            </div>
        </div>
    </div>
</header>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
