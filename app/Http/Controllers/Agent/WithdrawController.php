<?php

namespace App\Http\Controllers\Agent;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use Illuminate\Http\Request;
use App\Models\Admin\Currency;
use App\Models\Admin\PaymentGateway;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use App\Traits\ControlDynamicInputFields;
use Exception;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use App\Models\Admin\BasicSettings;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\Agent\Withdraw\WithdrawMail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use KingFlamez\Rave\Facades\Rave as Flutterwave;

class WithdrawController extends Controller
{
    use ControlDynamicInputFields;
    public function index()
    {
        $page_title = "Withdraw Money";
        $currencies = Currency::active()->get();
        $payment_gateways = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->get();
        $transactions = Transaction::agentAuth()->moneyOut()->orderByDesc("id")->latest()->take(10)->get();
        return view('agent.sections.money-out.index',compact('page_title','payment_gateways','transactions','currencies'));
    }
   public function paymentInsert(Request $request){
        $request->validate([
            'amount'    => 'required|numeric|gt:0',
            'gateway'   => "required|exists:payment_gateway_currencies,alias",
        ]);
        $amount = $request->amount;
        $user = authGuardApi()['user'];

        $userWallet = AgentWallet::auth()->active()->first();
        if(!$userWallet){
            return back()->with(['error' => ['Agent wallet not found']]);
        }
        $gate =PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->where('alias',$request->gateway)->first();

        if (!$gate) {
            return back()->with(['error' => ['Gateway is not available right now! Please contact with system administration']]);
        }
        $min_amount = (($gate->min_limit/$gate->rate) * $userWallet->currency->rate);
        $max_amount = (($gate->max_limit/$gate->rate) * $userWallet->currency->rate);
        if($amount < $min_amount || $amount > $max_amount) {
            return back()->with(['error' => ["Please follow the transaction limit"]]);
        }
        $charges = $this->chargeCalculate( $gate,$userWallet,$amount);

        if( $charges->payable > $userWallet->balance){
            return back()->with(['error' => ['Sorry, insufficient balance']]);
        }
        $data['agent_id']= $user->id;
        $data['gateway_name']= $gate->gateway->name;
        $data['gateway_type']= $gate->gateway->type;
        $data['wallet_id']= $userWallet->id;
        $data['trx_id']= 'WM'.getTrxNum();
        $data['amount'] =  $amount;
        $data['gateway_id'] = $gate->gateway->id;
        $data['gateway_currency_id'] = $gate->id;
        $data['gateway_currency'] = strtoupper($gate->currency_code);
        $data['charges'] = $charges;

        session()->put('moneyoutData', $data);
        return redirect()->route('agent.money.out.preview');
   }
   public function preview(){
    $moneyOutData = (object)session()->get('moneyoutData');
    $moneyOutDataExist = session()->get('moneyoutData');
    if($moneyOutDataExist  == null){
        return redirect()->route('agent.money.out.index');
    }
    $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();
    if($gateway->type == "AUTOMATIC"){
        $page_title = __("Withdraw Via").' '.$gateway->name;
        if(strtolower($gateway->name) == "flutterwave"){
            $credentials = $gateway->credentials;
            $data = null;
            foreach ($credentials as $object) {
                $object = (object)$object;
                if ($object->label === "Secret key") {
                    $data = $object;
                    break;
                }
            }
            $countries = get_all_countries();
            $currency =  $moneyOutData->gateway_currency;
            $country = Collection::make($countries)->first(function ($item) use ($currency) {
                return $item->currency_code === $currency;
            });

            $allBanks = getFlutterwaveBanks($country->iso2);
            return view('agent.sections.money-out.automatic.'.strtolower($gateway->name),compact('page_title','gateway','moneyOutData','allBanks'));
        }else{
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }else{
        $page_title = __("Withdraw Via").' '.$gateway->name;
        return view('agent.sections.money-out.preview',compact('page_title','gateway','moneyOutData'));
    }

   }
   public function confirmMoneyOut(Request $request){
    $basic_setting = BasicSettings::first();
    $moneyOutData = (object)session()->get('moneyoutData');
    $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();
    $payment_fields = $gateway->input_fields ?? [];

    $validation_rules = $this->generateValidationRules($payment_fields);
    $payment_field_validate = Validator::make($request->all(),$validation_rules)->validate();
    $get_values = $this->placeValueWithFields($payment_fields,$payment_field_validate);
        try{
            //send notifications
            $get_values =[
                'user_data' => $get_values,
                'charges' => $moneyOutData->charges,

            ];
            $user = auth()->user();
            $inserted_id = $this->insertRecordManual($moneyOutData,$gateway,$get_values,$reference= null,PaymentGatewayConst::STATUSPENDING);
            $this->insertChargesManual($moneyOutData,$inserted_id);
            $this->adminNotification($moneyOutData,PaymentGatewayConst::STATUSPENDING);
            $this->insertDeviceManual($moneyOutData,$inserted_id);
            try{
                if( $basic_setting->email_notification == true){
                    $user->notify(new WithdrawMail($user,$moneyOutData));
                }
            }catch(Exception $e){

            }

            session()->forget('moneyoutData');

            return redirect()->route("agent.money.out.index")->with(['success' => [__('Withdraw Money Request Send To Admin Successful')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }

   }
   public function confirmMoneyOutAutomatic(Request $request){
    $basic_setting = BasicSettings::first();
    if($request->gateway_name == 'flutterwave'){
        $request->validate([
            'bank_name' => 'required',
            'account_number' => 'required'
        ]);
        $moneyOutData = (object)session()->get('moneyoutData');
        $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();

        $credentials = $gateway->credentials;
        $data = null;
        $secret_key = getPaymentCredentials($credentials,'Secret key');
        $base_url = getPaymentCredentials($credentials,'Base Url');
        $callback_url = url('/').'/flutterwave/withdraw_webhooks';
        $ch = curl_init();
        $url =  $base_url.'/transfers';
        $reference =  generateTransactionReference();
        $data = [
            "account_bank" => $request->bank_name,
            "account_number" => $request->account_number,
            "amount" => $moneyOutData->charges->will_get,
            "narration" => "Withdraw from wallet",
            "currency" =>$moneyOutData->gateway_currency,
            "reference" =>  $reference,
            "callback_url" => $callback_url,
            "debit_currency" => $moneyOutData->gateway_currency
        ];
        $headers = [
            'Authorization: Bearer '.$secret_key,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return back()->with(['error' => [curl_error($ch)]]);
        } else {
            $result = json_decode($response,true);
            if($result['status'] && $result['status'] == 'success'){
                try{
                    $get_values =[
                        'user_data' => null,
                        'charges' => $moneyOutData->charges,
                    ];
                    //send notifications
                    $user = auth()->user();
                    $inserted_id = $this->insertRecordManual($moneyOutData,$gateway,$get_values,$reference,PaymentGatewayConst::STATUSWAITING);
                    $this->insertChargesAutomatic($moneyOutData,$inserted_id);
                    $this->adminNotification($moneyOutData,PaymentGatewayConst::STATUSSUCCESS);
                    $this->insertDeviceManual($moneyOutData,$inserted_id);
                    session()->forget('moneyoutData');
                    try{
                        if( $basic_setting->email_notification == true){
                            $user->notify(new WithdrawMail($user,$moneyOutData));
                        }
                    }catch(Exception $e){

                    }

                    return redirect()->route("agent.money.out.index")->with(['success' => [__('Withdraw money request send to admin successful')]]);
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }

            }else{
                return back()->with(['error' => [$result['message']]]);
            }
        }

        curl_close($ch);

    }else{
        return back()->with(['error' => [__("Invalid request,please try again later")]]);
    }

   }
   //check flutterwave banks
    public function checkBanks(Request $request){
        $bank_account = $request->account_number;
        $bank_code = $request->bank_code;
        $exist['data'] = (checkBankAccount($secret_key = null,$bank_account,$bank_code));
        return response( $exist);
    }
    public function insertRecordManual($moneyOutData,$gateway,$get_values,$reference,$status) {

        $trx_id = $moneyOutData->trx_id ??'MO'.getTrxNum();
        $authWallet = AgentWallet::where('id',$moneyOutData->wallet_id)->where('agent_id',$moneyOutData->agent_id)->first();
        if($moneyOutData->gateway_type != "AUTOMATIC"){
            $afterCharge = ($authWallet->balance - ($moneyOutData->amount));
        }else{
            $afterCharge = $authWallet->balance;
        }
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      =>authGuardApi()['user']->id,
                'agent_wallet_id'               => $moneyOutData->wallet_id,
                'payment_gateway_currency_id'   => $moneyOutData->gateway_currency_id,
                'type'                          => PaymentGatewayConst::TYPEMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $moneyOutData->charges->requested_amount,
                'payable'                       => $moneyOutData->charges->will_get,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMONEYOUT," ")) . " by " .$gateway->name,
                'details'                       => json_encode($get_values),
                'status'                        => $status,
                'callback_ref'                  => $reference??null,
                'created_at'                    => now(),
            ]);

            if($moneyOutData->gateway_type != "AUTOMATIC"){
                $this->updateWalletBalanceManual($authWallet,$afterCharge);
            }

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return $id;
    }
    public function updateWalletBalanceManual($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertChargesManual($moneyOutData,$id) {

        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $moneyOutData->charges->percent_charge,
                'fixed_charge'      => $moneyOutData->charges->fixed_charge,
                'total_charge'      => $moneyOutData->charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Withdraw"),
                'message'       => __("Your Withdraw Request Send To Admin")." " .$moneyOutData->amount.' '.$moneyOutData->charges->wallet_cur_code." ".__("Successful"),
                'image'         => get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  $user->id,
                'message'   => $notification_content,
            ]);

            //Push Notifications
            (new PushNotificationHelper())->prepare([$user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'agent',
            ])->send();
            //admin notification
            $notification_content['title'] = 'Withdraw Request Send '.$moneyOutData->amount.' '.$moneyOutData->charges->wallet_cur_code.' By '.$moneyOutData->gateway_name.' '.$moneyOutData->gateway_currency.' ('.$user->username.')';
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function insertChargesAutomatic($moneyOutData,$id) {

        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $moneyOutData->charges->percent_charge,
                'fixed_charge'      => $moneyOutData->charges->fixed_charge,
                'total_charge'      => $moneyOutData->charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__( "Withdraw"),
                'message'       => __("Your Withdraw Request")."  " .$moneyOutData->amount.' '.$moneyOutData->charges->wallet_cur_code." ".__("Successful"),
                'image'         => get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  $user->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            (new PushNotificationHelper())->prepare([$user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'agent',
            ])->send();
            //admin notification
            $notification_content['title'] = 'Withdraw Request '.$moneyOutData->amount.' '.$moneyOutData->charges->wallet_cur_code.' By '.$moneyOutData->gateway_name.' '.$moneyOutData->gateway_currency.' Successful ('.$user->username.')';
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function insertDeviceManual($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function chargeCalculate($currency,$receiver_currency,$amount) {

        $amount = $amount;
        $sender_currency_rate = $currency->rate;
        ($sender_currency_rate == "" || $sender_currency_rate == null) ? $sender_currency_rate = 0 : $sender_currency_rate;
        ($amount == "" || $amount == null) ? $amount : $amount;

        if($currency != null) {
            $fixed_charges = $currency->fixed_charge;
            $percent_charges = $currency->percent_charge;
        }else {
            $fixed_charges = 0;
            $percent_charges = 0;
        }

        $fixed_charge_calc =  $fixed_charges;
        $percent_charge_calc = ($amount / 100 ) * $percent_charges;

        $total_charge = $fixed_charge_calc + $percent_charge_calc;

        $receiver_currency = $receiver_currency->currency;
        $receiver_currency_rate = $receiver_currency->rate;
        ($receiver_currency_rate == "" || $receiver_currency_rate == null) ? $receiver_currency_rate = 0 : $receiver_currency_rate;
        $exchange_rate = ($sender_currency_rate/$receiver_currency_rate);
        $conversion_amount =  $amount * $exchange_rate;
        $will_get = $conversion_amount  - $total_charge;
        $payable =  $amount;

        $data = [
            'requested_amount'          => $amount,
            'gateway_cur_code'          => $currency->currency_code,
            'gateway_cur_rate'          => $sender_currency_rate ?? 0,
            'wallet_cur_code'           => $receiver_currency->code,
            'wallet_cur_rate'           => $receiver_currency->rate ?? 0,
            'fixed_charge'              => $fixed_charge_calc,
            'percent_charge'            => $percent_charge_calc,
            'total_charge'              => $total_charge,
            'conversion_amount'         => $conversion_amount,
            'payable'                   => $payable,
            'exchange_rate'             => $exchange_rate,
            'will_get'                  => $will_get,
            'default_currency'          => get_default_currency_code(),
        ];

        return (object) $data;
    }
    public function flutterWaveWebHooks(Request $request){
        //This verifies the webhook is sent from Flutterwave
         $verified = Flutterwave::verifyWebhook();
        // if it is a transfer event, verify and confirm it is a successful transfer
        if ($verified && $request->event == 'transfer.completed') {

            $transfer = Flutterwave::transfers()->fetch($request->data['id']);

            if($transfer['data']['status'] === 'SUCCESSFUL') {
                return redirect()->route("agent.money.out.index");
            } else if ($transfer['data']['status'] === 'FAILED') {
                return redirect()->route("agent.money.out.index");
            } else if ($transfer['data']['status'] === 'PENDING') {
                return redirect()->route("agent.money.out.index");
            }

        }else{
            return redirect()->route("agent.money.out.index");
        }

    }
    //admin notification global(Agent & User)
    public function adminNotification($data,$status){
        $user = auth()->guard(userGuard()['guard'])->user();
        $exchange_rate = " 1 ". get_default_currency_code().' = '. get_amount($data->charges->gateway_cur_rate,$data->charges->gateway_cur_code);
        if($status == PaymentGatewayConst::STATUSSUCCESS){
            $status ="success";
        }elseif($status == PaymentGatewayConst::STATUSPENDING){
            $status ="Pending";
        }elseif($status == PaymentGatewayConst::STATUSHOLD){
            $status ="Hold";
        }elseif($status == PaymentGatewayConst::STATUSWAITING){
            $status ="Waiting";
        }elseif($status == PaymentGatewayConst::STATUSPROCESSING){
            $status ="Processing";
        }elseif($status == PaymentGatewayConst::STATUSFAILD){
            $status ="Failed";
        }

        $notification_content = [
            //email notification
            'subject' =>__("Withdraw Money")." (".userGuard()['type'].")",
            'greeting' =>__("Withdraw Money Via")." ".$data->gateway_name.' ('.$data->gateway_type.' )',
            'email_content' =>__("web_trx_id")." : ".$data->trx_id."<br>".__("request Amount")." : ".get_amount($data->amount,get_default_currency_code())."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($data->charges->gateway_cur_rate,$data->charges->gateway_cur_code)."<br>".__("Total Payable Amount")." : ".get_amount($data->charges->payable,get_default_currency_code())."<br>".__("Will Get")." : ".get_amount($data->charges->will_get,$data->gateway_currency,2)."<br>".__("Status")." : ".__($status),
            //push notification
            'push_title' =>  __("Withdraw Money")." (".userGuard()['type'].")",
            'push_content' => __('web_trx_id')." ".$data->trx_id." ". __("Withdraw Money").' '.get_amount($data->amount,get_default_currency_code()).' '.__('By').' '.$data->gateway_name.' ('.$user->username.')',

            //admin db notification
            'notification_type' =>  NotificationConst::MONEY_OUT,
            'trx_id' => $data->trx_id,
            'admin_db_title' =>  "Withdraw Money"." (".userGuard()['type'].")",
            'admin_db_message' =>  "Withdraw Money".' '.get_amount($data->amount,get_default_currency_code()).' '.'By'.' '.$data->gateway_name.' ('.$user->username.')'
        ];

        try{
            //notification
            (new NotificationHelper())->admin(['admin.money.out.index','admin.money.out.pending','admin.money.out.complete','admin.money.out.canceled','admin.money.out.details','admin.money.out.approved','admin.money.out.rejected','admin.money.out.export.data'])
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
