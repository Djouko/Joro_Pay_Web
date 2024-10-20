<?php
namespace App\Http\Controllers\Agent;

use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\Currency;
use App\Models\Agent;
use App\Models\AgentProfit;
use App\Models\AgentQrCode;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use App\Models\UserQrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\AdminNotifications\AuthNotifications;

class DashboardController extends Controller
{
    use AuthNotifications;
    public function index()
    {
        $page_title =__( "Dashboard");
        $baseCurrency = Currency::default();
        $transactions = Transaction::agentAuth()->latest()->take(5)->get();
        $data['totalAddMoney'] = Transaction::agentAuth()->addMoney()->where('status',1)->sum('request_amount');
        $data['totalWithdrawMoney'] = Transaction::agentAuth()->moneyOut()->where('status',1)->sum('request_amount');
        $data['totalSendMoney'] = Transaction::agentAuth()->senMoney()->where('status',1)->sum('request_amount');
        $data['totalMoneyIn'] = Transaction::agentAuth()->moneyIn()->where('status',1)->sum('request_amount');
        $data['totalReceiveMoney'] = Transaction::agentAuth()->agentMoneyOut()->where('status',1)->sum('request_amount');;
        $data['totalSendRemittance'] =Transaction::agentAuth()->remitance()->where('attribute',"SEND")->sum('request_amount');
        $data['billPay'] = amountOnBaseCurrency(Transaction::agentAuth()->billPay()->where('status',1)->get());

        $data['topUps'] = amountOnBaseCurrency(Transaction::agentAuth()->where('status',1)->mobileTopup()->get());
        $data['total_transaction'] = Transaction::agentAuth()->where('status', 1)->count();
        $data['agent_profits'] = AgentProfit::agentAuth()->sum('total_charge');

        $start = strtotime(date('Y-m-01'));
        $end = strtotime(date('Y-m-31'));
          // Add Money
        $pending_data  = [];
        $success_data  = [];
        $canceled_data = [];
        $hold_data     = [];
        $month_day  = [];
        // Money Out
        $Money_out_pending_data  = [];
        $Money_out_success_data  = [];
        $Money_out_canceled_data = [];
        $Money_out_hold_data     = [];
        while ($start <= $end) {
            $start_date = date('Y-m-d', $start);

            // Monthley add money
            $pending = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 2)
                                        ->count();
            $success = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 1)
                                        ->count();
            $canceled = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 4)
                                        ->count();
            $hold = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 3)
                                        ->count();
            $pending_data[]  = $pending;
            $success_data[]  = $success;
            $canceled_data[] = $canceled;
            $hold_data[]     = $hold;

              // Monthley money Out
              $money_pending = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 2)
                                        ->count();
            $money_success = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                ->whereDate('created_at',$start_date)
                                ->where('status', 1)
                                ->count();
            $money_canceled = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                ->whereDate('created_at',$start_date)
                                ->where('status', 4)
                                ->count();
            $money_hold = Transaction::agentAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                            ->whereDate('created_at',$start_date)
                            ->where('status', 3)
                            ->count();
            $Money_out_pending_data[]  = $money_pending;
            $Money_out_success_data[]  = $money_success;
            $Money_out_canceled_data[] = $money_canceled;
            $Money_out_hold_data[]     = $money_hold;

            $month_day[] = date('Y-m-d', $start);
            $start = strtotime('+1 day',$start);
        }
         // Chart one
         $chart_one_data = [
            'pending_data'  => $pending_data,
            'success_data'  => $success_data,
            'canceled_data' => $canceled_data,
            'hold_data'     => $hold_data,
        ];
         // Chart two
         $chart_two_data = [
            'pending_data'  => $Money_out_pending_data,
            'success_data'  => $Money_out_success_data,
            'canceled_data' => $Money_out_canceled_data,
            'hold_data'     => $Money_out_hold_data,
        ];
        $chartData =[
            'chart_one_data'   => $chart_one_data,
            'chart_two_data'   => $chart_two_data,
            'month_day'        => $month_day,
        ];

         //
        return view('agent.dashboard',compact("page_title","baseCurrency",'transactions','data','chartData'));
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('agent.login')->with(['success' => [__('Logout Successfully!')]]);
    }
    public function qrScan($qr_code)
    {

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: PUT, GET, POST");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $qrCode = AgentQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            return response()->json(['error'=>__("Invalid request")]);
        }
        $user = Agent::where('id',$qrCode->agent_id)->active()->first();
        if(!$user){
            return response()->json(['error'=>__('Invalid Agent')]);
        }
        return $user->email;
    }
    public function userQrScan($qr_code)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: PUT, GET, POST");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $qrCode = UserQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            return response()->json(['error'=>__("Invalid request")]);
        }
        $user = User::fwhere('id',$qrCode->user_id)->active()->first();
        if(!$user){
            return response()->json(['error'=>__('Not found')]);
        }
        return $user->email;
    }

    public function deleteAccount(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required',
        ]);

        $user = auth()->user();
        //make unsubscribe
        try{
            (new PushNotificationHelper(['users' => [$user->id],'user_type' => 'agent']))->unsubscribe();
        }catch(Exception $e) {
            // handle exception
        }
        //admin notification
        $this->deleteUserNotificationToAdmin($user,"AGENT",'agent');
        $user->status = false;
        $user->email_verified = false;
        $user->kyc_verified = false;
        $user->deleted_at = now();
        $user->save();
        try{
            Auth::logout();
            return redirect()->route('agent.login')->with(['success' => [__('Your profile deleted successfully!')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
}
