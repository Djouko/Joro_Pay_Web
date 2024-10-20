<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\UserWallet;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\PaymentLink;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\PayLinkPaymentGateway;
use App\Models\Admin\Currency;
use App\Models\Admin\GatewayAPi;
use App\Models\Admin\PaymentGateway;
use App\Models\Admin\TransactionSetting;
use App\Models\Merchants\MerchantWallet;
use App\Models\TemporaryData;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use App\Traits\PaymentGateway\StripeLinkPayment;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Illuminate\Http\RedirectResponse;
use App\Traits\PayLink\WalletTransactionTrait;

class PaymentLinkController extends Controller
{
    use StripeLinkPayment,WalletTransactionTrait;
    /**
     * Payment link page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function index(){
        $page_title = __('Payment Links');
        $payment_links = PaymentLink::auth()->orderBy('id', 'desc')->paginate(12);
        return view('user.sections.payment-link.index', compact('page_title', 'payment_links'));
    }


    /**
     * Payment link create page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function create(){
        $page_title = __('Payment Link Create');
        try {
            $currency_data = Currency::active()->get();
        } catch (\Exception $th) {
            return back()->with(['error' => [__('Unable to connect with API, Please Contact Support!!')]]);
        }

        return view('user.sections.payment-link.create', compact('page_title','currency_data'));
    }

    /**
     * Payment link store
     *
     * @param Illuminate\Http\Request $request
     * @method POST
     * @return Illuminate\Http\Request
     */
    public function store(Request $request){

        $token = generate_unique_string('payment_links', 'token', 60);

        if($request->type == PaymentGatewayConst::LINK_TYPE_PAY){
            $validator = Validator::make($request->all(), [
                'currency'        => 'required|string',
                'currency_symbol' => 'required|string',
                'country'         => 'required|string',
                'currency_name'   => 'required|string',
                'title'           => 'required|string|max:180',
                'type'            => 'required|string',
                'details'         => 'nullable|string',
                'limit'           => 'nullable',
                'min_amount'      => 'nullable|numeric|min:0.1',
                'max_amount'      => 'nullable|numeric|gt:min_amount',
                'image'           => 'nullable|image|mimes:png,jpg,jpeg,svg,webp',
            ]);

            if($validator->stopOnFirstFailure()->fails()){
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
            $validated = Arr::except($validated, ['image']);
            $validated['limit'] = $request->limit ? 1 : 2;
            $validated['token'] = $token;
            $validated['status'] = 1;
            $validated['user_id'] = Auth::id();

            try {
                $payment_link = PaymentLink::create($validated);

                if($request->hasFile('image')) {
                    try{
                        $image = get_files_from_fileholder($request,'image');
                        $upload_image = upload_files_from_path_dynamic($image,'payment-link-image');
                        $payment_link->update([
                            'image'  => $upload_image,
                        ]);
                    }catch(Exception $e) {
                        return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
                    }
                }
            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

        }else{
            $validator = Validator::make($request->all(), [
                'sub_currency'    => 'required',
                'currency_symbol' => 'required',
                'currency_name'   => 'required',
                'country'         => 'required',
                'sub_title'       => 'required|max:180',
                'type'            => 'required',
                'price'           => 'nullable:numeric',
                'qty'             => 'nullable:integer',
            ]);


            if($validator->fails()){
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
            $validated['currency'] = $validated['sub_currency'];
            $validated['title'] = $validated['sub_title'];
            $validated['token'] = $token;
            $validated['status'] = 1;
            $validated['user_id'] = Auth::id();

            $validated = Arr::except($validated, ['sub_currency','sub_title']);
            try {
                $payment_link = PaymentLink::create($validated);
            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }


        return redirect()->route('user.payment-link.share', $payment_link->id)->with(['success' => [__('payment Link Created Successfully')]]);
    }
    /**
     * Payment link eidt page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function edit($id){
        $page_title = __('Payment Link Edit');

        try {
            $currency_data = Currency::active()->get();
        } catch (\Exception $th) {
            return back()->with(['error' => [__('Unable to connect with API, Please Contact Support!!')]]);
        }

        $payment_link = PaymentLink::findOrFail($id);
        return view('user.sections.payment-link.edit', compact('page_title','currency_data','payment_link'));
    }
    /**
     * Payment link store
     *
     * @param Illuminate\Http\Request $request
     * @method POST
     * @return Illuminate\Http\Request
     */
    public function update(Request $request){

        $paymentLink = PaymentLink::find($request->target);

        if($request->type == PaymentGatewayConst::LINK_TYPE_PAY){
            $validator = Validator::make($request->all(), [
                'currency'        => 'required',
                'currency_symbol' => 'required',
                'currency_name'   => 'required',
                'title'           => 'required|max:180',
                'type'            => 'required',
                'details'         => 'nullable',
                'limit'           => 'nullable',
                'min_amount'      => 'nullable|min:0.1',
                'max_amount'      => 'nullable|gt:min_amount',
                'image'           => 'nullable|image|mimes:png,jpg,jpeg,svg,webp',
            ]);

            if($validator->fails()){
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();

            if($paymentLink->type == PaymentGatewayConst::LINK_TYPE_SUB){
                $validated['price'] = NULL;
                $validated['qty'] = NULL;
            }


            $validated = Arr::except($validated, ['image']);
            $validated['limit'] = $request->limit ? 1 : 2;
            $validated['user_id'] = Auth::id();

            try {

                if($request->hasFile('image')) {
                    try{
                        $image = get_files_from_fileholder($request,'image');
                        $upload_image = upload_files_from_path_dynamic($image,'payment-link-image',$paymentLink->image);
                        $validated['image'] = $upload_image;
                    }catch(Exception $e) {
                        return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
                    }
                }

                $paymentLink->update($validated);

            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

        }else{
            $validator = Validator::make($request->all(), [
                'sub_currency'    => 'required',
                'currency_symbol' => 'required',
                'currency_name'   => 'required',
                'sub_title'       => 'required|max:180',
                'type'            => 'required',
                'price'           => 'nullable',
                'qty'             => 'nullable',
            ]);

            $validated = $validator->validated();
            $validated['currency'] = $validated['sub_currency'];
            $validated['title'] = $validated['sub_title'];
            $validated['user_id'] = Auth::id();

            if($paymentLink->type == PaymentGatewayConst::LINK_TYPE_PAY){

                $validated['image'] = NULL;
                $validated['details'] = NULL;
                $validated['limit'] = 2;
                $validated['min_amount'] = NULL;
                $validated['max_amount'] = NULL;

                $image_link = get_files_path('payment-link-image') . '/' . $paymentLink->image;
                delete_file($image_link);
            }

            $validated = Arr::except($validated, ['sub_currency','sub_title']);
            try {
                $paymentLink->update($validated);
            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }


        return redirect()->route('user.payment-link.share', $paymentLink->id)->with(['success' => [__('Payment Link Updated Successful')]]);
    }
    /**
     * Payment link store
     *
     * @param Illuminate\Http\Request $request
     * @method POST
     * @return Illuminate\Http\Request
     */
    public function status(Request $request){
        $validator = Validator::make($request->all(), [
            'target'        => 'required',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $paymentLink = PaymentLink::find($validated['target']);

        try {
            $status = $paymentLink->status == 1 ? 2 : 1;
            $paymentLink->update(['status' => $status]);

        } catch (\Exception $th) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return redirect()->route('user.payment-link.index')->with(['success' => [__('Payment Link Status Updated Successful')]]);
    }

    /**
     * Payment link eidt page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function share($id){
        $page_title = __('Link Share');
        $payment_link = PaymentLink::findOrFail($id);
        return view('user.sections.payment-link.share', compact('page_title','payment_link'));
    }

    /**
     * Payment Link Share
     *
     * @method GET
     * @return Illuminate\Http\Request
     */

    public function paymentLinkShare($token){
        $payment_link = PaymentLink::with('user','merchant')->where('status', 1)->where('token', $token)->first();
        if(empty($payment_link)){
            return redirect()->route('index')->with(['error' => [__('Invalid Payment Link')]]);
        }
        $payment_gateways = PaymentGateway::active()->addMoney()->whereNot('alias','tatum')->automatic()->get();
        $credentials = GatewayAPi::first();
        if(empty($credentials)){
            return redirect()->route('index')->with(['error' => [__('Can Not Payment Now, Please Contact Support')]]);
        }
        $public_key = $credentials->public_key;

        $page_title = __('Payment Link');
        return view('frontend.paylink.share', compact('payment_link', 'page_title', 'public_key','payment_gateways'));
    }

    /**
     * Payment Link Share
     *
     * @param @return Illuminate\Http\Request $request
     * @method POST
     * @return Illuminate\Http\Request
     */

    public function paymentLinkSubmit(Request $request){
        $validator = Validator::make($request->all(), [
            'target'          => 'required',
            'payment_type'    => 'required|string'
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $payment_method = [
            PaymentGatewayConst::TYPE_GATEWAY_PAYMENT => 'gatewayPaymentRequest',
            PaymentGatewayConst::TYPE_WALLET_SYSTEM => 'walletPaymentRequest',
            PaymentGatewayConst::TYPE_CARD_PAYMENT => 'cardPaymentRequest',
        ];

        if(!array_key_exists($validated['payment_type'], $payment_method)) return abort(404);
        $method = $payment_method[$validated['payment_type']];
        return $this->$method($request);

    }
     /**
     * Gateway Payment Request
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function gatewayPaymentRequest(Request $request){

        $validator = Validator::make($request->all(),[
            'target'          => 'required',
            'amount'          => 'required',
            'email'           => 'required|email',
            'full_name'       => 'required|string',
            'phone'           => 'required|numeric',
            'payment_gateway' => 'required|exists:payment_gateways,alias',
        ]);

        if($validator->fails()) return back()->withErrors($validator)->withInput();
        $validated = $validator->validated();
        $payment_link = PaymentLink::with('user','merchant')->find($validated['target']);
        if(!empty($payment_link->price)){
            $amount = $payment_link->price * $payment_link->qty;
            if($validated['amount'] != $amount){
                return back()->with(['error' => [__('Please Enter A Valid Amount')]]);
            }
        }else{
            if($payment_link->limit == 1){
                if($validated['amount'] < $payment_link->min_amount || $validated['amount'] > $payment_link->max_amount){
                    return back()->with(['error' => [__("Please follow the transaction limit")]]);
                }else{
                    $amount = $validated['amount'];
                }
            }else{
                $amount = $validated['amount'];
            }
        }
        $validated['payment_link'] = $payment_link;
        if(empty($payment_link)) return back()->with(['error' => [__('Invalid Request!')]]);

        $payment_gateway = PaymentGateway::where('alias', $validated['payment_gateway'])->withWhereHas('currency',function($q) use ($payment_link){
            $q->where("currency_code",$payment_link->currency);
        })->first();

        if(!$payment_gateway) return back()->with(['error' => [__('Gateway Currency Is Not Supported!')]]);

        $request->merge(['currency' => $payment_gateway->currency->alias]);

       try {
            $instance = PayLinkPaymentGateway::init($request->all())->type(PaymentGatewayConst::TYPEPAYLINK)->payLinkGateway()->render();
       }catch (\Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
       }

       return $instance;
    }
    /**
     * Card Payment Request
     *
     * @method POST
     * @return Illuminate\Http\Request
     */

    public function cardPaymentRequest(Request $request){
        $validator = Validator::make($request->all(),[
            'target'     => 'required',
            'email'      => 'required|email',
            'phone'      => 'nullable',
            'card_name'  => 'required',
            'token'      => 'required',
            'last4_card' => 'required',
            'amount'     => 'required|gt:0',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $credentials = GatewayAPi::first();
        if(empty($credentials)){
            return back()->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        }

        $payment_link = PaymentLink::with('user','merchant')->find($validated['target']);
        if(!empty($payment_link->price)){
            $amount = $payment_link->price * $payment_link->qty;
            if($validated['amount'] != $amount){
                return back()->with(['error' => [__('Please Enter A Valid Amount')]]);
            }
        }else{
            if($payment_link->limit == 1){
                if($validated['amount'] < $payment_link->min_amount || $validated['amount'] > $payment_link->max_amount){
                    return back()->with(['error' => [__("Please follow the transaction limit")]]);
                }else{
                    $amount = $validated['amount'];
                }
            }else{
                $amount = $validated['amount'];
            }
        }
        $validated['payment_link'] = $payment_link;
        $receiver_currency = Currency::where('code', $validated['payment_link']->currency)->first();
        if(empty($receiver_currency)){
            return back()->with(['error' => [__('Receiver currency not found!')]]);
        }
        if($payment_link->user_id != null){
            $receiver_wallet = UserWallet::with('user','currency')->where('user_id', $payment_link->user_id)->first();
            $userType = "USER";
            $user_guard = "web";
        }elseif($payment_link->merchant_id != null){
            $receiver_wallet = MerchantWallet::with('merchant','currency')->where('merchant_id', $payment_link->merchant_id)->first();
            $userType = "MERCHANT";
            $user_guard = "merchant";
        }

        if(empty($receiver_wallet)){
            return back()->with(['error' => [__('Receiver wallet not found')]]);
        }

        $sender_currency = Currency::where('code', $payment_link->currency)->where('name', $payment_link->currency_name)->first();

        $validated['receiver_wallet'] = $receiver_wallet;
        $validated['sender_currency'] = $sender_currency;
        $validated['transaction_type'] = PaymentGatewayConst::TYPEPAYLINK;

        $payment_link_charge = TransactionSetting::where('slug', PaymentGatewayConst::paylink_slug())->where('status',1)->first();

        $fixedCharge        = $payment_link_charge->fixed_charge * $sender_currency->rate;
        $percent_charge     = ($amount / 100) * $payment_link_charge->percent_charge;
        $total_charge       = $fixedCharge + $percent_charge;
        $payable            = $amount - $total_charge;

        if($payable <= 0 ){
            return back()->with(['error' => [__('Transaction Failed, Please Contact With Support!')]]);
        }

        $conversion_charge  = conversionAmountCalculation($total_charge, $sender_currency->rate, $receiver_currency->rate);
        $conversion_payable = conversionAmountCalculation($payable, $sender_currency->rate ,$receiver_currency->rate);
        $exchange_rate      = conversionAmountCalculation(1, $receiver_currency->rate, $sender_currency->rate);
        $conversion_admin_charge = $total_charge / $sender_currency->rate;

        $charge_calculation = [
            'requested_amount'       => $amount,
            'request_amount_admin'   => $amount / $sender_currency->rate,
            'fixed_charge'           => $fixedCharge,
            'percent_charge'         => $percent_charge,
            'total_charge'           => $total_charge,
            'conversion_charge'      => $conversion_charge,
            'conversion_admin_charge'=> $conversion_admin_charge,
            'payable'                => $payable,
            'conversion_payable'     => $conversion_payable,
            'exchange_rate'          => $exchange_rate,
            'sender_cur_code'        => $payment_link->currency,
            'receiver_currency_code' => $receiver_currency->code,
            'base_currency_code'     => get_default_currency_code(),
        ];

        $validated['charge_calculation'] = $charge_calculation;
        $validated['userType'] = $userType??"";
        $validated['user_guard'] = $user_guard??"";
       try {
            $this->stripeLinkInit($validated, $credentials);
            return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
       } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
       }

    }
    /**
     * User Login By Paylink
     *
     * @method Get
     * @return Illuminate\Http\Request
    */

    public function userLogin($token){
        try {
            $guards = ['merchant', 'agent', 'admin'];
            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    Auth::guard($guard)->logout();
                    break; // Exit the loop after the first successful logout
                }
            }
        } catch (Exception $e) {}

        return redirect()->route('payment-link.share', $token);
    }
    /**
     * Wallet Payment Request
     *
     * @method POST
     * @return Illuminate\Http\Request
     */
    public function walletPaymentRequest(Request $request){
        $validator = Validator::make($request->all(),[
            'target'        => 'required',
            'email'         => 'required|email',
            'phone'         => 'required|numeric',
            'full_name'     => 'required|string',
            'wallet_system' => 'required|string',
            'amount'        => 'required|gt:0',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $payment_link = PaymentLink::with('user','merchant')->find($validated['target']);
        if(!empty($payment_link->price)){
            $amount = $payment_link->price * $payment_link->qty;
            if($validated['amount'] != $amount){
                return back()->with(['error' => [__('Please Enter A Valid Amount')]]);
            }
        }else{
            if($payment_link->limit == 1){
                if($validated['amount'] < $payment_link->min_amount || $validated['amount'] > $payment_link->max_amount){
                    return back()->with(['error' => [__("Please follow the transaction limit")]]);
                }else{
                    $amount = $validated['amount'];
                }
            }else{
                $amount = $validated['amount'];
            }
        }
        $validated['payment_link'] = $payment_link;
        $receiver_currency = Currency::where('code', $validated['payment_link']->currency)->first();
        if(empty($receiver_currency)){
            return back()->with(['error' => [__('Receiver currency not found!')]]);
        }
        if($payment_link->user_id != null){
            $receiver_wallet = UserWallet::with('user','currency')->where('user_id', $payment_link->user_id)->first();
            $userType = "USER";
            $relation_name = 'user';
            $user_guard = "web";
        }elseif($payment_link->merchant_id != null){
            $receiver_wallet = MerchantWallet::with('merchant','currency')->where('merchant_id', $payment_link->merchant_id)->first();
            $userType = "MERCHANT";
            $user_guard = "merchant";
            $relation_name ="merchant";
        }

        if(empty($receiver_wallet)){
            return back()->with(['error' => [__('Receiver wallet not found')]]);
        }
        $sender = userGuard()['user'];
        if(!$sender){
            return back()->with(['error' => [__('Sender Not Found')]]);
        }
        $sender_wallet = UserWallet::where('user_id', $sender->id)->first();
        if(!$sender_wallet){
            return back()->with(['error' => [__('Sender Wallet Not Found')]]);
        }
        if($userType === "USER" && $sender->email ===  $receiver_wallet->$relation_name->email){
            return back()->with(['error' => [__('Can not pay to your own payment link')]]);

        }
        $sender_currency =   $sender_wallet->currency;

        $validated['receiver']          = $receiver_wallet->$relation_name??'user';
        $validated['receiver_wallet']   = $receiver_wallet;
        $validated['sender']            = $sender;
        $validated['sender_wallet']     = $sender_wallet;
        $validated['sender_currency']   = $sender_currency;
        $validated['transaction_type']  = PaymentGatewayConst::TYPEPAYLINK;

        $payment_link_charge = TransactionSetting::where('slug', PaymentGatewayConst::paylink_slug())->where('status',1)->first();

        $fixedCharge        = $payment_link_charge->fixed_charge * $sender_currency->rate;
        $percent_charge     = ($amount / 100) * $payment_link_charge->percent_charge;
        $total_charge       = $fixedCharge + $percent_charge;
        $payable            = $amount - $total_charge;
        $sender_payable     = $amount + $total_charge;

        if($payable <= 0 ){
            return back()->with(['error' => [__('Transaction Failed, Please Contact With Support!')]]);
        }
        if($sender_payable > $sender_wallet->balance ){
            return back()->with(['error' => [__('Your wallet balance is insufficient to complete this transaction, Please add funds to proceed.')]]);
        }

        $conversion_charge  = conversionAmountCalculation($total_charge,$sender_currency->rate, $receiver_currency->rate);
        $conversion_payable = conversionAmountCalculation($payable,$sender_currency->rate ,$receiver_currency->rate);
        $exchange_rate      = conversionAmountCalculation(1, $receiver_currency->rate, $sender_currency->rate);
        $conversion_admin_charge = $total_charge / $sender_currency->rate;

        $charge_calculation = [
            'requested_amount'       => $amount,
            'request_amount_admin'   => $amount / $sender_currency->rate,
            'fixed_charge'           => $fixedCharge,
            'percent_charge'         => $percent_charge,
            'total_charge'           => $total_charge,
            'conversion_charge'      => $conversion_charge,
            'conversion_admin_charge'=> $conversion_admin_charge,
            'payable'                => $payable,
            'sender_payable'         => $sender_payable,
            'conversion_payable'     => $conversion_payable,
            'exchange_rate'          => $exchange_rate,
            'sender_cur_code'        => $payment_link->currency,
            'receiver_currency_code' => $receiver_currency->code,
            'base_currency_code'     => get_default_currency_code(),
        ];

        $validated['charge_calculation'] = $charge_calculation;
        $validated['user_type'] = $userType??"";
        $validated['user_guard'] = $user_guard??"";
        $validated['payment_type'] = PaymentGatewayConst::TYPE_WALLET_SYSTEM;
        try {
            $this->createWalletTransactionPayLink($validated,PaymentGatewayConst::STATUSSUCCESS);
            return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
       } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
       }



    }

     /**
     * Transaction Success
     *
     * @method GET
     * @return Illuminate\Http\Request
     */

     public function transactionSuccess($token){
        $payment_link = PaymentLink::with('user')->where('token', $token)->first();
        $page_title = __('payment Success');
        return view('frontend.paylink.transaction-success', compact('payment_link', 'page_title'));
    }
     /**
     * Stripe Success
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function stripeSuccess($trx){
        $token = $trx;
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::STRIPE)->where("identifier",$token)->first();
        if(!$checkTempData) return redirect()->route('index')->with(['error' => [__('Transaction Failed. Record didn\'t saved properly. Please try again.')]]);
        $checkTempData = $checkTempData->toArray();
        try{
            PayLinkPaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEPAYLINK)->responseReceive('stripe');
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        $payment_link = PaymentLink::find($checkTempData['data']->validated->target);
        return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
    }
     /**
     * Paypal Success
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function paypalSuccess(Request $request, $gateway){
        $requestData = $request->all();
        $token = $requestData['token'] ?? "";
        $checkTempData = TemporaryData::where("type",$gateway)->where("identifier",$token)->first();
        if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        $checkTempData = $checkTempData->toArray();

        try{
            PayLinkPaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEPAYLINK)->responseReceive();
        }catch(Exception $e) {
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }
        $payment_link = PaymentLink::find($checkTempData['data']->validated->target);
        return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
    }
    /**
     * Paypal Canceled
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function paypalCancel(Request $request, $gateway) {
        $requestData = $request->all();
        $token = $requestData['token'] ?? "";
        if( $token){
            TemporaryData::where("identifier",$token)->delete();
        }
        return redirect()->route('index')->with(['error' => [__('Transaction failed')]]);
    }
    /**
     * Flutter-Wave Callback
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function flutterwaveCallback()
    {
        $status = request()->status;
        //if payment is successful
        if ($status ==  'successful' || $status == 'completed') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);
            $token = request()->tx_ref;
            $checkTempData = TemporaryData::where("type",'flutterwave')->where("identifier",$token)->first();
            if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
            $checkTempData = $checkTempData->toArray();
            try{
                PayLinkPaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEPAYLINK)->responseReceive('flutterWave');
            }catch(Exception $e){
                return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
            }
            $payment_link = PaymentLink::find($checkTempData['data']->validated->target);
            return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
        }
        elseif ($status ==  'cancelled'){
            return redirect()->route('index')->with(['error' => [__('Transaction failed')]]);
        }
        else{
            return redirect()->route('index')->with(['error' => [__("Transaction failed")]]);
        }
    }
    /**
     * Redirect Users for collecting payment via Button Pay (JS Checkout)
     */
    public function callback(Request $request,$gateway){
        $callback_token = $request->get('token');
        $callback_data = $request->all();
        try{
            PayLinkPaymentGateway::init([])->type(PaymentGatewayConst::TYPEPAYLINK)->handleCallback($callback_token,$callback_data,$gateway);
        }catch(Exception $e) {
            // handle Error
            logger($e);
        }
    }
    /**
     * Redirect Users for collecting payment via Button Pay (JS Checkout)
     */
    public function redirectBtnPay(Request $request, $gateway)
    {
        try{
            return PayLinkPaymentGateway::init([])->type(PaymentGatewayConst::TYPEPAYLINK)->handleBtnPay($gateway, $request->all());
        }catch(Exception $e) {
            return redirect()->route('user.add.money.index')->with(['error' => [$e->getMessage()]]);
        }
    }
    public function successGlobal(Request $request, $gateway){
        try{
            $token = PayLinkPaymentGateway::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
            if(Transaction::where('callback_ref', $token)->exists()) {
                $payment_link = PaymentLink::find($temp_data->data->validated->target);
                if(!$temp_data) return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
            }else {
                if(!$temp_data) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
            }

            $update_temp_data = json_decode(json_encode($temp_data->data),true);
            $update_temp_data['callback_data']  = $request->all();
            $temp_data->update([
                'data'  => $update_temp_data,
            ]);
            $temp_data = $temp_data->toArray();
            $instance = PayLinkPaymentGateway::init($temp_data)->type(PaymentGatewayConst::TYPEPAYLINK)->responseReceive($temp_data['type']);
            if($instance instanceof RedirectResponse) return $instance;
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }
        $payment_link = PaymentLink::find($temp_data['data']->validated->target);
        return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
    }
    public function cancelGlobal(Request $request, $gateway) {
        $token = PayLinkPaymentGateway::getToken($request->all(),$gateway);
        if($temp_data = TemporaryData::where("identifier",$token)->first()) {
            $temp_data->delete();
        }
        return redirect()->route('index')->with(['error' => [__('Transaction canceled')]]);
    }
    public function postSuccess(Request $request, $gateway)
    {

        try{
            $token = PayLinkPaymentGateway::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
        }catch(Exception $e) {
            return redirect()->route('index')->with(['error' => [__('Transaction failed')]]);
        }
        return $this->successGlobal($request, $gateway);
    }
    public function postCancel(Request $request, $gateway)
    {
        try{
            $token = PayLinkPaymentGateway::getToken($request->all(),$gateway);
            $temp_data = TemporaryData::where("identifier",$token)->first();
        }catch(Exception $e) {
            return redirect()->route('index')->with(['error' => [__('Transaction failed')]]);
        }
        return $this->cancelGlobal($request, $gateway);
    }
    /**
     * SLL-COMMERZ Callback
     *
     * @method POST
     * @return Illuminate\Http\Request
     */

    //sslcommerz success
    public function sllCommerzSuccess(Request $request){
        $data = $request->all();
        $token = $data['tran_id'];
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::SSLCOMMERZ)->where("identifier",$token)->first();
        if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        $checkTempData = $checkTempData->toArray();
        if( $data['status'] != "VALID"){
            return redirect()->route("index")->with(['error' => [__('Transaction failed')]]);
        }
        try{
            PayLinkPaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEPAYLINK)->responseReceive('sslcommerz');
        }catch(Exception $e) {
            return back()->with(['error' => ["Something went wrong! Please try again."]]);
        }
        $payment_link = PaymentLink::find($checkTempData['data']->validated->target);
        return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
    }
    //sslCommerz fails
    public function sllCommerzFails(Request $request){
        $data = $request->all();
        $token = $data['tran_id'];
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::SSLCOMMERZ)->where("identifier",$token)->first();
        if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        $checkTempData = $checkTempData->toArray();
        if( $data['status'] == "FAILED"){
            TemporaryData::destroy($checkTempData['id']);
            return redirect()->route("index")->with(['error' => [__('Transaction failed')]]);
        }

    }
    //sslCommerz canceled
    public function sllCommerzCancel(Request $request){
        $data = $request->all();
        $token = $data['tran_id'];
        $checkTempData = TemporaryData::where("type",PaymentGatewayConst::SSLCOMMERZ)->where("identifier",$token)->first();
        if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        $checkTempData = $checkTempData->toArray();
        if( $data['status'] != "VALID"){
            TemporaryData::destroy($checkTempData['id']);
            return redirect()->route("index")->with(['error' => [__('Transaction failed')]]);
        }
    }
      /**
     * CoinGate Callback
     *
     * @method POST
     * @return Illuminate\Http\Request
     */
    //coingate response start
    public function coinGateSuccess(Request $request, $gateway){
        try{
            $token = $request->token;
            $checkTempData = TemporaryData::where("type",PaymentGatewayConst::COINGATE)->where("identifier",$token)->first();
            if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
            $payment_link = PaymentLink::find($checkTempData->data->validated->target);

            if(Transaction::where('callback_ref', $token)->exists()) {
                if(!$checkTempData) return  redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
            }else {
                if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
            }
            $update_temp_data = json_decode(json_encode($checkTempData->data),true);
            $update_temp_data['callback_data']  = $request->all();
            $checkTempData->update([
                'data'  => $update_temp_data,
            ]);
            $temp_data = $checkTempData->toArray();
            PayLinkPaymentGateway::init($temp_data)->type(PaymentGatewayConst::TYPEPAYLINK)->responseReceive('coingate');
        }catch(Exception $e) {
            return redirect()->route("index")->with(['error' => [__('Something went wrong! Please try again.')]]);
        }
        return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
    }
    public function coinGateCancel(Request $request, $gateway){
        if($request->has('token')) {
            $identifier = $request->token;
            if($temp_data = TemporaryData::where('identifier', $identifier)->first()) {
                $temp_data->delete();
            }
        }
        return redirect()->route("index")->with(['error' => [__('Transaction failed')]]);
    }
    /**
         * redirectUsingHTMLForm
         *
         * @method POST
         * @return Illuminate\Http\Request
     */
    public function redirectUsingHTMLForm(Request $request, $gateway)
    {
        $temp_data = TemporaryData::where('identifier', $request->token)->first();
        if(!$temp_data || $temp_data->data->action_type != PaymentGatewayConst::REDIRECT_USING_HTML_FORM) return back()->with(['error' => ['Request token is invalid!']]);
        $redirect_form_data = $temp_data->data->redirect_form_data;
        $action_url         = $temp_data->data->action_url;
        $form_method        = $temp_data->data->form_method;

        return view('payment-gateway.redirect-form', compact('redirect_form_data', 'action_url', 'form_method'));
    }
}
