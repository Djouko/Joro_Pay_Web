<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Events\Admin\NotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\Response;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\BasicSettings;
use App\Models\Merchants\Merchant;
use App\Models\Merchants\MerchantLoginLog;
use App\Models\Merchants\MerchantMailLog;
use App\Models\Merchants\MerchantNotification;
use App\Models\Merchants\MerchantWallet;
use App\Models\Transaction;
use App\Notifications\Kyc\Approved;
use App\Notifications\Kyc\Rejected;
use Exception;
use Illuminate\Support\Arr;
use App\Notifications\User\SendMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Jenssegers\Agent\Agent;


class MerchantCareController extends Controller
{
    public function index()
    {
        $page_title = __("All Merchants");
        $merchants = Merchant::orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.merchant-care.index', compact(
            'page_title',
            'merchants'
        ));
    }
    public function active()
    {
        $page_title = __("Active Merchants");
        $merchants = Merchant::active()->orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.merchant-care.index', compact(
            'page_title',
            'merchants'
        ));
    }
    public function banned()
    {
        $page_title = __("Banned Merchants");
        $merchants = Merchant::banned()->orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.merchant-care.index', compact(
            'page_title',
            'merchants',
        ));
    }
    public function emailUnverified()
    {
        $page_title = __("Email Unverified Merchants");
        $merchants =  Merchant::emailUnverified()->orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.merchant-care.index', compact(
            'page_title',
            'merchants'
        ));
    }
    public function SmsUnverified()
    {
        $page_title = __("SMS Unverified Merchants");
        $merchants =  Merchant::smsUnverified()->orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.merchant-care.index', compact(
            'page_title',
            'merchants'
        ));
    }
    public function KycUnverified()
    {
        $page_title = __("KYC Unverified Merchants");
        $merchants = Merchant::kycUnverified()->orderBy('id', 'desc')->paginate(8);
        return view('admin.sections.merchant-care.index', compact(
            'page_title',
            'merchants'
        ));
    }
    public function emailAllUsers()
    {
        $page_title = __("Email To Merchants");
        return view('admin.sections.merchant-care.email-to-users', compact(
            'page_title',
        ));
    }
    public function sendMailUsers(Request $request) {
        $request->validate([
            'user_type'     => "required|string|max:30",
            'subject'       => "required|string|max:250",
            'message'       => "required|string|max:2000",
        ]);
        $basic_setting = BasicSettings::first();
        $users = [];
        switch($request->user_type) {
            case "active";
                $users = Merchant::active()->get();
                break;
            case "all";
                $users = Merchant::get();
                break;
            case "email_unverified";
                $users = Merchant::emailUnverified()->get();
                break;
            case "kyc_unverified";
                $users = Merchant::kycUnverified()->get();
                break;
            case "banned";
                $users = Merchant::banned()->get();
                break;
        }

        try{
            if( $basic_setting->email_notification == true){
             Notification::send($users,new SendMail((object) $request->all()));
            }
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Email successfully sended")]]);

    }
    public function userDetails($username)
    {
        $page_title = __("Merchants Details");
        $user = Merchant::where('username', $username)->first();
        if(!$user) return back()->with(['error' => [__("Oops! Merchant not exists")]]);
        $balance = MerchantWallet::where('merchant_id', $user->id)->first()->balance ?? 0;
        $money_out_amount = Transaction::toBase()->where('merchant_id', $user->id)->where('type', PaymentGatewayConst::TYPEMONEYOUT)->where('status', 1)->sum('request_amount');
        $total_transaction = Transaction::toBase()->where('merchant_id', $user->id)->where('status', 1)->sum('request_amount');
        $data = [
            'balance'              => $balance,
            'money_out_amount'    => $money_out_amount,
            'total_transaction'    => $total_transaction,
        ];
        return view('admin.sections.merchant-care.details', compact(
            'page_title',
            'user',
            'data'
        ));
    }
    public function userDetailsUpdate(Request $request, $username)
    {

        $request->merge(['username' => $username]);
        $validator = Validator::make($request->all(),[
            'username'              => "required|exists:merchants,username",
            'firstname'             => "required|string|max:60",
            'lastname'              => "required|string|max:60",
            'mobile_code'           => "required|string|max:10",
            'mobile'                => "required|string|max:20",
            'address'               => "nullable|string|max:250",
            'country'               => "nullable|string|max:50",
            'state'                 => "nullable|string|max:50",
            'city'                  => "nullable|string|max:50",
            'zip_code'              => "nullable|numeric|max_digits:8",
            'email_verified'        => 'required|boolean',
            'two_factor_status'   => 'required|boolean',
            'kyc_verified'          => 'required|boolean',
            'status'                => 'required|boolean',
        ]);
        $validated = $validator->validate();
        $validated['address']  = [
            'country'       => $validated['country'] ?? "",
            'state'         => $validated['state'] ?? "",
            'city'          => $validated['city'] ?? "",
            'zip'           => $validated['zip_code'] ?? "",
            'address'       => $validated['address'] ?? "",
        ];
        $validated['mobile_code']       = remove_speacial_char($validated['mobile_code']);
        $validated['mobile']            = remove_speacial_char($validated['mobile']);
        $validated['full_mobile']       = $validated['mobile_code'] . $validated['mobile'];

        $user = Merchant::where('username', $username)->first();

        if(!$user) return back()->with(['error' => [__("Oops! Merchant not exists")]]);

        try {
            $user->update($validated);
        } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Profile Information Updated Successfully!")]]);
    }
    public function kycDetails($username) {
        $user = Merchant::where("username",$username)->first();
        if(!$user) return back()->with(['error' => [__("Oops! Merchant doesn't exists")]]);

        $page_title = __("KYC Profile");
        return view('admin.sections.merchant-care.kyc-details',compact("page_title","user"));
    }

    public function kycApprove(Request $request, $username) {
        $request->merge(['username' => $username]);
        $request->validate([
            'target'        => "required|exists:merchants,username",
            'username'      => "required_without:target|exists:merchants,username",
        ]);
        $basic_setting = BasicSettings::first();
        $user = Merchant::where('username',$request->target)->orWhere('username',$request->username)->first();
        if($user->kyc_verified == GlobalConst::VERIFIED) return back()->with(['warning' => ['Merchant already KYC verified']]);
        if($user->kyc == null) return back()->with(['error' => ['Merchant KYC information not found']]);

        try{
            try{
                if( $basic_setting->email_notification == true){
                    $user->notify(new Approved($user));
                }
            }catch(Exception $e){

            }
            $user->update([
                'kyc_verified'  => GlobalConst::APPROVED,
            ]);
        }catch(Exception $e) {
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("Merchants KYC successfully approved")]]);
    }

    public function kycReject(Request $request, $username) {
        $request->validate([
            'target'        => "required|exists:merchants,username",
            'reason'        => "required|string|max:500"
        ]);
        $basic_setting = BasicSettings::first();
        $user = Merchant::where("username",$request->target)->first();
        if(!$user) return back()->with(['error' => [__("Merchant doesn't exists")]]);
        if($user->kyc == null) return back()->with(['error' => [__("Merchant KYC information not found")]]);

        try{
            try{
                if( $basic_setting->email_notification == true){
                    $user->notify(new Rejected($user,$request->reason));
                }
            }catch(Exception $e){

            }
            $user->update([
                'kyc_verified'  => GlobalConst::REJECTED,
            ]);
            $user->kyc->update([
                'reject_reason' => $request->reason,
            ]);
        }catch(Exception $e) {
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            $user->kyc->update([
                'reject_reason' => null,
            ]);

            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Merchant KYC information is rejected")]]);
    }

    public function search(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);

        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        $merchants = Merchant::search($validated['text'])->limit(10)->get();
        return view('admin.components.search.merchant-search',compact(
            'merchants',
        ));
    }
    public function sendMail(Request $request, $username)
    {
        $request->merge(['username' => $username]);
        $validator = Validator::make($request->all(),[
            'subject'       => 'required|string|max:200',
            'message'       => 'required|string|max:2000',
            'username'      => 'required|string|exists:merchants,username',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with("modal","email-send");
        }
        $validated = $validator->validate();
        $basic_setting = BasicSettings::first();
        $user = Merchant::where("username",$username)->first();

        $validated['merchant_id'] = $user->id;
        $validated = Arr::except($validated,['username']);
        $validated['method']   = "SMTP";
        try{
            MerchantMailLog::create($validated);
            try{
                if( $basic_setting->email_notification == true){
                $user->notify(new SendMail((object) $validated));
                }
            }catch(Exception $e){

            }
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("Mail successfully sended")]]);
    }
    public function mailLogs($username) {
        $page_title = __("Merchant Email Logs");
        $user = Merchant::where("username",$username)->first();
        if(!$user) return back()->with(['error' => [__("Oops! Merchant doesn't exists")]]);
        $logs = MerchantMailLog::where("merchant_id",$user->id)->paginate(12);
        return view('admin.sections.merchant-care.mail-logs',compact(
            'page_title',
            'logs',
        ));
    }
    public function loginLogs($username)
    {
        $page_title = __("Login Logs");
        $user = Merchant::where("username",$username)->first();
        if(!$user) return back()->with(['error' => [__("Oops! Merchant doesn't exists")]]);
        $logs = MerchantLoginLog::where('merchant_id',$user->id)->paginate(12);
        return view('admin.sections.merchant-care.login-logs', compact(
            'logs',
            'page_title',
        ));
    }
    public function loginAsMember(Request $request,$username) {
        $request->merge(['username' => $username]);
        $request->validate([
            'target'            => 'required|string|exists:merchants,username',
            'username'          => 'required_without:target|string|exists:merchants',
        ]);

        try{
            $user = Merchant::where("username",$request->username)->first();
            Auth::guard("merchant")->login($user);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }
        return redirect()->intended(route('merchant.dashboard'));
    }

    public function walletBalanceUpdate(Request $request,$username) {
        $validator = Validator::make($request->all(),[
            'type'      => "required|string|in:add,subtract",
            'wallet'    => "required|numeric|exists:merchant_wallets,id",
            'amount'    => "required|numeric",
            'remark'    => "required|string|max:200",
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','wallet-balance-update-modal');
        }

        $validated = $validator->validate();
        $user_wallet = MerchantWallet::whereHas('merchant',function($q) use ($username){
            $q->where('username',$username);
        })->find($validated['wallet']);
        if(!$user_wallet) return back()->with(['error' => [__("Merchant wallet not found!")]]);
        DB::beginTransaction();
        try{
            $user_wallet_balance = 0;

            switch($validated['type']){
                case "add":
                    $type = "Added";
                    $user_wallet_balance = $user_wallet->balance + $validated['amount'];
                    $user_wallet->balance += $validated['amount'];
                    break;

                case "subtract":
                    $type = "Subtracted";
                    if($user_wallet->balance >= $validated['amount']) {
                        $user_wallet_balance = $user_wallet->balance - $validated['amount'];
                        $user_wallet->balance -= $validated['amount'];
                    }else {
                        return back()->with(['error' => [__("Merchant do not have sufficient balance")]]);
                    }
                    break;
            }

            $inserted_id = DB::table("transactions")->insertGetId([
                'admin_id'          => auth()->user()->id,
                'merchant_id'       => $user_wallet->merchant->id,
                'merchant_wallet_id'=> $user_wallet->id,
                'type'              => PaymentGatewayConst::TYPEADDSUBTRACTBALANCE,
                'attribute'         => $validated['type'] === 'subtract' ? PaymentGatewayConst::SEND: PaymentGatewayConst::RECEIVED,
                'trx_id'            => generate_unique_string("transactions","trx_id",16),
                'request_amount'    => $validated['amount'],
                'payable'           => $validated['amount'],
                'available_balance' => $user_wallet_balance,
                'remark'            => $validated['remark'],
                'status'            => GlobalConst::SUCCESS,
                'created_at'                    => now(),
            ]);


            DB::table('transaction_charges')->insert([
                'transaction_id'    => $inserted_id,
                'percent_charge'    => 0,
                'fixed_charge'      => 0,
                'total_charge'      => 0,
                'created_at'        => now(),
            ]);


            $client_ip = request()->ip() ?? false;
            $location = geoip()->getLocation($client_ip);
            $agent = new Agent();

            // $mac = exec('getmac');
            // $mac = explode(" ",$mac);
            // $mac = array_shift($mac);
            $mac = "";

            DB::table("transaction_devices")->insert([
                'transaction_id'=> $inserted_id,
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

            $user_wallet->save();

            $notification_content = [
                'title'         => __("Update Balance"),
                'message'       => "Your Wallet (".$user_wallet->currency->code.") Balance Has Been ". $type??"",
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => files_asset_path('profile-default'),
            ];

            MerchantNotification::create([
                'type'      => NotificationConst::BALANCE_UPDATE,
                'merchant_id'  => $user_wallet->merchant->id,
                'message'   => $notification_content,
            ]);
            event(new NotificationEvent($notification_content,$user_wallet->merchant));

            //admin notification
            $notification_content['title'] = $user_wallet->merchant->username."'s  Wallet (".$user_wallet->currency->code.") Balance Has Been ". $type??"";
            AdminNotification::create([
                'type'      => NotificationConst::BALANCE_UPDATE,
                'admin_id'  => 1,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Transaction Failed!")]]);
        }

        return back()->with(['success' => [__("Transaction success")]]);
    }
}
