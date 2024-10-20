<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\ReceiverCounty;
use App\Models\Admin\TransactionSetting;
use App\Models\AgentNotification;
use App\Models\AgentRecipient;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\Agent\Remittance\SenderEmail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RemittanceController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'RT'.getTrxNum();
    }
    public function remittanceInfo(){
        $user = auth()->user();
        $basic_settings = BasicSettings::first();
        $userWallet = AgentWallet::where('agent_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,4),
                'currency' => $data->currency->code,
                'rate' => getAmount($data->currency->rate,4),
            ];
        })->first();
        $transactionType = [
            [
                'id'    => 1,
                'field_name' => Str::slug(GlobalConst::TRX_BANK_TRANSFER),
                'label_name' => "Bank Transfer",
            ],
            [
                'id'    => 2,
                'field_name' =>Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER),
                'label_name' => $basic_settings->site_name.' Wallet',
            ],

            [
                'id'    => 3,
                'field_name' => Str::slug(GlobalConst::TRX_CASH_PICKUP),
                'label_name' => "Cash Pickup",
            ]
        ];
        $transaction_type = (array) $transactionType;
        $remittanceCharge = TransactionSetting::where('slug','remittance')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
                'monthly_limit' => getAmount($data->monthly_limit,2),
                'daily_limit' => getAmount($data->daily_limit,2),
                'agent_fixed_commissions' => getAmount($data->agent_fixed_commissions,2),
                'agent_percent_commissions' => getAmount($data->agent_percent_commissions,2),
                'agent_profit' => $data->agent_profit,
            ];
        })->first();
        $fromCountry = Currency::default()->get()->map(function($data){
            return[
                'id' => $data->id,
                'country' => $data->country,
                'name' => $data->name,
                'code' => $data->code,
                'symbol' => $data->symbol,
                'flag' => $data->flag,
                'rate' => getAmount( $data->rate,4),
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        $toCountries = ReceiverCounty::active()->get()->map(function($data){
            return[
                'id' => $data->id,
                'country' => $data->country,
                'name' => $data->name,
                'code' => $data->code,
                'mobile_code' => $data->mobile_code,
                'symbol' => $data->symbol,
                'flag' => $data->flag,
                'rate' => getAmount( $data->rate,4),
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        $sender_recipients = AgentRecipient::auth()->sender()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => $data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];
            }else{
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => @$data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];

            }

        });
        $receiver_recipients = AgentRecipient::auth()->receiver()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => $data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];
            }else{
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => @$data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];

            }

        });
        $transactions = Transaction::agentAuth()->remitance()->latest()->take(5)->get()->map(function($item){
            $basic_settings = BasicSettings::first();
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                if( @$item->details->remitance_type == "wallet-to-wallet-transfer"){
                    $transactionType = @$basic_settings->site_name." Wallet";

                }else{
                    $transactionType = ucwords(str_replace('-', ' ', @$item->details->remitance_type));
                }
                if(@$item->attribute == payment_gateway_const()::SEND){
                    if(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>@$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => @$item->type,
                            'transaction_heading' => "Send Remitance to @" .@$item->details->receiver_recipient->email,
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'total_charge' => getAmount(@$item->charge->total_charge,2).' '.get_default_currency_code(),
                            'exchange_rate' => "1".' '. get_default_currency_code().' = '. get_amount(@$item->details->to_country->rate,@$item->details->to_country->code),
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'sender_recipient_name' => @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname,
                            'receiver_recipient_name' => @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) ,
                            'remittance_type_name' => $transactionType ,
                            'recipient_get' =>  get_amount(@$item->details->recipient_amount,@$item->details->to_country->code),
                            'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>@$item->reject_reason??"" ,
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_BANK_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>@$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => @$item->type,
                            'transaction_heading' => "Send Remitance to @" . @$item->details->receiver_recipient->email,
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'total_charge' => getAmount(@$item->charge->total_charge,2).' '.get_default_currency_code(),
                            'exchange_rate' => "1".' '. get_default_currency_code().' = '. get_amount(@$item->details->to_country->rate,@$item->details->to_country->code),
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'sender_recipient_name' => @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname,
                            'receiver_recipient_name' => @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_BANK_TRANSFER) ,
                            'remittance_type_name' => $transactionType ,
                            'recipient_get' =>  get_amount(@$item->details->recipient_amount,@$item->details->to_country->code),
                            'bank_name' => ucwords(str_replace('-', ' ', @$item->details->receiver_recipient->alias)),
                            'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>@$item->reject_reason??"",
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_CASH_PICKUP)){
                        return[
                            'id' => @$item->id,
                            'type' =>@$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => @$item->type,
                            'transaction_heading' => "Send Remitance to @" . @$item->details->receiver_recipient->email,
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'total_charge' => getAmount(@$item->charge->total_charge,2).' '.get_default_currency_code(),
                            'exchange_rate' => "1".' '. get_default_currency_code().' = '. get_amount(@$item->details->to_country->rate,@$item->details->to_country->code),
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'sender_recipient_name' => @$item->details->sender_recipient->firstname.' '.@$item->details->sender_recipient->lastname,
                            'receiver_recipient_name' => @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_CASH_PICKUP) ,
                            'remittance_type_name' => $transactionType ,
                            'receipient_get' =>  get_amount(@$item->details->recipient_amount,@$item->details->to_country->code),
                            'pickup_point' => ucwords(str_replace('-', ' ', @$item->details->receiver_recipient->alias)),
                            'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>@$item->reject_reason??"" ,
                        ];
                    }
                }

        });
        $data =[
            'fromCountryFlugPath'   => 'backend/images/currency-flag',
            'toCountryFlugPath'   => 'public/backend/images/country-flag',
            'default_image'    => "public/backend/images/default/default.webp",
            'agentWallet'   => (object)$userWallet,
            'transactionTypes'   => $transaction_type,
            'remittanceCharge'   => (object)$remittanceCharge,
            'fromCountry'   => $fromCountry,
            'toCountries'   => $toCountries,
            'sender_recipients'   => $sender_recipients,
            'receiver_recipients'   => $receiver_recipients,
            'transactions'   => $transactions,


        ];

        $message =  ['success'=>[__('Remittance Information')]];
        return Helpers::success($data,$message);
    }
    public function getRecipientSender(Request $request){
        $validator = Validator::make(request()->all(), [
            'transaction_type'     => "nullable|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $transaction_type = $request->transaction_type;
        if( $transaction_type != null || $transaction_type != ''){
            $recipient = AgentRecipient::auth()->sender()->where('type',$transaction_type)->get()->map(function($data){
                $basic_settings = BasicSettings::first();
                if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country,
                        'trx_type' => $data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => $basic_settings->site_name.' Wallet',
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];
                }else{
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country,
                        'trx_type' => @$data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];

                }
            });

        }
        $data =[
            'sender_recipient' => $recipient,
        ];
        $message =  ['success'=>[__('Successfully got sender recipient')]];
        return Helpers::success($data,$message);
    }
    public function getRecipientReceiver(Request $request){
        $validator = Validator::make(request()->all(), [
            'to_country'     => "required",
            'transaction_type'     => "nullable|string",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $receiver_country = $request->to_country;
        $transaction_type = $request->transaction_type;
        if( $transaction_type != null || $transaction_type != ''){
            $recipient = AgentRecipient::auth()->receiver()->where('country', $receiver_country)->where('type',$transaction_type)->get()->map(function($data){
                $basic_settings = BasicSettings::first();
                if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country,
                        'trx_type' => $data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => $basic_settings->site_name.' Wallet',
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];
                }else{
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country,
                        'trx_type' => @$data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];

                }
            });

        }else{
            $recipient = AgentRecipient::auth()->receiver()->where('country', $receiver_country)->get()->map(function($data){
                $basic_settings = BasicSettings::first();
                if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country,
                        'trx_type' => $data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => $basic_settings->site_name.' Wallet',
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];
                }else{
                    return[
                        'id' => $data->id,
                        'country' => $data->country,
                        'country_name' => $data->receiver_country->country,
                        'trx_type' => @$data->type,
                        'recipient_type' => $data->recipient_type,
                        'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                        'alias' => $data->alias,
                        'firstname' => $data->firstname,
                        'lastname' => $data->lastname,
                        'email' => $data->email,
                        'account_number' => $data->account_number??'',
                        'mobile_code' => $data->mobile_code,
                        'mobile' => $data->mobile,
                        'city' => $data->city,
                        'state' => $data->state,
                        'address' => $data->address,
                        'zip_code' => $data->zip_code,
                        'created_at' => $data->created_at,
                        'updated_at' => $data->updated_at,

                    ];

                }
            });
        }
        $data =[
            'sender_recipient' => $recipient,
        ];
        $message =  ['success'=>[__('Successfully got receiver recipient')]];
        return Helpers::success($data,$message);
    }
    //confirmed remittance
    public function confirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'form_country'               =>'required',
            'to_country'                 =>'required',
            'transaction_type'           =>'required|string',
            'sender_recipient'                  =>'required',
            'receiver_recipient'           =>'required',
            'send_amount'                =>"required|numeric",
            'receive_amount'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated =  $validator->validate();
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $user = authGuardApi()['user'];
        $transaction_type = $validated['transaction_type'];
        $basic_setting = BasicSettings::first();

        $userWallet = AgentWallet::where('agent_id',$user->id)->first();
        if(!$userWallet){
            $error = ['error'=>[__("Agent doesn't exists.")]];
            return Helpers::error($error);
        }
        $baseCurrency = Currency::default();
        if(!$baseCurrency){
            $error = ['error'=>[__('Default currency not found')]];
            return Helpers::error($error);
        }
        if($baseCurrency->id != $request->form_country){
            $error = ['error'=>[__('From country is not a valid country')]];
            return Helpers::error($error);

        }
        $form_country =  $baseCurrency->country;
        $to_country = ReceiverCounty::where('id',$request->to_country)->first();
        if(!$to_country){
            $error = ['error'=>[__('Receiver country not found')]];
            return Helpers::error($error);
        }
        $receipient = AgentRecipient::auth()->sender()->where("id",$request->sender_recipient)->first();
        if(!$receipient){
            $error = ['error'=>[__('Recipient is invalid/mismatch transaction type or country')]];
            return Helpers::error($error);
        }
        $receiver_recipient = AgentRecipient::auth()->receiver()->where("id",$request->receiver_recipient)->first();
        if(!$receiver_recipient){
            $error = ['error'=>[__('Receiver Recipient is invalid')]];
            return Helpers::error($error);
        }

        $charges = $this->chargeCalculate($userWallet->currency,$receiver_recipient->receiver_country, $validated['send_amount'],$exchangeCharge);
        $sender_currency_rate = $userWallet->currency->rate;
        $min_amount = $exchangeCharge->min_limit * $sender_currency_rate;
        $max_amount = $exchangeCharge->max_limit * $sender_currency_rate;

        if($charges->sender_amount < $min_amount || $charges->sender_amount > $max_amount) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }

        if($charges->payable > $userWallet->balance) {
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
         }
         $trx_id = $this->trx_id;
         $notifyData = [
            'trx_id'  => $trx_id??'ndf',
            'title'  => __("Send Remittance to")." @" . $receiver_recipient->fullname." (".@$receipient->email.")",
            'request_amount'  => getAmount($charges->sender_amount,4).' '.$charges->sender_cur_code,
            'exchange_rate'  => "1 " .get_default_currency_code().' = '.get_amount($charges->exchange_rate,$charges->receiver_cur_code,4),
            'charges'   => getAmount( $charges->total_charge,4).' ' .$charges->sender_cur_code,
            'payable'   =>  getAmount($charges->payable,4).' ' .$charges->sender_cur_code,
            'sending_country'   => @$form_country,
            'receiving_country'   => @$to_country->country,
            'sender_recipient_name'  =>  @$receipient->fullname,
            'receiver_recipient_name'  =>  @$receiver_recipient->fullname,
            'alias'  =>  ucwords(str_replace('-', ' ', @$receipient->alias)),
            'transaction_type'  =>  @$transaction_type,
            'receiver_get'   =>  getAmount($charges->will_get,4).' ' .$charges->receiver_cur_code,
            'status'  => __("Pending"),
          ];
        try{
            if($transaction_type === Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $receiver_user =  json_decode($receiver_recipient->details);
                $receiver_user_info =  $receiver_user;
                $receiver_user =  $receiver_user->id;
                $receiver_wallet = UserWallet::where('user_id',$receiver_user)->first();
                if(!$receiver_wallet){
                    $error = ['error'=>[__('Sorry, Receiver wallet not found')]];
                    return Helpers::error($error);
                }

                $sender = $this->insertSender( $trx_id,$userWallet,$receipient,$form_country,$to_country,$transaction_type, $receiver_recipient,$charges);
                if($sender){
                     $this->insertSenderCharges( $sender,$charges,$user,$receiver_recipient);
                     try{
                        if( $basic_setting->agent_email_notification == true){
                            $user->notify(new SenderEmail($user,(object)$notifyData));
                        }
                     }catch(Exception $e){

                     }
                }
                $receiverTrans = $this->insertReceiver($trx_id,$userWallet,$receipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient,$charges);
                if($receiverTrans){
                     $this->insertReceiverCharges( $receiverTrans,$charges,$user,$receipient,$receiver_recipient,$receiver_user_info);
                }

            }else{
                $trx_id = $this->trx_id;
                $sender = $this->insertSender($trx_id,$userWallet,$receipient,$form_country,$to_country,$transaction_type, $receiver_recipient,$charges);
                if($sender){
                     $this->insertSenderCharges($sender,$charges,$user,$receiver_recipient);
                     try{
                         if( $basic_setting->agent_email_notification == true){
                             $user->notify(new SenderEmail($user,(object)$notifyData));
                         }
                     }catch(Exception $e){

                     }

                }
            }
            if($transaction_type != Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->adminNotification($trx_id,$charges,$user,$receipient,$receiver_recipient,$to_country,$form_country,$transaction_type);
            }
            $message =  ['success'=>[__('Remittance Money send successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {

            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }

    }
    //sender transaction
    public function insertSender($trx_id,$userWallet,$receipient,$form_country,$to_country,$transaction_type, $receiver_recipient,$charges) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;

        if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
            $status = 1;
            $afterCharge = ($authWallet->balance - $charges->payable) + $charges->agent_total_commission;

        }else{
            $status = 2;
            $afterCharge = ($authWallet->balance - $charges->payable);
        }

        $details =[
            'recipient_amount' => $charges->will_get,
            'sender_recipient' => $receipient,
            'receiver_recipient' => $receiver_recipient,
            'form_country' => $form_country,
            'to_country' => $to_country,
            'remitance_type' => $transaction_type,
            'sender' => $userWallet->agent,
            'charges' => $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $userWallet->agent->id,
                'agent_wallet_id'               => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges->sender_amount,
                'payable'                       => $charges->payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::SENDREMITTANCE," ")) . " To " .$receiver_recipient->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => $status,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);
            if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->agentProfitInsert($id,$authWallet,$charges);
            }

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id,$charges,$user,$receiver_recipient) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      =>$charges->fixed_charge,
                'total_charge'      =>$charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();


            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => "Send Remittance Request to ".$receiver_recipient->fullname.' ' .$charges->sender_amount.' '.$charges->sender_cur_code." successful",
                'image'         =>  get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'agent_id'  => $user->id,
                'message'   => $notification_content,
            ]);
             //Push Notifications
             (new PushNotificationHelper())->prepareApi([$user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'agent',
            ])->send();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$userWallet,$receipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient,$charges) {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges->will_get);
        $details =[
            'recipient_amount' => $charges->will_get,
            'receiver' => $receiver_recipient,
            'form_country' => $form_country,
            'to_country' => $to_country,
            'remitance_type' => $transaction_type,
            'sender' => $userWallet->agent,
            'charges' => $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver_user,
                'user_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges->sender_amount,
                'payable'                       => $charges->payable,
                'available_balance'             => $recipient_amount,
                'remark'                        =>  ucwords(remove_speacial_char(PaymentGatewayConst::RECEIVEREMITTANCE," ")) . " From " . $userWallet->agent->username,
                'details'                       => json_encode($details),
                'attribute'                      => PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {

        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges( $id,$charges,$user,$receipient,$receiver_recipient,$receiver_user_info) {

        DB::beginTransaction();

        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges->percent_charge,
                'fixed_charge'      =>$charges->fixed_charge,
                'total_charge'      =>$charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Send Remittance"),
                'message'       => "Send Remittance from ".$user->fullname.' ' .$charges->will_get.' '.$charges->receiver_cur_code." successful",
                'image'         =>  get_image($receiver_user_info->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::SEND_REMITTANCE,
                'user_id'  => $receiver_user_info->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            (new PushNotificationHelper())->prepareApi([$receiver_user_info->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();
        }catch(Exception $e) {

            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    //end transaction helpers

    public function agentProfitInsert($id,$authWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges->agent_percent_commission,
                'fixed_charge'      => $charges->agent_fixed_commission,
                'total_charge'      => $charges->agent_total_commission,
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
    }
    public function chargeCalculate($sender_currency,$receiver_currency,$amount,$exchangeCharge) {
        $amount = $amount;
        $sender_currency_rate = $sender_currency->rate;
        ($sender_currency_rate == "" || $sender_currency_rate == null) ? $sender_currency_rate = 0 : $sender_currency_rate;
        ($amount == "" || $amount == null) ? $amount : $amount;

        if($sender_currency != null) {
            $fixed_charges = $exchangeCharge->fixed_charge;
            $percent_charges = $exchangeCharge->percent_charge;
        }else {
            $fixed_charges = 0;
            $percent_charges = 0;
        }

        $fixed_charge_calc =  $fixed_charges * $sender_currency_rate;
        $percent_charge_calc = ($amount / 100 ) * $percent_charges;
        $total_charge = $fixed_charge_calc + $percent_charge_calc;

        $receiver_currency_rate = $receiver_currency->rate;

        ($receiver_currency_rate == "" || $receiver_currency_rate == null) ? $receiver_currency_rate = 0 : $receiver_currency_rate;
        $exchange_rate = ($receiver_currency_rate / $sender_currency_rate );
        $conversion_amount =  $amount * $exchange_rate;
        $will_get = $conversion_amount;
        $payable =  $amount + $total_charge;

        $agent_percent_commission  = ($amount / 100) * $exchangeCharge->agent_percent_commissions ?? 0;
        $agent_fixed_commission    = $sender_currency_rate * $exchangeCharge->agent_fixed_commissions ?? 0;


        $data = [
            'sender_amount'               => $amount,
            'sender_cur_code'           => $sender_currency->code,
            'sender_cur_rate'           => $sender_currency_rate ?? 0,
            'receiver_cur_code'         => $receiver_currency->code,
            'receiver_cur_rate'         => $receiver_currency->rate ?? 0,
            'fixed_charge'              => $fixed_charge_calc,
            'percent_charge'            => $percent_charge_calc,
            'total_charge'              => $total_charge,
            'conversion_amount'         => $conversion_amount,
            'payable'                   => $payable,
            'exchange_rate'             => $exchange_rate,
            'will_get'                  => $will_get,
            'agent_percent_commission'  => $agent_percent_commission,
            'agent_fixed_commission'    => $agent_fixed_commission,
            'agent_total_commission'    => $agent_percent_commission + $agent_fixed_commission,
            'default_currency'          => get_default_currency_code(),
        ];

        return (object) $data;
    }
    //admin notification
    public function adminNotification($trx_id,$charges,$user,$receipient,$receiver_recipient,$to_country,$form_country,$transaction_type){
        $exchange_rate = "1 " .get_default_currency_code().' = '.get_amount($to_country->rate,$to_country->code);
        if($transaction_type == 'bank-transfer'){
            $input_field = "bank Name";
        }else{
            $input_field = "Pickup Point";
        }
        $notification_content = [
            //email notification
            'subject' =>__("Send Remittance to")." @" . $receiver_recipient->firstname.' '.@$receiver_recipient->lastname." (".@$receiver_recipient->email.")",
            'greeting' =>__("Send Remittance Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$receipient->email."<br>".__("Receiver").": @".$receiver_recipient->email."<br>".__("Sender Amount")." : ".get_amount($charges->sender_amount,get_default_currency_code())."<br>".__("Exchange Rate")." : ".$exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($charges->total_charge,get_default_currency_code())."<br>".__("Total Payable Amount")." : ".get_amount($charges->payable,get_default_currency_code())."<br>".__("Recipient Received")." : ".get_amount($charges->will_get,$to_country->code)."<br>".__("Transaction Type")." : ".ucwords(str_replace('-', ' ', @$transaction_type))."<br>".__("sending Country")." : ".$form_country."<br>".__("Receiving Country")." : ".$to_country->country."<br>".__($input_field)." : ".ucwords(str_replace('-', ' ', @$receipient->alias)),

            //push notification
            'push_title' => __("Send Remittance to")." @". $receiver_recipient->firstname.' '.@$receiver_recipient->lastname." (".@$receiver_recipient->email.")"." ".__('Successful'),
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$receipient->email." ".__("Receiver").": @".$receiver_recipient->email." ".__("Sender Amount")." : ".get_amount($charges->sender_amount,get_default_currency_code())." ".__("Receiver Amount")." : ".get_amount($charges->will_get,$to_country->code),

            //admin db notification
            'notification_type' =>  NotificationConst::SEND_REMITTANCE,
            'admin_db_title' => "Send Remittance"." ".get_amount($charges->sender_amount,get_default_currency_code())." (".$trx_id.")",
            'admin_db_message' =>"Sender".": @".$receipient->email.","."Receiver".": @".$receiver_recipient->email.","."Sender Amount"." : ".get_amount($charges->sender_amount,get_default_currency_code()).","."Receiver Amount"." : ".get_amount($charges->will_get,$to_country->code)
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.remitance.index','admin.remitance.pending','admin.remitance.complete','admin.remitance.canceled','admin.remitance.details','admin.remitance.approved','admin.remitance.rejected','admin.remitance.export.data'])
                                    ->mail(ActivityNotification::class, [
                                            'subject'   => $notification_content['subject'],
                                            'greeting'  => $notification_content['greeting'],
                                            'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                            'user_type' => "admin",
                                            'title' => $notification_content['push_title'],
                                            'desc'  => $notification_content['push_content'],
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
}
