<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\Response;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\BasicSettings;
use App\Models\AgentLoginLog;
use App\Models\AgentMailLog;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
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

class AgentCareController extends Controller
{
    public function index()
    {
        $page_title = __("All Agents");
        $agents = Agent::orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.agent-care.index', compact(
            'page_title',
            'agents'
        ));
    }
    public function active()
    {
        $page_title = __("Active Agent");
        $agents = Agent::active()->orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.agent-care.index', compact(
            'page_title',
            'agents'
        ));
    }
    public function banned()
    {
        $page_title = __( "Banned Agents");
        $agents = Agent::banned()->orderBy('id', 'desc')->paginate(12);
        return view('admin.sections.agent-care.index', compact(
            'page_title',
            'agents',
        ));
    }
    public function emailUnverified()
    {
        $page_title = __("Email Unverified Agents");
        $agents = Agent::active()->orderBy('id', 'desc')->emailUnverified()->paginate(12);
        return view('admin.sections.agent-care.index', compact(
            'page_title',
            'agents'
        ));
    }
    public function KycUnverified()
    {
        $page_title = __("KYC Unverified Agents");
        $agents = Agent::kycUnverified()->orderBy('id', 'desc')->paginate(8);
        return view('admin.sections.agent-care.index', compact(
            'page_title',
            'agents'
        ));
    }
    public function emailAllUsers()
    {
        $page_title = __("Email To Agents");
        return view('admin.sections.agent-care.email-to-users', compact(
            'page_title',
        ));
    }
    public function sendMailUsers(Request $request) {
        $request->validate([
            'user_type'     => "required|string|max:30",
            'subject'       => "required|string|max:250",
            'message'       => "required|string|max:2000",
        ]);

        $users = [];
        switch($request->user_type) {
            case "active";
                $users = Agent::active()->get();
                break;
            case "all";
                $users = Agent::get();
                break;
            case "email_unverified";
                $users = Agent::emailUnverified()->get();
                break;
            case "kyc_unverified";
                $users = Agent::kycUnverified()->get();
                break;
            case "banned";
                $users = Agent::banned()->get();
                break;
        }

        try{
            Notification::send($users,new SendMail((object) $request->all()));
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Email successfully sended")]]);

    }
    public function userDetails($username)
    {
        $page_title = __("Agent Details");
        $user = Agent::where('username', $username)->first();
        if(!$user) return back()->with(['error' => ['Opps! Agent not exists']]);
        $balance = AgentWallet::where('agent_id', $user->id)->first()->balance ?? 0;
        $add_money_amount = Transaction::where('agent_id', $user->id)->where('type', PaymentGatewayConst::TYPEADDMONEY)->where('status', 1)->sum('request_amount');
        $money_out_amount = Transaction::where('agent_id', $user->id)->where('type', PaymentGatewayConst::TYPEMONEYOUT)->where('status', 1)->sum('request_amount');
        $total_transaction = Transaction::where('agent_id', $user->id)->where('status', 1)->sum('request_amount');
        $data = [
            'balance'              => $balance,
            'total_transaction'    => $total_transaction,
            'add_money_amount'    => $add_money_amount,
            'money_out_amount'    => $money_out_amount,
        ];
        return view('admin.sections.agent-care.details', compact(
            'page_title',
            'user',
            'data'
        ));
    }
    public function userDetailsUpdate(Request $request, $username)
    {
        $request->merge(['username' => $username]);
        $validator = Validator::make($request->all(),[
            'username'              => "required|exists:agents,username",
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
            'two_factor_verified'   => 'required|boolean',
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

        $user = Agent::where('username', $username)->first();

        if(!$user) return back()->with(['error' => [__("Ops! Agent not exists")]]);

        try {
            $user->update($validated);
        } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Profile Information Updated Successfully!")]]);
    }
    public function kycDetails($username) {
        $user = Agent::where("username",$username)->first();
        if(!$user) return back()->with(['error' => [__("Ops! agent doesn't exists")]]);

        $page_title = __("KYC Profile");
        return view('admin.sections.agent-care.kyc-details',compact("page_title","user"));
    }

    public function kycApprove(Request $request, $username) {
        $request->merge(['username' => $username]);
        $request->validate([
            'target'        => "required|exists:agents,username",
            'username'      => "required_without:target|exists:agents,username",
        ]);
        $basic_setting = BasicSettings::first();
        $user = Agent::where('username',$request->target)->orWhere('username',$request->username)->first();
        if($user->kyc_verified == GlobalConst::VERIFIED) return back()->with(['warning' => ['Agent already KYC verified']]);
        if($user->kyc == null) return back()->with(['error' => [__("Agent KYC information not found")]]);

        try{
            $user->update([
                'kyc_verified'  => GlobalConst::APPROVED,
            ]);
            try{
                if( $basic_setting->email_notification == true){
                    $user->notify(new Approved($user));
                }
            }catch(Exception $e){

            }
        }catch(Exception $e) {
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("Agent KYC successfully approved")]]);
    }

    public function kycReject(Request $request, $username) {
        $request->validate([
            'target'        => "required|exists:agents,username",
            'reason'        => "required|string|max:500"
        ]);
        $basic_setting = BasicSettings::first();
        $user = Agent::where("username",$request->target)->first();
        if(!$user) return back()->with(['error' => [__("Agent doesn't exists")]]);
        if($user->kyc == null) return back()->with(['error' => [__("Agent KYC information not found")]]);

        try{
            $user->update([
                'kyc_verified'  => GlobalConst::REJECTED,
            ]);
            $user->kyc->update([
                'reject_reason' => $request->reason,
            ]);
            try{
                if( $basic_setting->email_notification == true){
                    $user->notify(new Rejected($user,$request->reason));
                }
            }catch(Exception $e){

            }
        }catch(Exception $e) {
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            $user->kyc->update([
                'reject_reason' => null,
            ]);

            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Agent KYC information is rejected")]]);
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
        $agents = Agent::search($validated['text'])->limit(10)->get();
        return view('admin.components.search.agent-search',compact(
            'agents',
        ));
    }
    public function sendMail(Request $request, $username)
    {
        $request->merge(['username' => $username]);
        $validator = Validator::make($request->all(),[
            'subject'       => 'required|string|max:200',
            'message'       => 'required|string|max:2000',
            'username'      => 'required|string|exists:agents,username',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with("modal","email-send");
        }
        $validated = $validator->validate();
        $user = Agent::where("username",$username)->first();

        $validated['agent_id'] = $user->id;
        $validated = Arr::except($validated,['username']);
        $validated['method']   = "SMTP";
        try{
            AgentMailLog::create($validated);
            try{
                $user->notify(new SendMail((object) $validated));
            }catch(Exception $e){

            }
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("Mail successfully sended")]]);
    }
    public function mailLogs($username) {
        $page_title = "Agent Email Logs";
        $user = Agent::where("username",$username)->first();
        if(!$user) return back()->with(['error' => [__("Ops! Agent doesn't exists")]]);
        $logs = AgentMailLog::where("agent_id",$user->id)->paginate(12);
        return view('admin.sections.agent-care.mail-logs',compact(
            'page_title',
            'logs',
        ));
    }
    public function loginLogs($username)
    {
        $page_title = "Login Logs";
        $user = Agent::where("username",$username)->first();
        if(!$user) return back()->with(['error' => [__("Ops! Agent doesn't exists")]]);
        $logs = AgentLoginLog::where('agent_id',$user->id)->paginate(12);
        return view('admin.sections.agent-care.login-logs', compact(
            'logs',
            'page_title',
        ));
    }
    public function loginAsMember(Request $request,$username) {
        $request->merge(['username' => $username]);
        $request->validate([
            'target'            => 'required|string|exists:agents,username',
            'username'          => 'required_without:target|string|exists:agents',
        ]);

        try{
            $user = Agent::where("username",$request->username)->first();
            Auth::guard("agent")->login($user);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }
        return redirect()->intended(route('agent.dashboard'));
    }
    public function walletBalanceUpdate(Request $request,$username) {
        $validator = Validator::make($request->all(),[
            'type'      => "required|string|in:add,subtract",
            'wallet'    => "required|numeric|exists:agent_wallets,id",
            'amount'    => "required|numeric",
            'remark'    => "required|string|max:200",
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','wallet-balance-update-modal');
        }
        $validated = $validator->validate();
        $user_wallet = AgentWallet::whereHas('agent',function($q) use ($username){
            $q->where('username',$username);
        })->find($validated['wallet']);
        if(!$user_wallet) return back()->with(['error' => [__("Agent wallet not found!")]]);
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
                        return back()->with(['error' => [__("Agent do not have sufficient balance")]]);
                    }
                    break;
            }

            $inserted_id = DB::table("transactions")->insertGetId([
                'admin_id'          => auth()->user()->id,
                'agent_id'           => $user_wallet->agent->id,
                'agent_wallet_id'    => $user_wallet->id,
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
            $user_wallet->save();

            $notification_content = [
                'title'         => __("Update Balance"),
                'message'       => "Your Wallet (".$user_wallet->currency->code.") Balance Has Been ". $type??"",
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => files_asset_path('profile-default'),
            ];
            AgentNotification::create([
                'type'      => NotificationConst::BALANCE_UPDATE,
                'agent_id'  => $user_wallet->agent->id,
                'message'   => $notification_content,
            ]);

            //admin notification
             $notification_content['title'] = $user_wallet->agent->username."'s  Wallet (".$user_wallet->currency->code.") Balance Has Been ". $type??"";
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
