<?php

namespace App\Http\Controllers\PaymentGateway\QrPay\v1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\MerchantConfiguration;
use App\Models\Merchants\PaymentOrderRequest;
use Illuminate\Validation\ValidationException;
use App\Notifications\PaymentGateway\PaymentVerification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Redirect;

class UserAuthenticationController extends Controller
{
    protected $access_token_expire_time = 600;

    protected $testuser_email       = "sandbox@appdevs.net";
    protected $testuser_password    = "appdevs";
    protected $testuser_otp         = 12345;
    protected $test_email_verification_code = 123456;

    public function showAuthForm($token) {

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

        // Check request comes from sandbox or production url
        if(request()->is("*/sandbox/*")) {
            // sandbox request
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

            $preview_url = route('qrpay.pay.sandbox.v1.user.payment.preview',$token);
            $auth_form_submit = route('qrpay.pay.sandbox.v1.user.auth.form.submit',$token);
        }else {
            // production request
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
            $preview_url = route('qrpay.pay.v1.user.payment.preview',$token);
            $auth_form_submit = route('qrpay.pay.v1.user.auth.form.submit',$token);
        }

        // If the token already authenticated
        if($request_record->authentication == true) {
            return redirect($preview_url);
        }

        $page_title = "Authentication";
        return view('qrpay-gateway.pages.login',compact('page_title','payment_gateway_image','merchant_configuration','token','auth_form_submit'));
    }

    public function authFormSubmit(Request $request, $token) {
        $validated = Validator::make($request->all(),[
            'email'         => 'required|string|email|max:255',
            'password'      => 'required|string',
        ])->validate();

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

        // Check request comes from sandbox or production url
        if(request()->is("*/sandbox/*")) {
            // sandbox request
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

            if($validated['email'] != $this->testuser_email || $validated['password'] != $this->testuser_password) {
                return throw ValidationException::withMessages([
                    'password'      => "Credentials didn't match.",
                ]);
            }

            // no need to check like real user
            $request_record->update([
                'authentication'    => true,
                'user_id'           => null,
            ]);

            $preview_url = route('qrpay.pay.sandbox.v1.user.payment.preview',$token);
            return redirect($preview_url);

        }else {
            // production request
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
            $preview_url = route('qrpay.pay.v1.user.payment.preview',$token);
        }

        $user = User::where('email',$validated['email'])->first();
        if(!$user) {
            return throw ValidationException::withMessages([
                'password'      => "Credentials didn't match",
            ]);
        }

        if(Hash::check($validated['password'],$user->password)) {

            $request_record->update([
                'authentication'    => true,
                'user_id'           => $user->id,
            ]);

            // Authentication success
            return redirect($preview_url);
        }

        return throw ValidationException::withMessages([
            'password'      => "Credentials didn't match",
        ]);

    }

    public function showMailVerify($token) {
        $page_title = "Account Verification";

        if(request()->is('*/sandbox/*')) {
            $form_submit_url = route('qrpay.pay.sandbox.v1.user.auth.mail.verify.form.submit',$token);
        }else {
            $form_submit_url = route('qrpay.pay.v1.user.auth.mail.verify.form.submit',$token);
        }

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

        return view('qrpay-gateway.pages.mail-verify',compact('page_title','form_submit_url','token','payment_gateway_image'));
    }

    public function mailVerifySubmit(Request $request,$token) {
        $request->merge(['token' => $token]);
        $request->validate([
            'token'     => "required|string|exists:payment_order_requests,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("",$code);

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
        if($code  != $request_record->email_code){
            return back()->with(['error' => ['The verification code does not match']]);
        }

        try{
            $request_record->update([
                'email_verify'  => true,
            ]);
        }catch(Exception $e) {
            return throw ValidationException::withMessages([
                'code'      => "Submitted code is invalid!",
            ]);
        }

        // Check request comes from sandbox or production url
        if(request()->is("*/sandbox/*")) {
            // sandbox request
            $preview_url = route('qrpay.pay.sandbox.v1.user.payment.preview.submit',$token);
        }else {
            // production request
            $preview_url = route('qrpay.pay.v1.user.payment.preview.submit',$token);
        }


        // return new FormRequest();

        $payment_controller = new PaymentController();

        $request = new Request([
            'token' => $token,
        ]);

        return $payment_controller->paymentConfirm($request,$token);

        // return redirect($preview_url);
    }
}
