<?php

namespace App\Http\Controllers\Admin;

use App\Constants\NotificationConst;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Notifications\User\AddMoney\ApprovedByAdminMail;
use App\Notifications\User\AddMoney\RejectedByAdminMail;
use App\Providers\Admin\BasicSettingsProvider;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AddMoneyTransactionExport;
use App\Models\AgentNotification;
use App\Models\UserNotification;

class AddMoneyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index()
    {
        $page_title = __( "All Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'ADD-MONEY')->latest()->paginate(20);


        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }


    /**
     * Pending Add Money Logs View.
     * @return view $pending-add-money-logs
     */
    public function pending()
    {
        $page_title = __("Pending Logs");
        $transactions = Transaction::with(
         'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'add-money')->where('status', 2)->latest()->paginate(20);
        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }


    /**
     * Complete Add Money Logs View.
     * @return view $complete-add-money-logs
     */
    public function complete()
    {
        $page_title = __("Complete Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'add-money')->where('status', 1)->latest()->paginate(20);
        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }

    /**
     * Canceled Add Money Logs View.
     * @return view $canceled-add-money-logs
     */
    public function canceled()
    {
        $page_title = __("Canceled Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'add-money')->where('status',4)->latest()->paginate(20);
        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }
    public function addMoneyDetails($id){

        $data = Transaction::where('id',$id)->with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name,alias,payment_gateway_id,currency_code,rate',
        )->where('type', 'add-money')->first();
        $pre_title = __("Add money details for");
        $page_title = $pre_title.'  '.$data->trx_id;
        return view('admin.sections.add-money.details', compact(
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
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', 'add-money')->first();
        try{
            //notification
            $notification_content = [
                'title'         => __("Add Money"),
                'message'       => "Your Add Money request approved by admin " .getAmount($data->request_amount,2).' '.get_default_currency_code()." Successful.",
                'image'         => files_asset_path('profile-default'),
            ];
            if($data->user_id != null) {
                //update wallet
                $userWallet = $data->user->wallet;
                $userWallet->balance +=  $data->request_amount;
                $userWallet->save();
                //update transaction
                $data->status = 1;
                $data->available_balance =  $userWallet->balance;
                $data->save();
                UserNotification::create([
                    'type'      => NotificationConst::BALANCE_ADDED,
                    'user_id'  =>  $data->user_id,
                    'message'   => $notification_content,
                ]);
                try{
                    if( $this->basic_settings->email_notification == true){
                        $data->user->notify(new ApprovedByAdminMail($data->user,$data));
                    }
                }catch(Exception $e){

                }
            }elseif($data->agent_id != null){
                    //update wallet
                    $userWallet = $data->agent->wallet;
                    $userWallet->balance +=  $data->request_amount;
                    $userWallet->save();
                    //update transaction
                    $data->status = 1;
                    $data->available_balance =  $userWallet->balance;
                    $data->save();
                    AgentNotification::create([
                        'type'      => NotificationConst::BALANCE_ADDED,
                        'agent_id'  =>  $data->agent_id,
                        'message'   => $notification_content,
                    ]);
                    try{
                        if( $this->basic_settings->email_notification == true){
                            $data->agent->notify(new ApprovedByAdminMail($data->agent,$data));
                        }
                    }catch(Exception $e){

                    }

                }
            return redirect()->back()->with(['success' => [__("Add Money request approved successfully")]]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function rejected(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
            'reject_reason' => 'required|string|max:200',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', 'add-money')->first();
        try{
             $notification_content = [
                'title'         => __("Add Money"),
                'message'       => "Your Add Money request rejected by admin " .getAmount($data->request_amount,2).' '.get_default_currency_code(),
                'image'         => files_asset_path('profile-default'),
            ];
            if($data->user_id != null) {

                //update transaction
                $data->status = 4;
                $data->reject_reason = $request->reject_reason;
                $data->save();
                UserNotification::create([
                    'type'      => NotificationConst::BALANCE_ADDED,
                    'user_id'  =>  $data->user_id,
                    'message'   => $notification_content,
                ]);
                try{
                    if( $this->basic_settings->email_notification == true){
                        $data->user->notify(new RejectedByAdminMail($data->user,$data));
                    }
                }catch(Exception $e){

                }
            }elseif($data->agent_id != null){
                    //update transaction
                    $data->status = 4;
                    $data->reject_reason = $request->reject_reason;
                    $data->save();
                    AgentNotification::create([
                        'type'      => NotificationConst::BALANCE_ADDED,
                        'agent_id'  =>  $data->agent_id,
                        'message'   => $notification_content,
                    ]);
                    try{
                        if( $this->basic_settings->email_notification == true){
                            $data->agent->notify(new RejectedByAdminMail($data->agent,$data));
                        }
                    }catch(Exception $e){

                    }

                }
            return redirect()->back()->with(['success' => [__("Add Money request rejected successfully")]]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_Add_Money_Logs".'.xlsx';
        return Excel::download(new AddMoneyTransactionExport, $file_name);
    }

}
