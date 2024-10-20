<?php

namespace App\Http\Controllers;

use App\Constants\GlobalConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Response;
use App\Models\Admin\ExchangeRate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWallet;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Facades\Agent;

class GlobalController extends Controller
{
    /**
     * Funtion for get state under a country
     * @param country_id
     * @return json $state list
     */
    public function getStates(Request $request) {
        $request->validate([
            'country_id' => 'required|integer',
        ]);
        $country_id = $request->country_id;
        // Get All States From Country
        $country_states = get_country_states($country_id);
        return response()->json($country_states,200);
    }
    public function getCities(Request $request) {
        $request->validate([
            'state_id' => 'required|integer',
        ]);

        $state_id = $request->state_id;
        $state_cities = get_state_cities($state_id);

        return response()->json($state_cities,200);
        // return $state_id;
    }
    public function getCountries(Request $request) {
        $countries = freedom_countries(GlobalConst::USER);
        return response()->json($countries,200);
    }
    public function getCountriesAgent(Request $request) {
        $countries = freedom_countries(GlobalConst::AGENT);
        return response()->json($countries,200);
    }
    public function getCountriesMerchant(Request $request) {
        $countries = freedom_countries(GlobalConst::MERCHANT);
        return response()->json($countries,200);
    }
    public function getTimezones(Request $request) {
        $timeZones = get_all_timezones();

        return response()->json($timeZones,200);
    }
    public function userInfo(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'      => "required|string",
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors(),null,400);
        }
        $validated = $validator->validate();
        $field_name = "email";
        // if(check_email($validated['text'])) {
        //     $field_name = "email";
        // }

        try{
            $user = User::where($field_name,$validated['text'])->first();
            if($user != null) {
                if(@$user->address->country === null ||  @$user->address->country != get_default_currency_name()) {
                    $error = ['error' => [__("User Country doesn't match with default currency country")]];
                    return Response::error($error, null, 500);
                }
            }
        }catch(Exception $e) {
            $error = ['error' => [$e->getMessage()]];
            return Response::error($error,null,500);
        }
        $success = ['success' => [__('Successfully executed')]];
        return Response::success($success,$user,200);
    }
    public function webHookResponse(Request $request){
        $response_data = $request->all();
        $transaction = Transaction::where('callback_ref',$response_data['data']['reference'])->first();
        if($response_data['data']['status'] === "SUCCESSFUL"){
            $reduce_balance = ($transaction->creator_wallet->balance - $transaction->request_amount);
            $transaction->update([
                'status'            => PaymentGatewayConst::STATUSSUCCESS,
                'details'           => $response_data,
                'available_balance' => $reduce_balance,
            ]);

            $transaction->creator_wallet->update([
                'balance'   => $reduce_balance,
            ]);
            logger("Transaction Status: " . $response_data['data']['status']);
        }elseif($response_data['data']['status'] === "FAILED"){

            $transaction->update([
                'status'    => PaymentGatewayConst::STATUSFAILD,
                'details'   => $response_data,
                'reject_reason'   => $response_data['data']['complete_message']??null,
                'available_balance' => $transaction->creator_wallet->balance,
            ]);
            logger("Transaction Status: " . $response_data['data']['status']." Reason: ".$response_data['data']['complete_message']??"");
        }

    }
    public function setCookie(Request $request){
        $userAgent = $request->header('User-Agent');
        $cookie_status = $request->type;
        if($cookie_status == 'allow'){
            $response_message = __("Cookie Allowed Success");
            $expirationTime = 2147483647; //Maximum Unix timestamp.
        }else{
            $response_message = __("Cookie Declined");
            $expirationTime = Carbon::now()->addHours(24)->timestamp;// Set the expiration time to 24 hours from now.
        }
        $browser = Agent::browser();
        $platform = Agent::platform();
        $ipAddress = $request->ip();
        // Set the expiration time to a very distant future
        return response($response_message)->cookie('approval_status', $cookie_status,$expirationTime)
                                            ->cookie('user_agent', $userAgent,$expirationTime)
                                            ->cookie('ip_address', $ipAddress,$expirationTime)
                                            ->cookie('browser', $browser,$expirationTime)
                                            ->cookie('platform', $platform,$expirationTime);

    }
     // ajax call for get user available balance by currency
     public function userWalletBalance(Request $request){
        $user_wallets = UserWallet::where(['user_id' => auth()->user()->id, 'currency_id' => $request->id])->first();
        return $user_wallets->balance;
    }
    public function receiverWallet(Request $request){
        $receiver_currency = ExchangeRate::where(['currency_code' => $request->code])->first();
        return $receiver_currency;
    }
    //reloadly webhook response
    public function webhookInfo(Request $request){
        $response_data = $request->all();
        $custom_identifier = $response_data['data']['customIdentifier'];
        $transaction = Transaction::where('type',PaymentGatewayConst::MOBILETOPUP)->where('callback_ref',$custom_identifier)->first();
        if( $response_data ['data']['status'] =="SUCCESSFUL"){
            $transaction->update([
                'status' => true,
            ]);
        }elseif($response_data ['data']['status'] !="SUCCESSFUL" ){
            $afterCharge = (($transaction->creator_wallet->balance + $transaction->details->charges->payable) - $transaction->details->charges->agent_total_commission);
            $transaction->update([
                'status'            => PaymentGatewayConst::STATUSREJECTED,
                'available_balance' =>  $afterCharge,
            ]);
            //refund balance
            $transaction->creator_wallet->update([
                'balance'   => $afterCharge,
            ]);

        }
        logger("Mobile Top Up Success!", ['custom_identifier' => $custom_identifier, 'status' => $response_data ['data']['status']]);
    }


}
