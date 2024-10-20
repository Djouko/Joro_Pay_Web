<?php
namespace App\Http\Controllers\Merchant;

use App\Constants\GlobalConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\Currency;
use App\Models\Merchants\SandboxWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $page_title = __("Merchant Dashboard");
        $baseCurrency = Currency::default();
        $transactions = Transaction::merchantAuth()->latest()->take(5)->get();
        $money_out_amount = Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)->where('status', 1)->sum('request_amount');
        $receive_money = Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMAKEPAYMENT)->where('status', 1)->where('attribute','RECEIVED')->sum('request_amount');
        $total_transaction = Transaction::merchantAuth()->where('status', 1)->count();
        $sandbox_fiat_wallets = SandboxWallet::auth()->whereHas("currency",function($q) {
            return $q->where("type",GlobalConst::FIAT);
        })->orderByDesc("balance")->limit(8)->get();
        $data = [
            'receive_money'    => $receive_money,
            'money_out_amount'    => $money_out_amount,
            'total_transaction'    => $total_transaction,
            'sandbox_fiat_wallets'    => $sandbox_fiat_wallets
        ];

        $start = strtotime(date('Y-m-01'));
        $end = strtotime(date('Y-m-31'));
          // Add Money
        $pending_data  = [];
        $success_data  = [];
        $canceled_data = [];
        $hold_data     = [];
        $month_day  = [];
        while ($start <= $end) {
            $start_date = date('Y-m-d', $start);

            // Monthley money out
            $pending = Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 2)
                                        ->count();
            $success = Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 1)
                                        ->count();
            $canceled = Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 4)
                                        ->count();
            $hold = Transaction::merchantAuth()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 3)
                                        ->count();
            $pending_data[]  = $pending;
            $success_data[]  = $success;
            $canceled_data[] = $canceled;
            $hold_data[]     = $hold;

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

        $chartData =[
            'chart_one_data'   => $chart_one_data,
            'month_day'        => $month_day,
        ];
        return view('merchant.dashboard',compact("page_title","baseCurrency",'transactions','data','chartData'));
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('merchant.login')->with(['success' => [__('Logout Successfully!')]]);
    }

}
