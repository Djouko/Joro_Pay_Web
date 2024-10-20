<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pusher\PushNotifications\PushNotifications;
use App\Models\Admin\AdminNotification;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Response;
use App\Models\Blog;
use App\Models\Merchants\Merchant;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\User;
use App\Models\UserSupportTicket;


class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $page_title = __("Dashboard");
        $transactions = Transaction::where('type', PaymentGatewayConst::TYPEADDMONEY)->latest()->take(5)->get();
        $last_month_start =  date('Y-m-01', strtotime('-1 month', strtotime(date('Y-m-d'))));
        $last_month_end =  date('Y-m-31', strtotime('-1 month', strtotime(date('Y-m-d'))));
        $this_month_start = date('Y-m-01');
        $this_month_end = date('Y-m-d');
        $this_weak = date('Y-m-d', strtotime('-1 week', strtotime(date('Y-m-d'))));
        $this_month = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
        $this_year = date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d'))));

         // Add Money
         $add_money_total_balance = Transaction::toBase()->where('type', PaymentGatewayConst::TYPEADDMONEY)->sum('request_amount');
         $completed_add_money =  Transaction::toBase()
                             ->where('type', PaymentGatewayConst::TYPEADDMONEY)
                             ->where('status', 1)
                             ->sum('request_amount');
         $pending_add_money =  Transaction::toBase()->where('status', 2)
                                             ->where('type', PaymentGatewayConst::TYPEADDMONEY)
                                             ->sum('request_amount');

         if($pending_add_money == 0){
             $add_money_percent = 0;
         }else{
            $add_money_percent = ($completed_add_money / ($completed_add_money + $pending_add_money)) * 100;
         }
         //Money out
         $total_money_out = Transaction::toBase()->where('type', PaymentGatewayConst::TYPEMONEYOUT)->where('status','!=',4)->sum('request_amount');
         $completed_money_out =  Transaction::toBase()
                             ->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                             ->where('status', 1)
                             ->sum('request_amount');
         $pending_money_out =  Transaction::toBase()->where('status', 2)
                                             ->where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                             ->sum('request_amount');

         if($pending_money_out == 0){
             $money_out_percent = 0;
         }else{
            $money_out_percent = ($completed_money_out / ($completed_money_out + $pending_money_out)) * 100;
         }

         //total profits
         $total_profits = TransactionCharge::toBase()->sum('total_charge');

         $this_month_profits = TransactionCharge::toBase()
             ->whereBetween('created_at', [$this_month_start, $this_month_end])
             ->sum('total_charge');

         $last_month_profits = TransactionCharge::toBase()
             ->whereBetween('created_at', [$last_month_start, $last_month_end])
             ->sum('total_charge');

         if ($last_month_profits == 0) {
             $profit_percent  = 0;
         } else {
             $profit_percent = ($this_month_profits / ($this_month_profits + $last_month_profits)) * 100;
         }
        //Virtual Cards
        //   $total_cards = VirtualCard::toBase()->count();
          $get_cards = activeCardData();
          $total_cards =  $get_cards['virtual_cards'];

          if($get_cards['inactive_cards'] == 0){
              $card_perchant = 0;
          }else{
             $card_perchant = ($get_cards['active_cards'] / ($get_cards['active_cards'] + $get_cards['inactive_cards'])) * 100;
          }
           //Remittance
         $total_remittance = Transaction::toBase()->where('type', PaymentGatewayConst::SENDREMITTANCE)->where('status','!=',4)->sum('request_amount');
         $completed_remittance =  Transaction::toBase()
                             ->where('type', PaymentGatewayConst::SENDREMITTANCE)
                             ->where('status', 1)
                             ->sum('request_amount');
         $pending_remittance =  Transaction::toBase()->where('status', 2)
                                             ->where('type', PaymentGatewayConst::SENDREMITTANCE)
                                             ->sum('request_amount');

         if($pending_remittance == 0 && $completed_remittance != 0){
             $remittance_percent = 100;
         }elseif($pending_remittance == 0 && $completed_remittance == 0){
            $remittance_percent = 0;
         }else{
            $remittance_percent = ($completed_remittance / ($completed_remittance + $pending_remittance)) * 100;
         }
           //Users
           $total_users = User::toBase()->count();

           $active_users =  User::active()->count();
           $unverified_users =User::emailUnverified()->count();

           if($unverified_users == 0 && $active_users != 0){
               $user_perchant = 100;
           }elseif($unverified_users == 0 && $active_users == 0){
            $user_perchant = 0;
           }else{
              $user_perchant = ($active_users / ($active_users + $unverified_users)) * 100;
           }


            //Merchants
            $total_merchants = Merchant::toBase()->count();

            $active_merchants =  Merchant::active()->count();
            $unverified_merchants =Merchant::smsUnverified()->count();

            if($unverified_merchants == 0 && $active_merchants != 0){
                $merchant_perchant = 100;
            }elseif($unverified_merchants == 0 && $active_merchants == 0){
             $merchant_perchant = 0;
            }else{
               $merchant_perchant = ($active_merchants / ($active_merchants + $unverified_merchants)) * 100;
            }
            //Support Tikets
            $total_tickets = UserSupportTicket::toBase()->count();

            $active_tickets =  UserSupportTicket::active()->count();
            $pending_tickets = UserSupportTicket::Pending()->count();

            if($pending_tickets == 0 && $active_tickets != 0){
                $ticket_perchant = 100;
            }elseif($pending_tickets == 0 && $active_tickets == 0){
             $ticket_perchant = 0;
            }else{
               $ticket_perchant = ($active_tickets / ($active_tickets + $pending_tickets)) * 100;
            }

        //charts
        // Monthly Add Money
        $start = strtotime(date('Y-m-01'));
        $end = strtotime(date('Y-m-31'));

        // Add Money
        $pending_data  = [];
        $success_data  = [];
        $canceled_data = [];
        $hold_data     = [];
        // Money Out
        $Money_out_pending_data  = [];
        $Money_out_success_data  = [];
        $Money_out_canceled_data = [];
        $Money_out_hold_data     = [];
        //virtual card
        $card_pending_data  =[];
        $card_success_data  = [];
        $card_canceled_data = [];
        $card_hold_data     = [];
         //Announcement
         $event_data    = [];
         $all_data    = [];

        $month_day  = [];
        while ($start <= $end) {
            $start_date = date('Y-m-d', $start);

            // Monthley add money
            $pending = Transaction::where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 2)
                                        ->count();
            $success = Transaction::where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 1)
                                        ->count();
            $canceled = Transaction::where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 4)
                                        ->count();
            $hold = Transaction::where('type', PaymentGatewayConst::TYPEADDMONEY)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 3)
                                        ->count();
            $pending_data[]  = $pending;
            $success_data[]  = $success;
            $canceled_data[] = $canceled;
            $hold_data[]     = $hold;

            // Monthley money Out
            $money_pending = Transaction::where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 2)
                                        ->count();
            $money_success = Transaction::where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 1)
                                        ->count();
            $money_canceled = Transaction::where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 4)
                                        ->count();
            $money_hold = Transaction::where('type', PaymentGatewayConst::TYPEMONEYOUT)
                                        ->whereDate('created_at',$start_date)
                                        ->where('status', 3)
                                        ->count();
            $Money_out_pending_data[]  = $money_pending;
            $Money_out_success_data[]  = $money_success;
            $Money_out_canceled_data[] = $money_canceled;
            $Money_out_hold_data[]     = $money_hold;

            //Monthley virtual
            $card_pending = Transaction::where('type', PaymentGatewayConst::VIRTUALCARD)
                        ->whereDate('created_at',$start_date)
                        ->where('status', 2)
                        ->count();
            $card_success = Transaction::where('type', PaymentGatewayConst::VIRTUALCARD)
                        ->whereDate('created_at',$start_date)
                        ->where('status', 1)
                        ->count();
            $card_canceled = Transaction::where('type', PaymentGatewayConst::VIRTUALCARD)
                        ->whereDate('created_at',$start_date)
                        ->where('status', 4)
                        ->count();
            $card_hold = Transaction::where('type', PaymentGatewayConst::VIRTUALCARD)
                        ->whereDate('created_at',$start_date)
                        ->where('status', 3)
                        ->count();

            $card_pending_data[]  = $card_pending;
            $card_success_data[]  = $card_success;
            $card_canceled_data[] = $card_canceled;
            $card_hold_data[]    = $card_hold;

            // Event,Campaign,Gallery

            $event = Blog::where('status', 1)
            ->whereDate('created_at',$start_date)
            ->count();
            $event_data[]    = $event;
            $all_data[]      = $event ;

            $month_day[] = date('Y-m-d', $start);
            $start = strtotime('+1 day',$start);
        }
         //

        // Chart one
        $chart_one_data = [
            'pending_data'  => $pending_data,
            'success_data'  => $success_data,
            'canceled_data' => $canceled_data,
            'hold_data'     => $hold_data,
        ];
         // Chart two
         $chart_two_data = [
            'pending_data'  => $card_pending_data,
            'success_data'  => $card_success_data,
            'canceled_data' => $card_canceled_data,
            'hold_data'     => $card_hold_data,
        ];
         // Chart three
         $chart_three_data = [
            'pending_data'  => $Money_out_pending_data,
            'success_data'  => $Money_out_success_data,
            'canceled_data' => $Money_out_canceled_data,
            'hold_data'     => $Money_out_hold_data,
        ];

        $total_user = User::toBase()->count();
        $unverified_user = User::toBase()->where('sms_verified', 0)->count();
        $active_user = User::toBase()->where('status', 1)->count();
        $banned_user = User::toBase()->where('status', 0)->count();
        // Chart four | User analysis
        $chart_four = [$active_user, $banned_user,$unverified_user,$total_user];

        // Chart for merchant analysis
        $total_merchant = Merchant::toBase()->count();
        $unverified_merchant = Merchant::toBase()->where('sms_verified', 0)->count();
        $active_merchant = Merchant::toBase()->where('status', 1)->count();
        $banned_merchant = Merchant::toBase()->where('status', 0)->count();
        $chart_merchant = [$active_merchant, $banned_merchant,$unverified_merchant,$total_merchant];

         $data = [
            'add_money_total_balance'    => $add_money_total_balance,
            'completed_add_money'      => $completed_add_money,
            'pending_add_money' => $pending_add_money,
            'add_money_percent'    => $add_money_percent,

            'total_money_out'    => $total_money_out,
            'completed_money_out'      => $completed_money_out,
            'pending_money_out' => $pending_money_out,
            'money_out_percent'    => $money_out_percent,

            'total_profits'    => $total_profits,
            'this_month_profits'      => $this_month_profits,
            'last_month_profits' => $last_month_profits,
            'profit_percent'    => $profit_percent,

            'total_cards'    => $total_cards,
            'active_cards'      => $get_cards['active_cards'],
            'inactive_cards' => $get_cards['inactive_cards'],
            'card_perchant'    => $card_perchant,

            'total_remittance'    => $total_remittance,
            'completed_remittance'      => $completed_remittance,
            'pending_remittance' => $pending_remittance,
            'remittance_percent'    => $remittance_percent,

            'total_users'    => $total_users,
            'active_users'      => $active_users,
            'unverified_users' => $unverified_users,
            'user_perchant'    => $user_perchant,

            'total_merchants'    => $total_merchants,
            'active_merchants'      => $active_merchants,
            'unverified_merchants' => $unverified_merchants,
            'merchant_perchant'    => $merchant_perchant,

            'total_tickets'    => $total_tickets,
            'active_tickets'      => $active_tickets,
            'pending_tickets' => $pending_tickets,
            'ticket_perchant'    => $ticket_perchant,

            'chart_one_data'   => $chart_one_data,
            'chart_two_data'   => $chart_two_data,
            'chart_three_data' => $chart_three_data,
            'chart_four_data'  => $chart_four,
            'chart_merchant'  => $chart_merchant,
            'month_day'        => $month_day,

            'transactions'        => $transactions
        ];
        return view('admin.sections.dashboard.index',compact(
            'page_title','data'
        ));
    }


    /**
     * Logout Admin From Dashboard
     * @return view
     */
    public function logout(Request $request) {

        // $push_notification_setting = BasicSettingsProvider::get()->push_notification_config;

        // if($push_notification_setting) {
        //     $method = $push_notification_setting->method ?? false;

        //     if($method == "pusher") {
        //         $instant_id     = $push_notification_setting->instance_id ?? false;
        //         $primary_key    = $push_notification_setting->primary_key ?? false;

        //         if($instant_id && $primary_key) {
        //             $pusher_instance = new PushNotifications([
        //                 "instanceId"    => $instant_id,
        //                 "secretKey"     => $primary_key,
        //             ]);

        //             $pusher_instance->deleteUser("".Auth::user()->id."");
        //         }
        //     }

        // }

        $admin = auth()->user();
        try{
            $admin->update([
                'last_logged_out'   => now(),
                'login_status'      => false,
            ]);
        }catch(Exception $e) {
            // Handle Error
        }

        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }


    /**
     * Function for clear admin notification
     */
    public function notificationsClear() {
        $admin = auth()->user();
        if(!$admin) {
            return false;
        }
        try{
            $notifications = AdminNotification::auth()->where('clear_at',null)->get();
            foreach( $notifications as $notify){
                $notify->clear_at = now();
                $notify->save();

            }
        }catch(Exception $e) {
            $error = ['error' => [__("Something went wrong! Please try again.")]];
            return Response::error($error,null,404);
        }

        $success = ['success' => [__("Notifications clear successfully!")]];
        return Response::success($success,null,200);
    }
}
