@isset($crypto_wallets)
    @if ($crypto_wallets->count() > 0)
        <div class="dashboard-item-area">
            <div class="dashboard-header-wrapper">
                <h5 class="title">{{ __("Crypto Currency") }}</h5>
            </div>
            <div class="row mb-20-none">
                @forelse ($crypto_wallets as $item)
                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-20">
                        <div class="dashbord-item">
                            <div class="dashboard-content">
                                <span class="sub-title">{{ @$item->currency->name }}</span>
                                <h3 class="title">{{ $item->balance }} <span class="text--danger">{{ $item->currency->code }}</span></h3>
                            </div>
                            <div class="dashboard-icon">
                                <img src="{{ get_image($item->currency->flag,"currency-flag") }}" alt="flag">
                            </div>
                        </div>
                    </div>
                @empty

                @endforelse
            </div>
        </div>
    @endif
@endisset
