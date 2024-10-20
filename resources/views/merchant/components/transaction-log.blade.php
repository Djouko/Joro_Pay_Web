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

                            @if ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <h4 class="title">{{ __("Withdraw Money") }} <span class="text--warning">{{ @$item->currency->name }}</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <h4 class="title">{{ __("Balance Update From Admin") }}{{ __(" (".$item->creator_wallet->currency->code.")") }} </h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)
                                @if ($item->isAuthUserMerchant())
                                    @if ($item->attribute == payment_gateway_const()::SEND)
                                        <h4 class="title">{{ __("Make Payment to") }} {{ __("@" . @$item->details->receiver->username." (".@$item->details->receiver->email.")") }} </h4>
                                    @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                        <h4 class="title">{{ __("Make Payment From") }} {{ __("@" .@$item->details->sender->username." (".@$item->details->sender->email.")") }} </h4>
                                    @endif
                                @endif
                            @elseif ($item->type == payment_gateway_const()::MERCHANTPAYMENT)
                                @if ($item->isAuthUserMerchant())
                                    @if ($item->attribute == payment_gateway_const()::RECEIVED)
                                        <h4 class="title">{{ __("Payment Money from") }}{{ __("@" . @$item->details->sender_username." (".@$item->details->pay_type.")") }} </h4>
                                        <span class="d-block py-1 text-warning font-weight-bold">{{ __(@$item->details->env_type) }}</span>
                                    @endif
                                @endif

                            @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                                <h4 class="title">{{ __('Add Balance via') }} <span class="text--warning">({{ $item->type }})</span></h4>
                            @endif
                            <span class="{{ $item->stringStatus->class }}">{{__($item->stringStatus->value) }} </span>
                        </div>
                    </div>
                </div>
                <div class="dashboard-list-right">
                    @if($item->type == payment_gateway_const()::TYPEMONEYOUT)
                        <h6 class="exchange-money text--warning fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money ">{{ get_amount($item->payable,$item->currency->currency_code??get_default_currency_code()) }}</h4>
                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                        <h4 class="main-money text--base">{{ get_transaction_numeric_attribute($item->attribute) }}{{ get_amount($item->request_amount,$item->creator_wallet->currency->code) }}</h4>
                        <h6 class="exchange-money">{{ get_amount($item->available_balance,$item->creator_wallet->currency->code) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)
                        @if ($item->attribute == payment_gateway_const()::SEND)
                        <h6 class="exchange-money text--warning ">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h4>
                        @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        @endif

                    @elseif ($item->type == payment_gateway_const()::MERCHANTPAYMENT)
                        @if ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h4>
                        @endif
                    @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                        <h4 class="main-money text--base">{{ get_amount($item->request_amount, @$item->details->charge_calculation->sender_cur_code) }}</h4>
                        <h6 class="exchange-money text--warning">{{ get_amount($item->details->charge_calculation->conversion_payable,  @$item->details->charge_calculation->receiver_currency_code) }}</h6>
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

                @if ($item->type != payment_gateway_const()::TYPEMAKEPAYMENT )
                @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT )

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
                        @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                            <span>1 {{ $item->creator_wallet->currency->code }} = {{ get_amount($item->details->exchange_rate,$item->details->exchange_currency) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->creator_wallet->currency->rate,$item->creator_wallet->currency->code) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                            <span>1 {{ @$item->details->charge_calculation->receiver_currency_code }} = {{ get_amount(@$item->details->charge_calculation->exchange_rate, @$item->details->charge_calculation->sender_cur_code) }}</span>
                        @endif
                    </div>
                </div>
                @endif
                @endif

                @if ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)
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
                                <span>{{ get_amount($item->details->recipient_amount,get_default_currency_code()) }}</span>
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

                    @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT )
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
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span>{{ get_amount($item->details->total_charge,$item->creator_wallet->currency->code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEPAYLINK)
                                <span class="text--danger">{{ get_amount(@$item->details->charge_calculation->conversion_charge ?? 0, $item->details->charge_calculation->receiver_currency_code, 4) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif


                    @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT)
                    @if ($item->type != payment_gateway_const()::TYPEPAYLINK)
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
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                        <span>{{ __("Total Payable") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                        @if($item->attribute ==  payment_gateway_const()::SEND)
                                            <span>{{ __("Total Deducted") }}</span>
                                            @else
                                            <span>{{ __("total Received") }}</span>
                                        @endif
                                    @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                        <span>{{ __("card Amount") }}</span>
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
                                <span class="fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                <span class="fw-bold"> {{ get_amount(@$item->details->card_info->amount,get_default_currency_code()) }}</span>

                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span>{{ get_amount($item->payable,$item->creator_wallet->currency->code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->payable,$item->creator_wallet->currency->code) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif

                    @if ($item->type != payment_gateway_const()::MERCHANTPAYMENT)
                    @if ($item->type != payment_gateway_const()::TYPEPAYLINK)
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
                                    @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                        <span>{{ __("Card Number") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                        <span>{{ __("Exchange Amount") }}</span>
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
                                <span class="text--danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span class="text--danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                @php
                                    $card_pan = str_split(@$item->details->card_info->card_pan, 4);
                                @endphp
                                @foreach($card_pan as $key => $value)
                                <span class="text--base fw-bold">{{ $value }}</span>
                                @endforeach
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span class="text--warning">{{ get_amount($item->details->exchange_amount,$item->details->exchange_currency) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span class="text--warning">{{ $item->remark }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                @endif
                 {{-- make pay to merchant by payemt gateway --}}
                 @if ($item->type == payment_gateway_const()::MERCHANTPAYMENT)

                 <div class="preview-list-item">
                     <div class="preview-list-left">
                         <div class="preview-list-user-wrapper">
                             <div class="preview-list-user-icon">
                                 <i class="las la-balance-scale"></i>
                             </div>
                             <div class="preview-list-user-content">
                                 <span>{{ __("Bussines Name") }}</span>
                             </div>
                         </div>
                     </div>
                     <div class="preview-list-right">
                         <span>{{ $item->details->payment_to }}</span>
                     </div>
                 </div>
                 <div class="preview-list-item">
                     <div class="preview-list-left">
                         <div class="preview-list-user-wrapper">
                             <div class="preview-list-user-icon">
                                 <i class="las la-user"></i>
                             </div>
                             <div class="preview-list-user-content">
                                 <span>{{ __("sender") }}</span>
                             </div>
                         </div>
                     </div>
                     <div class="preview-list-right">
                         <span>{{ $item->details->sender_username }}</span>
                     </div>
                 </div>
                 <div class="preview-list-item">
                     <div class="preview-list-left">
                         <div class="preview-list-user-wrapper">
                             <div class="preview-list-user-icon">
                                 <i class="las la-receipt"></i>
                             </div>
                             <div class="preview-list-user-content">
                                 <span>{{ __("payment Amount") }}</span>
                             </div>
                         </div>
                     </div>
                     <div class="preview-list-right">
                         <span>{{ get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency) }}</span>
                     </div>
                 </div>
            @endif
            @if ($item->type == payment_gateway_const()::TYPEPAYLINK)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="lab la-get-pocket"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __('availabe Blance') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--success">{{ get_amount($item->available_balance, $item->details->charge_calculation->receiver_currency_code) }}</span>
                    </div>
                </div>
                @if(isset($item->details->payment_type) && $item->details->payment_type == payment_gateway_const()::TYPE_CARD_PAYMENT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Type') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ ucwords(str_replace('_',' ',$item->details->payment_type) )}}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-envelope"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Sender Email') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ $item->details->email }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-user"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Card Holder Name') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ $item->details->card_name }}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-credit-card"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Sender Card Number') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">**** **** **** {{ @$item->details->last4_card }}</span>
                        </div>
                    </div>
                @endif
                @if(isset($item->details->payment_type) && $item->details->payment_type == payment_gateway_const()::TYPE_WALLET_SYSTEM)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Type') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ ucwords(str_replace('_',' ',$item->details->payment_type) )}}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-envelope"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Sender Email') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ $item->details->sender->email }}</span>
                        </div>
                    </div>
                @endif
                @if(isset($item->details->payment_type) && $item->details->payment_type == payment_gateway_const()::TYPE_GATEWAY_PAYMENT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Type') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{ ucwords(str_replace('_',' ',$item->details->payment_type) )}}</span>
                        </div>
                    </div>
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-hand-holding-usd"></i>
                                </div>
                                <div class="preview-list-user-content">
                                        <span>{{ __('Payment Gateway') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                                <span class="text--bold">{{$item->details->currency->name}}</span>
                        </div>
                    </div>
                @endif
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
