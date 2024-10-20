<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\PaymentGatewayApi;
use App\Models\Admin\Currency;
use App\Models\Admin\PaymentGateway;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\TemporaryData;
use App\Models\Transaction;
use App\Models\UserWallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\PaymentGateway\Stripe;
use App\Traits\PaymentGateway\Manual;
use App\Traits\TransactionAgent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\PaymentGateway as PaymentGatewayHelper;
use App\Models\Admin\CryptoTransaction;
use App\Models\UserNotification;
use Carbon\Carbon;
use KingFlamez\Rave\Facades\Rave as Flutterwave;

class AddMoneyController extends Controller
{
    use Stripe,Manual,TransactionAgent;
    public function addMoneyInformation(){
        $user = auth()->user();
        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => get_default_currency_code(),
            ];
            })->first();
            $transactions = Transaction::auth()->addMoney()->latest()->take(5)->get()->map(function($item){
                $statusInfo = [
                    "success" =>      1,
                    "pending" =>      2,
                    "rejected" =>     3,
                    ];
                return[
                    'id' => $item->id,
                    'trx' => $item->trx_id,
                    'gateway_name' => @$item->currency->name,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,4).' '.get_default_currency_code() ,
                    'payable' => getAmount($item->payable,4).' '.@$item->currency->currency_code,
                    'exchange_rate' => '1 ' .get_default_currency_code().' = '.getAmount($item->currency->rate,4).' '.@$item->currency->currency_code,
                    'total_charge' => getAmount($item->charge->total_charge,4).' '.@$item->currency->currency_code,
                    'current_balance' => getAmount($item->available_balance,4).' '.get_default_currency_code(),
                    "confirm" => $item->confirm??false,
                    "dynamic_inputs" => $item->dynamic_inputs,
                    "confirm_url" => $item->confirm_url,
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                    'status_info' =>(object)$statusInfo ,
                    'rejection_reason' =>$item->reject_reason??"" ,

                ];
                });


        $gateways = PaymentGateway::where('status', 1)->where('slug', PaymentGatewayConst::add_money_slug())->get()->map(function($gateway){
            $currencies = PaymentGatewayCurrency::where('payment_gateway_id',$gateway->id)->get()->map(function($data){
              return[
                'id' => $data->id,
                'payment_gateway_id' => $data->payment_gateway_id,
                'type' => $data->gateway->type,
                'name' => $data->name,
                'alias' => $data->alias,
                'currency_code' => $data->currency_code,
                'currency_symbol' => $data->currency_symbol,
                'image' => $data->image,
                'min_limit' => getAmount($data->min_limit,4),
                'max_limit' => getAmount($data->max_limit,4),
                'percent_charge' => getAmount($data->percent_charge,4),
                'fixed_charge' => getAmount($data->fixed_charge,4),
                'rate' => getAmount($data->rate,4),
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
              ];

            });
            return[
                'id' => $gateway->id,
                'image' => $gateway->image,
                'slug' => $gateway->slug,
                'code' => $gateway->code,
                'type' => $gateway->type,
                'alias' => $gateway->alias,
                'supported_currencies' => $gateway->supported_currencies,
                'status' => $gateway->status,
                'currencies' => $currencies

            ];
        });
        $data =[
            'base_curr'    => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'default_image'    => "public/backend/images/default/default.webp",
            "image_path"  =>  "public/backend/images/payment-gateways",
            'userWallet'   =>   (object)$userWallet,
            'gateways'   => $gateways,
            'transactionss'   =>   $transactions,
            ];
            $message =  ['success'=>[__('Add Money Information!')]];
            return Helpers::success($data,$message);
    }
    public function submitData(Request $request) {
         $validator = Validator::make($request->all(), [
            'currency'  => "required",
            'amount'        => "required|numeric|gt:0",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $user = auth()->user();
        $alias = $request->currency;
        $amount = $request->amount;
        $payment_gateways_currencies = PaymentGatewayCurrency::where('alias',$alias)->whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::add_money_slug());
            $gateway->where('status', 1);
             })->first();
        if( !$payment_gateways_currencies){
        $error = ['error'=>[__('Gateway Information is not available. Please provide payment gateway currency alias')]];
        return Helpers::error($error);
        }
        $defualt_currency = Currency::default();

        $user_wallet = UserWallet::auth()->where('currency_id', $defualt_currency->id)->first();

        if(!$user_wallet) {
            $error = ['error'=>[__('User wallet not found')]];
            return Helpers::error($error);
        }
        if($amount < ($payment_gateways_currencies->min_limit/$payment_gateways_currencies->rate) || $amount > ($payment_gateways_currencies->max_limit/$payment_gateways_currencies->rate)) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        try{
            $instance = PaymentGatewayApi::init($request->all())->type(PaymentGatewayConst::TYPEADDMONEY)->gateway()->api()->get();
            if( $instance['distribute'] == "tatumInitApi" ){
                $data = [
                    'gateway_info' =>$instance['response'],
                    'payment_info' =>[
                        'request_amount' => get_amount($instance['amount']->requested_amount,$instance['amount']->default_currency,8),
                        'exchange_rate' => "1".' '.$instance['amount']->default_currency.' = '.get_amount($instance['amount']->sender_cur_rate,$instance['amount']->sender_cur_code,8),
                        'total_charge' => get_amount($instance['amount']->total_charge,$instance['amount']->sender_cur_code,8),
                        'will_get' => get_amount($instance['amount']->will_get,$instance['amount']->default_currency,8),
                        'payable_amount' =>  get_amount($instance['amount']->total_amount,$instance['amount']->sender_cur_code,8),
                    ]
                ];
                $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                return Helpers::success($data,$message);

            }
            $trx = $instance['response']['id']??$instance['response']['trx']??$instance['response']['reference_id']??$instance['response']['order_id']??$instance['response']['temp_identifier']??$instance['response']??'';
            $temData = TemporaryData::where('identifier',$trx)->first();
            if(!$temData){
                $error = ['error'=>["Invalid Request"]];
                return Helpers::error($error);
            }
            $payment_gateway_currency = PaymentGatewayCurrency::where('id', $temData->data->currency)->first();
            $payment_gateway = PaymentGateway::where('id', $temData->data->gateway)->first();
            if($payment_gateway->type == "AUTOMATIC") {
                if($temData->type == PaymentGatewayConst::STRIPE) {
                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate,4).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,4).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,4).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,4).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type' => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias' => $payment_gateway_currency->alias,
                        'identify' => $temData->type,
                        'payment_informations' => $payment_informations,
                        'url' => @$temData->data->response->link."?prefilled_email=".@$user->email,
                        'method' => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data,$message);
                }elseif($temData->type == PaymentGatewayConst::COINGATE) {
                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate,4).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,4).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,4).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,4).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type' => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias' => $payment_gateway_currency->alias,
                        'identify' => $temData->type,
                        'payment_informations' => $payment_informations,
                        'url' => $instance['response']['link'],
                        'method' => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data,$message);
                }elseif($temData->type == PaymentGatewayConst::SSLCOMMERZ) {

                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate,4).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,4).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,4).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,4).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type' => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias' => $payment_gateway_currency->alias,
                        'identify' => $temData->type,
                        'payment_informations' => $payment_informations,
                        'url' => $instance['response']['link'],
                        'method' => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data,$message);
                }else if($temData->type == PaymentGatewayConst::PAYPAL) {

                    $payment_informations =[
                    'trx' =>  $temData->identifier,
                    'gateway_currency_name' =>  $payment_gateway_currency->name,
                    'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                    'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate).' '.$temData->data->amount->sender_cur_code,
                    'total_charge' => getAmount($temData->data->amount->total_charge,2).' '.$temData->data->amount->sender_cur_code,
                    'will_get' => getAmount($temData->data->amount->will_get,2).' '.$temData->data->amount->default_currency,
                    'payable_amount' =>  getAmount($temData->data->amount->total_amount,2).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type' => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias' => $payment_gateway_currency->alias,
                        'identify' => $temData->type,
                        'payment_informations' => $payment_informations,
                        'url' => @$temData->data->response->links,
                        'method' => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data, $message);

                }else if($temData->type == PaymentGatewayConst::FLUTTER_WAVE) {
                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate,4).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,4).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,4).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,4).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type' => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias' => $payment_gateway_currency->alias,
                        'identify' => $temData->type,
                        'payment_informations' => $payment_informations,
                        'url' => @$temData->data->response->link,
                        'method' => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data,$message);
                }else if($temData->type == PaymentGatewayConst::RAZORPAY){
                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate,4).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,4).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,4).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,4).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type'          => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias'                 => $payment_gateway_currency->alias,
                        'identify'              => $temData->type,
                        'payment_informations'  => $payment_informations,
                        'url'                   => $instance['response']['redirect_url'],
                        'method'                => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data,$message);

                }else if($temData->type == PaymentGatewayConst::PAGADITO){
                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate,4).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,4).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,4).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,4).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type' => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias' => $payment_gateway_currency->alias,
                        'identify' => $temData->type,
                        'payment_informations' => $payment_informations,
                        'url' => @$temData->data->response->value,
                        'method' => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data,$message);

                }else if($temData->type == PaymentGatewayConst::PERFECT_MONEY){
                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,4).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate,4).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,4).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,4).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,4).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type'          => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias'                 => $payment_gateway_currency->alias,
                        'identify'              => $temData->type,
                        'payment_informations'  => $payment_informations,
                        'url'                   => $instance['response']['redirect_url'],
                        'method'                => "get",
                    ];
                    $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                    return Helpers::success($data,$message);

                }
            }elseif($payment_gateway->type == "MANUAL"){

                    $payment_informations =[
                        'trx' =>  $temData->identifier,
                        'gateway_currency_name' =>  $payment_gateway_currency->name,
                        'request_amount' => getAmount($temData->data->amount->requested_amount,2).' '.$temData->data->amount->default_currency,
                        'exchange_rate' => "1".' '.$temData->data->amount->default_currency.' = '.getAmount($temData->data->amount->sender_cur_rate).' '.$temData->data->amount->sender_cur_code,
                        'total_charge' => getAmount($temData->data->amount->total_charge,2).' '.$temData->data->amount->sender_cur_code,
                        'will_get' => getAmount($temData->data->amount->will_get,2).' '.$temData->data->amount->default_currency,
                        'payable_amount' =>  getAmount($temData->data->amount->total_amount,2).' '.$temData->data->amount->sender_cur_code,
                    ];
                    $data =[
                        'gateway_type' => $payment_gateway->type,
                        'gateway_currency_name' => $payment_gateway_currency->name,
                        'alias' => $payment_gateway_currency->alias,
                        'identify' => $temData->type,
                        'details' => $payment_gateway->desc??null,
                        'input_fields' => $payment_gateway->input_fields??null,
                        'payment_informations' => $payment_informations,
                        'url' => route('api.manual.payment.confirmed'),
                        'method' => "post",
                        ];
                        $message =  ['success'=>[__('Add Money Inserted Successfully')]];
                        return Helpers::success($data, $message);
            }else{
                $error = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($error);
            }

        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        // return $instance;
    }
    public function success(Request $request, $gateway)
    {
        $requestData = $request->all();
        $token = $requestData['token'] ?? "";
        $checkTempData = TemporaryData::where("type", $gateway)->where("identifier", $token)->first();
        if (!$checkTempData){
            $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
            return Helpers::error($message);
        }

        $checkTempData = $checkTempData->toArray();
        try {
            PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive();
        } catch (Exception $e) {
            $message = ['error' => [__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
        return Helpers::onlysuccess($message);
    }
    public function cancel(Request $request, $gateway)
    {
        $message = ['error' => [__("Something went wrong! Please try again.")]];
        return Helpers::error($message);
    }
    public function flutterwaveCallback()
    {
        $status = request()->status;

        if ($status ==  'successful' || $status == 'completed') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);

            $requestData = request()->tx_ref;

            $token = $requestData;

            $checkTempData = TemporaryData::where("type",'flutterwave')->where("identifier",$token)->first();

            $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];

            if(!$checkTempData) return Helpers::error($message);

            $checkTempData = $checkTempData->toArray();
            try{
                 PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('flutterWave');
            }catch(Exception $e) {
                 $message = ['error' => [__("Something went wrong! Please try again.")]];
                 return Helpers::error($message);
            }
             $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
             return Helpers::onlysuccess($message);
        }
        elseif ($status ==  'cancelled'){
             $message = ['error' => [__('Add money cancelled')]];
             return  Helpers::error($message);
        }
        else{
             $message = ['error' => [__("Transaction failed")]];
             return Helpers::error($message);
        }
    }
     //stripe success
     public function stripePaymentSuccess($trx){
        $token = $trx;
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::STRIPE)->where("identifier",$token)->first();
        $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];

        if(!$checkTempData) return Helpers::error($message);
        $checkTempData = $checkTempData->toArray();
        try{
            PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('stripe');
        }catch(Exception $e) {
            $message = ['error' => [__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
        return Helpers::onlysuccess($message);

    }
    //sslcommerz success
    public function sllCommerzSuccess(Request $request){
        $data = $request->all();
        $token = $data['tran_id'];
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::SSLCOMMERZ)->where("identifier",$token)->first();
        $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
        if(!$checkTempData) return Helpers::error($message);
        $checkTempData = $checkTempData->toArray();

        $creator_table = $checkTempData['data']->creator_table ?? null;
        $creator_id = $checkTempData['data']->creator_id ?? null;
        $creator_guard = $checkTempData['data']->creator_guard ?? null;
        $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
        if($creator_table != null && $creator_id != null && $creator_guard != null) {
            if(!array_key_exists($creator_guard,$api_authenticated_guards)) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
            $creator = DB::table($creator_table)->where("id",$creator_id)->first();
            if(!$creator) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
            $api_user_login_guard = $api_authenticated_guards[$creator_guard];
            Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
        }
        if( $data['status'] != "VALID"){
            $message = ['error' => [__("Added Money Failed")]];
            return Helpers::error($message);
        }
        try{
            PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('sslcommerz');
        }catch(Exception $e) {
            $message = ['error' => [__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
        return Helpers::onlysuccess($message);
    }
    //sslCommerz fails
    public function sllCommerzFails(Request $request){
        $data = $request->all();

        $token = $data['tran_id'];
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::SSLCOMMERZ)->where("identifier",$token)->first();
        $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
        if(!$checkTempData) return Helpers::error($message);
        $checkTempData = $checkTempData->toArray();

        $creator_table = $checkTempData['data']->creator_table ?? null;
        $creator_id = $checkTempData['data']->creator_id ?? null;
        $creator_guard = $checkTempData['data']->creator_guard ?? null;

        $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
        if($creator_table != null && $creator_id != null && $creator_guard != null) {
            if(!array_key_exists($creator_guard,$api_authenticated_guards)) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
            $creator = DB::table($creator_table)->where("id",$creator_id)->first();
            if(!$creator) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
            $api_user_login_guard = $api_authenticated_guards[$creator_guard];
            Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
        }
        if( $data['status'] == "FAILED"){
            TemporaryData::destroy($checkTempData['id']);
            $message = ['error' => [__("Added Money Failed")]];
            return Helpers::error($message);
        }

    }
    //sslCommerz canceled
    public function sllCommerzCancel(Request $request){
        $data = $request->all();
        $token = $data['tran_id'];
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::SSLCOMMERZ)->where("identifier",$token)->first();
        $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
        if(!$checkTempData) return Helpers::error($message);
        $checkTempData = $checkTempData->toArray();


        $creator_table = $checkTempData['data']->creator_table ?? null;
        $creator_id = $checkTempData['data']->creator_id ?? null;
        $creator_guard = $checkTempData['data']->creator_guard ?? null;

        $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
        if($creator_table != null && $creator_id != null && $creator_guard != null) {
            if(!array_key_exists($creator_guard,$api_authenticated_guards)) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
            $creator = DB::table($creator_table)->where("id",$creator_id)->first();
            if(!$creator) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
            $api_user_login_guard = $api_authenticated_guards[$creator_guard];
            Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
        }
        if( $data['status'] != "VALID"){
            TemporaryData::destroy($checkTempData['id']);
            $message = ['error' => [__('Add money cancelled')]];
            return Helpers::error($message);
        }
    }
    //coingate
    public function coinGateSuccess(Request $request, $gateway){
        try{
            $token = $request->token;
            $checkTempData = TemporaryData::where("type",PaymentGatewayConst::COINGATE)->where("identifier",$token)->first();

            if(Transaction::where('callback_ref', $token)->exists()) {
                if(!$checkTempData){
                    $message = ['error' => [__('Transaction request sended successfully!')]];
                    return Helpers::error($message);
                }
            }else {
                if(!$checkTempData){
                    $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
                    return Helpers::error($message);
                }
            }
            $update_temp_data = json_decode(json_encode($checkTempData->data),true);
            $update_temp_data['callback_data']  = $request->all();
            $checkTempData->update([
                'data'  => $update_temp_data,
            ]);
            $temp_data = $checkTempData->toArray();
            PaymentGatewayApi::init($temp_data)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('coingate');
        }catch(Exception $e) {
            $message = ['error' => [__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
        return Helpers::onlysuccess($message);
    }
    public function coinGateCancel(Request $request, $gateway){
        if($request->has('token')) {
            $identifier = $request->token;
            if($temp_data = TemporaryData::where('identifier', $identifier)->first()) {
                $temp_data->delete();
            }
        }
        $message = ['success' => [__('Add money cancelled')]];
        return Helpers::onlysuccess($message);
    }
    public function cryptoPaymentConfirm(Request $request, $trx_id)
    {
        $transaction = Transaction::where('trx_id',$trx_id)->where('status', PaymentGatewayConst::STATUSWAITING)->first();
        if(!$transaction){
            $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
            return Helpers::error($message);
        }
        $user =  $transaction->user;
        $gateway_currency =  $transaction->currency->alias;
        $request_data = $request->merge([
            'currency' => $gateway_currency,
            'amount' => $transaction->request_amount,
        ]);
        $output = PaymentGatewayHelper::init($request_data->all())->type(PaymentGatewayConst::TYPEADDMONEY)->gateway()->get();


        $dy_input_fields = $transaction->details->payment_info->requirements ?? [];
        $validation_rules = $this->generateValidationRules($dy_input_fields);


        $validator = [];
        if(count($validation_rules) > 0) {
            $validator = Validator::make($request->all(), $validation_rules);
            if($validator->fails()){
                $error =  ['error'=>$validator->errors()->all()];
                return Helpers::validation($error);
            }
            $validated =  $validator->validate();
        }

        if(!isset($validated['txn_hash'])){
            $message = ['error' => [__('Transaction hash is required for verify')]];
            return Helpers::error($message);
        }

        $receiver_address = $transaction->details->payment_info->receiver_address ?? "";

        // check hash is valid or not
        $crypto_transaction = CryptoTransaction::where('txn_hash', $validated['txn_hash'])
                                                ->where('receiver_address', $receiver_address)
                                                ->where('asset',$transaction->gateway_currency->currency_code)
                                                ->where(function($query) {
                                                    return $query->where('transaction_type',"Native")
                                                                ->orWhere('transaction_type', "native");
                                                })
                                                ->where('status',PaymentGatewayConst::NOT_USED)
                                                ->first();

        if(!$crypto_transaction){
            $message = ['error' => [__('Transaction hash is not valid! Please input a valid hash')]];
            return Helpers::error($message);
        }

        if($crypto_transaction->amount >= $transaction->total_payable == false) {
            if(!$crypto_transaction){
                $message = ['error' => [__("Insufficient amount added. Please contact with system administrator")]];
                return Helpers::error($message);
            }
        }

        DB::beginTransaction();
        try{

            // Update user wallet balance
            DB::table($transaction->creator_wallet->getTable())
                ->where('id',$transaction->creator_wallet->id)
                ->increment('balance',$transaction->request_amount);

            // update crypto transaction as used
            DB::table($crypto_transaction->getTable())->where('id', $crypto_transaction->id)->update([
                'status'        => PaymentGatewayConst::USED,
            ]);

            // update transaction status
            $transaction_details = json_decode(json_encode($transaction->details), true);
            $transaction_details['payment_info']['txn_hash'] = $validated['txn_hash'];

            DB::table($transaction->getTable())->where('id', $transaction->id)->update([
                'details'       => json_encode($transaction_details),
                'status'        => PaymentGatewayConst::STATUSSUCCESS,
                'available_balance'        => $transaction->available_balance + $transaction->request_amount,
            ]);
            //notification
            $notification_content = [
                'title'         => __("Add Money"),
                'message'       => __("Your Wallet")." (".$output['wallet']->currency->code.")  ".__("balance  has been added")." ".$output['amount']->requested_amount.' '. $output['wallet']->currency->code,
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'user_id'  =>  $user->id,
                'message'   => $notification_content,
            ]);

          //admin notification
          $this->adminNotification($trx_id,$output,PaymentGatewayConst::STATUSSUCCESS);

        }catch(Exception $e) {
            DB::rollback();
            $message = ['error' => [__('Something went wrong! Please try again')]];
            return Helpers::error($message);
        }

        $message = ['success' => [__('Payment Confirmation Success')]];
        return Helpers::onlysuccess($message);
    }

    public function redirectBtnPay(Request $request, $gateway)
    {
        try{
            return PaymentGatewayApi::init([])->handleBtnPay($gateway, $request->all());
        }catch(Exception $e) {
            $message = ['error' => [$e->getMessage()]];
            return Helpers::error($message);
        }
    }
    public function successGlobal(Request $request, $gateway){
        try{
            $token = PaymentGatewayApi::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();

            if(!$temp_data) {
                if(Transaction::where('callback_ref',$token)->exists()) {
                    $message = ['error' => [__('Transaction request sended successfully!')]];
                    return Helpers::error($message);
                }else {
                    $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
                    return Helpers::error($message);
                }
            }

            $update_temp_data = json_decode(json_encode($temp_data->data),true);
            $update_temp_data['callback_data']  = $request->all();
            $temp_data->update([
                'data'  => $update_temp_data,
            ]);
            $temp_data = $temp_data->toArray();
            $instance = PaymentGatewayApi::init($temp_data)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive($temp_data['type']);

            // return $instance;
        }catch(Exception $e) {
            $message = ['error' => [$e->getMessage()]];
            return Helpers::error($message);
        }
        $message = ['success' => [__('Successfully Added Money')]];
        return Helpers::onlysuccess($message);
    }
    public function cancelGlobal(Request $request,$gateway) {
        $token = PaymentGatewayApi::getToken($request->all(),$gateway);
        $temp_data = TemporaryData::where("identifier",$token)->first();
        try{
            if($temp_data != null) {
                $temp_data->delete();
            }
        }catch(Exception $e) {
            // Handel error
        }
        $message = ['success' => [__('Added Money Canceled Successfully')]];
        return Helpers::error($message);
    }
    public function postSuccess(Request $request, $gateway)
    {
        try{
            $token = PaymentGatewayApi::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
            if($temp_data && $temp_data->data->creator_guard != 'api') {
                Auth::guard($temp_data->data->creator_guard)->loginUsingId($temp_data->data->creator_id);
            }
        }catch(Exception $e) {
            $message = ['error' => [$e->getMessage()]];
            return Helpers::error($message);
        }

        return $this->successGlobal($request, $gateway);
    }
    public function postCancel(Request $request, $gateway)
    {
        try{
            $token = PaymentGatewayApi::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
            if($temp_data && $temp_data->data->creator_guard != 'api') {
                Auth::guard($temp_data->data->creator_guard)->loginUsingId($temp_data->data->creator_id);
            }
        }catch(Exception $e) {
            $message = ['error' => [$e->getMessage()]];
            return Helpers::error($message);
        }

        return $this->cancelGlobal($request, $gateway);
    }

}
