@php
    $type = App\Constants\GlobalConst::SETUP_PAGE;
    $menues = DB::table('setup_pages')
            ->where('status', 1)
            ->where('type', Str::slug($type))
            ->get();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<header class="header-section home">
    <div class="header">
        <div class="header-bottom-area">
            <div class="container">
                <div class="header-menu-content">
                    <nav class="navbar navbar-expand-lg p-0">
                        <a class="site-logo site-title" href="{{ setRoute('index') }}">
                            <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                            data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                                alt="site-logo">
                        </a>
                        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="fas fa-bars"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav main-menu ms-auto me-auto">
                            @php
                                $current_url = URL::current();
                            @endphp
                            @foreach ($menues as $item)
                                @php
                                    $title = json_decode($item->title);
                                @endphp
                                <li><a href="{{ url($item->url) }}" class="@if ($current_url == url($item->url)) active @endif"><span>{{ __($title->title) }}</span></a></li>
                            @endforeach
                            </ul>

                            <div class="header-action">
                                <div class="lang-select">
                                    @php
                                    $session_lan = session('local')??get_default_language_code();
                                    @endphp
                                    <select class="form--control langSel nice-select">
                                        @foreach($__languages as $item)
                                        <option value="{{$item->code}}" @if( $session_lan == $item->code) selected  @endif>{{ __($item->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                               @auth
                               <a href="{{ setRoute('user.dashboard') }}" class="btn--base"><i class="las la-user-edit me-2"></i></i>{{ __("Dashboard") }}</a>
                               @else
                               <a href="{{ setRoute('user.login') }}" class="btn--base"><i class="las la-user-edit me-2"></i></i>{{ __("Login Now") }}</a>
                               @endauth
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
