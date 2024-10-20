<div class="sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-logo">
            <a href="{{ setRoute('admin.dashboard') }}" class="sidebar-main-logo">
                <img src="{{ get_logo($basic_settings) }}" data-white_img="{{ get_logo($basic_settings,'white') }}"
                data-dark_img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
            </a>
            <button class="sidebar-menu-bar">
                <i class="fas fa-exchange-alt"></i>
            </button>
        </div>
        <div class="sidebar-user-area">
            <div class="sidebar-user-thumb">
                <a href="{{ setRoute('admin.profile.index') }}"><img src="{{ get_image(Auth::user()->image,'admin-profile','profile') }}" alt="user"></a>
            </div>
            <div class="sidebar-user-content">
                <h6 class="title">{{ Auth::user()->fullname }}</h6>
                <span class="sub-title">{{ Auth::user()->getRolesString() }}</span>
            </div>
        </div>
        @php
            $current_route = Route::currentRouteName();
        @endphp
        <div class="sidebar-menu-wrapper">
            <ul class="sidebar-menu">

                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.dashboard',
                    'title'     => "Dashboard",
                    'icon'      => "menu-icon las la-rocket",
                ])

                {{-- Section Default --}}
                @include('admin.components.side-nav.link-group',[
                    'group_title'       => __("DEFAULT"),
                    'group_links'       => [
                        [
                            'title'     => "Setup Currency",
                            'route'     => "admin.currency.index",
                            'icon'      => "menu-icon las la-coins",
                        ],
                        [
                            'title'     => __("Exchange Rate"),
                            'route'     => "admin.exchange.rate.index",
                            'icon'      => "menu-icon las la-wallet",
                        ],
                        [
                            'title'     => "Fees & Charges",
                            'route'     => "admin.trx.settings.index",
                            'icon'      => "menu-icon las la-hand-holding-usd",
                        ],
                        [
                            'title'     => "Virtual Card Api",
                            'route'     => "admin.virtual.card.api",
                            'icon'      => "menu-icon las la-wallet",
                        ],
                        [
                            'title'     => "PayLink Api",
                            'route'     => "admin.gateway.api.index",
                            'icon'      => "menu-icon las la-code",
                        ],
                        [
                            'title'     => __("Gift Card Api"),
                            'route'     => "admin.gift.card.index",
                            'icon'      => "menu-icon las la-gift",
                        ],
                    ]
                ])
                {{-- manage Bill Pay & Mobile top Up Methods  --}}
                @include('admin.components.side-nav.link-group',[
                    'group_links'       => [
                        'dropdown'      => [
                            [
                                'title'     => __("Bill Pay Method"),
                                'icon'      => "menu-icon las la-shopping-bag",
                                'links'     => [
                                    [
                                        'title'     => "Automatic",
                                        'route'     => "admin.bill.pay.method.automatic.index",
                                    ],
                                    [
                                        'title'     => "Manual",
                                        'route'     => "admin.bill.pay.method.manual.category.index",
                                    ]
                                ],
                            ],
                            [
                                'title'     => __("Mobile TopUp Method"),
                                'icon'      => "menu-icon las la-mobile",
                                'links'     => [
                                    [
                                        'title'     => "Automatic",
                                        'route'     => "admin.mobile.topup.method.automatic.index",
                                    ],
                                    [
                                        'title'     => "Manual",
                                        'route'     => "admin.mobile.topup.method.manual.index",
                                    ]
                                ],
                            ]

                        ],

                    ]
                ])


                {{-- Section Transaction & Logs --}}
                @include('admin.components.side-nav.link-group',[
                    'group_title'       => __("TRANSACTIONS & LOGS"),
                    'group_links'       => [
                        'dropdown'      => [
                            [
                                'title'     => __("Add Money Logs"),
                                'icon'      => "menu-icon las la-calculator",
                                'links'     => [
                                    [
                                        'title'     => "Pending Logs",
                                        'route'     => "admin.add.money.pending",
                                    ],
                                    [
                                        'title'     => "Completed Logs",
                                        'route'     => "admin.add.money.complete",
                                    ],
                                    [
                                        'title'     => "Canceled Logs",
                                        'route'     => "admin.add.money.canceled",
                                    ],
                                    [
                                        'title'     => "All Logs",
                                        'route'     => "admin.add.money.index",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("withdraw Log"),
                                'icon'              => "menu-icon las la-sign-out-alt",
                                'links'     => [
                                    [
                                        'title'     => "Pending Logs",
                                        'route'     => "admin.money.out.pending",
                                    ],
                                    [
                                        'title'     => "Completed Logs",
                                        'route'     => "admin.money.out.complete",
                                    ],
                                    [
                                        'title'     => "Canceled Logs",
                                        'route'     => "admin.money.out.canceled",
                                    ],
                                    [
                                        'title'     => "All Logs",
                                        'route'     => "admin.money.out.index",
                                    ]
                                ],
                            ],
                            [
                                'title'     => __("Remittance"),
                                'icon'      => "menu-icon las la-calculator",
                                'links'     => [
                                    [
                                        'title'     => "Receviver Countries",
                                        'route'     => "admin.remitance.countries",
                                    ],
                                    [
                                        'title'     => "Bank Deposits",
                                        'route'     => "admin.remitance.bank.deposit.index",
                                    ],
                                    [
                                        'title'     => "Cash Pickups",
                                        'route'     => "admin.remitance.cash.pickup.index",
                                    ],
                                    //logs
                                    [
                                        'title'     => "Pending Logs",
                                        'route'     => "admin.remitance.pending",
                                    ],
                                    [
                                        'title'     => "Completed Logs",
                                        'route'     => "admin.remitance.complete",
                                    ],
                                    [
                                        'title'     => "Canceled Logs",
                                        'route'     => "admin.remitance.canceled",
                                    ],
                                    [
                                        'title'     => "All Logs",
                                        'route'     => "admin.remitance.index",
                                    ]

                                ],
                            ],

                             [
                                'title'             => __("Bill Pay Logs"),
                                'icon'              => "menu-icon las la-shopping-bag",
                                'links'     => [
                                    [
                                        'title'     => "Pending Logs",
                                        'route'     => "admin.bill.pay.pending",
                                    ],
                                    [
                                        'title'     => "Processing Logs",
                                        'route'     => "admin.bill.pay.processing",
                                    ],
                                    [
                                        'title'     => "Completed Logs",
                                        'route'     => "admin.bill.pay.complete",
                                    ],
                                    [
                                        'title'     => "Canceled Logs",
                                        'route'     => "admin.bill.pay.canceled",
                                    ],
                                    [
                                        'title'     => "All Logs",
                                        'route'     => "admin.bill.pay.index",
                                    ]

                                ],
                            ],
                            [
                                'title'             =>__( "Mobile Topup"),
                                'icon'              => "menu-icon las la-mobile",
                                'links'     => [
                                    [
                                        'title'     => "Pending Logs",
                                        'route'     => "admin.mobile.topup.pending",
                                    ],
                                    [
                                        'title'     => "Processing Logs",
                                        'route'     => "admin.mobile.topup.processing",
                                    ],
                                    [
                                        'title'     => "Completed Logs",
                                        'route'     => "admin.mobile.topup.complete",
                                    ],
                                    [
                                        'title'     => "Canceled Logs",
                                        'route'     => "admin.mobile.topup.canceled",
                                    ],
                                    [
                                        'title'     => "All Logs",
                                        'route'     => "admin.mobile.topup.index",
                                    ]

                                ],
                            ],
                            [
                                'title'             => __("Send Money Log"),
                                'icon'              => "menu-icon las la-paper-plane",
                                'links'     => [

                                    [
                                        'title'     => __("All Logs"),
                                        'route'     => "admin.send.money.index",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("Money Out Logs"),
                                'icon'              => "menu-icon las la-arrow-alt-circle-left",
                                'links'     => [

                                    [
                                        'title'     => __("All Logs"),
                                        'route'     => "admin.agent.money.out.index",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("Make Payment Log"),
                                'icon'              => "menu-icon las la-arrow-alt-circle-right",
                                'links'     => [

                                    [
                                        'title'     => __("All Logs"),
                                        'route'     => "admin.make.payment.index",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("Money In Log"),
                                'icon'              => "menu-icon las la-paper-plane",
                                'links'     => [

                                    [
                                        'title'     => __("All Logs"),
                                        'route'     => "admin.money.in.index",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("request Money Log"),
                                'icon'              => "menu-icon las la-hand-holding-usd",
                                'links'     => [
                                    [
                                        'title'     => "Pending Logs",
                                        'route'     => "admin.request.money.pending",
                                    ],
                                    [
                                        'title'     => "Completed Logs",
                                        'route'     => "admin.request.money.complete",
                                    ],
                                    [
                                        'title'     => "Canceled Logs",
                                        'route'     => "admin.request.money.canceled",
                                    ],

                                    [
                                        'title'     => __("All Logs"),
                                        'route'     => "admin.request.money.index",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("Virtual Card Logs"),
                                'icon'              => "menu-icon las la-wallet",
                                'links'     => [

                                    [
                                        'title'     => __("All Logs"),
                                        'route'     => "admin.virtual.card.logs",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("SPayment Link Logs"),
                                'icon'              => "menu-icon las la-receipt",
                                'links'     => [
                                    [
                                        'title'     => __("transaction Log"),
                                        'route'     => "admin.payment.link.index",
                                    ],
                                    [
                                        'title'     => "All Link",
                                        'route'     => "admin.payment.link.all.link",
                                    ],
                                    [
                                        'title'     => "Active Link",
                                        'route'     => "admin.payment.link.active.link",
                                    ],
                                    [
                                        'title'     => "Closed Link",
                                        'route'     => "admin.payment.link.closed.link",
                                    ],
                                ],
                            ],
                            [
                                'title'             => __("Gift Card Logs"),
                                'icon'              => "menu-icon las la-gift",
                                'links'     => [

                                    [
                                        'title'     => __("All Logs"),
                                        'route'     => "admin.gift.card.logs",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("Profit Logs"),
                                'icon'              => "menu-icon las la-coins",
                                'links'     => [
                                    [
                                        'title'     => "All Profits",
                                        'route'     => "admin.profit.logs.index",
                                    ],
                                ],
                            ],
                        ],

                    ]
                ])
                {{-- Interface Panel --}}
                @include('admin.components.side-nav.link-group',[
                    'group_title'       => __("Interface Panel"),
                    'group_links'       => [
                        'dropdown'      => [
                            [
                                'title'     => __("User Care"),
                                'icon'      => "menu-icon las la-user-edit",
                                'links'     => [
                                    [
                                        'title'     => "Active Users",
                                        'route'     => "admin.users.active",
                                    ],
                                    [
                                        'title'     => "Email unverified",
                                        'route'     => "admin.users.email.unverified",
                                    ],

                                    [
                                        'title'     => "KYC Unverified",
                                        'route'     => "admin.users.kyc.unverified",
                                    ],
                                    [
                                        'title'     => "All Users",
                                        'route'     => "admin.users.index",
                                    ],
                                    [
                                        'title'     => "Email To Users",
                                        'route'     => "admin.users.email.users",
                                    ],
                                    [
                                        'title'     => "Banned Users",
                                        'route'     => "admin.users.banned",
                                    ]
                                ],
                            ],
                            [
                                'title'     => __("Agent Care"),
                                'icon'      => "menu-icon las la-user-edit",
                                'links'     => [
                                    [
                                        'title'     => "Active Agents",
                                        'route'     => "admin.agents.active",
                                    ],
                                    [
                                        'title'     => "Email unverified",
                                        'route'     => "admin.agents.email.unverified",
                                    ],
                                    [
                                        'title'     => "KYC Unverified",
                                        'route'     => "admin.agents.kyc.unverified",
                                    ],
                                    [
                                        'title'     => "All Agents",
                                        'route'     => "admin.agents.index",
                                    ],
                                    [
                                        'title'     => "Email To Agents",
                                        'route'     => "admin.agents.email.agents",
                                    ],
                                    [
                                        'title'     => "Banned Agents",
                                        'route'     => "admin.agents.banned",
                                    ]
                                ],
                            ],
                            [
                                'title'     => __("Merchant Care"),
                                'icon'      => "menu-icon las la-user-edit",
                                'links'     => [
                                    [
                                        'title'     => "Active Merchants",
                                        'route'     => "admin.merchants.active",
                                    ],
                                    [
                                        'title'     => "Email unverified",
                                        'route'     => "admin.users.email.unverified",
                                    ],

                                    [
                                        'title'     => "KYC Unverified",
                                        'route'     => "admin.merchants.kyc.unverified",
                                    ],
                                    [
                                        'title'     => "All Merchants",
                                        'route'     => "admin.merchants.index",
                                    ],
                                    [
                                        'title'     => "Email To Merchants",
                                        'route'     => "admin.merchants.email.merchants",
                                    ],
                                    [
                                        'title'     => "Banned Merchants",
                                        'route'     => "admin.merchants.banned",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("Admin Care"),
                                'icon'              => "menu-icon las la-user-shield",
                                'links'     => [
                                    [
                                        'title'     => "All Admin",
                                        'route'     => "admin.admins.index",
                                    ],
                                    [
                                        'title'     => "Admin Role",
                                        'route'     => "admin.admins.role.index",
                                    ],
                                    [
                                        'title'     => "Role Permission",
                                        'route'     => "admin.admins.role.permission.index",
                                    ],
                                    [
                                        'title'     => "Email To Admin",
                                        'route'     => "admin.admins.email.admins",
                                    ]
                                ],
                            ],

                        ],

                    ]
                ])

                {{-- Section Settings --}}
                @include('admin.components.side-nav.link-group',[
                    'group_title'       => __("Settings"),
                    'group_links'       => [
                        'dropdown'      => [
                            [
                                'title'     => __("Web Settings"),
                                'icon'      => "menu-icon lab la-safari",
                                'links'     => [
                                    [
                                        'title'     => "Basic Settings",
                                        'route'     => "admin.web.settings.basic.settings",
                                    ],
                                    [
                                        'title'     => "Image Assets",
                                        'route'     => "admin.web.settings.image.assets",
                                    ],
                                    [
                                        'title'     => "Setup SEO",
                                        'route'     => "admin.web.settings.setup.seo",
                                    ]
                                ],
                            ],
                            [
                                'title'             => __("App Settings"),
                                'icon'              => "menu-icon las la-mobile",
                                'links'     => [
                                    [
                                        'title'     => "Splash Screen",
                                        'route'     => "admin.app.settings.splash.screen",
                                    ],
                                    [
                                        'title'     => "Onboard Screen",
                                        'route'     => "admin.app.settings.onboard.index",
                                    ],
                                    [
                                        'title'     => "App URLs",
                                        'route'     => "admin.app.settings.urls",
                                    ],
                                ],
                            ],
                        ],
                    ]
                ])
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.module.setting.index',
                    'title'     => __("Setup Module"),
                    'icon'      => "menu-icon las la-box",
                ])
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.country.restriction.index',
                    'title'     => __("Country Restriction"),
                    'icon'      => "menu-icon las la-box",
                ])
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.languages.index',
                    'title'     => "Languages",
                    'icon'      => "menu-icon las la-language",
                ])

                {{-- Verification Center --}}
                @include('admin.components.side-nav.link-group',[
                    'group_title'       => __("VERIFICATION CENTER"),
                    'group_links'       => [
                        'dropdown'      => [
                            [
                                'title'     => __("Setup Email"),
                                'icon'      => "menu-icon las la-envelope-open-text",
                                'links'     => [
                                    [
                                        'title'     => "Email Method",
                                        'route'     => "admin.setup.email.config",
                                    ],
                                    // [
                                    //     'title'     => "Default Template",
                                    //     'route'     => "admin.setup.email.template.default",
                                    // ]
                                ],
                            ]
                        ],

                    ]
                ])

                 @include('admin.components.side-nav.link',[
                    'route'     => 'admin.setup.kyc.index',
                    'title'     => "Setup KYC",
                    'icon'      => "menu-icon las la-clipboard-list",
                ])


                @if (admin_permission_by_name("admin.setup.sections.section"))
                    <li class="sidebar-menu-header">{{ __("SETUP WEB CONTENT") }}</li>
                    @php
                        $current_url = URL::current();

                        $setup_section_childs  = [
                            setRoute('admin.setup.sections.section','auth-section'),
                            setRoute('admin.setup.sections.section','app-section'),
                            setRoute('admin.setup.sections.section','banner'),
                            setRoute('admin.setup.sections.section','banner-floting'),
                            setRoute('admin.setup.sections.section','work-section'),
                            setRoute('admin.setup.sections.section','about-section'),
                            setRoute('admin.setup.sections.section','security-section'),
                            setRoute('admin.setup.sections.section','overview-section'),
                            setRoute('admin.setup.sections.section','why-choose-section'),
                            setRoute('admin.setup.sections.section','brand-section'),
                            setRoute('admin.setup.sections.section','service-section'),
                            setRoute('admin.setup.sections.section','faq-section'),
                            setRoute('admin.setup.sections.section','testimonials-section'),
                            setRoute('admin.setup.sections.section','category'),
                            setRoute('admin.setup.sections.section','blog-section'),
                            setRoute('admin.setup.sections.section','agent-section'),
                            setRoute('admin.setup.sections.section','agent-app'),
                            setRoute('admin.setup.sections.section','merchant-section'),
                            setRoute('admin.setup.sections.section','merchant-app'),
                            setRoute('admin.setup.sections.section','developer-introduction'),
                            setRoute('admin.setup.sections.section','developer-faq'),
                            setRoute('admin.setup.sections.section','contact-us-section'),
                            setRoute('admin.setup.sections.section','footer-section'),
                        ];
                    @endphp

                    <li class="sidebar-menu-item sidebar-dropdown @if (in_array($current_url,$setup_section_childs)) active @endif">
                        <a href="javascript:void(0)">
                            <i class="menu-icon las la-terminal"></i>
                            <span class="menu-title">{{ __("Setup Section") }}</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li class="sidebar-menu-item">
                                <a href="{{ setRoute('admin.setup.sections.section','auth-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','auth-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Auth Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','app-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','app-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("APP Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','banner') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','banner')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Banner Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','banner-floting') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','banner-floting')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Banner Floting") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','work-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','work-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Work Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','about-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','about-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("About Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','security-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','security-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Security Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','overview-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','overview-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Overview Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','why-choose-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','why-choose-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Why Choose Us") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','brand-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','brand-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Brand Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','service-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','service-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Service Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','faq-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','faq-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("FAQ Section") }}</span>
                                </a>

                                <a href="{{ setRoute('admin.setup.sections.section','testimonials-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','testimonials-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Testimonials Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','category') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','category')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Blog Category") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','blog-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','blog-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Blog Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','agent-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','agent-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Agent Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','agent-app') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','agent-app')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Agent App") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','merchant-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','merchant-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Merchant Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','merchant-app') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','merchant-app')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Merchant App") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','developer-introduction') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','developer-introduction')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Developer Intro") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','developer-faq') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','developer-faq')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Developer FAQ") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','contact-us-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','contact-us-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Contact Us Section") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.setup.sections.section','footer-section') }}" class="nav-link @if ($current_url == setRoute('admin.setup.sections.section','footer-section')) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Footer Section") }}</span>
                                </a>

                            </li>
                        </ul>
                    </li>
                @endif
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.setup.pages.index',
                    'title'     => __("Setup Pages"),
                    'icon'      => "menu-icon las la-file-alt",
                ])

                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.useful.links.index',
                    'title'     => __("Useful LInks"),
                    'icon'      => "menu-icon las la-link",
                ])

                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.extensions.index',
                    'title'     => __("Extensions"),
                    'icon'      => "menu-icon las la-puzzle-piece",
                ])

                @if (admin_permission_by_name("admin.payment.gateway.view"))
                    <li class="sidebar-menu-header">{{ __("PAYMENT METHODS") }}</li>
                    @php
                        $payment_add_money_childs  = [
                            setRoute('admin.payment.gateway.view',['add-money','automatic']),
                            setRoute('admin.payment.gateway.view',['add-money','manual']),
                        ]
                    @endphp
                    <li class="sidebar-menu-item sidebar-dropdown @if (in_array($current_url,$payment_add_money_childs)) active @endif">
                        <a href="javascript:void(0)">
                            <i class="menu-icon las la-funnel-dollar"></i>
                            <span class="menu-title">{{ __("Add Money") }}</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li class="sidebar-menu-item">
                                <a href="{{ setRoute('admin.payment.gateway.view',['add-money','automatic']) }}" class="nav-link @if ($current_url == setRoute('admin.payment.gateway.view',['add-money','automatic'])) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Automatic") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.payment.gateway.view',['add-money','manual']) }}" class="nav-link @if ($current_url == setRoute('admin.payment.gateway.view',['add-money','manual'])) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Manual") }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    {{-- //money-out --}}
                    @php
                        $payment_money_out_childs  = [
                            setRoute('admin.payment.gateway.view',['withdraw','automatic']),
                            setRoute('admin.payment.gateway.view',['withdraw','manual']),
                        ]

                    @endphp
                    <li class="sidebar-menu-item sidebar-dropdown @if (in_array($current_url,$payment_money_out_childs)) active @endif">
                        <a href="javascript:void(0)">
                            <i class="menu-icon las la-funnel-dollar"></i>
                            <span class="menu-title">{{ __("withdraw") }}</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li class="sidebar-menu-item">
                                <a href="{{ setRoute('admin.payment.gateway.view',['withdraw','automatic']) }}" class="nav-link @if ($current_url == setRoute('admin.payment.gateway.view',['withdraw','automatic'])) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Automatic") }}</span>
                                </a>
                                <a href="{{ setRoute('admin.payment.gateway.view',['withdraw','manual']) }}" class="nav-link @if ($current_url == setRoute('admin.payment.gateway.view',['withdraw','manual'])) active @endif">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Manual") }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Notifications --}}
                @include('admin.components.side-nav.link-group',[
                    'group_title'       => __("NOTIFICATION-UPPER"),
                    'group_links'       => [
                        'dropdown'      => [
                            [
                                'title'     => __("Push Notification"),
                                'icon'      => "menu-icon las la-bell",
                                'links'     => [
                                    [
                                        'title'     => "Setup Notification",
                                        'route'     => "admin.push.notification.config",
                                    ],
                                    [
                                        'title'     => "Send Notification",
                                        'route'     => "admin.push.notification.index",
                                    ]
                                ],
                            ]
                        ],

                    ]
                ])

                @php
                    $bonus_routes = [
                        'admin.cookie.index',
                        'admin.server.info.index',
                        'admin.cache.clear',
                    ];
                @endphp

                @if (admin_permission_by_name_array($bonus_routes))
                    <li class="sidebar-menu-header">{{ __("BONUS") }}</li>
                @endif
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.newsletter.index',
                    'title'     => "Newsletter",
                    'icon'      => "menu-icon las la-newspaper",
                ])
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.contact.messages.index',
                    'title'     => __("Contact Messages"),
                    'icon'      => "menu-icon las la-envelope",
                ])
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.cookie.index',
                    'title'     => __("GDPR Cookie"),
                    'icon'      => "menu-icon las la-cookie-bite",
                ])
                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.server.info.index',
                    'title'     => "Server Info",
                    'icon'      => "menu-icon las la-sitemap",
                ])

                @include('admin.components.side-nav.link',[
                    'route'     => 'admin.cache.clear',
                    'title'     => "Clear Cache",
                    'icon'      => "menu-icon las la-broom",
                ])
            </ul>
        </div>
    </div>
</div>
