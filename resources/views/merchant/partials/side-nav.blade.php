
<div class="sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-menu-inner-wrapper">
            <div class="sidebar-logo">
                <a href="{{ setRoute('index') }}" class="sidebar-main-logo">
                    <img src="{{ get_logo_merchant($basic_settings) }}" data-white_img="{{ get_logo_merchant($basic_settings,"dark") }}"
                    data-dark_img="{{ get_logo_merchant($basic_settings) }}" alt="logo">
                </a>
                <button class="sidebar-menu-bar">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>
            <div class="sidebar-menu-wrapper">
                <ul class="sidebar-menu">
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('merchant.dashboard') }}">
                            <i class="menu-icon fas fa-th-large"></i>
                            <span class="menu-title">{{ __("Dashboard") }}</span>
                        </a>
                    </li>
                    @if(module_access('merchant-receive-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('merchant.receive.money.index') }}">
                            <i class="menu-icon fas fa-receipt"></i>
                            <span class="menu-title">{{__("Receive Money")}}</span>

                        </a>
                    </li>
                    @endif
                    @if(module_access('merchant-pay-link',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ route('merchant.payment-link.index') }}">
                            <i class="menu-icon fas fa-link"></i>
                            <span class="menu-title">{{ __('pay Link') }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('merchant-withdraw-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('merchant.withdraw.index') }}">
                            <i class="menu-icon fas fa-arrow-alt-circle-right"></i>
                            <span class="menu-title">{{ __("withdraw") }}</span>
                        </a>
                    </li>
                    @endif
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('merchant.transactions.index') }}">
                            <i class="menu-icon fas fa-arrows-alt-h"></i>
                            <span class="menu-title">{{ __("Transactions") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('merchant.security.google.2fa') }}">
                            <i class="menu-icon fas fa-qrcode"></i>
                            <span class="menu-title">{{ __("2FA Security") }}</span>
                        </a>
                    </li>
                    @if(module_access('merchant-api-key',$module)->status)
                        @if(auth()->user()->developerApi )
                            <li class="sidebar-menu-item">
                                <a href="{{ setRoute('merchant.developer.api.index') }}">
                                    <i class="menu-icon las la-key"></i>
                                    <span class="menu-title">{{ __("api Key") }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                    @if(module_access('merchant-gateway-settings',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('merchant.gateway.setting.index') }}">
                            <i class="menu-icon fas fa-tools"></i>
                            <span class="menu-title">{{ __("gateway Settings") }}</span>
                        </a>
                    </li>
                    @endif
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('merchant.support.ticket.index') }}">
                            <i class="menu-icon fas fa-headset"></i>
                            <span class="menu-title">{{ __("Support") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="javascript:void(0)" class="logout-btn">
                            <i class="menu-icon fas fa-sign-out-alt"></i>
                            <span class="menu-title">{{ __("Logout") }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="sidebar-doc-box bg_img" data-background="{{ asset('public/frontend/') }}/images/element/support.jpg">
            <div class="sidebar-doc-icon mx-auto">
                <i class="las la-code"></i>
            </div>
            <div class="sidebar-doc-content">
                <h4 class="title text-center pb-20">{{ __("Integrate Payment Gateway?") }}</h4>
                <div class="sidebar-doc-btn">
                    <a href="{{ setRoute('developer.index') }}" class="btn--base w-100">{{ __("developer API") }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        $(".logout-btn").click(function(){
            var actionRoute =  "{{ setRoute('merchant.logout') }}";
            var target      = 1;
            var sureText = '{{ __("Are you sure to") }}';
            var message     = `${sureText} <strong>{{ __("Logout") }}</strong>?`;
            var logout = `{{ __("Logout") }}`;

            openAlertModal(actionRoute,target,message,logout,"POST");
        });
    </script>
@endpush
