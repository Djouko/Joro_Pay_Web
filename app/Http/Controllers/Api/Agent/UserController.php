<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\GlobalConst;
use Exception;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\Api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\AgentNotification;
use App\Models\AgentProfit;
use App\Models\AgentWallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Providers\Admin\BasicSettingsProvider;
use App\Traits\AdminNotifications\AuthNotifications;


class UserController extends Controller
{
    use AuthNotifications;

    public function home(){
        $agent = authGuardApi()['user'];
        $totalAddMoney = Transaction::agentAuth()->addMoney()->where('status',1)->sum('request_amount');
        $totalWithdrawMoney = Transaction::agentAuth()->moneyOut()->where('status',1)->sum('request_amount');
        $totalSendMoney = Transaction::agentAuth()->senMoney()->where('status',1)->sum('request_amount');
        $totalMoneyIn = Transaction::agentAuth()->moneyIn()->where('status',1)->sum('request_amount');
        $totalReceiveMoney = Transaction::agentAuth()->agentMoneyOut()->where('status',1)->sum('request_amount');;
        $totalSendRemittance =Transaction::agentAuth()->remitance()->where('attribute',"SEND")->sum('request_amount');
        $billPay =  amountOnBaseCurrency(Transaction::agentAuth()->billPay()->where('status',1)->get());
        $topUps =   amountOnBaseCurrency(Transaction::agentAuth()->where('status',1)->mobileTopup()->get());
        $total_transaction = Transaction::agentAuth()->where('status', 1)->count();
        $agent_profits = AgentProfit::agentAuth()->sum('total_charge');

        $agentWallet = AgentWallet::where('agent_id',$agent->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => get_default_currency_code(),
            ];
        })->first();

        $transactions = Transaction::agentAuth()->latest()->take(10)->get()->map(function($item){
            $basic_settings = BasicSettings::first();
            if($item->type == payment_gateway_const()::TYPEADDMONEY){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                    'payable' => getAmount($item->payable,2).' '.$item->currency->currency_code,
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at ,
                ];
            }elseif($item->type == payment_gateway_const()::BILLPAY){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,2).' '.billPayCurrency($item)['sender_currency'] ,
                    'payable' => getAmount($item->payable,2).' '.billPayCurrency($item)['wallet_currency'],
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at ,

                ];

            }elseif($item->type == payment_gateway_const()::MOBILETOPUP){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,2).' '.topUpCurrency($item)['destination_currency'],
                    'payable' => getAmount($item->payable,2).' '.topUpCurrency($item)['wallet_currency'],
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at ,

                ];

            }elseif($item->type == payment_gateway_const()::TYPEMONEYOUT){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                    'payable' => getAmount($item->payable,2).' '.$item->currency->currency_code,
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at ,
                ];

            }elseif($item->type == payment_gateway_const()::SENDREMITTANCE){
                if( @$item->details->remitance_type == "wallet-to-wallet-transfer"){
                    $transactionType = @$basic_settings->site_name." Wallet";

                }else{
                    $transactionType = ucwords(str_replace('-', ' ', @$item->details->remitance_type));
                }
                if($item->attribute == payment_gateway_const()::SEND){
                    if(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'remark' => $item->remark??"",
                            'date_time' => @$item->created_at ,
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_BANK_TRANSFER)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'remark' => $item->remark??"",
                            'date_time' => @$item->created_at ,
                        ];
                    }elseif(@$item->details->remitance_type == Str::slug(GlobalConst::TRX_CASH_PICKUP)){
                        return[
                            'id' => @$item->id,
                            'type' =>$item->attribute,
                            'trx' => @$item->trx_id,
                            'transaction_type' => $item->type,
                            'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                            'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                            'status' => @$item->stringStatus->value ,
                            'remark' => $item->remark??"",
                            'date_time' => @$item->created_at ,
                        ];
                    }

                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                        'status' => @$item->stringStatus->value ,
                        'remark' => $item->remark??"",
                        'date_time' => @$item->created_at ,
                    ];

                }

            }elseif($item->type == payment_gateway_const()::TYPETRANSFERMONEY){
                if($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                        'remark' => $item->remark??"",
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                    ];
                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'status' => @$item->stringStatus->value ,
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                        'remark' => $item->remark??"",
                        'date_time' => @$item->created_at ,
                    ];

                }

            }elseif($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_transaction_numeric_attribute($item->attribute).getAmount($item->request_amount,2).' '.get_default_currency_code(),
                    'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at
                ];

            }elseif($item->type == payment_gateway_const()::AGENTMONEYOUT){

                if($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                        'remark' => $item->remark??"",
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                    ];
                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                        'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                        'remark' => $item->remark??"",
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                    ];

                }

            }elseif($item->type == payment_gateway_const()::MONEYIN){
                return[
                    'id' => @$item->id,
                    'type' =>$item->attribute,
                    'trx' => @$item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount(@$item->request_amount,2).' '.get_default_currency_code() ,
                    'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                    'remark' => $item->remark??"",
                    'status' => @$item->stringStatus->value ,
                    'date_time' => @$item->created_at ,
                ];
            }
        });
        $module_access =[
            'receive_money' => module_access_api('agent-receive-money'),
            'add_money' => module_access_api('agent-add-money'),
            'withdraw_money' => module_access_api('agent-withdraw-money'),
            'transfer_money' => module_access_api('agent-transfer-money'),
            'money_in' => module_access_api('agent-money-in'),
            'bill_pay' => module_access_api('agent-bill-pay'),
            'mobile_top_up' => module_access_api('agent-mobile-top-up'),
            'remittance_money' => module_access_api('agent-remittance-money'),
        ];

        $basic_settings = BasicSettingsProvider::get();
        if(!$basic_settings) {
            $message = ['error'=>[__("Basic setting not found!")]];
            return Helpers::error($message);
        }
        $notification_config = $basic_settings->push_notification_config;

        if(!$notification_config) {
            $message = ['error'=>[__("Notification configuration not found!")]];
            return Helpers::error($message);
        }

        $pusher_credentials = [
            "instanceId" => $notification_config->instance_id ?? '',
            "secretKey" => $notification_config->primary_key ?? '',
        ];
        $data =[
            'base_curr'                 => get_default_currency_code(),
            'pusher_credentials'        => (object)$pusher_credentials,
            'agentWallet'               => (object)$agentWallet,
            'base_url'                  => url("/"),
            'default_image'             => files_asset_path_basename("default"),
            "image_path"                => files_asset_path_basename('agent-profile'),
            'module_access'             => $module_access,
            'agent'                     => $agent,
            'totalAddMoney'             => getAmount($totalAddMoney,2).' '.get_default_currency_code(),
            'totalWithdrawMoney'        => getAmount($totalWithdrawMoney,2).' '.get_default_currency_code(),
            'totalSendMoney'            => getAmount($totalSendMoney,2).' '.get_default_currency_code(),
            'totalMoneyIn'              => getAmount($totalMoneyIn,2).' '.get_default_currency_code(),
            'totalReceiveMoney'         => getAmount($totalReceiveMoney,2).' '.get_default_currency_code(),
            'totalSendRemittance'       => getAmount($totalSendRemittance,2).' '.get_default_currency_code(),
            'billPay'                   => getAmount($billPay,2).' '.get_default_currency_code(),
            'topUps'                    => getAmount($topUps,2).' '.get_default_currency_code(),
            'total_transaction'         => $total_transaction,
            'agent_profits'             => getAmount($agent_profits,2).' '.get_default_currency_code(),
            'transactions'              => $transactions,
        ];
        $message =  ['success'=>[__('Agent Dashboard')]];
        return Helpers::success($data,$message);
    }
    public function profile(){
        $user = authGuardApi()['user'];
        $data =[
            'base_url'          => url("/"),
            'default_image'     => files_asset_path_basename("default"),
            "image_path"        => files_asset_path_basename('agent-profile'),
            'agent'             => $user,
        ];
        $message =  ['success'=>[__('Agent Profile')]];
        return Helpers::success($data,$message);
    }
    public function profileUpdate(Request $request){
        $user =authGuardApi()['user'];
        $validator = Validator::make($request->all(), [
            'firstname'     => "required|string|max:60",
            'lastname'      => "required|string|max:60",
            'store_name'    => "required|string|max:60",
            'country'       => "required|string|max:50",
            'phone_code'    => "required|string|max:6",
            'phone'         => "required|string|max:20|unique:agents,mobile,".$user->id,
            'state'         => "nullable|string|max:50",
            'city'          => "nullable|string|max:50",
            'zip_code'      => "nullable|string",
            'address'       => "nullable|string|max:250",
            'image'         => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $data = $request->all();
        $mobileCode = remove_speacial_char($data['phone_code']);
        $mobile = remove_speacial_char($data['phone']);

        $validated['firstname']     = $data['firstname'];
        $validated['lastname']      = $data['lastname'];
        $validated['store_name']      =$data['store_name'];
        $validated['mobile']        = $mobile;
        $validated['mobile_code']   = $mobileCode;
        $complete_phone             = $mobileCode.$mobile;

        $validated['full_mobile']   = $complete_phone;

        $validated['address']       = [
            'country'   => $data['country']??"",
            'state'     => $data['state'] ?? "",
            'city'      => $data['city'] ?? "",
            'zip'       => $data['zip_code'] ?? "",
            'address'   => $data['address'] ?? "",
        ];
        if($request->hasFile("image")) {
            $oldImage = $user->image;
            $image = upload_file($data['image'],'agent-profile', $oldImage);
            $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'agent-profile');
            delete_file($image['dev_path']);
            $validated['image']     = $upload_image;
        }
        try{
            $user->update($validated);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Profile successfully updated!')]];
        return Helpers::onlysuccess($message);
    }
    public function passwordUpdate(Request $request) {
        $basic_settings = BasicSettingsProvider::get();
        $password_rule = "required|string|min:6|confirmed";
        if($basic_settings->agent_secure_password) {
            $password_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }
        $validator = Validator::make($request->all(), [
            'current_password'      => "required|string",
            'password'              => $password_rule,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        if(!Hash::check($request->current_password,auth()->user()->password)) {
            $error = ['error'=>[__("Current password didn't match")]];
            return Helpers::error($error);
        }
        try{
            auth()->user()->update([
                'password'  => Hash::make($request->password),
            ]);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Password successfully updated!')]];
        return Helpers::onlysuccess($message);

    }
    public function deleteAccount(Request $request) {
        $user = authGuardApi()['user'];
         //make unsubscribe
         try{
            (new PushNotificationHelper(['users' => [$user->id],'user_type' => 'agent']))->unsubscribe();
        }catch(Exception $e) {
            // handle exception
        }
        //admin notification
        $this->deleteUserNotificationToAdmin($user,"AGENT",'agent_api');
        $user->status = false;
        $user->email_verified = false;
        $user->kyc_verified = false;
        $user->deleted_at = now();
        $user->save();

        try{
            $user->token()->revoke();
            $message =  ['success'=>[__('Your profile deleted successfully!')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function notifications(){
        $notifications = AgentNotification::auth()->latest()->get()->map(function($item){
            return[
                'id' => $item->id,
                'type' => $item->type,
                'title' => $item->message->title??"",
                'message' => $item->message->message??"",
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });
        $data =[
            'notifications'  => $notifications
        ];
        $message =  ['success'=>[__('Agent Notifications')]];
        return Helpers::success($data,$message);
    }
}
