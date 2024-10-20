
<div class="sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-menu-inner-wrapper">
            <div class="sidebar-logo">
                <a href="{{ setRoute('index') }}" class="sidebar-main-logo">
                    <img src="{{ get_logo_agent($basic_settings) }}" data-white_img="{{ get_logo_agent($basic_settings,"dark") }}"
                    data-dark_img="{{ get_logo_agent($basic_settings) }}" alt="logo">
                </a>
                <button class="sidebar-menu-bar">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>
            <div class="sidebar-menu-wrapper">
                <ul class="sidebar-menu">
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.dashboard') }}">
                            <i class="menu-icon fas fa-th-large"></i>
                            <span class="menu-title">{{ __("Dashboard") }}</span>
                        </a>
                    </li>
                    @if(module_access('agent-receive-money',$module)->status)
                        <li class="sidebar-menu-item">
                            <a href="{{ setRoute('agent.receive.money.index') }}">
                                <i class="menu-icon fas fa-receipt"></i>
                                <span class="menu-title">{{__("Receive Money")}}</span>

                            </a>
                        </li>
                    @endif
                    @if(module_access('agent-add-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.add.money.index') }}">
                            <i class="menu-icon fas fa-plus-circle"></i>
                            <span class="menu-title">{{ __("Add Money") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('agent-transfer-money',$module)->status)
                        <li class="sidebar-menu-item">
                            <a href="{{ setRoute('agent.send.money.index') }}">
                                <i class="menu-icon fas fa-paper-plane"></i>
                                <span class="menu-title">{{ __("Send Money") }}</span>
                            </a>
                        </li>
                    @endif
                    @if(module_access('agent-money-in',$module)->status)
                        <li class="sidebar-menu-item">
                            <a href="{{ setRoute('agent.money.in.index') }}">
                                <i class="menu-icon fas fa-paper-plane"></i>
                                <span class="menu-title">{{ __("Money In") }}</span>
                            </a>
                        </li>
                    @endif
                    @if(module_access('agent-bill-pay',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.bill.pay.index') }}">
                            <i class="menu-icon fas fa-shopping-bag"></i>
                            <span class="menu-title">{{ __("Bill Pay") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('agent-mobile-top-up',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.mobile.topup.index') }}">
                            <i class="menu-icon fas fa-mobile"></i>
                            <span class="menu-title">{{ __("Mobile Topup") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('agent-withdraw-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.money.out.index') }}">
                            <i class="menu-icon fas fa-arrow-alt-circle-right"></i>
                            <span class="menu-title">{{ __("withdraw") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('agent-remittance-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.remittance.index') }}">
                            <i class="menu-icon fas fa-coins"></i>
                            <span class="menu-title">{{ __("Remittance") }}</span>
                        </a>
                    </li>
                    @endif
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.sender.recipient.index') }}">
                            <i class="menu-icon fas fa-user-edit"></i>
                            <span class="menu-title">{{ __("Saved My Sender") }}</span>
                        </a>
                    </li>

                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.receiver.recipient.index') }}">
                            <i class="menu-icon fas fa-user-check"></i>
                            <span class="menu-title">{{ __("Saved My Receiver") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.transactions.index') }}">
                            <i class="menu-icon fas fa-arrows-alt-h"></i>
                            <span class="menu-title">{{ __("Transactions") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.profits.index') }}">
                            <i class="menu-icon fas fa-hand-holding-usd"></i>
                            <span class="menu-title">{{ __("Profits Log") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.security.google.2fa') }}">
                            <i class="menu-icon fas fa-qrcode"></i>
                            <span class="menu-title">{{ __("2FA Security") }}</span>
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
            <div class="sidebar-doc-icon">
                <i class="las la-question-circle"></i>
            </div>
            <div class="sidebar-doc-content">
                <h4 class="title">{{ __("help Center") }}?</h4>
                <p>{{ __("How can we help you?") }}</p>
                <div class="sidebar-doc-btn">
                    <a href="{{ setRoute('agent.support.ticket.index') }}" class="btn--base w-100">{{ __("Get Support") }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        $(".logout-btn").click(function(){
            var actionRoute =  "{{ setRoute('agent.logout') }}";
            var target      = 1;
            var sureText = '{{ __("Are you sure to") }}';
            var message     = `${sureText} <strong>{{ __("Logout") }}</strong>?`;
            var logout = `{{ __("Logout") }}`;
            openAlertModal(actionRoute,target,message,logout,"POST");
        });
    </script>
@endpush
