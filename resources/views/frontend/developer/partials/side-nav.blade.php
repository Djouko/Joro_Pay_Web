<div class="developer-bar">
    <div class="developer-bar-wrapper">
        <ul class="developer-bar-main-menu">
            <li class="sidebar-single-menu {{ menuActive('developer.index') }}">
                <a href="{{ setRoute('developer.index') }}">
                    <span class="title">{{ __("Introduction") }}</span>
                </a>
            </li>
            <li class="sidebar-single-menu has-sub @if(
                menuActive('developer.prerequisites') ||
                menuActive('developer.authentication')||
                menuActive('developer.base.url')

                ) active @endif">
                <a href="javacript:void(0)">
                    <span class="title">{{ __("Getting Started") }}</span>
                </a>
                <ul class="sidebar-submenu
                @if(
                menuActive('developer.prerequisites') ||
                menuActive('developer.authentication')||
                menuActive('developer.base.url')

                ) open @endif">
                    <li class="nav-item {{ menuActive('developer.prerequisites') }}">
                        <a href="{{ setRoute('developer.prerequisites') }}">
                            <span class="title">{{ __("Prerequisites") }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{ menuActive('developer.authentication') }}">
                        <a href="{{ setRoute('developer.authentication') }}">
                            <span class="title">{{ __("Authentication") }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{ menuActive('developer.base.url') }}">
                        <a href="{{ setRoute('developer.base.url') }}">
                            <span class="title">{{ __("Base URL") }}</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-single-menu has-sub  @if(
                menuActive('developer.initiate.payment')||
                menuActive('developer.check.status.payment') ||
                menuActive('developer.access.token')
                ) active @endif">
                <a href="javascript:void(0)">
                    <span class="title">{{ __("API Reference") }}</span>
                </a>
                <ul class="sidebar-submenu  @if(
                    menuActive('developer.initiate.payment')||
                    menuActive('developer.check.status.payment') ||
                    menuActive('developer.access.token')
                    ) open @endif">
                    <li class="nav-item {{ menuActive('developer.access.token') }}">
                        <a href="{{  setRoute('developer.access.token')  }}">
                            <span class="title">{{ __("Access Token") }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{ menuActive('developer.initiate.payment') }}">
                        <a href="{{ setRoute('developer.initiate.payment') }}">
                            <span class="title">{{ __("Initiate Payment") }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{  menuActive('developer.check.status.payment') }}">
                        <a href="{{ setRoute('developer.check.status.payment') }}">
                            <span class="title">{{ __("Check Payment Status") }}</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-single-menu {{ menuActive('developer.response.code') }}">
                <a href="{{ setRoute("developer.response.code") }}">
                    <span class="title">{{ __("Response Codes") }}</span>
                </a>
            </li>
            <li class="sidebar-single-menu {{ menuActive('developer.error.handling') }}">
                <a href="{{ setRoute("developer.error.handling") }}">
                    <span class="title">{{ __("Error Handling") }}</span>
                </a>
            </li>
            <li class="sidebar-single-menu {{ menuActive('developer.best.practices') }}">
                <a href="{{ setRoute("developer.best.practices") }}">
                    <span class="title">{{ __("Best Practices") }}</span>
                </a>
            </li>
            <li class="sidebar-single-menu {{ menuActive('developer.examples') }}">
                <a href="{{ setRoute("developer.examples") }}">
                    <span class="title">{{ __("Examples") }}</span>
                </a>
            </li>
            <li class="sidebar-single-menu  {{ menuActive('developer.faq') }}">
                <a href="{{ setRoute('developer.faq') }}">
                    <span class="title">{{ __("FAQ") }}</span>
                </a>
            </li>
            <li class="sidebar-single-menu {{ menuActive('developer.support') }}">
                <a href="{{setRoute('developer.support') }}">
                    <span class="title">{{ __("Support") }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>
