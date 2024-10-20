<?php

namespace App\Jobs;

use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\PushNotificationHelper;
use App\Http\Helpers\UtilityHelper;
use App\Models\Transaction;
use App\Notifications\User\BillPay\BillPayFromReloadly;
use App\Notifications\User\BillPay\BillPayFromReloadlyRjected;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessBillPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $transaction_id;



    public function __construct($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transaction = Transaction::where('id',$this->transaction_id)->where('type',PaymentGatewayConst::BILLPAY)->where('status',PaymentGatewayConst::STATUSPROCESSING)->first();
        $api_response = $transaction->details->api_response;
        if( $api_response->status === "PROCESSING"){
              // Call method to send request to Reloadly for transaction data
            // $transaction_latest_info = (new UtilityHelper())->getTransaction( $api_response->id);
            $transaction_latest_info = (new UtilityHelper())->getTransaction(2703);
            if(isset($transaction_latest_info['transaction']) && isset($transaction_latest_info['transaction']['status']) && $transaction_latest_info['transaction']['status'] === "SUCCESSFUL"){
                $details = $transaction->details;
                $details = json_encode($details);
                $details = json_decode($details,true);
                $details['api_response']['status'] = $transaction_latest_info['transaction']['status'];
                $details['api_response']['code'] = $transaction_latest_info['code'];
                $details['api_response']['message'] = $transaction_latest_info['message'];
                $details['api_transaction'] = $transaction_latest_info['transaction'];
                //update transaction
                $transaction->update([
                    'status' => PaymentGatewayConst::STATUSSUCCESS,
                    'details' => $details
                ]);
                //for agent profit(If Pay Bill By Agent)
                if($transaction->agent_id != null){
                    $charges =  $transaction->details->charges;
                    $afterCharge = ($transaction->creator_wallet->balance + $charges->agent_total_commission);
                    $this->agentProfitInsert($transaction->id,$transaction->creator_wallet,$charges);
                    $this->updateSenderWalletBalance($transaction->creator_wallet,$afterCharge);
                    $transaction->update([
                        'available_balance' =>  $afterCharge,
                    ]);
                }
                //Email Notification
                $notifyData = [
                    'trx_id'            => $transaction->trx_id,
                    'biller_name'       => $transaction->details->bill_type_name,
                    'bill_month'        => $transaction->details->bill_month,
                    'bill_number'       => $transaction->details->bill_number,
                    'sender_amount'       => get_amount($transaction->details->charges->sender_amount,$transaction->details->charges->sender_currency),
                    'status'            => __("success"),
                ];
                try{
                    //send notifications
                    $transaction->creator->notify(new BillPayFromReloadly($transaction->creator,(object)$notifyData));
                }catch(Exception $e){
                    //error handle
                }
                info('Bill Approved By Reloadly');
            }elseif(isset($transaction_latest_info['transaction']) && isset($transaction_latest_info['transaction']['status']) || $transaction_latest_info['transaction']['status'] != "SUCCESSFUL" || $transaction_latest_info['transaction']['status'] != "PROCESSING"){
                //update transaction
                $afterCharge = (($transaction->creator_wallet->balance + $transaction->details->charges->payable) - $transaction->details->charges->agent_total_commission);
                $transaction->update([
                    'status' => PaymentGatewayConst::STATUSREJECTED,
                    'available_balance' =>  $afterCharge,
                ]);
                $this->updateSenderWalletBalance($transaction->creator_wallet,$afterCharge);
                 //Email Notification
                 $notifyData = [
                    'trx_id'            => $transaction->trx_id,
                    'biller_name'       => $transaction->details->bill_type_name,
                    'bill_month'        => $transaction->details->bill_month,
                    'bill_number'       => $transaction->details->bill_number,
                    'sender_amount'       => get_amount($transaction->details->charges->sender_amount,$transaction->details->charges->sender_currency),
                    'status'            => __("Failed"),
                ];
                try{
                    //send notifications
                    $transaction->creator->notify(new BillPayFromReloadlyRjected($transaction->creator,(object)$notifyData));
                }catch(Exception $e){
                    //error handle
                }
                info('Balance Refund');
            }

        }
    }
    //return amount
    public function updateSenderWalletBalance($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    //agent profit
    public function agentProfitInsert($id,$authWallet,$charges) {
        DB::beginTransaction();
        try{
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'percent_charge'    => $charges->agent_percent_commission??0,
                'fixed_charge'      => $charges->agent_fixed_commission??0,
                'total_charge'      => $charges->agent_total_commission??0,
                'created_at'        => now(),
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
           //
        }
    }

}
