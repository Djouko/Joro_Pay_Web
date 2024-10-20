<?php

namespace App\Http\Controllers\PaymentGateway\QrPay\v1;

use Exception;
use App\Models\UserWallet;
use App\Traits\Transaction;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Merchants\SandboxWallet;
use App\Models\Merchants\MerchantWallet;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\MerchantConfiguration;
use App\Models\Merchants\GatewaySetting;
use App\Models\Merchants\PaymentOrderRequest;
use App\Models\StripeVirtualCard;
use App\Models\SudoVirtualCard;
use App\Models\VirtualCard;
use App\Notifications\PaymentGateway\PaymentVerification;
use Stripe\Charge;
use Stripe\Stripe as StripePackage;
use Stripe\Token;


class PaymentController extends Controller
{

    use Transaction;

    protected $access_token_expire_time = 600;
    protected $test_email_verification_code = 123456;
    protected $testuser_email = "sandbox@appdevs.net";
    protected $testuser_username = "appdevs";

    public function paymentCreate(Request $request) {
        $access_token = $request->bearerToken();
        if(!$access_token) return Response::paymentApiError([__('Access denied! Token not found')],[],403);

        $request_record = PaymentOrderRequest::where('access_token',$access_token)->first();
        if(!$request_record) return Response::paymentApiError([__('Requested with invalid token!')],[],403);

        if(Carbon::now() > $request_record->created_at->addSeconds($this->access_token_expire_time)) {
            try{
                $request_record->update([
                    'status'    => PaymentGatewayConst::EXPIRED,
                ]);
            }catch(Exception $e) {
                return Response::paymentApiError([__("Failed to create payment! Please try again")],[],500);
            }
        }

        if($request_record->status == PaymentGatewayConst::EXPIRED) return Response::paymentApiError([__('Request token is expired')],[],401);

        if($request_record->status != PaymentGatewayConst::CREATED) return Response::paymentApiError([__('Requested with invalid token!')],[],400);

        $validator = Validator::make($request->all(),[
            'custom'        => 'nullable|string|max:255',
            'amount'        => 'required|string|numeric|gt:0',
            'currency'      => 'required|string|exists:currencies,code',
            'return_url'    => 'required|string|url',
            'cancel_url'    => 'required|string|url',
        ]);

        if($validator->fails()) return Response::paymentApiError($validator->errors()->all(),[],400);
        $validated = $validator->validate();

        $merchant = $request_record->merchant;
        $developer_credentials = $merchant->developerApi;

        if(!$merchant || !$developer_credentials) return Response::paymentApiError([__("Merchant does't exists")]);

        // check request URL is sandbox or production
        if(request()->is("*/sandbox/*")) {
            // Requested with sandbox URL
            if($developer_credentials->mode != PaymentGatewayConst::ENV_SANDBOX) return Response::paymentApiError([__("Requested with invalid credentials")]);
            $payment_url = route('qrpay.pay.sandbox.v1.user.auth.form',$request_record->token);
        }else {
            if($developer_credentials->mode != PaymentGatewayConst::ENV_PRODUCTION) return Response::paymentApiError([__("Requested with invalid credentials")]);
            $payment_url = route('qrpay.pay.v1.user.auth.form',$request_record->token);
        }

        // Update and generate redirect links
        try{
            $request_record->update([
                'amount'        => $validated['amount'],
                'currency'      => $validated['currency'],
                'data'          => [
                    'custom'    => $validated['custom'],
                    'return_url'    => $validated['return_url'],
                    'cancel_url'    => $validated['cancel_url'],
                ],
            ]);
        }catch(Exception $e) {

            return Response::paymentApiError([__("Failed to create payment! Please try again")],[],500);
        }


        return Response::paymentApiSuccess([$request_record->status],[
            'token'         => $request_record->token,
            'payment_url'   => $payment_url,
        ],200);

    }

    public function paymentPreview($token) {

        // common code Start
        $request_record = PaymentOrderRequest::where('token',$token)->first();
        if(!$request_record) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Payment request is invalid!",
                'subtitle'      => "Something went wrong! Go back and try again.",
                'button_text'   => "Home",
                'link'          => url('/'),
                'logo'          => "",
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }

        $merchant_configuration = MerchantConfiguration::first();
        if(!$merchant_configuration) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Payment gateway no longer available",
                'subtitle'      => "",
                'button_text'   => "Cancel and return/Home",
                'link'          => $request_record->data->cancel_url ?? url("/"),
                'logo'          => "",
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }
        $payment_gateway_image = get_image($merchant_configuration->image,'merchant-config');

        if($request_record->authentication != true) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Authentication Failed!",
                'subtitle'      => "You are requested with unauthenticated user. Please make sure you are authenticated",
                'button_text'   => "Got to Login",
                'link'          => route('qrpay.pay.sandbox.v1.user.auth.form',$token),
                'logo'          => $payment_gateway_image,
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }

        $merchant = $request_record->merchant;
        $developer_credentials = $merchant->developerApi;

        if(!$merchant || !$developer_credentials) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Merchant doesn't exists or credentials is invalid!",
                'subtitle'      => "",
                'button_text'   => "Cancel and return/Home",
                'link'          => $request_record->data->cancel_url ?? url("/"),
                'logo'          => "",
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }

        if(Carbon::now() > $request_record->created_at->addSeconds($this->access_token_expire_time)) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Session Expired!",
                'subtitle'      => "Your token session is expired. Go back and try again",
                'button_text'   => "Cancel and return",
                'link'          => $request_record->data->cancel_url ?? url("/"),
                'logo'          => $payment_gateway_image,
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }
        // common code End

        // Check request comes from sandbox or production url
        if(request()->is("*/sandbox/*")) {
            if($developer_credentials->mode != PaymentGatewayConst::ENV_SANDBOX) {
                $page_title = "Process Error";
                $data = [
                    'title'         => "Requested with invalid credentials!",
                    'subtitle'      => "",
                    'button_text'   => "Cancel and return/Home",
                    'link'          => $request_record->data->cancel_url ?? url("/"),
                    'logo'          => "",
                ];
                return view('qrpay-gateway.pages.error',compact('data','page_title'));
            }
            // sandbox request
            $submit_form_url = route('qrpay.pay.sandbox.v1.user.payment.preview.submit',$token);
            $user = (object) [
                'fullname'  => "Test User",
                'payment_type'  => "Sandbox",
                'address'   => [
                    'address'   => "V942+HW4, Dhaka 1230",
                ],
                'image'     => "",
                'wallet'   => [
                    'balance'   => 1000,
                ],
                'virtual_card'   => [
                    'amount'   => 1000,
                ],
            ];
        }else {
            if($developer_credentials->mode != PaymentGatewayConst::ENV_PRODUCTION) {
                $page_title = "Process Error";
                $data = [
                    'title'         => "Requested with invalid credentials!",
                    'subtitle'      => "",
                    'button_text'   => "Cancel and return/Home",
                    'link'          => $request_record->data->cancel_url ?? url("/"),
                    'logo'          => "",
                ];
                return view('qrpay-gateway.pages.error',compact('data','page_title'));
            }
            $submit_form_url = route('qrpay.pay.v1.user.payment.preview.submit',$token);
            $user = $request_record->user;

        }
        $gateway_setting = GatewaySetting::where('merchant_id',$merchant->id)->first();
        $page_title = "Payment Confirm";
        return view('qrpay-gateway.pages.confirm',compact('page_title','merchant_configuration','request_record','user','payment_gateway_image','token','submit_form_url','gateway_setting'));
    }

    public function paymentConfirm(Request $request,$token) {
        $request_record = PaymentOrderRequest::where('token',$token)->first();
        $merchant = $request_record->merchant;

        //start finding trx pay type
        if($request->trx_type == PaymentGatewayConst::WALLET){
            $trx_type = $request->trx_type;
        }elseif($request->trx_type == PaymentGatewayConst::VIRTUAL){
            $trx_type = $request->trx_type;
        }elseif($request->trx_type == PaymentGatewayConst::MASTER){
            $trx_type = $request->trx_type;
            $credentials = GatewaySetting::where('merchant_id', $merchant->id)->first();
            $credentials =$credentials->credentials->secret_key;
            $request->validate([
                'name' => 'required',
                'cardNumber' => 'required',
                'cardExpiry' => 'required',
                'cardCVC' => 'required',
            ]);
            $cc = $request->cardNumber;
            $exp = $request->cardExpiry;
            $cvc = $request->cardCVC;

            $exp = explode("/", $_POST['cardExpiry']);
            $emo = trim($exp[0]);
            $eyr = trim($exp[1]);
            $cnts = round( $request_record->amount, 2) * 100;

            StripePackage::setApiKey(@$credentials);
            StripePackage::setApiVersion("2020-03-02");
            try {
                $token = Token::create(array(
                        "card" => array(
                        "number" => "$cc",
                        "exp_month" => $emo,
                        "exp_year" => $eyr,
                        "cvc" => "$cvc"
                    )
                ));
                try {
                    $charge = Charge::create(array(
                        'card' => $token['id'],
                        'currency' => $request_record->currency,
                        'amount' => $cnts,
                        'description' => 'item',
                    ));

                    // if ($charge['status'] == 'succeeded') {
                    //
                    // }
                } catch (\Exception $e) {

                    return back()->with(['error' => [$e->getMessage()]]);
                }
            } catch (\Exception $e) {
                return back()->with(['error' => [$e->getMessage()]]);
            }

        }
        // common code Start

        if(!$request_record) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Payment request is invalid!",
                'subtitle'      => "Something went wrong! Go back and try again.",
                'button_text'   => "Home",
                'link'          => url('/'),
                'logo'          => "",
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }

        $merchant_configuration = MerchantConfiguration::first();
        if(!$merchant_configuration) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Payment gateway no longer available",
                'subtitle'      => "",
                'button_text'   => "Cancel and return/Home",
                'link'          => $request_record->data->cancel_url ?? url("/"),
                'logo'          => "",
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }
        $payment_gateway_image = get_image($merchant_configuration->image,'merchant-config');

        if($request_record->authentication != true) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Authentication Failed!",
                'subtitle'      => "You are requested with unauthenticated user. Please make sure you are authenticated",
                'button_text'   => "Got to Login",
                'link'          => route('qrpay.pay.sandbox.v1.user.auth.form',$token),
                'logo'          => $payment_gateway_image,
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }


        $developer_credentials = $merchant->developerApi;

        if(Carbon::now() > $request_record->created_at->addSeconds($this->access_token_expire_time)) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Session Expired!",
                'subtitle'      => "Your token session is expired. Go back and try again",
                'button_text'   => "Cancel and return",
                'link'          => $request_record->data->cancel_url ?? url("/"),
                'logo'          => $payment_gateway_image,
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }
        // common code End

        if($merchant_configuration->email_verify == true && (int) $request_record->email_verify == false) {

            if($developer_credentials->mode == PaymentGatewayConst::ENV_SANDBOX) {
                // No need to send mail, redirect verify page

                $request_record->update([
                    'email_code' => $this->test_email_verification_code,
                ]);

            }else {
                // Need to send mail to user email address
                $user = $request_record->user;

                $code = generate_random_code(6);
                $request_record->update([
                    'email_code' => $code,
                ]);

                $data = [
                    'fullname'  => $user->fullname,
                    'username'  => $user->username,
                    'email'     => $user->email,
                    'amount'    => $request_record->amount,
                    'currency'  => $request_record->currency,
                    'code'      => $code,
                ];

                try{
                    $user->notify(new PaymentVerification((object) $data));
                }catch(Exception $e){}

            }

            if(request()->is('*/sandbox/*')) {
                return redirect()->route('qrpay.pay.sandbox.v1.user.auth.mail.verify.form',$token);
            }else {
                return redirect()->route('qrpay.pay.v1.user.auth.mail.verify.form',$token);
            }
        }

        if($request_record->status != PaymentGatewayConst::CREATED) {
            $page_title = "Process Error";
            $data = [
                'title'         => "Payment request is invalid!",
                'subtitle'      => "Something went wrong! Go back and try again.",
                'button_text'   => "Cancel and return",
                'link'          => $request_record->data->cancel_url ?? url("/"),
                'logo'          => $payment_gateway_image,
            ];
            return view('qrpay-gateway.pages.error',compact('data','page_title'));
        }
        if($request_record->merchant->developerApi->mode == PaymentGatewayConst::ENV_SANDBOX) {
            $merchant_wallet = SandboxWallet::where('merchant_id',$merchant->id)->whereHas('currency',function($q) use ($request_record) {
                $q->where('code',$request_record->currency);
            })->where('status',true)->first();
        }else {
            $merchant_wallet = MerchantWallet::where('merchant_id',$merchant->id)->whereHas('currency',function($q) use ($request_record) {
                $q->where('code',$request_record->currency);
            })->where('status',true)->first();
        }
        $charges = [
            'exchange_rate'         => 1,
            'sender_amount'         => $request_record->amount,
            'sender_currency'       => $request_record->currency,
            'receiver_amount'       => $request_record->amount,
            'receiver_currency'     => $request_record->currency,
            'percent_charge'        => 0,
            'fixed_charge'          => 0,
            'total_charge'          => 0,
            'sender_wallet_balance' => 100000, // set demo to testuser/testpayment
            'payable'               => $request_record->amount,
        ];

        // Check request comes from sandbox or production url
        if(request()->is("*/sandbox/*")) {
            // sandbox requested
            if($developer_credentials->mode != PaymentGatewayConst::ENV_SANDBOX) {
                $page_title = "Process Error";
                $data = [
                    'title'         => "Requested with invalid credentials!",
                    'subtitle'      => "",
                    'button_text'   => "Cancel and return/Home",
                    'link'          => $request_record->data->cancel_url ?? url("/"),
                    'logo'          => "",
                ];
                return view('qrpay-gateway.pages.error',compact('data','page_title'));
            }

            // Sandbox transaction
            DB::beginTransaction();
            try{
                $trx_id = generate_unique_string("transactions","trx_id",16);
                // Receiver TRX
                if($trx_type == PaymentGatewayConst::WALLET){
                    $inserted_id = DB::table("transactions")->insert([
                        'merchant_id'       => $merchant_wallet->merchant->id,
                        'sandbox_wallet_id' => $merchant_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $charges['receiver_amount'],
                        'payable'           => $charges['receiver_amount'],
                        'available_balance' => $merchant_wallet->balance + $charges['receiver_amount'],
                        'attribute'         => PaymentGatewayConst::RECEIVED,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $this->testuser_username,
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::WALLET,
                            'env_type'          =>  PaymentGatewayConst::ENV_SANDBOX,
                            'payment_to'        =>  "Demo Shop"
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'created_at'        => now(),
                    ]);

                }elseif($trx_type == PaymentGatewayConst::VIRTUAL){
                    $inserted_id = DB::table("transactions")->insert([
                        'merchant_id'       => $merchant_wallet->merchant->id,
                        'sandbox_wallet_id' => $merchant_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $charges['receiver_amount'],
                        'payable'           => $charges['receiver_amount'],
                        'available_balance' => $merchant_wallet->balance + $charges['receiver_amount'],
                        'attribute'         => PaymentGatewayConst::RECEIVED,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $this->testuser_username,
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::VIRTUAL,
                            'env_type'          =>  PaymentGatewayConst::ENV_SANDBOX,
                            'payment_to'        =>  "Demo Shop"
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'created_at'        => now(),
                    ]);
                }elseif($request->trx_type == PaymentGatewayConst::MASTER){
                    $inserted_id = DB::table("transactions")->insert([
                        'merchant_id'       => $merchant_wallet->merchant->id,
                        'sandbox_wallet_id' => $merchant_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $charges['receiver_amount'],
                        'payable'           => $charges['receiver_amount'],
                        'available_balance' => $merchant_wallet->balance + $charges['receiver_amount'],
                        'attribute'         => PaymentGatewayConst::RECEIVED,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $this->testuser_username,
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::MASTER,
                            'env_type'          =>  PaymentGatewayConst::ENV_SANDBOX,
                            'payment_to'        =>  "Demo Shop"
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'created_at'        => now(),
                    ]);
                }


                $merchant_wallet->balance += $charges['receiver_amount'];
                $merchant_wallet->save();

                $request_record->update([
                    'status'    => PaymentGatewayConst::SUCCESS,
                ]);

                DB::commit();
            }catch(Exception $e) {

                DB::rollBack();
                $http_query = http_build_query([
                    'message'     => [
                        'code'  => 500,
                        'error' => [
                            "Failed to create payment! Please try again",
                        ],
                    ],
                    'data'  => [],
                    'type'  => 'error',
                ]);

                return redirect($request_record->data->return_url."?".$http_query);
            }

            $payer = [
                'username'  => $this->testuser_username,
                'email'     => $this->testuser_email,
            ];

        }else {
            // production requested

            if($developer_credentials->mode != PaymentGatewayConst::ENV_PRODUCTION) {
                $page_title = "Process Error";
                $data = [
                    'title'         => "Requested with invalid credentials!",
                    'subtitle'      => "",
                    'button_text'   => "Cancel and return/Home",
                    'link'          => $request_record->data->cancel_url ?? url("/"),
                    'logo'          => "",
                ];
                return view('qrpay-gateway.pages.error',compact('data','page_title'));
            }

            // Production transaction
            $user = $request_record->user;
            $user_wallet = UserWallet::where('user_id',$user->id)->whereHas('currency',function($q) use ($request_record){
                $q->where('code',$request_record->currency);
            })->where('status',GlobalConst::ACTIVE)->first();
            // check virtual card
            if($developer_credentials->mode != PaymentGatewayConst::ENV_PRODUCTION || $trx_type == PaymentGatewayConst::VIRTUAL) {
                if(virtual_card_system('flutterwave') ){
                    $virtual_card = VirtualCard::where('user_id',$user->id)->where('is_default',true)->first();

                    if( !$virtual_card){
                        $page_title = "Process Error";
                            $data = [
                                'title'         => "Virtual Card Not Found!",
                                'subtitle'      => "You Don't have any virtual card yet",
                                'button_text'   => "Cancel and return",
                                'link'          => $request_record->data->cancel_url ?? url("/"),
                                'logo'          => $payment_gateway_image,
                            ];
                            return view('qrpay-gateway.pages.error',compact('data','page_title'));
                    }
                }elseif(virtual_card_system('sudo') ){
                    $virtual_card = SudoVirtualCard::where('user_id',$user->id)->where('is_default',true)->first();
                    if(!$virtual_card){
                        $page_title = "Process Error";
                        $data = [
                            'title'         => "Virtual Card Not Found!",
                            'subtitle'      => "Virtual Card Not Found Or Your Card Not Have Set As A Default",
                            'button_text'   => "Cancel and return",
                            'link'          => $request_record->data->cancel_url ?? url("/"),
                            'logo'          => $payment_gateway_image,
                        ];
                        return view('qrpay-gateway.pages.error',compact('data','page_title'));
                    }
                }elseif(virtual_card_system('stripe') ){
                    $virtual_card = StripeVirtualCard::where('user_id',$user->id)->where('is_default',true)->first();
                    if(!$virtual_card){
                        $page_title = "Process Error";
                        $data = [
                            'title'         => "Virtual Card Not Found!",
                            'subtitle'      => "Virtual Card Not Found Or Your Card Not Have Set As A Default",
                            'button_text'   => "Cancel and return",
                            'link'          => $request_record->data->cancel_url ?? url("/"),
                            'logo'          => $payment_gateway_image,
                        ];
                        return view('qrpay-gateway.pages.error',compact('data','page_title'));
                    }
                }

                if($charges['payable'] >  $virtual_card->amount){
                    $page_title = "Process Error";
                    $data = [
                        'title'         => "Insufficient balance!",
                        'subtitle'      => "Insufficient Balance on your virtual card",
                        'button_text'   => "Cancel and return",
                        'link'          => $request_record->data->cancel_url ?? url("/"),
                        'logo'          => $payment_gateway_image,
                    ];
                    return view('qrpay-gateway.pages.error',compact('data','page_title'));
                }
             }
            if(!$user_wallet) {
                $page_title = "Process Error";
                $data = [
                    'title'         => "Wallet not found!",
                    'subtitle'      => "Your wallet not available/active with currency (".$request_record->currency.")",
                    'button_text'   => "Cancel and return",
                    'link'          => $request_record->data->cancel_url ?? url("/"),
                    'logo'          => $payment_gateway_image,
                ];
                return view('qrpay-gateway.pages.error',compact('data','page_title'));
            }
            if($trx_type == PaymentGatewayConst::WALLET){
                if($user_wallet->balance < $request_record->amount) {
                    $page_title = "Process Error";
                    $data = [
                        'title'         => "Insufficient balance!",
                        'subtitle'      => "Request failed due to insufficient balance in your wallet",
                        'button_text'   => "Cancel and return",
                        'link'          => $request_record->data->cancel_url ?? url("/"),
                        'logo'          => $payment_gateway_image,
                    ];
                    return view('qrpay-gateway.pages.error',compact('data','page_title'));
                }
            }

            $charges['sender_wallet_balance'] = $user_wallet->balance - $request_record->amount;

            // Create Transactions  for production
            DB::beginTransaction();
            try{
                $trx_id = generate_unique_string("transactions","trx_id",16);
                if($trx_type == PaymentGatewayConst::WALLET){
                    // Sender TRX
                    $inserted_id = DB::table("transactions")->insertGetId([
                        'user_id'           => $user_wallet->user->id,
                        'user_wallet_id'    => $user_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $request_record->amount,
                        'payable'           => $request_record->amount,
                        'available_balance' => $user_wallet->balance - $request_record->amount,
                        'attribute'         => PaymentGatewayConst::SEND,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $user->username??'',
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::WALLET,
                            'env_type'          =>  PaymentGatewayConst::ENV_PRODUCTION,
                            'payment_to'        =>  $merchant_wallet->merchant->business_name??''

                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'remark'            => "",
                        'created_at'        => now(),
                    ]);
                    //make transaction charge
                    $this->insertSenderCharges($charges,$inserted_id);
                    // Receiver TRX
                    DB::table("transactions")->insert([
                        'merchant_id'       => $merchant_wallet->merchant->id,
                        'merchant_wallet_id'=> $merchant_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $charges['receiver_amount'],
                        'payable'           => $charges['receiver_amount'],
                        'available_balance' => $merchant_wallet->balance + $charges['receiver_amount'],
                        'attribute'         => PaymentGatewayConst::RECEIVED,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $user->username??'',
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::WALLET,
                            'env_type'          =>  PaymentGatewayConst::ENV_PRODUCTION,
                            'payment_to'        =>  $merchant_wallet->merchant->business_name??''
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'created_at'        => now(),
                    ]);

                    $this->createTransactionChildRecords($inserted_id,(object) $charges);

                    $user_wallet->balance -= $charges['payable'];
                    $user_wallet->save();

                    $merchant_wallet->balance += $charges['receiver_amount'];
                    $merchant_wallet->save();
                }elseif($trx_type == PaymentGatewayConst::MASTER){
                     // Sender TRX
                     $inserted_id = DB::table("transactions")->insertGetId([
                        'user_id'           => $user_wallet->user->id,
                        'user_wallet_id'    => $user_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $request_record->amount,
                        'payable'           => $request_record->amount,
                        'available_balance' => $user_wallet->balance,
                        'attribute'         => PaymentGatewayConst::SEND,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $user->username??'',
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::MASTER,
                            'env_type'          =>  PaymentGatewayConst::ENV_PRODUCTION,
                            'payment_to'        =>  $merchant_wallet->merchant->business_name??''
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'remark'            => "",
                        'created_at'        => now(),
                    ]);
                    //make transaction charge
                    $this->insertSenderCharges($charges,$inserted_id);
                    // Receiver TRX
                    DB::table("transactions")->insert([
                        'merchant_id'       => $merchant_wallet->merchant->id,
                        'merchant_wallet_id'=> $merchant_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $charges['receiver_amount'],
                        'payable'           => $charges['receiver_amount'],
                        'available_balance' => $merchant_wallet->balance + $charges['receiver_amount'],
                        'attribute'         => PaymentGatewayConst::RECEIVED,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $user->username??'',
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::MASTER,
                            'env_type'          =>  PaymentGatewayConst::ENV_PRODUCTION,
                            'payment_to'        =>  $merchant_wallet->merchant->business_name??''
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'created_at'        => now(),
                    ]);

                    $this->createTransactionChildRecords($inserted_id,(object) $charges);

                    $merchant_wallet->balance += $charges['receiver_amount'];
                    $merchant_wallet->save();

                }elseif($trx_type == PaymentGatewayConst::VIRTUAL){
                    // $virtual_card = VirtualCard::where('user_id',$user->id)->first();

                    $inserted_id = DB::table("transactions")->insertGetId([
                        'user_id'           => $user_wallet->user->id,
                        'user_wallet_id'    => $user_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $request_record->amount,
                        'payable'           => $request_record->amount,
                        'available_balance' => $user_wallet->balance,
                        'attribute'         => PaymentGatewayConst::SEND,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $user->username??'',
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::VIRTUAL,
                            'env_type'          =>  PaymentGatewayConst::ENV_PRODUCTION,
                            'payment_to'        =>  $merchant_wallet->merchant->business_name??'',
                            'virtual_amount'    =>  $virtual_card->amount??0,
                            'virtual_currency'    =>  $virtual_card->currency??''
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'remark'            => "",
                        'created_at'        => now(),
                    ]);
                    //make transaction charge
                    $this->insertSenderCharges($charges,$inserted_id);
                    // Receiver TRX
                    DB::table("transactions")->insert([
                        'merchant_id'       => $merchant_wallet->merchant->id,
                        'merchant_wallet_id'=> $merchant_wallet->id,
                        'type'              => PaymentGatewayConst::MERCHANTPAYMENT,
                        'trx_id'            => $trx_id,
                        'request_amount'    => $charges['receiver_amount'],
                        'payable'           => $charges['receiver_amount'],
                        'available_balance' => $merchant_wallet->balance + $charges['receiver_amount'],
                        'attribute'         => PaymentGatewayConst::RECEIVED,
                        'details'           => json_encode([
                            'receiver_username' => $merchant_wallet->merchant->username,
                            'sender_username'   => $user->username??'',
                            'charges'           => $charges,
                            'pay_type'          =>  PaymentGatewayConst::VIRTUAL,
                            'env_type'          =>  PaymentGatewayConst::ENV_PRODUCTION,
                            'payment_to'        =>  $merchant_wallet->merchant->business_name??'',
                            'virtual_amount'    =>  $virtual_card->amount??0,
                            'virtual_currency'    =>  $virtual_card->currency??'',
                        ]),
                        'status'            => GlobalConst::SUCCESS,
                        'created_at'        => now(),
                    ]);

                    $this->createTransactionChildRecords($inserted_id,(object) $charges);

                    $merchant_wallet->balance += $charges['receiver_amount'];
                    $merchant_wallet->save();

                    //virtual card amount reduce
                    $virtual_card->amount -= $charges['payable'];
                    $virtual_card->save();


                }

                $request_record->update([
                    'status'    => PaymentGatewayConst::SUCCESS,
                ]);

                DB::commit();
            }catch(Exception $e) {

                DB::rollBack();
                $http_query = http_build_query([
                    'message'     => [
                        'code'  => 500,
                        'error' => [
                            "Failed to create payment! Please try again",
                        ],
                    ],
                    'data'  => [],
                    'type'  => 'error',
                ]);

                return redirect($request_record->data->return_url."?".$http_query);
            }

            $payer = [
                'username'  => $user->username,
                'email'     => $user->email,
            ];
        }

        $http_query = http_build_query([
            'message'     => [
                'code'  => 200,
                'success' => [
                    PaymentGatewayConst::SUCCESS,
                ],
            ],
            'data'  => [
                'token'     => $token,
                'trx_id'    => $trx_id,
                'payer'     => $payer,
                'amount'    => $charges['receiver_amount'],
                'custom'    =>$request_record->data->custom??"",
            ],
            'type'  => 'success',
        ]);


        $return_url = $this->getReturnUrl($request_record->data->return_url,$http_query);
        return redirect($return_url);
    }

    public function insertSenderCharges($charges,$id) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges['percent_charge'],
                'fixed_charge'      =>$charges['fixed_charge'],
                'total_charge'      =>$charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function getReturnUrl(string $url, $params) {
        $parse = parse_url($url);
        if(array_key_exists("query",$parse)) {
            // parameter available
            return $url . "&" . $params;
        }
        return $url . "?" . $params;
    }
}
