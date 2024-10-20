<?php
namespace App\Http\Helpers;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\TemporaryData;
use App\Models\PaymentLink;
use App\Models\Admin\ExchangeRate;
use Illuminate\Support\Facades\DB;
use App\Traits\PaymentGateway\Paypal;
use App\Traits\PaymentGateway\Stripe;
use Illuminate\Support\Facades\Route;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\Currency;
use App\Traits\PaymentGateway\CoinGate;
use App\Traits\PaymentGateway\RazorTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Traits\PaymentGateway\PagaditoTrait;
use App\Traits\PaymentGateway\SslcommerzTrait;
use Illuminate\Validation\ValidationException;
use App\Models\Admin\PaymentGateway as PaymentGatewayModel;
use App\Models\Merchants\MerchantWallet;
use App\Traits\PaymentGateway\FlutterwaveTrait;
use App\Traits\PaymentGateway\PerfectMoney;

class PayLinkPaymentGateway {

    use Paypal,
        Stripe,
        FlutterwaveTrait,
        SslcommerzTrait,
        RazorTrait,
        PagaditoTrait,
        CoinGate,
        PerfectMoney;

    protected $request_data;
    protected $output;
    protected $currency_input_name = "currency";
    protected $amount_input = "amount";
    protected $target = "target";
    protected $email = "email";
    protected $full_name = "full_name";
    protected $phone = "phone";
    protected $predefined_user_wallet;
    protected $predefined_guard;
    protected $predefined_user;

    public function __construct(array $request_data)
    {
        $this->request_data = $request_data;
    }

    public static function init(array $data) {
        return new PayLinkPaymentGateway($data);
    }

    public function payLinkGateway() {
        $request_data = $this->request_data;
        if(empty($request_data)) throw new Exception("Gateway Information is not available. Please provide payment gateway currency alias");
        $validated = $this->validator($request_data)->validate();
        $this->output['validated'] = $validated;
        $gateway_currency = PaymentGatewayCurrency::where("alias",$validated[$this->currency_input_name])->first();
        if(!$gateway_currency || !$gateway_currency->gateway) {
            throw ValidationException::withMessages([
                $this->currency_input_name = "Gateway not available",
            ]);
        }

        if($this->output['type'] == PaymentGatewayConst::TYPEPAYLINK){
            $data = PaymentLink::with('user','merchant')->find($validated['target']);
            $this->output['payment_link']       = $data;
        }
        if($this->output['payment_link']->user != null){
            $receiver_wallet = UserWallet::with('user','currency')->where('user_id',$this->output['payment_link']->user->id)->first();
            $userType = "USER";
            $userGuard = "web";
        }elseif($this->output['payment_link']->merchant != null){
            $receiver_wallet = MerchantWallet::with('merchant','currency')->where('merchant_id',$this->output['payment_link']->merchant->id)->first();
            $userType = "MERCHANT";
            $userGuard = "merchant";
        }
        $user_wallet = $receiver_wallet;
        if(!$user_wallet) {
            throw ValidationException::withMessages([
                $this->currency_input_name = "User wallet not found!",
            ]);
        }
        $sender_currency = Currency::where('code', $this->output['payment_link']->currency)->where('name', $this->output['payment_link']->currency_name)->first();


        $this->output['sender_currency']    = $sender_currency;
        $this->output['receiver_wallet']    = $user_wallet;
        $this->output['gateway']            = $gateway_currency->gateway;
        $this->output['currency']           = $gateway_currency;
        $this->output['charge_calculation'] = $this->amount();
        $this->output['user_type']          = $userType;
        $this->output['user_guard']         = $userGuard;
        $this->output['wallet']             = $user_wallet;
        $this->output['distribute']         = $this->gatewayDistribute($gateway_currency->gateway);

        // limit validation
        // $this->limitValidation($this->output);

        return $this;
    }

    public function validator($data) {
        return Validator::make($data,[
            $this->currency_input_name => "required|exists:payment_gateway_currencies,alias",
            $this->amount_input        => "required|numeric",
            $this->target              => 'required|numeric',
            $this->email               => 'required|email',
            $this->full_name           => 'required|string',
            $this->phone               => 'required|numeric',
        ]);
    }

    public function limitValidation($output) {

        $gateway_currency = $output['currency'];
        $requested_amount = $output['charge_calculation']['requested_amount'];

        if(($requested_amount < $gateway_currency->min_limit) || ($requested_amount > $gateway_currency->max_limit)) {
            throw ValidationException::withMessages([
                $this->amount_input = "Please follow the transaction limit",
            ]);
        }
    }

    public function get() {
        return $this->output;
    }

    public function gatewayDistribute($gateway = null) {
        if(!$gateway) $gateway = $this->output['gateway'];
        $alias = Str::lower($gateway->alias);
        if($gateway->type == PaymentGatewayConst::AUTOMATIC){
            $method = PaymentGatewayConst::register($alias);
        }elseif($gateway->type == PaymentGatewayConst::MANUAL){
            $method = PaymentGatewayConst::register(strtolower($gateway->type));
        }
        if(method_exists($this,$method)) {
            return $method;
        }
        return throw new Exception("Gateway(".$gateway->name.") Trait or Method (".$method."()) does not exists");
    }

    public function amount() {
        $currency = $this->output['currency'] ?? null;
        if(!$currency) throw new Exception("Gateway currency not found");
        return $this->chargeCalculate($currency);
    }

    public function chargeCalculate($currency) {

        $request_amount = $this->request_data[$this->amount_input];

        if($this->output['type'] == PaymentGatewayConst::TYPEPAYLINK){
            $data = PaymentLink::find($this->request_data[$this->target]);
            if(!empty($data->price)){
                $amount = $data->price * $data->qty;
            }else{
                if($data->limit == 1){
                    if($request_amount < $data->min_amount || $request_amount > $data->max_amount){
                        throw new Exception("Please Follow The Transaction Limit!");
                    }else{
                        $amount = $request_amount;
                    }
                }else{
                    $amount = $request_amount;
                }
            }
        }
        $receiver_currency = Currency::where('code',  $data->currency)->first();

        if(empty($receiver_currency)){
            return back()->with(['error' => ['Receiver currency not found!']]);
        }

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

        $fixed_charge_calc = ($fixed_charges);
        $percent_charge_calc = (($amount / 100 ) * $percent_charges );
        $total_charge = $fixed_charge_calc + $percent_charge_calc;
        $payable            = $amount - $total_charge;

        $conversion_charge       = conversionAmountCalculation($total_charge, $sender_currency_rate, $receiver_currency->rate);
        $conversion_payable      = conversionAmountCalculation($payable, $sender_currency_rate ,$receiver_currency->rate);
        $total_conversion        = conversionAmountCalculation($amount, $sender_currency_rate ,$receiver_currency->rate);
        $exchange_rate           = conversionAmountCalculation(1, $receiver_currency->rate, $sender_currency_rate);
        $conversion_admin_charge = $total_charge / $sender_currency_rate;

        $data = [
            'requested_amount'       => $amount,
            'request_amount_admin'   => $amount / $sender_currency_rate,
            'fixed_charge'           => $fixed_charge_calc,
            'percent_charge'         => $percent_charge_calc,
            'total_charge'           => $total_charge,
            'conversion_charge'      => $conversion_charge,
            'conversion_admin_charge'=> $conversion_admin_charge,
            'payable'                => $payable,
            'conversion_payable'     => $conversion_payable,
            'exchange_rate'          => $exchange_rate,
            'sender_cur_code'        => $data->currency,
            'receiver_currency_code' => $receiver_currency->code,
            'base_currency_code'     => get_default_currency_code(),
        ];

        return $data;
    }

    public function render() {
        $output = $this->output;

        if(!is_array($output)) throw new Exception("Render Failed! Please call with valid gateway/credentials");

        $common_keys = ['gateway','currency','amount','distribute'];
        foreach($output as $key => $item) {
            if(!array_key_exists($key,$common_keys)) {
                $this->payLinkGateway();
                break;
            }
        }

        $distributeMethod = $this->output['distribute'];
        return $this->$distributeMethod($output) ?? throw new Exception("Something went worng! Please try again.");
    }

    public function authenticateTempData()
    {
        $tempData = $this->request_data;
        if(empty($tempData) || empty($tempData['type'])) throw new Exception(__("Transaction Failed. Record didn\'t saved properly. Please try again"));

        $currency_id = $tempData['data']->currency ?? "";
        $gateway_currency = PaymentGatewayCurrency::find($currency_id);
        if(!$gateway_currency) throw new Exception('Transaction Failed. Gateway currency not available.');
        $requested_amount = $tempData['data']->charge_calculation->requested_amount ?? 0;

        $validator_data = [
            $this->currency_input_name => $gateway_currency->alias,
            $this->amount_input        => $requested_amount,
            $this->target              => $tempData['data']->validated->target,
            $this->email               => $tempData['data']->validated->email,
            $this->full_name           => $tempData['data']->validated->full_name,
            $this->phone               => $tempData['data']->validated->phone,
        ];

        $this->request_data = $validator_data;

        $this->payLinkGateway();
        $this->output['tempData'] = $tempData;
    }

    public function responseReceive($type = null) {
        $tempData = $this->request_data;
        if(empty($tempData) || empty($tempData['type'])) throw new Exception('Transaction failed. Record didn\'t saved properly. Please try again.');

        if($tempData['type'] == PaymentGatewayConst::PERFECT_MONEY){
            $method_name = "perfectmoneySuccess";
        }else{
            $method_name = $tempData['type']."Success";
        }
        $currency_id = $tempData['data']->currency ?? "";
        $gateway_currency = PaymentGatewayCurrency::find($currency_id);
        if(!$gateway_currency) throw new Exception('Transaction failed. Gateway currency not available.');
        $requested_amount = $tempData['data']->charge_calculation->requested_amount ?? 0;

        $validator_data = [
            $this->currency_input_name => $gateway_currency->alias,
            $this->amount_input        => $requested_amount,
            $this->target              => $tempData['data']->validated->target,
            $this->email               => $tempData['data']->validated->email,
            $this->full_name           => $tempData['data']->validated->full_name,
            $this->phone               => $tempData['data']->validated->phone,
        ];

        $this->request_data = $validator_data;

        $this->payLinkGateway();
        $this->output['tempData'] = $tempData;

        if($type == 'stripe'){
            if(method_exists(Stripe::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'flutterWave'){
            if(method_exists(FlutterwaveTrait::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'sslcommerz'){
            if(method_exists(SslcommerzTrait::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'razorpay'){
            if(method_exists(RazorTrait::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'pagadito'){
            if(method_exists(PagaditoTrait::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'coingate'){
            if(method_exists(CoinGate::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'perfect-money'){
            if(method_exists(PerfectMoney::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }else{
            if(method_exists(Paypal::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }

        throw new Exception("Response method ".$method_name."() does not exists.");
    }

    public function setUrlParams(string $url_params) {
        $output = $this->output;
        if(!$output) return throw new Exception("Something went wrong! Gateway render failed. Please call gateway() method before calling api() method");
        if(isset($output['url_params'])) {
            // if already param has
            $params = $this->output['url_params'];
            $update_params = $params . "&" . $url_params;
            $this->output['url_params'] = $update_params; // Update/ reassign URL Parameters
        }else {
            $this->output['url_params']  = $url_params; // add new URL Parameters;
        }
    }

    public function getUrlParams() {
        $output = $this->output;
        if(!$output || !isset($output['url_params'])) $params = "";
        $params = $output['url_params'] ?? "";
        return $params;
    }

    public function getRedirection() {
        $redirection = PaymentGatewayConst::registerRedirection();
        $guard = get_auth_guard();
        $output = $this->output;
        if($output['type'] === PaymentGatewayConst::TYPEPAYLINK){
            $guard = 'pay-link';
            if(!array_key_exists($guard,$redirection)) {
                throw new Exception("Gateway Redirection URLs/Route Not Registered. Please Register in PaymentGatewayConst::class");
            }
        }else{
            if(!array_key_exists($guard,$redirection)) {
                throw new Exception("Gateway Redirection URLs/Route Not Registered. Please Register in PaymentGatewayConst::class");
            }
        }
        $gateway_redirect_route = $redirection[$guard];
        return $gateway_redirect_route;
    }

    public function setGatewayRoute($route_name, $gateway, $params = null) {
        if(!Route::has($route_name)) return throw new Exception('Route name ('.$route_name.') is not defined');
        if($params) {
            return route($route_name,$gateway."?".$params);
        }
        return route($route_name,$gateway);
    }

    public function getUserWallet($gateway_currency) {

        if($this->predefined_user_wallet) return $this->predefined_user_wallet;

        $guard = get_auth_guard();
        $register_wallets = PaymentGatewayConst::registerWallet();
        if(!array_key_exists($guard,$register_wallets)) {
            throw new Exception("Wallet Not Registered. Please register user wallet in PaymentGatewayConst::class with user guard name");
        }
        $wallet_model = $register_wallets[$guard];
        $user_wallet = $wallet_model::auth()->whereHas("currency",function($q) use ($gateway_currency){
            $q->where("code",$gateway_currency->code);
        })->first();

        if(!$user_wallet) {
            if(request()->acceptsJson()) throw new Exception(__("User wallet not found!"));
            throw ValidationException::withMessages([
                $this->currency_input_name = __("User wallet not found!"),
            ]);
        }

        return $user_wallet;
    }

    public function type($type) {
        $this->output['type']  = $type;
        return $this;
    }
    public function requestIsApiUser() {
        $request_source = request()->get('r-source');
        if($request_source != null && $request_source == PaymentGatewayConst::APP) return true;
        return false;
    }

    public static function getValueFromGatewayCredentials($gateway, $keywords) {
        $result = "";
        $outer_break = false;
        foreach($keywords as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = PayLinkPaymentGateway::makePlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = PayLinkPaymentGateway::makePlainText($label);

                if($label == $modify_item) {
                    $result = $gatewayInput->value ?? "";
                    $outer_break = true;
                    break;
                }
            }
        }
        return $result;
    }
    public static function makePlainText($string) {
        $string = Str::lower($string);
        return preg_replace("/[^A-Za-z0-9]/","",$string);
    }

    public function api() {
        $output               = $this->output;
        $output['distribute'] = $this->gatewayDistribute() . "Api";
        $method               = $output['distribute'];
        $response             = $this->$method($output);
        $output['response']   = $response;
        $this->output         = $output;
        return $this;
    }


    public function setSource(string $source) {
        $sources = [
            'r-source'  => $source,
        ];

        return $sources;
    }

    public function makeUrlParams(array $sources) {
        try{
            $params = http_build_query($sources);
        }catch(Exception $e) {
            throw new Exception("Something went wrong! Failed to make URL Params.");
        }
        return $params;
    }

    public function handleCallback($reference,$callback_data,$gateway_name) {
        if($reference == PaymentGatewayConst::CALLBACK_HANDLE_INTERNAL) {
            $gateway = PaymentGatewayModel::gateway($gateway_name)->first();
            $callback_response_receive_method = $this->getCallbackResponseMethod($gateway);
            return $this->$callback_response_receive_method($callback_data, $gateway);
        }
        $transaction = Transaction::where('callback_ref',$reference)->first();
        $this->output['callback_ref']       = $reference;
        $this->output['capture']            = $callback_data;
        if($transaction) {
            $gateway_currency = $transaction->gateway_currency;
            $gateway = $gateway_currency->gateway;
            $requested_amount = $transaction->request_amount;
            $validator_data = [
                $this->currency_input_name => $gateway_currency->alias,
                $this->amount_input        => $requested_amount,
                $this->target              => $transaction->details->validated->target,
                $this->email               => $transaction->details->validated->email,
                $this->full_name           => $transaction->details->validated->full_name,
                $this->phone               => $transaction->details->validated->phone,
            ];
            $this->output['transaction']    = $transaction;
        }else {
            // find reference on temp table
            $tempData = TemporaryData::where('identifier',$reference)->first();
            if($tempData) {
                $gateway_currency_id = $tempData->data->currency ?? null;
                $gateway_currency = PaymentGatewayCurrency::find($gateway_currency_id);
                if($gateway_currency) {
                    $gateway = $gateway_currency->gateway;
                    $requested_amount = $tempData['data']->charge_calculation->requested_amount ?? 0;
                    $validator_data = [
                        $this->currency_input_name  => $gateway_currency->alias,
                        $this->amount_input         => $requested_amount,
                        $this->target               => $tempData['data']->validated->target,
                        $this->email                => $tempData['data']->validated->email,
                        $this->full_name            => $tempData['data']->validated->full_name,
                        $this->phone                => $tempData['data']->validated->phone,
                    ];
                    $this->output['tempData'] = $tempData;
                }
            }
        }
        if(isset($gateway)) {
            $this->request_data = $validator_data;
            $this->payLinkGateway();
            $callback_response_receive_method = $this->getCallbackResponseMethod($gateway);
            return $this->$callback_response_receive_method($reference, $callback_data, $this->output);
        }
        logger(__("Gateway not found") , [
            "reference"     => $reference,
        ]);
    }

    public function getCallbackResponseMethod($gateway) {
        $gateway_is = PaymentGatewayConst::registerGatewayRecognization();
        foreach($gateway_is as $method => $gateway_name) {
            if(method_exists($this,$method)) {
                if($this->$method($gateway)) {
                    return $this->generateCallbackMethodName($gateway_name);
                    break;
                }
            }
        }

    }

    public function generateCallbackMethodName(string $name) {
        if($name == 'perfect-money'){
            $name = 'perfectmoney';
        }
        return $name . "CallbackResponse";
    }
    public function generateSuccessMethodName(string $name) {
        return $name . "Success";
    }
    public function searchWithReferenceInTransaction($reference) {
        $transaction = DB::table('transactions')->where('callback_ref',$reference)->first();
        if($transaction) {
            return $transaction;
        }
        return false;
    }

    public function generateLinkForRedirectForm($token, $gateway)
    {
        $redirection = $this->getRedirection();
        $form_redirect_route = $redirection['redirect_form'];
        return route($form_redirect_route, [$gateway, 'token' => $token]);
    }

    public static function getToken(array $response, string $gateway) {
        switch($gateway) {
            case PaymentGatewayConst::PERFECT_MONEY:
                return $response['PAYMENT_ID'] ?? "";
                break;
            case PaymentGatewayConst::RAZORPAY:
                return $response['token'] ?? "";
                break;
            default:
                throw new Exception("Oops! Gateway not registered in getToken method");
        }
        throw new Exception("Gateway token not found!");
    }

    function removeSpacialChar($string, $replace_string = "") {
        return preg_replace("/[^A-Za-z0-9]/",$replace_string,$string);
    }

    /**
     * Link generation for button pay (JS checkout)
     */
    public function generateLinkForBtnPay($token, $gateway)
    {
        $redirection = $this->getRedirection();
        $form_redirect_route = $redirection['btn_pay'];
        return route($form_redirect_route, [$gateway, 'token' => $token]);
    }
    public function generateBtnPayResponseMethod(string $gateway)
    {
        $name = $this->removeSpacialChar($gateway,"");
        return $name . "BtnPay";
    }

    /**
     * Handle Button Pay (JS Checkout) Redirection
     */
    public function handleBtnPay($gateway, $request_data)
    {
        if(!array_key_exists('token', $request_data)) throw new Exception("Requested with invalid token");
        $temp_token = $request_data['token'];
        $temp_data = TemporaryData::where('identifier', $temp_token)->first();
        if(!$temp_data) throw new Exception("Requested with invalid token");
        $this->request_data = $temp_data->toArray();
        $this->authenticateTempData();
        $method = $this->generateBtnPayResponseMethod($gateway);
        if(method_exists($this, $method)) {
            return $this->$method($temp_data);
        }
        throw new Exception("Button Pay response method [" . $method ."()] not available in this gateway");
    }


}
