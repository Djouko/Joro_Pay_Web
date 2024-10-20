<?php

namespace App\Traits\PaymentGateway;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\PaymentGatewayApi;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\BasicSettings;
use App\Models\TemporaryData;
use App\Models\UserNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use App\Models\Admin\PaymentGateway as PaymentGatewayModel;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Notifications\User\AddMoney\ManualMail;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;
use App\Models\AgentNotification;
use Illuminate\Support\Facades\Auth;
use App\Traits\TransactionAgent;

trait Manual
{
use ControlDynamicInputFields,TransactionAgent;
    public function manualInit($output = null) {
        if(!$output) $output = $this->output;
        $identifier = generate_unique_string("transactions","trx_id",16);
        $this->manualJunkInsert($identifier);
        Session::put('identifier',$identifier);
        Session::put('output',$output);
        if(userGuard()['guard']  === 'web'){
            return redirect()->route('user.add.money.manual.payment');
        }elseif(userGuard()['guard']  === 'agent'){
            return redirect()->route('agent.add.money.manual.payment');
        }

    }
    public function manualJunkInsert($response) {
        $output = $this->output;
        $data = [
            'gateway'   => $output['gateway']->id,
            'currency'  => $output['currency']->id,
            'amount'    => json_decode(json_encode($output['amount']),true),
            'response'  => $response,
        ];

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::MANUA_GATEWAY,
            'identifier'    => $response,
            'data'          => $data,
        ]);
    }
    public function manualPaymentConfirmed(Request $request){
        $basic_setting = BasicSettings::first();
        $output = session()->get('output');
        $tempData = Session::get('identifier');
        $hasData = TemporaryData::where('identifier', $tempData)->first();
        $gateway = PaymentGatewayModel::manual()->where('slug',PaymentGatewayConst::add_money_slug())->where('id',$hasData->data->gateway)->first();
        $payment_fields = $gateway->input_fields ?? [];

        $validation_rules = $this->generateValidationRules($payment_fields);
        $payment_field_validate = Validator::make($request->all(),$validation_rules)->validate();
        $get_values = $this->placeValueWithFields($payment_fields,$payment_field_validate);


        try{
            $trx_id = 'AM'.getTrxNum();
            $user = authGuardApi()['user'];
            if(userGuard()['type'] == "USER"){
                $inserted_id = $this->insertRecordManual($output,$get_values,$trx_id);
                $this->insertChargesManual($output,$inserted_id);
                $this->adminNotification($trx_id,$output,PaymentGatewayConst::STATUSPENDING);
                try{
                    if( $basic_setting->email_notification == true){
                        $user->notify(new ManualMail($user,$output,$trx_id));
                    }
                }catch(Exception $e){}
                $return_url = "user.add.money.index";
            }elseif(userGuard()['type'] == "AGENT"){
                $inserted_id = $this->insertRecordManualAgent($output,$get_values,$trx_id);
                $this->insertChargesManualAgent($output,$inserted_id);
                $this->adminNotification($trx_id,$output,PaymentGatewayConst::STATUSPENDING);
                try{
                    if( $basic_setting->agent_email_notification == true){
                        $user->notify(new ManualMail($user,$output,$trx_id));
                    }
                }catch(Exception $e){}
                $return_url = $return_url = "agent.add.money.index";
            }
            $this->insertDeviceManual($output,$inserted_id);
            $this->removeTempDataManual($output);

            return redirect()->route($return_url)->with(['success' => [__('Add Money request send to admin successfully')]]);
        }catch(Exception $e) {
            return redirect()->route($return_url)->with(['error' => [__("Something went wrong! Please try again.")]]);
        }



    }
    //user transaction
    public function insertRecordManual($output,$get_values,$trx_id) {
        $trx_id = $trx_id;
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => auth()->user()->id,
                'user_wallet_id'                => $output['wallet']->id,
                'payment_gateway_currency_id'   => $output['currency']->id,
                'type'                          => PaymentGatewayConst::TYPEADDMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $output['amount']->requested_amount,
                'payable'                       => $output['amount']->total_amount,
                'available_balance'             => $output['wallet']->balance,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEADDMONEY," ")) . " With " . $output['gateway']->name,
                'details'                       => json_encode($get_values),
                'status'                        => 2,
                'attribute'                      =>PaymentGatewayConst::SEND,
                'created_at'                    => now(),
            ]);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function insertChargesManual($output,$id) {
        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['amount']->percent_charge,
                'fixed_charge'      => $output['amount']->fixed_charge,
                'total_charge'      => $output['amount']->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Add Money"),
                'message'       => __("Your Wallet")." (".$output['wallet']->currency->code.")  ".__("balance  has been added")." ".$output['amount']->requested_amount.' '. $output['wallet']->currency->code,
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'user_id'  =>  auth()->user()->id,
                'message'   => $notification_content,
            ]);

             //Push Notifications
             (new PushNotificationHelper())->prepare([$user->id],[
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
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
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    //agent transaction
    public function insertRecordManualAgent($output,$get_values,$trx_id) {
        $agent = authGuardApi()['user'];
        $trx_id = $trx_id;
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $agent->id,
                'agent_wallet_id'               => $output['wallet']->id,
                'payment_gateway_currency_id'   => $output['currency']->id,
                'type'                          => PaymentGatewayConst::TYPEADDMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $output['amount']->requested_amount,
                'payable'                       => $output['amount']->total_amount,
                'available_balance'             => $output['wallet']->balance,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEADDMONEY," ")) . " With " . $output['gateway']->name,
                'details'                       => json_encode($get_values),
                'status'                        => 2,
                'attribute'                      =>PaymentGatewayConst::SEND,
                'created_at'                    => now(),
            ]);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function insertChargesManualAgent($output,$id) {
        $user = authGuardApi()['user'];
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['amount']->percent_charge,
                'fixed_charge'      => $output['amount']->fixed_charge,
                'total_charge'      => $output['amount']->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Add Money"),
                'message'       => __("Your Wallet")." (".$output['wallet']->currency->code.")  ".__("balance  has been added")." ".$output['amount']->requested_amount.' '. $output['wallet']->currency->code,
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'agent_id'  =>   $user->id,
                'message'   => $notification_content,
            ]);

           //admin notification
            $notification_content['title'] = __('Add Money').' '.$output['amount']->requested_amount.' '.$output['amount']->default_currency.' '.__('By '). $output['currency']->name.' ('.$user->username.')';
            AdminNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'admin_id'  => 1,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }

    public function removeTempDataManual($output) {
        $token = session()->get('identifier');
        TemporaryData::where("identifier",$token)->delete();
    }
     //for api
     public function manualInitApi($output = null) {
        if(!$output) $output = $this->output;
        $gatewayAlias = $output['gateway']['alias'];
        $identifier = generate_unique_string("transactions","trx_id",16);
        $this->manualJunkInsert($identifier);
        $response=[
            'trx' => $identifier,
        ];
        return $response;
    }
    public function manualPaymentConfirmedApi(Request $request){
        $validator = Validator::make($request->all(), [
            'track' => 'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $track = $request->track;
        $hasData = TemporaryData::where('identifier', $track)->first();
        if(!$hasData){
            $error = ['error'=>["Sorry, your payment information is invalid"]];
            return Helpers::error($error);
        }

        $gateway = PaymentGatewayModel::manual()->where('slug',PaymentGatewayConst::add_money_slug())->where('id',$hasData->data->gateway)->first();
        $payment_fields = $gateway->input_fields ?? [];

        $validation_rules = $this->generateValidationRules($payment_fields);
        $validator2 = Validator::make($request->all(), $validation_rules);

        if ($validator2->fails()) {
            $message =  ['error' => $validator2->errors()->all()];
            return Helpers::error($message);
        }
        $validated = $validator2->validate();
        $get_values = $this->placeValueWithFields($payment_fields, $validated);
        $payment_gateway_currency = PaymentGatewayCurrency::where('id', $hasData->data->currency)->first();
        $gateway_request = ['currency' => $payment_gateway_currency->alias, 'amount'  => $hasData->data->amount->requested_amount];
        $output = PaymentGatewayApi::init($gateway_request)->gateway()->get();

        try{
            $trx_id = 'AM'.getTrxNum();
            $user = authGuardApi()['user'];

            if(authGuardApi()['guard'] == 'agent_api'){
                $inserted_id = $this->insertRecordManualAgent($output,$get_values,$trx_id);
                $this->insertChargesManualAgent($output,$inserted_id);
                $this->adminNotification($trx_id,$output,PaymentGatewayConst::STATUSPENDING);
            }elseif(auth()->guard(get_auth_guard())->check()){
                $inserted_id = $this->insertRecordManual($output,$get_values,$trx_id);
                $this->insertChargesManual($output,$inserted_id);
                $this->adminNotification($trx_id,$output,PaymentGatewayConst::STATUSPENDING);
            }

            $this->insertDeviceManual($output,$inserted_id);
            $hasData->delete();
            try{
                $user->notify(new ManualMail($user,$output,$trx_id));
            }catch(Exception $e){

            }

            $message =  ['success'=>[__('Add Money request send to admin successfully')]];
            return Helpers::onlysuccess( $message);
        }catch(Exception $e) {


            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }



    }

}
