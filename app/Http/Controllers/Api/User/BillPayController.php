<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\UtilityHelper;
use App\Jobs\ProcessBillPayment;
use App\Models\Admin\Currency;
use App\Models\Admin\ExchangeRate;
use App\Models\Admin\TransactionSetting;
use App\Models\BillPayCategory;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\BillPay\BillPayMail;
use App\Notifications\User\BillPay\BillPayMailAutomatic;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\PushNotificationHelper;
use Illuminate\Support\Facades\Validator;

class BillPayController extends Controller
{
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function billPayInfo(){
        $user = auth()->user();
        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => get_default_currency_code(),
            ];
        })->first();
        $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->get()->map(function($data){
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
        $billType = BillPayCategory::active()->orderByDesc('id')->get();
        try{
            $billers =  (new UtilityHelper())->getBillers([
                'size' => 500,
                'page' =>0
            ], false);
        }catch(Exception $e){

        }
        $contentArray = $billers["content"]??[];
        $billTypeArray = $billType->toArray();
        foreach ($billTypeArray as &$item) {
            $item['item_type'] = 'MANUAL';
            $item['receiver_currency_rate'] = getAmount(get_default_currency_rate(),2);
            $item['receiver_currency_code'] = get_default_currency_code();
        }
        foreach ($contentArray as &$item) {
            $item['item_type'] = 'AUTOMATIC';
            $item['receiver_currency_rate'] = getAmount(receiver_currency($item['localTransactionCurrencyCode'])['rate'],2);
            $item['receiver_currency_code'] = receiver_currency($item['localTransactionCurrencyCode'])['currency'];
        }
        $billTypeCollection = collect($billTypeArray);
        $mergedCollection = collect($contentArray)->merge($billTypeCollection);
        $billType = $mergedCollection;

        $transactions = Transaction::auth()->billPay()->latest()->take(5)->get()->map(function($item){
            $statusInfo = [
                "success"       => 1,
                "pending"       => 2,
                "hold"          => 3,
                "rejected"      => 4,
                "waiting"       => 5,
                "failed"        => 6,
                "processing"    => 7,
                ];
            return[
                'id' => $item->id,
                'trx' => $item->trx_id,
                'transaction_type' => $item->type,
                'request_amount' => getAmount($item->request_amount,2).' '.billPayCurrency($item)['sender_currency'],
                'payable' => getAmount($item->payable,2).' '.billPayCurrency($item)['wallet_currency'],
                'bill_type' =>$item->details->bill_type_name,
                'bill_month' =>$item->details->bill_month??"",
                'bill_number' =>$item->details->bill_number,
                'total_charge' => getAmount($item->charge->total_charge,2).' '.billPayCurrency($item)['wallet_currency'],
                'current_balance' => getAmount($item->available_balance,2).' '.billPayCurrency($item)['wallet_currency'],
                'status' => $item->stringStatus->value,
                'date_time' => $item->created_at,
                'status_info' =>(object)$statusInfo,
                'status_value' => $item->status,
                'rejection_reason' =>$item->reject_reason??"",

            ];
        });
        $bill_months =[
            [
                'id'            => 1,
                'field_name'    => "January ".date("Y"),
                'value'         => "January ".date("Y"),
            ],
            [
                'id'            => 2,
                'field_name'    => "February ".date("Y"),
                'value'         => "February ".date("Y"),
            ],
            [
                'id'            => 3,
                'field_name'    => "March ".date("Y"),
                'value'         => "March ".date("Y"),
            ],
            [
                'id'            => 4,
                'field_name'    => "April ".date("Y"),
                'value'         => "April ".date("Y"),
            ],
            [
                'id'            => 5,
                'field_name'    => "May ".date("Y"),
                'value'         => "May ".date("Y"),
            ],
            [
                'id'            => 6,
                'field_name'    => "June ".date("Y"),
                'value'         => "June ".date("Y"),
            ],
            [
                'id'            => 7,
                'field_name'    => "July ".date("Y"),
                'value'         => "July ".date("Y"),
            ],
            [
                'id'            => 8,
                'field_name'    => "August ".date("Y"),
                'value'         => "August ".date("Y"),
            ],
            [
                'id'            => 9,
                'field_name'    => "September ".date("Y"),
                'value'         => "September ".date("Y"),
            ],
            [
                'id'            => 10,
                'field_name'    => "October ".date("Y"),
                'value'         => "October ".date("Y"),
            ],
            [
                'id'            => 11,
                'field_name'    => "November ".date("Y"),
                'value'         => "November ".date("Y"),
            ],
            [
                'id'            => 12,
                'field_name'    => "December ".date("Y"),
                'value'         => "December ".date("Y"),
            ]

        ];
        $data =[
            'base_curr'         => get_default_currency_code(),
            'base_curr_rate'    => get_default_currency_rate(),
            'billPayCharge'     => (object)$billPayCharge,
            'userWallet'        => (object)$userWallet,
            'billTypes'         => $billType,
            'bill_months'       => $bill_months,
            'transactions'      => $transactions,
        ];
        $message =  ['success'=>[__('Bill Pay Information')]];
        return Helpers::success($data,$message);
    }
    public function billPayConfirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'biller_item_type' => 'required|string',
            'bill_type' => 'required|string',
            'bill_month' => 'required|string',
            'bill_number' => 'required|min:8',
            'amount' => 'required|numeric|gt:0',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        if($request->biller_item_type === "AUTOMATIC"){
            return $this->automaticBillPay($request->all());
        }
        $user = auth()->user();
        $amount = $request->amount;
        $billType = $request->bill_type;
        $bill_type = BillPayCategory::where('id', $billType)->first();
        if(!$bill_type){
            $error = ['error'=>[__('Invalid bill type')]];
            return Helpers::error($error);
        }
        $bill_number = $request->bill_number;
        $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();
        if(!$billPayCharge ){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $userWallet = UserWallet::where('user_id',$user->id)->first();
        if(!$userWallet){
            $error = ['error'=>[__('User wallet not found!')]];
            return Helpers::error($error);
        }
        $baseCurrency = Currency::default();
        if(!$baseCurrency){
            $error = ['error'=>[__('Default currency not found')]];
            return Helpers::error($error);
        }
        $charges = $this->manualBillPayCharge($amount,$billPayCharge,$userWallet);

        $sender_currency_rate = $userWallet->currency->rate;
        $min_amount =  $billPayCharge->min_limit *  $sender_currency_rate;
        $max_amount =  $billPayCharge->max_limit *  $sender_currency_rate;
        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        if($charges['payable'] > $userWallet->balance) {
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }
        try{
            $trx_id = 'BP'.getTrxNum();
            $notifyData = [
                'trx_id'  => $trx_id,
                'bill_type'  => @$bill_type->name,
                'bill_number'  => $bill_number,
                'request_amount'   => $amount,
                'charges'   => $charges['total_charge'],
                'payable'  => $charges['payable'],
                'current_balance'  => getAmount($userWallet->balance, 4),
                'status'  => __("Pending"),
              ];
            $sender = $this->insertSender($trx_id,$user,$userWallet,$amount,$bill_type,$bill_number,$charges,$request->biller_item_type,$request->bill_month);
            $this->insertSenderCharges($charges,$amount,$user,$sender);
            //send notifications
            try{
                if( $this->basic_settings->email_notification == true){
                    $user->notify(new BillPayMail($user,(object)$notifyData));
                }
            }catch(Exception $e){}
            //admin notification
            $this->adminNotificationManual($trx_id,$charges,$bill_type,$user,$request->all());
            $message =  ['success'=>[__('Bill pay request sent to admin successful')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function insertSender($trx_id,$user,$userWallet,$amount,$bill_type, $bill_number,$charges,$biller_item_type,$bill_month) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);
        $details =[
            'bill_type_id'      => $bill_type->id??'',
            'bill_type_name'    => $bill_type->name??'',
            'bill_number'       => $bill_number,
            'sender_amount'     => $amount??"",
            'bill_month'        => $bill_month??'',
            'bill_type'         => $biller_item_type??'',
            'biller_info'       => [],
            'api_response'      => [],
            'charges'           => $charges??[],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::BILLPAY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::BILLPAY," ")) . " Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => 2,
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
    public function insertSenderCharges($charges,$amount,$user,$id) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges['percent_charge'],
                'fixed_charge'      => $charges['fixed_charge'],
                'total_charge'      => $charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Bill Pay"),
                'message'       => __("Bill pay request send to admin")." " .$amount.' '.get_default_currency_code()." ".__("Successful"),
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::BILL_PAY,
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
    public function manualBillPayCharge($sender_amount,$charges,$userWallet){
        $exchange_rate = 1;

        $data['exchange_rate' ]             = $exchange_rate;
        $data['sender_amount']              = $sender_amount;
        $data['sender_currency']            = $userWallet->currency->code;
        $data['sender_currency_rate']       = $userWallet->currency->rate;
        $data['wallet_currency']            = $userWallet->currency->code;
        $data['wallet_currency_rate']       = $userWallet->currency->rate;
        $data['percent_charge']             = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']               = $userWallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']               = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']      = $userWallet->balance;
        $data['conversion_amount']          = $sender_amount * $exchange_rate;
        $data['payable']                    = $data['conversion_amount'] +  $data['total_charge'];

        return $data;
    }

    //start automatic bill pay
    public function automaticBillPay($request_data){
        $user = auth()->user();
        try{
        $biller = (new UtilityHelper())->getSingleBiller($request_data['bill_type']);
       }catch(Exception $e){
          $biller = [
            'status' => false,
            'message' => $e->getMessage()
          ];
       }

       if( isset($biller['status']) &&  $biller['status'] == false ){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
       }elseif( isset($biller['content']) && empty( $biller['content'])){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
       }
       $biller =  $biller['content'][0];

       $referenceId =  remove_special_char($user->username.getFirstChar($biller['name']).$request_data['bill_month']).rand(1323,5666);
       $bill_amount = $request_data['amount'];
       $bill_number = $request_data['bill_number'];

       $userWallet = UserWallet::where('user_id',$user->id)->first();
       if(!$userWallet){
            $error = ['error'=>[__('User wallet not found!')]];
            return Helpers::error($error);
       }
       $baseCurrency = Currency::default();
       if(!$baseCurrency){
            $error = ['error'=>[__('Default currency not found')]];
            return Helpers::error($error);
       }
       $charges = $this->automaticBillPayCharge($userWallet,$biller,$bill_amount);
       $minLimit =  $biller['minLocalTransactionAmount'];
       $maxLimit =  $biller['maxLocalTransactionAmount'];
       if($bill_amount < $minLimit || $bill_amount > $maxLimit) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
       }
       if( $charges['payable'] > $userWallet->balance ){
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
       }

       $payBillData = [
            'subscriberAccountNumber'=> $bill_number,
            'amount'                 => $bill_amount,
            'amountId'               => null,
            'billerId'               => $biller['id'],
            'useLocalAmount'         => false,
            'referenceId'            => $referenceId,
            'additionalInfo' => [
                'invoiceId'       => null,
            ],
        ];
        $payBill = (new UtilityHelper())->payUtilityBill($payBillData);
        if( $payBill['status'] === false){
            if($payBill['message'] === "The provided reference ID has already been used. Please provide another one."){
                $errorMessage = __("Bill payment already taken for")." ".$biller['name']." ".$request_data['bill_month'];
            }else{
                $errorMessage = $payBill['message'];
            }
            $error = ['error'=>[__($errorMessage)]];
            return Helpers::error($error);
        }
        try{
            $trx_id = 'BP'.getTrxNum();
            $transaction = $this->insertTransactionAutomatic($trx_id,$user,$userWallet,$charges,$request_data,$payBill??[],$biller);
            $this->insertAutomaticCharges($transaction,$charges,$biller,$request_data,$user);
            try{
                if( $this->basic_settings->email_notification == true){
                    $notifyData = [
                        'trx_id'            => $trx_id,
                        'biller_name'       => $biller['name'],
                        'bill_month'        => $request_data['bill_month'],
                        'bill_number'       => $bill_number,
                        'bill_amount'       => get_amount($charges['sender_amount'],$charges['sender_currency']),
                        'exchange_rate'     => get_amount(1,$charges['sender_currency'])." = ".get_amount($charges['exchange_rate'],$charges['wallet_currency'],4),
                        'charges'           => get_amount($charges['total_charge'],$charges['wallet_currency']),
                        'payable'           => get_amount($charges['payable'],$charges['wallet_currency']),
                        'current_balance'   => getAmount($userWallet->balance,4),
                        'status'            => $payBill['status']??__("Successful"),
                    ];
                    //send notifications
                    $user->notify(new BillPayMailAutomatic($user,(object)$notifyData));
                }

            }catch(Exception $e){}
              //admin notification
              $this->adminNotificationAutomatic($trx_id,$charges,$biller,$request_data,$user,$payBill);
             // Dispatch the job to process the payment status
            ProcessBillPayment::dispatch($transaction)->delay(now()->addSeconds(scheduleBillPayApiCall($payBill)));
            // ProcessBillPayment::dispatch($transaction)->delay(now()->addSeconds(10));

            $message =  ['success'=>[__('Bill Pay Request Successful')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }



    }
    public function insertTransactionAutomatic($trx_id,$user,$userWallet,$charges,$request_data,$payBill,$biller){
        if($payBill['status'] === "PROCESSING"){
            $status = PaymentGatewayConst::STATUSPROCESSING;
        }elseif($payBill['status'] === "SUCCESSFUL"){
            $status = PaymentGatewayConst::STATUSSUCCESS;
        }else{
            $status = PaymentGatewayConst::STATUSFAILD;
        }
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $charges['payable']);
        $details =[
            'bill_type_id'      => $request_data['bill_type']??'',
            'bill_type_name'    => $biller['name']??'',
            'bill_number'       => $request_data['bill_number']??'',
            'sender_amount'     => $request_data['amount']??0,
            'bill_month'        => $request_data['bill_month']??'',
            'bill_type'         => $request_data['biller_item_type']??'',
            'biller_info'       => $biller??[],
            'api_response'      => $payBill??[],
            'charges'           => $charges??[],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::BILLPAY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::BILLPAY," ")) . " Request Successful",
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
    public function insertAutomaticCharges($id,$charges,$biller,$request_data,$user) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges['percent_charge'],
                'fixed_charge'      => $charges['fixed_charge'],
                'total_charge'      => $charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Bill Pay"),
                'message'       => __("Bill Pay For")." (".$biller['name']." ".$request_data['bill_month'].") " .$charges['sender_amount'].' '.$charges['sender_currency']." ".__("Successful"),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::BILL_PAY,
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
    public function automaticBillPayCharge($userWallet,$biller,$amount){
        $sender_currency = ExchangeRate::where(['currency_code' => $biller['localTransactionCurrencyCode']])->first();
        $exchange_rate = $userWallet->currency->rate/$sender_currency->rate;

        $data['exchange_rate' ]             = $exchange_rate;
        $data['sender_amount']               = $amount;
        $data['sender_currency']            = $sender_currency->currency_code;
        $data['sender_currency_rate']       = $sender_currency->rate;
        $data['wallet_currency']            = $userWallet->currency->code;
        $data['wallet_currency_rate']       = $userWallet->currency->rate;
        $data['percent_charge']             = 0;
        $data['fixed_charge']               = 0;
        $data['total_charge']               = $biller['localTransactionFee'] * $exchange_rate;
        $data['sender_wallet_balance']      = $userWallet->balance;
        $data['conversion_amount']          = $amount * $exchange_rate;
        $data['payable']                    =  $data['conversion_amount'] + $data['total_charge'];

        return $data;

    }
    //admin notification
    public function adminNotificationManual($trx_id,$charges,$bill_type,$user,$request_data){
        $exchange_rate = get_amount(1,$charges['sender_currency'])." = ".get_amount($charges['exchange_rate'],$charges['wallet_currency'],4);
        $notification_content = [
            //email notification
            'subject' => __("Bill Pay For")." ". $bill_type->name.' ('.$request_data['bill_number'].' )',
            'greeting' =>__("Bill pay request sent to admin successful")." (".$request_data['bill_month'].")",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Bill Number")." : ".$request_data['bill_number']."<br>".__("bill Type")." : ".$bill_type->name."<br>".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($charges['total_charge'],$charges['wallet_currency'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['wallet_currency'])."<br>".__("Status")." : ".__("Pending"),

            //push notification
            'push_title' => __("Bill pay request sent to admin successful")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'],

            //admin db notification
            'notification_type' =>  NotificationConst::BILL_PAY,
            'admin_db_title' => "Bill pay request sent to admin successful"." (".userGuard()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Bill Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'].","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['wallet_currency'])." (".$user->email.")"
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.bill.pay.index','admin.bill.pay.pending','admin.bill.pay.processing','admin.bill.pay.complete','admin.bill.pay.canceled','admin.bill.pay.details','admin.bill.pay.approved','admin.bill.pay.rejected','admin.bill.pay.export.data'])
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
    public function adminNotificationAutomatic($trx_id,$charges,$biller,$request_data,$user,$payBill){
        $exchange_rate = get_amount(1,$charges['sender_currency'])." = ".get_amount($charges['exchange_rate'],$charges['wallet_currency'],4);
        if($payBill['status'] === "PROCESSING"){
            $status ="Processing";
        }elseif($payBill['status'] === "SUCCESSFUL"){
            $status ="success";
        }else{
            $status ="Failed";
        }
        $notification_content = [
            //email notification
            'subject' => __("Bill Pay For")." ". $biller['name'].' ('.$request_data['bill_number'].' )',
            'greeting' =>__("Bill pay successful")." (".$request_data['bill_month'].")",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Bill Number")." : ".$request_data['bill_number']."<br>".__("bill Type")." : ".$biller['name']."<br>".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($charges['total_charge'],$charges['wallet_currency'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['wallet_currency'])."<br>".__("Status")." : ".__($status),

            //push notification
            'push_title' => __("Bill pay successful")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("Bill Amount")." : ".get_amount($charges['sender_amount'],$charges['sender_currency']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'].",".__("Biller Name")." : ".$biller['name'],

            //admin db notification
            'notification_type' =>  NotificationConst::BILL_PAY,
            'admin_db_title' => "Bill pay successful"." (".userGuard()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Bill Amount"." : ".get_amount($charges['sender_amount'],$charges['sender_currency']).",".__("Bill Month")." : ".$request_data['bill_month'].",".__("Bill Number")." : ".$request_data['bill_number'].","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['wallet_currency']).",".__("Biller Name")." : ".$biller['name']." (".$user->email.")"
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.make.payment.index','admin.make.payment.export.data'])
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
