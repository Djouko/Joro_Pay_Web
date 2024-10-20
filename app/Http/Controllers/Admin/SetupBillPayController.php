<?php

namespace App\Http\Controllers\Admin;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Exports\BillPayTrxExport;
use App\Http\Controllers\Controller;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\BillPay\Approved;
use App\Notifications\User\BillPay\Rejected;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SetupBillPayController extends Controller
{
    protected $basic_settings;

    public function __construct()
    {
            $this->basic_settings = BasicSettingsProvider::get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title =__( "All Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',

        )->where('type', PaymentGatewayConst::BILLPAY)->latest()->paginate(20);
        return view('admin.sections.bill-pay.index',compact(
            'page_title','transactions'
        ));
    }

    /**
     * Display All Pending Logs
     * @return view
     */
    public function pending() {
        $page_title =__("Pending Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',

         )->where('type', PaymentGatewayConst::BILLPAY)->where('status', 2)->latest()->paginate(20);
        return view('admin.sections.bill-pay.index',compact(
            'page_title','transactions'
        ));
    }
    /**
     * Display All Processing Logs
     * @return view
     */
    public function processing() {
        $page_title =__("Processing Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
         )->where('type', PaymentGatewayConst::BILLPAY)->where('status',7)->latest()->paginate(20);
        return view('admin.sections.bill-pay.index',compact(
            'page_title','transactions'
        ));
    }
    /**
     * Display All Complete Logs
     * @return view
     */
    public function complete() {
        $page_title = __("Complete Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
         )->where('type', PaymentGatewayConst::BILLPAY)->where('status', 1)->latest()->paginate(20);
        return view('admin.sections.bill-pay.index',compact(
            'page_title','transactions'
        ));
    }
    /**
     * Display All Canceled Logs
     * @return view
     */
    public function canceled() {
        $page_title = __("Canceled Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
         )->where('type', PaymentGatewayConst::BILLPAY)->where('status',4)->latest()->paginate(20);
        return view('admin.sections.bill-pay.index',compact(
            'page_title','transactions'
        ));
    }
    public function details($id){

        $data = Transaction::where('id',$id)->with(
          'user:id,firstname,lastname,email,username,full_mobile',
        )->where('type',PaymentGatewayConst::BILLPAY)->first();
        $pre_title = __("Bill Pay details for");
        $page_title = $pre_title.'  '.$data->trx_id.' ('.$data->details->bill_type_name.")";
        return view('admin.sections.bill-pay.details', compact(
            'page_title',
            'data'
        ));
    }
    public function approved(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', PaymentGatewayConst::BILLPAY)->first();

        $up['status'] = 1;
        try{
           $approved = $data->fill($up)->save();
           if( $approved){

            //notification
            $notification_content = [
                'title'         => __("Bill Pay"),
                'message'       => "Your Bill Pay request approved by admin " .getAmount($data->request_amount,2).' '.get_default_currency_code()." & Bill Number is: ".@$data->details->bill_number." successful.",
                'image'         => files_asset_path('profile-default'),
            ];

            if($data->user_id != null) {
                $notifyData = [
                    'trx_id'  => $data->trx_id,
                    'bill_type'  => @$data->details->bill_type_name,
                    'bill_number'  => @$data->details->bill_number,
                    'request_amount'   => $data->request_amount,
                    'charges'   => $data->charge->total_charge,
                    'payable'  => $data->payable,
                    'current_balance'  => getAmount($data->available_balance, 4),
                    'status'  => __("success"),
                  ];
                $user = $data->user;
                try{
                    if( $this->basic_settings->email_notification == true){
                        $user->notify(new Approved($user,(object)$notifyData));
                    }
                }catch(Exception $e){

                }
                UserNotification::create([
                    'type'      => NotificationConst::BILL_PAY,
                    'user_id'  =>  $data->user_id,
                    'message'   => $notification_content,
                ]);
                DB::commit();
            }else if($data->agent_id != null) {
                $notifyData = [
                    'trx_id'  => $data->trx_id,
                    'bill_type'  => @$data->details->bill_type_name,
                    'bill_number'  => @$data->details->bill_number,
                    'request_amount'   => $data->request_amount,
                    'charges'   => $data->charge->total_charge,
                    'payable'  => $data->payable,
                    'current_balance'  => getAmount($data->available_balance, 4),
                    'status'  => __("success"),
                  ];
                $user = $data->agent;
                $returnWithProfit = ($user->wallet->balance +  $data->details->charges->agent_total_commission);
                $this->updateSenderWalletBalance($user->wallet,$returnWithProfit,$data);
                $this->agentProfitInsert($data->id,$user->wallet,(array)$data->details->charges);
                try{
                    if( $this->basic_settings->agent_email_notification == true){
                        $user->notify(new Approved($user,(object)$notifyData));
                    }
                }catch(Exception $e){

                }
                AgentNotification::create([
                    'type'      => NotificationConst::BILL_PAY,
                    'agent_id'  =>  $data->agent_id,
                    'message'   => $notification_content,
                ]);
                DB::commit();
            }
           }

            return redirect()->back()->with(['success' => [__("Bill Pay request approved successfully")]]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function rejected(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
            'reject_reason' => 'required|string:max:200',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', PaymentGatewayConst::BILLPAY)->first();

        try{
            //user wallet
            if($data->user_id != null) {
                $userWallet = UserWallet::where('user_id',$data->user_id)->first();
                $userWallet->balance +=  $data->payable;
                $userWallet->save();
            }else if($data->agent_id != null) {
                $userWallet = AgentWallet::where('agent_id',$data->agent_id)->first();
                $userWallet->balance +=  $data->payable;
                $userWallet->save();
            }
            $up['status'] = 4;
            $up['reject_reason'] = $request->reject_reason;
            $up['available_balance'] = $userWallet->balance;
            $rejected =  $data->fill($up)->save();
            if( $rejected){

                //user notifications
                $notification_content = [
                    'title'         => __("Bill Pay"),
                    'message'       => "Your Bill Pay request rejected by admin " .getAmount($data->request_amount,2).' '.get_default_currency_code()." & Bill Number is: ".@$data->details->bill_number,
                    'image'         => files_asset_path('profile-default'),
                ];

                if($data->user_id != null) {
                    $notifyData = [
                        'trx_id'  => $data->trx_id,
                        'bill_type'  => @$data->details->bill_type_name,
                        'bill_number'  => @$data->details->bill_number,
                        'request_amount'   => $data->request_amount,
                        'charges'   => $data->charge->total_charge,
                        'payable'  => $data->payable,
                        'current_balance'  => getAmount($data->available_balance, 4),
                        'status'  => __("Rejected"),
                        'reason'  => $request->reject_reason,
                      ];
                    $user = $data->user;
                    try{
                        if( $this->basic_settings->email_notification == true){
                            $user->notify(new Rejected($user,(object)$notifyData));
                        }
                    }catch(Exception $e){

                    }
                    UserNotification::create([
                        'type'      => NotificationConst::BILL_PAY,
                        'user_id'  =>  $data->user_id,
                        'message'   => $notification_content,
                    ]);
                    DB::commit();
                }else if($data->agent_id != null) {
                    $notifyData = [
                        'trx_id'  => $data->trx_id,
                        'bill_type'  => @$data->details->bill_type_name,
                        'bill_number'  => @$data->details->bill_number,
                        'request_amount'   => $data->request_amount,
                        'charges'   => $data->charge->total_charge,
                        'payable'  => $data->payable,
                        'current_balance'  => getAmount($data->available_balance, 4),
                        'status'  => __("Rejected"),
                        'reason'  => $request->reject_reason,
                      ];
                    $user = $data->agent;
                    try{
                        if( $this->basic_settings->agent_email_notification == true){
                            $user->notify(new Rejected($user,(object)$notifyData));
                        }
                    }catch(Exception $e){

                    }
                    AgentNotification::create([
                        'type'      => NotificationConst::BILL_PAY,
                        'agent_id'  =>  $data->agent_id,
                        'message'   => $notification_content,
                    ]);
                    DB::commit();
                }
            }
            return redirect()->back()->with(['success' => [__("Bill Pay request rejected successfully")]]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function agentProfitInsert($id,$authWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges['agent_percent_commission'],
                'fixed_charge'      => $charges['agent_fixed_commission'],
                'total_charge'      => $charges['agent_total_commission'],
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function updateSenderWalletBalance($senderWallet,$afterCharge,$transaction) {
        $transaction->update([
            'available_balance'   => $afterCharge,
        ]);
        $senderWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_Bill_Pay_Logs".'.xlsx';
        return Excel::download(new BillPayTrxExport, $file_name);
    }

}
