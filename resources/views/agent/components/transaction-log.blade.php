@isset($transactions)
    @forelse ($transactions as $item)
        <div class="dashboard-list-item-wrapper">
            <div class="dashboard-list-item sent">
                <div class="dashboard-list-left">
                    <div class="dashboard-list-user-wrapper">
                        <div class="dashboard-list-user-icon">
                            @if ($item->attribute == payment_gateway_const()::SEND)
                            <i class="las la-arrow-up"></i>
                            @else
                            <i class="las la-arrow-down"></i>
                            @endif
                        </div>
                        <div class="dashboard-list-user-content">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <h4 class="title">{{ __("Add Balance via") }} <span class="text--warning">{{ @$item->currency->name }}</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <h4 class="title">{{ __("Withdraw Money") }} <span class="text--warning">{{ @$item->currency->name }}</span></h4>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <h4 class="title">{{ __("Bill Pay") }} <span class="text--warning">({{ @$item->details->bill_type_name }})</span></h4>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <h4 class="title">{{ __("Mobile Topup") }} <span class="text--warning">({{ @$item->details->topup_type_name }})</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                            <h4 class="title">{{ __("Balance Update From Admin") }}{{ __(" (".$item->creator_wallet->currency->code.")") }} </h4>
                            @elseif ($item->type == payment_gateway_const()::TYPETRANSFERMONEY)
                                @if ($item->isAuthUserAgent())
                                    @if ($item->attribute == payment_gateway_const()::SEND)
                                        <h4 class="title">{{ __("Send Money to") }} {{ __(" @" . @$item->details->receiver_username." (".@$item->details->receiver_email.")") }} </h4>
                                    @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                        <h4 class="title">{{ __("Received Money from") }} {{ __(" @" .@$item->details->sender_username." (".@$item->details->sender_email.")") }} </h4>
                                    @endif
                                @endif
                            @elseif ($item->type == payment_gateway_const()::AGENTMONEYOUT)
                                @if ($item->isAuthUserAgent())
                                    <h4 class="title">{{ __("Received Money from") }} {{ __(" @" .@$item->details->sender_username." (".@$item->details->sender_email.")") }} </h4>
                                @endif
                            @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                                @if ($item->isAuthUserAgent())
                                    @if ($item->attribute == payment_gateway_const()::SEND)
                                        <h4 class="title">{{ __("Send Remittance to") }} {{ __(" @" . $item->details->receiver_recipient->email) }} </h4>
                                    @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                        <h4 class="title">{{ __("Received Remittance from") }} {{ __(" @" .@$item->details->sender->fullname." (".@$item->details->sender->full_mobile.")") }} </h4>
                                    @endif
                                @endif
                            @elseif ($item->type == payment_gateway_const()::MONEYIN)
                                @if ($item->isAuthUserAgent())
                                    @if ($item->attribute == payment_gateway_const()::SEND)
                                        <h4 class="title">{{ __("Money In To") }} {{ __(" @" . @$item->details->receiver_username." (".@$item->details->receiver_email.")") }} </h4>
                                    @endif
                                @endif
                            @endif
                            <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }} </span>
                        </div>
                    </div>
                </div>
                <div class="dashboard-list-right">
                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,$item->currency->currency_code??get_default_currency_code()) }}</h6>
                    @elseif($item->type == payment_gateway_const()::TYPEMONEYOUT)
                        <h6 class="exchange-money text--warning fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money ">{{ get_amount($item->payable,$item->currency->currency_code??get_default_currency_code()) }}</h4>
                    @elseif($item->type == payment_gateway_const()::BILLPAY)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,billPayCurrency($item)['sender_currency']) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,billPayCurrency($item)['wallet_currency']) }}</h6>
                    @elseif($item->type == payment_gateway_const()::MOBILETOPUP)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,topUpCurrency($item)['destination_currency']) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,topUpCurrency($item)['wallet_currency']) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                        <h4 class="main-money text--base">{{ get_transaction_numeric_attribute($item->attribute) }}{{ get_amount($item->request_amount,$item->creator_wallet->currency->code) }}</h4>
                        <h6 class="exchange-money">{{ get_amount($item->available_balance,$item->creator_wallet->currency->code) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPETRANSFERMONEY)
                        @if ($item->attribute == payment_gateway_const()::SEND)
                        <h6 class="exchange-money text--warning ">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h4>
                        @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        @endif
                    @elseif ($item->type == payment_gateway_const()::AGENTMONEYOUT)
                        @if ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        @endif
                    @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                        @if ($item->attribute == payment_gateway_const()::SEND)
                        <h6 class="exchange-money text--warning ">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h4>
                        @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        @endif
                    @elseif($item->type == payment_gateway_const()::MONEYIN)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h6>
                    @endif
                </div>
            </div>
            <div class="preview-list-wrapper">

                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="lab la-tumblr"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("web_trx_id") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->trx_id }}</span>
                    </div>
                </div>
                @if ($item->type != payment_gateway_const()::TYPETRANSFERMONEY )
                @if ($item->type != payment_gateway_const()::AGENTMONEYOUT )
                @if ($item->type != payment_gateway_const()::SENDREMITTANCE )
                @if ($item->type != payment_gateway_const()::MONEYIN )
                <div class="preview-list-item">

                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-exchange-alt"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Exchange Rate") }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-list-right">
                        @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->currency->rate??1,$item->currency->currency_code??get_default_currency_code()) }}</span>
                        @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->details->to_country->rate,$item->details->to_country->code) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->currency->rate??1,@$item->currency->currency_code??get_default_currency_code()) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->creator_wallet->currency->rate,$item->creator_wallet->currency->code) }}</span>
                        @elseif ($item->type == payment_gateway_const()::BILLPAY)
                            <span>{{ billPayExchangeRate($item)['exchange_info'] }}</span>
                        @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                            <span>{{ topUpExchangeRate($item)['exchange_info'] }}</span>
                        @endif
                    </div>
                </div>
                @endif
                @endif
                @endif
                @endif

                @if ($item->type == payment_gateway_const()::BILLPAY )
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("bill Type") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->bill_type_name }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las las la-list-ol"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Bill Month") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->bill_month }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Bill Number") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->bill_number }}</span>
                    </div>
                </div>
                @endif
                @if ($item->type == payment_gateway_const()::MOBILETOPUP )
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("TopUp Type") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->topup_type_name }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="fas fa-mobile"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Mobile Number") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->mobile_number }}</span>
                    </div>
                </div>
                @endif

                @if ($item->type == payment_gateway_const()::TYPETRANSFERMONEY)
                    @if ($item->attribute == payment_gateway_const()::SEND)
                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="las la-battery-half"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("fees And Charges") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code) }}</span>
                            </div>
                        </div>
                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="lab la-get-pocket"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Recipient Received") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span>{{ get_amount($item->details->charges->receiver_amount,get_default_currency_code()) }}</span>
                            </div>
                        </div>

                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="las la-balance-scale"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Current Balance") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span class="text--base">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            </div>
                        </div>
                    @else
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-balance-scale"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Current Balance") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span class="text--base">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                        </div>
                    </div>
                    @endif
                @else
                    @if ($item->type != payment_gateway_const()::AGENTMONEYOUT )
                    @if ($item->type != payment_gateway_const()::SENDREMITTANCE )
                    @if ($item->type != payment_gateway_const()::MONEYIN )
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-battery-half"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("fees And Charges") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span>{{ get_amount($item->charge->total_charge??0,@$item->currency->currency_code??get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <span>{{ get_amount($item->charge->total_charge??0,@$item->currency->currency_code??get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span>{{ get_amount($item->charge->total_charge,billPayCurrency($item)['wallet_currency']) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span>{{ get_amount($item->charge->total_charge,topUpCurrency($item)['wallet_currency']) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif
                    @if ($item->type != payment_gateway_const()::BILLPAY)
                    @if ($item->type != payment_gateway_const()::MOBILETOPUP)
                    @if ($item->type != payment_gateway_const()::SENDREMITTANCE)
                    @if ($item->type != payment_gateway_const()::MONEYIN)
                    @if ($item->type != payment_gateway_const()::AGENTMONEYOUT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="lab la-get-pocket"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                        <span>{{ __("Conversion Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                        <span>{{ __("Payable Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                        <span>{{ __("Payable Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                        @if($item->attribute ==  payment_gateway_const()::SEND)
                                            <span>{{ __("Total Deducted") }}</span>
                                            @else
                                            <span>{{ __("total Received") }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span class="text-danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                             @php
                                 $conversionAmount = $item->request_amount * $item->currency->rate??1;
                             @endphp
                                <span>{{ get_amount($conversionAmount,@$item->currency->currency_code??get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span class="fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span class="fw-bold">{{ get_amount($item->payable,topUpCurrency($item)['wallet_currency']) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->payable,$item->creator_wallet->currency->code) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif
                    @endif
                    @endif

                    @if ($item->type != payment_gateway_const()::TYPEADDMONEY)
                    @if ($item->type != payment_gateway_const()::SENDREMITTANCE)
                    @if ($item->type != payment_gateway_const()::MONEYIN)
                    @if ($item->type != payment_gateway_const()::AGENTMONEYOUT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                        <span>{{ __("Total Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                        <span>{{ __("remark") }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span class="text--warning">{{ get_amount($item->payable,@$item->currency->currency_code??get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <span class="text--danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span class="text--danger">{{ get_amount($item->available_balance,billPayCurrency($item)['wallet_currency']) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span class="text--danger">{{ get_amount($item->available_balance,topUpCurrency($item)['wallet_currency']) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span class="text--warning">{{ $item->remark }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif
                    @endif
                @endif
                @if ($item->type == payment_gateway_const()::SENDREMITTANCE)
                @if ($item->attribute == payment_gateway_const()::SEND)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-exchange-alt"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Exchange Rate") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->details->to_country->rate,$item->details->to_country->code) }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-battery-half"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("fees And Charges") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                    </div>
                </div>
                @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-flag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("sending Country") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->form_country }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-flag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Receiving Country") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->to_country->country }}</span>
                    </div>
                </div>
                @if ($item->attribute == payment_gateway_const()::SEND)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-user-tag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Sender Recipient Name") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname}}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-user-tag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Receiver Recipient Name") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname}}</span>
                    </div>
                </div>
                @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-cash-register"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Transaction Type") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                            @if( @$item->details->remitance_type == "wallet-to-wallet-transfer")
                                    <span class="text-base"> {{@$basic_settings->site_name}} {{__("Wallet")}}</span>
                                    @else
                                    <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$item->details->remitance_type))}}</span>

                            @endif
                    </div>
                </div>
                @if( @$item->details->remitance_type == "bank-transfer")

                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-piggy-bank"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("bank Name") }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="preview-list-right">
                        <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$item->details->receiver_recipient->alias))}}</span>
                        </div>
                    </div>
                @endif
                @if( @$item->details->remitance_type == "cash-pickup")
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-piggy-bank"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Pickup Point") }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="preview-list-right">
                        <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$item->details->receiver_recipient->alias))}}</span>
                        </div>
                    </div>
                @endif
                 @if ($item->attribute == payment_gateway_const()::SEND)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-piggy-bank"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Receipient Get") }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-list-right">
                    <span class="text-base fw-bold"> {{ number_format(@$item->details->recipient_amount,2)}} {{ $item->details->to_country->code }}</span>
                    </div>
                </div>
                @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-smoking"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Current Balance") }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-list-right">
                    <span class="text-base fw-bold"> {{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                    </div>
                </div>
                @endif
                @if ($item->type == payment_gateway_const()::MONEYIN)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-battery-half"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("fees And Charges") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code) }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="lab la-get-pocket"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Recipient Received") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ get_amount($item->details->charges->receiver_amount,get_default_currency_code()) }}</span>
                        </div>
                    </div>

                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-balance-scale"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Current Balance") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span class="text--base">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                        </div>
                    </div>
                    @endif
                    @if ($item->type == payment_gateway_const()::AGENTMONEYOUT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-battery-half"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("fees And Charges") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code) }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="lab la-get-pocket"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Recipient Received") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span>{{ get_amount($item->details->charges->receiver_amount,get_default_currency_code()) }}</span>
                        </div>
                    </div>

                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-balance-scale"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Current Balance") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span class="text--base">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                        </div>
                    </div>
                    @endif

                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-clock"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Time & Date") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->created_at->format('d-m-y h:i:s A') }}</span>
                    </div>
                </div>

                @if (@$item->type == payment_gateway_const()::TYPEADDMONEY)
                @if ($item->gateway_currency->gateway->isTatum($item->gateway_currency->gateway) && $item->status == payment_gateway_const()::STATUSWAITING)
                <div class="col-12">
                    <form action="{{ setRoute('agent.add.money.payment.crypto.confirm', $item->trx_id) }}" method="POST">
                        @csrf
                        @php
                            $input_fields = $item->details->payment_info->requirements ?? [];
                        @endphp

                        @foreach ($input_fields as $input)
                            <div class="p-3">
                                <h6 class="mb-2">{{ $input->label }}</h6>
                                <input type="text" class="form-control form--control ref-input text-light copiable" name="{{ $input->name }}" placeholder="{{ $input->placeholder ?? "" }}" required>
                            </div>
                        @endforeach

                        <div class="text-end">
                            <button type="submit" class="btn--base my-2">{{ __("Process") }}</button>
                        </div>

                    </form>
                </div>
                @endif
            @endif

            @if( $item->status == 4 || $item->status == 6 &&  $item->reject_reason != null)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-smoking"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Rejection Reason") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text-danger">{{ __($item->reject_reason) }}</span>
                    </div>
                </div>
                @endif



            </div>
        </div>
    @empty
        <div class="alert alert-primary text-center">
            {{ __("No data found!") }}
        </div>
    @endforelse

    {{ get_paginate($transactions) }}


@endisset
