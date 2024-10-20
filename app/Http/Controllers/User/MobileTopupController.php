<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\AirtimeHelper;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\TransactionSetting;
use App\Models\TopupCategory;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\MobileTopup\TopupMail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\ExchangeRate;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\MobileTopup\TopupAutomaticMail;
use Illuminate\Support\Str;

class MobileTopupController extends Controller

{
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }

    public function index() {
        $page_title = __("Mobile Topup");
        $transactions = Transaction::auth()->mobileTopup()->latest()->take(10)->get();
        return view('user.sections.mobile-top.index',compact("page_title","transactions"));
    }
    public function selectType(Request $request){
        $validated = Validator::make($request->all(),[
            'topup_type' => 'required|string'
        ])->validate();
        if($validated['topup_type'] === PaymentGatewayConst::MANUAL){
            return redirect()->route('user.mobile.topup.manual.index');
        }elseif($validated['topup_type'] === PaymentGatewayConst::AUTOMATIC){
            return redirect()->route('user.mobile.topup.automatic.index');
        }else{
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    //start automatic
    public function automaticTopUp(){
        $page_title = __("Mobile Topup");
        return view('user.sections.mobile-top.automatic.index',compact("page_title"));
    }
    public function checkOperator(Request $request){
        $phone = $request->mobile_code.$request->phone;
        $iso = $request->iso;
        $operator = (new AirtimeHelper())->autoDetectOperator($phone,$iso);
        if($operator['status'] === false){
            $data = [
                'status' => false,
                'message' => $operator['message']??"",
                'data' => [],
                'from' => "error",
            ];
        }else{
            $data = [
                'status' => true,
                'message' => 'Successfully Get Operator',
                'data' => $operator,
                'from' => "success",
            ];
        }
        return response($data);
    }
    public function payAutomatic(Request $request){
        $validated = Validator::make($request->all(),[
            'operator_id' => 'required',
            'phone_code' => 'required',
            'country_code' => 'required',
            'mobile_number' => 'required|min:10|max:15',
            'amount' => 'required|numeric|gt:0',
        ])->validate();

        $user = userGuard()['user'];
        $sender_phone = $user->full_mobile??"";
        $sender_country_name = @$user->address->country;
        $foundItem = '';
        foreach (freedom_countries(GlobalConst::USER) ?? [] as $item) {
            if ($item->name === $sender_country_name) {
                $foundItem = $item;
            }
        }
        $sender_country_iso = $foundItem->iso2;

        $phone = remove_speacial_char($validated['phone_code']).$validated['mobile_number'];
        $operator = (new AirtimeHelper())->autoDetectOperator($phone,$validated['country_code']);
        if($operator['status'] === false){
            return back()->with(['error' => [__($operator['message']??"")]]);
        }
        $sender_wallet = UserWallet::where('user_id',$user->id)->first();
        if(!$sender_wallet){
            return back()->with(['error' => [__('User Wallet not found')]]);
        }
        $charges = $this->topupChargeAutomatic($validated['amount'],$operator,$sender_wallet);
        if($operator['denominationType'] === "RANGE" && $operator['minAmount'] != null && $operator['maxAmount'] != null){
            $min_amount = $operator['minAmount'];
            $max_amount = $operator['maxAmount'];
            if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
                return back()->with(['error' => [__("Please follow the transaction limit")]]);
            }
        }

        if($charges['payable'] > $sender_wallet->balance) {
            return back()->with(['error' => [__("Sorry, insufficient balance")]]);
        }
        //topup api
         $topUpData = [
            'operatorId'        => $operator['operatorId'],
            'amount'            => $validated['amount'],
            'useLocalAmount'    => $operator['supportsLocalAmounts'],
            'customIdentifier'  => Str::uuid() . "|" . "AIRTIME",
            'recipientEmail'    => null,
            'recipientPhone'  => [
                'countryCode' => $validated['country_code'],
                'number'  => $phone,
            ],
            'senderPhone'   => [
                'countryCode' => $sender_country_iso,
                'number'      => $sender_phone,
            ]

        ];
        $topUpData = (new AirtimeHelper())->makeTopUp($topUpData);
        if( isset($topUpData['status']) && $topUpData['status'] === false){
            return back()->with(['error' => [$topUpData['message']]]);
        }

        try{
            $trx_id = 'MP'.getTrxNum();
            $sender = $this->insertTransaction($trx_id,$sender_wallet,$charges,$operator,$phone,$topUpData);
            $this->insertAutomaticCharges($sender,$charges,$sender_wallet);
            if($this->basic_settings->email_notification == true){
                //send notifications
                $notifyData = [
                    'trx_id'            => $trx_id,
                    'operator_name'     => $operator['name']??'',
                    'mobile_number'     => $phone,
                    'request_amount'    => get_amount($charges['sender_amount'],$charges['destination_currency']),
                    'exchange_rate'     => get_amount(1,$charges['destination_currency'])." = ".get_amount($charges['exchange_rate'],$charges['sender_currency'],4),
                    'charges'           => get_amount($charges['total_charge'],$charges['sender_currency']),
                    'payable'           => get_amount($charges['payable'],$charges['sender_currency']),
                    'current_balance'   => get_amount($sender_wallet->balance,$charges['sender_currency']),
                    'status'            => __("Successful"),
                ];
                try{
                    $user->notify(new TopupAutomaticMail($user,(object)$notifyData));
                }catch(Exception $e){}
            }
            //admin notification
            $this->adminNotificationAutomatic($trx_id,$charges,$operator,$user,$phone,$topUpData);
            return redirect()->route("user.mobile.topup.index")->with(['success' => [__('Mobile topup request successful')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function insertTransaction($trx_id,$sender_wallet,$charges,$operator,$mobile_number,$topUpData) {
        if(isset($topUpData) && isset($topUpData['status']) && $topUpData['status'] === "SUCCESSFUL"){
            $status = PaymentGatewayConst::STATUSSUCCESS;
        }else{
            $status = PaymentGatewayConst::STATUSPROCESSING;
        }
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge =  ($authWallet->balance - $charges['payable']);
        $details =[
            'topup_type'        => PaymentGatewayConst::AUTOMATIC,
            'topup_type_id'     => $operator['operatorId']??'',
            'topup_type_name'   => $operator['name']??'',
            'mobile_number'     => $mobile_number,
            'topup_amount'      => $charges['sender_amount']??0,
            'charges'           => $charges,
            'operator'          => $operator??[],
            'api_response'      => $topUpData??[],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $sender_wallet->user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MOBILETOPUP,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MOBILETOPUP," ")) . " Request Successful",
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::SEND,
                'callback_ref'                  => $topUpData['customIdentifier'],
                'status'                        => $status,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function insertAutomaticCharges($id,$charges,$sender_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    =>  $id,
                'percent_charge'    =>  $charges['percent_charge'],
                'fixed_charge'      =>  $charges['fixed_charge'],
                'total_charge'      =>  $charges['total_charge'],
                'created_at'        =>  now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Mobile Topup"),
                'message'       => __('Mobile topup request successful')." " .$charges['sender_amount'].' '.$charges['destination_currency'],
                'image'         => get_image($sender_wallet->user->image,'user-profile'),
            ];

            //user Notification
            UserNotification::create([
                'type'      =>  NotificationConst::MOBILE_TOPUP,
                'user_id'   =>  $sender_wallet->user->id,
                'message'   =>  $notification_content,
            ]);

            //Push Notification
            (new PushNotificationHelper())->prepare([$sender_wallet->user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function topupChargeAutomatic($sender_amount,$operator,$sender_wallet) {

        $destinationCurrency = ExchangeRate::where(['currency_code' => $operator['destinationCurrencyCode']])->first();
        $exchange_rate = $sender_wallet->currency->rate/$destinationCurrency->rate;

        $data['exchange_rate' ]                     = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;
        $data['sender_currency']                    = $sender_wallet->currency->code;
        $data['sender_currency_rate']               = $sender_wallet->currency->rate;
        $data['destination_currency']               = $destinationCurrency->currency_code;
        $data['destination_currency_rate']          = $destinationCurrency->rate;
        $data['conversion_amount']                  = $sender_amount * $exchange_rate;
        $data['percent_charge']                     = $operator['fees']['internationalPercentage'] * $exchange_rate;
        $data['fixed_charge']                       = $operator['fees']['international'] * $exchange_rate;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['payable']                            = $data['conversion_amount'] + $data['total_charge'];

        return $data;
    }
    //end automatic

    //start manual
    public function manualTopUp() {
        $page_title = __("Mobile Topup");
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->first();
        $topupType = TopupCategory::active()->orderByDesc('id')->get();
        return view('user.sections.mobile-top.manual',compact("page_title",'topupCharge','topupType'));
    }
    public function payConfirm(Request $request){
        $validated = Validator::make($request->all(),[
            'topup_type' => 'required|exists:topup_categories,id',
            'mobile_code' => 'required',
            'mobile_number' => 'required|min:10|max:15',
            'amount' => 'required|numeric|gt:0',
        ])->validate();

        $user = userGuard()['user'];
        $phone = remove_speacial_char($validated['mobile_code']).$validated['mobile_number'];

        $sender_wallet = UserWallet::where('user_id',$user->id)->first();
        if(!$sender_wallet){
            return back()->with(['error' => [__('User Wallet not found')]]);
        }
        $topup_type = TopupCategory::where('id', $validated['topup_type'])->first();
        if(! $topup_type){
            return back()->with(['error' => [__('Invalid type')]]);
        }
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->first();
        $charges = $this->topupCharge($validated['amount'],$topupCharge,$sender_wallet);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $topupCharge->min_limit * $sender_currency_rate;
        $max_amount = $topupCharge->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
        }
        if($charges['payable'] > $sender_wallet->balance) {
            return back()->with(['error' => [__("Sorry, insufficient balance")]]);
        }
        try{
            $trx_id = 'MP'.getTrxNum();
            $sender = $this->insertSender($trx_id,$sender_wallet,$charges,$topup_type,$phone);
            $this->insertSenderCharges($sender,$charges,$sender_wallet);
            if($this->basic_settings->email_notification == true){
                //send notifications
                $notifyData = [
                    'trx_id'  => $trx_id,
                    'topup_type'  => @$topup_type->name,
                    'mobile_number'  =>$phone,
                    'request_amount'   =>$charges['sender_amount'],
                    'charges'   =>  $charges['total_charge'],
                    'payable'  => $charges['payable'],
                    'current_balance'  => getAmount($sender_wallet->balance,2),
                    'status'  => __("Pending"),
                ];
                try{
                    $user->notify(new TopupMail($user,(object)$notifyData));
                }catch(Exception $e){ }
            //admin notification
            $this->adminNotificationManual($trx_id,$charges,$topup_type,$user,$phone);
            }
            return redirect()->route("user.mobile.topup.index")->with(['success' => [__('Mobile topup request send to admin successful')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function insertSender($trx_id,$sender_wallet,$charges,$topup_type,$mobile_number) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge =  ($authWallet->balance - $charges['payable']);
        $details =[
            'topup_type'        => PaymentGatewayConst::MANUAL,
            'topup_type_id'     => $topup_type->id??'',
            'topup_type_name'   => $topup_type->name??'',
            'mobile_number'     => $mobile_number,
            'topup_amount'      => $charges['sender_amount']??0,
            'charges'           => $charges,
            'api_response'      => [],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $sender_wallet->user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MOBILETOPUP,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MOBILETOPUP," ")) . " Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::SEND,
                'status'                        => PaymentGatewayConst::STATUSPENDING,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function insertSenderCharges($id,$charges,$sender_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    =>  $id,
                'percent_charge'    =>  $charges['percent_charge'],
                'fixed_charge'      =>  $charges['fixed_charge'],
                'total_charge'      =>  $charges['total_charge'],
                'created_at'        =>  now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Mobile Topup"),
                'message'       => __('Mobile topup request send to admin')." " .$charges['sender_amount'].' '.$charges['sender_currency']." ".__("Successful"),
                'image'         => get_image($sender_wallet->user->image,'user-profile'),
            ];

            //user Notification
            UserNotification::create([
                'type'      => NotificationConst::MOBILE_TOPUP,
                'user_id'  => $sender_wallet->user->id,
                'message'   => $notification_content,
            ]);

            //Push Notification
            (new PushNotificationHelper())->prepare([$sender_wallet->user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function topupCharge($sender_amount,$charges,$sender_wallet) {
        $exchange_rate = 1;

        $data['exchange_rate' ]                     = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;
        $data['sender_currency']                    = $sender_wallet->currency->code;
        $data['sender_currency_rate']               = $sender_wallet->currency->rate;
        $data['destination_currency']               = $sender_wallet->currency->code;
        $data['destination_currency_rate']          = $sender_wallet->currency->rate;
        $data['conversion_amount']                  = $sender_amount * $exchange_rate;
        $data['percent_charge']                     = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']                       = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['payable']                            = $sender_amount + $data['total_charge'];

        return $data;
    }
    //end manual
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    //admin notification
    public function adminNotificationManual($trx_id,$charges,$topup_type,$user,$phone){
        $exchange_rate = get_amount(1,$charges['destination_currency'])." = ".get_amount($charges['exchange_rate'],$charges['sender_currency'],4);
        $notification_content = [
            //email notification
            'subject' => __("Mobile Top Up For")." ". $topup_type->name.' ('.$phone.' )',
            'greeting' =>__("Mobile topup request send to admin successful")." (".$topup_type->name."-".$phone." )",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Mobile Number")." : ".$phone."<br>".__("Operator Name")." : ".$topup_type->name."<br>".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['destination_currency'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['sender_currency'])."<br>".__("Status")." : ".__("Pending"),

            //push notification
            'push_title' => __("Mobile topup request send to admin successful")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['destination_currency']).",".__("Operator Name")." : ".$topup_type->name.",".__("Mobile Number")." : ".$phone,

            //admin db notification
            'notification_type' =>  NotificationConst::MOBILE_TOPUP,
            'admin_db_title' => "Mobile topup request send to admin successful"." (".userGuard()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Request Amount"." : ".get_amount($charges['sender_amount'],$charges['destination_currency']).","."Operator Name"." : ".$topup_type->name.","."Mobile Number"." : ".$phone.","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['sender_currency'])." (".$user->email.")"
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.mobile.topup.index','admin.mobile.topup.pending','admin.mobile.topup.processing','admin.mobile.topup.complete','admin.mobile.topup.canceled','admin.mobile.topup.details','admin.mobile.topup.approved','admin.mobile.topup.rejected','admin.mobile.topup.export.data'])
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
    public function adminNotificationAutomatic($trx_id,$charges,$operator,$user,$phone,$topUpData){
        $exchange_rate = get_amount(1,$charges['destination_currency'])." = ".get_amount($charges['exchange_rate'],$charges['sender_currency'],4);
        if(isset($topUpData) && isset($topUpData['status']) && $topUpData['status'] === "SUCCESSFUL"){
            $status ="success";
        }else{
            $status ="Processing";
        }
        $notification_content = [
            //email notification
            'subject' => __("Mobile Top Up For")." ". $operator['name'].' ('.$phone.' )',
            'greeting' =>__("Mobile topup request successful")." (".$operator['name']."-".$phone." )",
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("Mobile Number")." : ".$phone."<br>".__("Operator Name")." : ".$operator['name']."<br>".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['destination_currency'])."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ".get_amount($charges['total_charge'],$charges['sender_currency'])."<br>".__("Total Payable Amount")." : ".get_amount($charges['payable'],$charges['sender_currency'])."<br>".__("Status")." : ".__($status),

            //push notification
            'push_title' => __("Mobile topup request successful")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." : ".$trx_id.",".__("request Amount")." : ".get_amount($charges['sender_amount'],$charges['destination_currency']).",".__("Operator Name")." : ".$operator['name'].",".__("Mobile Number")." : ".$phone,

            //admin db notification
            'notification_type' =>  NotificationConst::MOBILE_TOPUP,
            'admin_db_title' => "Mobile topup request successful"." (".userGuard()['type'].")",
            'admin_db_message' =>"Transaction ID"." : ".$trx_id.","."Request Amount"." : ".get_amount($charges['sender_amount'],$charges['destination_currency']).","."Operator Name"." : ".$operator['name'].","."Mobile Number"." : ".$phone.","."Total Payable Amount"." : ".get_amount($charges['payable'],$charges['sender_currency'])." (".$user->email.")"
        ];
        try{
            //notification
            (new NotificationHelper())->admin(['admin.mobile.topup.index','admin.mobile.topup.pending','admin.mobile.topup.processing','admin.mobile.topup.complete','admin.mobile.topup.canceled','admin.mobile.topup.details','admin.mobile.topup.approved','admin.mobile.topup.rejected','admin.mobile.topup.export.data'])
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
