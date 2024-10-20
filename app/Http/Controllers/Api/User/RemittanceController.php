<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Receipient;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\Currency;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Models\Admin\ReceiverCounty;
use App\Models\Admin\TransactionSetting;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\Remittance\BankTransferMail;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => get_default_currency_code(),
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
                'rate' => getAmount( $data->rate,2),
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
                'rate' => getAmount( $data->rate,2),
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        $recipients = Receipient::auth()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => $data->type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'email'  => $data->email,
                    'account_number' => $data->account_number??'',
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
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'email'  => $data->email,
                    'account_number' => $data->account_number??'',
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];

            }

        });
        $transactions = Transaction::auth()->remitance()->latest()->take(5)->get()->map(function($item){
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
                if($item->attribute == payment_gateway_const()::SEND){
                    if(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'transaction_heading' => "Send Remitance to @" . $item->details->receiver->firstname.' '.@$item->details->receiver->lastname." (".@$item->details->receiver->email.")",
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'total_charge' => getAmount(@$item->charge->total_charge,2).' '.get_default_currency_code(),
                            'exchange_rate' => "1".' '. get_default_currency_code().' = '. get_amount($item->details->to_country->rate,$item->details->to_country->code),
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'receipient_name' => @$item->details->receiver->firstname.' '.@$item->details->receiver->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) ,
                            'remittance_type_name' => $transactionType ,
                            'receipient_get' =>  get_amount(@$item->details->recipient_amount,$item->details->to_country->code),
                            'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>$item->reject_reason??"" ,
                            'account_number' => @$item->details->bank_account??""

                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_BANK_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'transaction_heading' => "Send Remitance to @" . $item->details->receiver->firstname.' '.@$item->details->receiver->lastname." (".@$item->details->receiver->email.")",
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'total_charge' => getAmount(@$item->charge->total_charge,2).' '.get_default_currency_code(),
                            'exchange_rate' => "1".' '. get_default_currency_code().' = '. get_amount($item->details->to_country->rate,$item->details->to_country->code),
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'receipient_name' => @$item->details->receiver->firstname.' '.@$item->details->receiver->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_BANK_TRANSFER) ,
                            'remittance_type_name' => $transactionType ,
                            'receipient_get' =>  get_amount(@$item->details->recipient_amount,$item->details->to_country->code),
                            'bank_name' => ucwords(str_replace('-', ' ', @$item->details->receiver->alias)),
                            'account_number' => @$item->details->bank_account??"",
                            'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>$item->reject_reason??"",
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_CASH_PICKUP)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'transaction_heading' => "Send Remitance to @" . $item->details->receiver->firstname.' '.@$item->details->receiver->lastname." (".@$item->details->receiver->email.")",
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'total_charge' => getAmount(@$item->charge->total_charge,2).' '.get_default_currency_code(),
                            'exchange_rate' => "1".' '. get_default_currency_code().' = '. get_amount($item->details->to_country->rate,$item->details->to_country->code),
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'sending_country' => @$item->details->form_country,
                            'receiving_country' => @$item->details->to_country->country,
                            'receipient_name' => @$item->details->receiver->firstname.' '.@$item->details->receiver->lastname,
                            'remittance_type' => Str::slug(GlobalConst::TRX_CASH_PICKUP) ,
                            'remittance_type_name' => $transactionType ,
                            'receipient_get' =>  get_amount(@$item->details->recipient_amount,$item->details->to_country->code),
                            'pickup_point' => ucwords(str_replace('-', ' ', @$item->details->receiver->alias)),
                            'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'date_time' => @$item->created_at ,
                            'status_info' =>(object)@$statusInfo ,
                            'rejection_reason' =>$item->reject_reason??"" ,
                            'account_number' => @$item->details->bank_account??''
                        ];
                    }

                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => "Received Remitance from @" .@$item->details->sender->fullname." (".@$item->details->sender->email.")",
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'sending_country' => @$item->details->form_country,
                        'receiving_country' => @$item->details->to_country->country,
                        'remittance_type' => Str::slug(GlobalConst::TRX_CASH_PICKUP) ,
                        'remittance_type_name' => $transactionType ,
                        'current_balance' => getAmount(@$item->available_balance,2).' '.get_default_currency_code(),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                        'rejection_reason' =>$item->reject_reason??"" ,
                    ];

                }

        });
        $data =[
            'fromCountryFlugPath'   => 'backend/images/currency-flag',
            'toCountryFlugPath'   => 'public/backend/images/country-flag',
            'default_image'    => "public/backend/images/default/default.webp",
            'userWallet'   => (object)$userWallet,
            'transactionTypes'   => $transaction_type,
            'remittanceCharge'   => (object)$remittanceCharge,
            'fromCountry'   => $fromCountry,
            'toCountries'   => $toCountries,
            'recipients'   => $recipients,
            'transactions'   => $transactions,


        ];

        $message =  ['success'=>[__('Remittance Information')]];
        return Helpers::success($data,$message);
    }
    public function getRecipient(Request $request){
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
            $recipient = Receipient::auth()->where('country', $receiver_country)->where('type',$transaction_type)->get();

        }else{
            $recipient = Receipient::auth()->where('country', $receiver_country)->get();
        }
        $data =[
            'recipient' => $recipient,
        ];
        $message =  ['success'=>[__('Successfully got recipient')]];
        return Helpers::success($data,$message);
    }
    public function confirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'form_country'               =>'required',
            'to_country'                 =>'required',
            'transaction_type'           =>'required|string',
            'recipient'                  =>'required',
            'send_amount'                =>"required|numeric",
            'receive_amount'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $userWallet = UserWallet::where('user_id',$user->id)->first();
        if(!$userWallet){;
            $error = ['error'=>[__('User wallet not found')]];
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
        $to_country = ReceiverCounty::where('id',$request->to_country)->first();
        if(!$to_country){
            $error = ['error'=>[__('Receiver country not found')]];
            return Helpers::error($error);
        }
        $receipient = Receipient::auth()->where("id",$request->recipient)->where('country',$request->to_country)->where('type',$request->transaction_type)->first();
        if(!$receipient){
            $error = ['error'=>[__('Recipient is invalid')]];
            return Helpers::error($error);
        }


        $base_rate = $baseCurrency->rate;
        $receiver_rate =$to_country->rate;
        $form_country =  $baseCurrency->country;
        $send_amount = $request->send_amount;
        $receive_amount = $request->receive_amount;
        $transaction_type = $request->transaction_type;
        $minLimit =  $exchangeCharge->min_limit *  $base_rate;
        $maxLimit =  $exchangeCharge->max_limit *  $base_rate;
        if($send_amount < $minLimit || $send_amount > $maxLimit) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);

        }
        //charge calculations
        $fixedCharge = $exchangeCharge->fixed_charge *  $base_rate;
        $percent_charge = ($send_amount / 100) * $exchangeCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $send_amount;
        //receiver amount
        $receiver_rate = (float) $receiver_rate / (float)$base_rate;
        $receiver_amount = $receiver_rate * $send_amount;
        $receiver_will_get = $receiver_amount;
        if($payable > $userWallet->balance ){;
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }
        try{
            if($transaction_type === Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $receiver_user =  json_decode($receipient->details);
                $receiver_user =  $receiver_user->id;
                $receiver_wallet = UserWallet::where('user_id',$receiver_user)->first();
                if(!$receiver_wallet){
                    $error = ['error'=>[__('Receiver wallet not found')]];
                    return Helpers::error($error);
                }
                $trx_id = $this->trx_id;
                $sender = $this->insertSender( $trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type);
                if($sender){
                     $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$sender,$receipient,$receiver_user);
                }
                $receiverTrans = $this->insertReceiver( $trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet);
                if($receiverTrans){
                     $this->insertReceiverCharges(  $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$receiverTrans,$receipient,$receiver_user);
                }
            }else{
                $trx_id = $this->trx_id;
                $notifyData = [
                    'trx_id'  => $trx_id,
                    'title'  => __("Send Remittance to")." @" . $receipient->firstname.' '.@$receipient->lastname." (".@$receipient->mobile_code.@$receipient->mobile.")",
                    'request_amount'  => getAmount($send_amount,4).' '.get_default_currency_code(),
                    'exchange_rate'  => "1 " .get_default_currency_code().' = '.get_amount($to_country->rate,$to_country->code),
                    'charges'   => getAmount( $total_charge, 2).' ' .get_default_currency_code(),
                    'payable'   =>  getAmount($payable,4).' ' .get_default_currency_code(),
                    'sending_country'   => @$form_country,
                    'receiving_country'   => @$to_country->country,
                    'receiver_name'  =>  @$receipient->firstname.' '.@$receipient->lastname,
                    'alias'  =>  ucwords(str_replace('-', ' ', @$receipient->alias)),
                    'transaction_type'  =>  @$transaction_type,
                    'receiver_get'   =>  getAmount($receiver_will_get,4).' ' .$to_country->code,
                    'status'  => __("Pending"),
                  ];

                $sender = $this->insertSender( $trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type);
                if($sender){
                     $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$sender,$receipient);
                }
                //sender notifications
                try{
                    if( $basic_setting->email_notification == true){
                        $user->notify(new BankTransferMail($user,(object)$notifyData));
                    }
                }catch(Exception $e){}
            }
            if($transaction_type != Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $this->adminNotification($trx_id,$total_charge,$send_amount,$payable,$receiver_will_get,$user,$receipient,$to_country,$form_country,$transaction_type);
            }
            $message =  ['success'=>[__('Remittance Money send successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
     //start transaction helpers
        //serder transaction
        public function insertSender($trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type) {
            $trx_id = $trx_id;
            $authWallet = $userWallet;
            $afterCharge = ($authWallet->balance - $payable);
            $details =[
                'recipient_amount' => $receiver_will_get,
                'receiver' => $receipient,
                'form_country' => $form_country,
                'to_country' => $to_country,
                'remitance_type' => $transaction_type,
                 'sender' => $user,
                 'bank_account' => $receipient->account_number??'',
            ];
            if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $status = 1;

            }else{
                $status = 2;
            }
            DB::beginTransaction();
            try{
                $id = DB::table("transactions")->insertGetId([
                    'user_id'                       => $user->id,
                    'user_wallet_id'                => $authWallet->id,
                    'payment_gateway_currency_id'   => null,
                    'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                    'trx_id'                        => $trx_id,
                    'request_amount'                => $send_amount,
                    'payable'                       => $payable,
                    'available_balance'             => $afterCharge,
                    'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::SENDREMITTANCE," ")) . " To " .$receipient->fullname,
                    'details'                       => json_encode($details),
                    'attribute'                      =>PaymentGatewayConst::SEND,
                    'status'                        => $status,
                    'created_at'                    => now(),
                ]);
                $this->updateSenderWalletBalance($authWallet,$afterCharge);

                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                $error = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }
            return $id;
        }
        public function updateSenderWalletBalance($authWalle,$afterCharge) {
            $authWalle->update([
                'balance'   => $afterCharge,
            ]);
        }
        public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$sender,$receipient) {
            DB::beginTransaction();
            try{
                DB::table('transaction_charges')->insert([
                    'transaction_id'    => $sender,
                    'percent_charge'    => $percent_charge,
                    'fixed_charge'      =>$fixedCharge,
                    'total_charge'      =>$total_charge,
                    'created_at'        => now(),
                ]);
                DB::commit();

                //notification
                $notification_content = [
                    'title'         =>__("Send Remittance"),
                    'message'       => __("Send Remittance Request to")." ".$receipient->fullname.' ' .$send_amount.' '.get_default_currency_code()." ".__("Successful"),
                    'image'         => files_asset_path('profile-default'),
                ];

                UserNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'user_id'  => $user->id,
                    'message'   => $notification_content,
                ]);
                 //Push Notifications
                (new PushNotificationHelper())->prepareApi([$user->id],[
                    'title' => $notification_content['title'],
                    'desc'  => $notification_content['message'],
                    'user_type' => 'user',
                ])->send();
            }catch(Exception $e) {
                DB::rollBack();
                $error = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }
        }
        //Receiver Transaction
        public function insertReceiver($trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet) {

            $trx_id = $trx_id;
            $receiverWallet = $receiver_wallet;
            $recipient_amount = ($receiverWallet->balance + $receiver_will_get);
            $details =[
                'recipient_amount' => $receiver_will_get,
                'receiver' => $receipient,
                'form_country' => $form_country,
                'to_country' => $to_country,
                'remitance_type' => $transaction_type,
                'sender' => $user,
                'bank_account' => $receipient->account_number??'',
            ];
            DB::beginTransaction();
            try{
                $id = DB::table("transactions")->insertGetId([
                    'user_id'                       => $receiver_user,
                    'user_wallet_id'                => $receiverWallet->id,
                    'payment_gateway_currency_id'   => null,
                    'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                    'trx_id'                        => $trx_id,
                    'request_amount'                => $send_amount,
                    'payable'                       => $payable,
                    'available_balance'             => $recipient_amount,
                    'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::RECEIVEREMITTANCE," ")) . " From " .$user->fullname,
                    'details'                       => json_encode($details),
                    'attribute'                      =>PaymentGatewayConst::RECEIVED,
                    'status'                        => true,
                    'created_at'                    => now(),
                ]);
                $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                $error = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }
            return $id;
        }
        public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {
            $receiverWallet->update([
                'balance'   => $recipient_amount,
            ]);
        }
        public function insertReceiverCharges( $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$receiverTrans,$receipient,$receiver_user) {
            DB::beginTransaction();
            try{
                DB::table('transaction_charges')->insert([
                    'transaction_id'    => $receiverTrans,
                    'percent_charge'    => $percent_charge,
                    'fixed_charge'      =>$fixedCharge,
                    'total_charge'      =>$total_charge,
                    'created_at'        => now(),
                ]);
                DB::commit();

                //notification
                $notification_content = [
                    'title'         =>__("Send Remittance"),
                    'message'       => __("Send Remittance From")." ".$user->fullname.' ' .$send_amount.' '.get_default_currency_code()." ".__('Successful'),
                    'image'         => files_asset_path('profile-default'),
                ];

                UserNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'user_id'  => $receiver_user,
                    'message'   => $notification_content,
                ]);
                 //Push Notifications
                (new PushNotificationHelper())->prepareApi([$receiver_user->id],[
                    'title' => $notification_content['title'],
                    'desc'  => $notification_content['message'],
                    'user_type' => 'user',
                ])->send();

            }catch(Exception $e) {
                DB::rollBack();
                $error = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }
        }
    //end transaction helpers
    //admin notification
    public function adminNotification($trx_id,$total_charge,$send_amount,$payable,$receiver_will_get,$user,$receipient,$to_country,$form_country,$transaction_type){
        $exchange_rate = "1 " .get_default_currency_code().' = '.get_amount($to_country->rate,$to_country->code);
        if($transaction_type == 'bank-transfer'){
            $input_field = "bank Name";
        }else{
            $input_field = "Pickup Point";
        }
        $notification_content = [
            //email notification
            'subject' =>__("Send Remittance to")." @" . $receipient->firstname.' '.@$receipient->lastname." (".@$receipient->email.")",
            'greeting' =>__("Send Remittance Information"),
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("sender").": @".$user->email."<br>".__("Receiver").": @".$receipient->email."<br>".__("Sender Amount")." : ".get_amount($send_amount,get_default_currency_code())."<br>".__("Exchange Rate")." : ".$exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($total_charge,get_default_currency_code())."<br>".__("Total Payable Amount")." : ".get_amount($payable,get_default_currency_code())."<br>".__("Recipient Received")." : ".get_amount($receiver_will_get,$to_country->code)."<br>".__("Transaction Type")." : ".ucwords(str_replace('-', ' ', @$transaction_type))."<br>".__("sending Country")." : ".$form_country."<br>".__("Receiving Country")." : ".$to_country->country."<br>".__($input_field)." : ".ucwords(str_replace('-', ' ', @$receipient->alias)),

            //push notification
            'push_title' => __("Send Remittance to")." @". $receipient->firstname.' '.@$receipient->lastname." (".@$receipient->email.")"." ".__('Successful'),
            'push_content' => __('web_trx_id')." ".$trx_id." ".__("sender").": @".$user->email." ".__("Receiver").": @".$receipient->email." ".__("Sender Amount")." : ".get_amount($send_amount,get_default_currency_code())." ".__("Receiver Amount")." : ".get_amount($receiver_will_get,$to_country->code),

            //admin db notification
            'notification_type' =>  NotificationConst::SEND_REMITTANCE,
            'admin_db_title' => "Send Remittance"." ".get_amount($send_amount,get_default_currency_code())." (".$trx_id.")",
            'admin_db_message' =>"Sender".": @".$user->email.","."Receiver".": @".$receipient->email.","."Sender Amount"." : ".get_amount($send_amount,get_default_currency_code()).","."Receiver Amount"." : ".get_amount($receiver_will_get,$to_country->code)
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
