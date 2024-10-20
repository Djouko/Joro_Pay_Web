<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Models\VirtualCardApi;
use App\Http\Helpers\Api\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\TransactionSetting;
use App\Models\GiftCard;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Providers\Admin\BasicSettingsProvider;
use App\Traits\AdminNotifications\AuthNotifications;

class UserController extends Controller
{
    use AuthNotifications;

    protected $api;
    public function __construct()
    {
        $cardApi = VirtualCardApi::first();
        $this->api =  $cardApi;
    }
    public function home(){
        $user = auth()->user();
        $totalAddMoney = Transaction::auth()->addMoney()->where('status',1)->sum('request_amount');
        $totalReceiveRemittance =Transaction::auth()->remitance()->where('attribute',"RECEIVED")->sum('request_amount');
        $totalSendRemittance =Transaction::auth()->remitance()->where('attribute',"SEND")->sum('request_amount');
        $cardAmount = userActiveCardData()['total_balance'];
        $billPay = amountOnBaseCurrency(Transaction::auth()->billPay()->where('status',1)->get());
        $topUps = amountOnBaseCurrency(Transaction::auth()->mobileTopup()->where('status',1)->get());
        $totalTransactions =Transaction::auth()->where('status', 1)->count();
        $total_gift_cards = GiftCard::auth()->count();

        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => get_default_currency_code(),
            ];
        })->first();
        $transactions = Transaction::auth()->latest()->take(5)->get()->map(function($item){

            $basic_settings = BasicSettings::first();
            if($item->type == payment_gateway_const()::TYPEADDMONEY){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                    'payable' => getAmount($item->payable,2).' '.@$item->currency->currency_code,
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
            }elseif($item->type == payment_gateway_const()::MERCHANTPAYMENT){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                    'payable' => getAmount($item->payable,2).' '.get_default_currency_code(),
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
                    'payable' => getAmount($item->payable,2).' '.@$item->currency->currency_code,
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

            }elseif($item->type == payment_gateway_const()::VIRTUALCARD){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => "Virtual Card".'('. @$item->remark.')',
                    'request_amount' => getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                    'payable' => getAmount($item->payable,2).' '.get_default_currency_code(),
                    'status' => $item->stringStatus->value ,
                    'remark' => $item->remark??"",
                    'date_time' => $item->created_at ,

                ];

            }elseif($item->type == payment_gateway_const()::TYPEMAKEPAYMENT){

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

            }elseif($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' =>  get_transaction_numeric_attribute($item->attribute).getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                    'payable' => getAmount(@$item->payable,2).' '.get_default_currency_code(),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,

                ];

            }elseif($item->type == payment_gateway_const()::TYPEPAYLINK){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,2).' '.@$item->details->charge_calculation->sender_cur_code,
                    'payable' => getAmount(@$item->details->charge_calculation->conversion_payable,2).' '.@$item->details->charge_calculation->receiver_currency_code,
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                ];

            }elseif($item->type == payment_gateway_const()::REQUESTMONEY){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->creator_wallet->currency->code),
                    'payable' =>get_amount($item->payable,$item->creator_wallet->currency->code),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                ];

            }elseif($item->type == payment_gateway_const()::AGENTMONEYOUT){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->creator_wallet->currency->code),
                    'payable' =>get_amount($item->payable,$item->creator_wallet->currency->code),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                ];

            }elseif($item->type == payment_gateway_const()::MONEYIN){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->creator_wallet->currency->code),
                    'payable' =>get_amount($item->request_amount,$item->creator_wallet->currency->code),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                ];
            }elseif($item->type == payment_gateway_const()::GIFTCARD){
                return[
                    'id' => $item->id,
                    'type' =>$item->attribute,
                    'trx' => $item->trx_id,
                    'transaction_type' => $item->type,
                    'request_amount' => get_amount($item->request_amount,$item->creator_wallet->currency->code),
                    'payable' =>get_amount($item->request_amount,$item->creator_wallet->currency->code),
                    'remark' => $item->remark??"",
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                ];
            }
        });
        //module access permissions
        $module_access =[
            'send_money' => module_access_api('send-money'),
            'receive_money' => module_access_api('receive-money'),
            'remittance_money' => module_access_api('remittance-money'),
            'add_money' => module_access_api('add-money'),
            'withdraw_money' => module_access_api('withdraw-money'),
            'make_payment' => module_access_api('make-payment'),
            'virtual_card' => module_access_api('virtual-card'),
            'bill_pay' => module_access_api('bill-pay'),
            'mobile_top_up' => module_access_api('mobile-top-up'),
            'request_money' => module_access_api('request-money'),
            'pay_link' => module_access_api('pay-link'),
            'money_out' => module_access_api('money-out'),
            'gift_cards' => module_access_api('gift-cards'),
        ];

        $cardCreateCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->get()->map(function($data){
            return [
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
            ];
        })->first();
        $cardReloadCharge = TransactionSetting::where('slug','reload_card')->where('status',1)->get()->map(function($data){
            return [
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
            ];
        })->first();

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
            'module_access'             => (object)$module_access,
            'active_virtual_system'     => activeCardSystem(),
            'card_create_charge'        =>  $cardCreateCharge,
            'card_reload_charge'        => $cardReloadCharge,
            'userWallet'                => (object)$userWallet,
            'default_image'             => "public/backend/images/default/profile-default.webp",
            "image_path"                => "public/frontend/user",
            'user'                      => $user,
            'totalAddMoney'             => getAmount($totalAddMoney,2).' '.get_default_currency_code(),
            'totalReceiveRemittance'    => getAmount($totalReceiveRemittance,2).' '.get_default_currency_code(),
            'totalSendRemittance'       => getAmount($totalSendRemittance,2).' '.get_default_currency_code(),
            'cardAmount'                => getAmount($cardAmount,2).' '.get_default_currency_code(),
            'billPay'                   => getAmount($billPay,2).' '.get_default_currency_code(),
            'topUps'                    => getAmount($topUps,2).' '.get_default_currency_code(),
            'totalTransactions'         => $totalTransactions,
            'totalGiftCards'            => $total_gift_cards,
            'transactions'              => $transactions,
        ];
        $message =  ['success'=>[__('User Dashboard')]];
        return Helpers::success($data,$message);
    }
    public function profile(){
        $user = auth()->user();
        $data =[
            'default_image'    => "public/backend/images/default/profile-default.webp",
            "image_path"  =>  "public/frontend/user",
            'user'         =>   $user,
        ];
        $message =  ['success'=>[__('User Profile')]];
        return Helpers::success($data,$message);
    }
    public function profileUpdate(Request $request){
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'firstname'     => "required|string|max:60",
            'lastname'      => "required|string|max:60",
            'country'       => "required|string|max:50",
            'phone_code'    => "required|string|max:6",
            'phone'         => "required|string|max:20|unique:users,mobile,".$user->id,
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

        $validated['firstname']      =$data['firstname'];
        $validated['lastname']      =$data['lastname'];
        $validated['mobile']        = $mobile;
        $validated['mobile_code']   = $mobileCode;
        $complete_phone             = $mobileCode.$mobile;

        $validated['full_mobile']   = $complete_phone;

        $validated['address']       = [
            'country'   =>$data['country']??"",
            'state'     => $data['state'] ?? "",
            'city'      => $data['city'] ?? "",
            'zip'       => $data['zip_code'] ?? "",
            'address'   => $data['address'] ?? "",
        ];
        if($request->hasFile("image")) {
            if($user->image == 'default.png'){
                $oldImage = null;
            }else{
                $oldImage = $user->image;
            }
            $image = upload_file($data['image'],'user-profile', $oldImage);
            $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'user-profile');
            delete_file($image['dev_path']);
            $validated['image']     = $upload_image;
        }

        try{
            $user->update($validated);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__("Profile successfully updated!")]];
        return Helpers::onlysuccess($message);
    }
    public function passwordUpdate(Request $request) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }
        $validator = Validator::make($request->all(), [
            'current_password'      => "required|string",
            'password'              => $passowrd_rule,
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
        $user = auth()->user();
        //make unsubscribe
         try{
            (new PushNotificationHelper(['users' => [$user->id],'user_type' => 'user']))->unsubscribe();
        }catch(Exception $e) {}
        //admin notification
        $this->deleteUserNotificationToAdmin($user,"USER",'api');
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
        $user = auth()->user();
        $notifications = UserNotification::auth()->latest()->get()->map(function($item){
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
        $message =  ['success'=>[__('User Notifications')]];
        return Helpers::success($data,$message);
    }
}
