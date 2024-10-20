<?php

namespace App\Http\Controllers\Admin;

use App\Constants\NotificationConst;
use App\Exports\RemittanceTransactionExport;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Admin\ReceiverCounty;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Models\RemitanceBankDeposit;
use App\Models\RemitanceCashPickup;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\Remittance\Approved;
use App\Notifications\User\Remittance\Rejected;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;


class RemitanceController extends Controller
{
    protected $basic_settings;

    public function __construct()
    {
            $this->basic_settings = BasicSettingsProvider::get();
    }
    ///========================Receiver countries start=============================================
        public function allCountries()
        {
            $page_title = __("Receiver Countries");
            $allCountries = ReceiverCounty::latest()->paginate(20);
            return view('admin.sections.remitance.countries.index', compact(
                'page_title','allCountries'
            ));
        }
        public function storeCountry(Request $request) {

            $validator = Validator::make($request->all(),[
                'country'   => 'required|string',
                'name'      => 'required|string',
                'code'      => 'required|string|unique:receiver_counties',
                'symbol'    => 'required|string',
                'flag'      => 'nullable|image|mimes: jpg,png,jpeg,svg,webp',
                'rate'      => 'required',
                'mobile_code'      => 'required',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','country_add');
            }
            $validated = $validator->validate();

            $validated['created_at']    = now();
            $validated['admin_id']      = Auth::user()->id;

            $validated = Arr::except($validated,['flag']);
            // insert_data
            try{
                $country = ReceiverCounty::create($validated);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            // Uplaod File
            if($request->hasFile('flag')) {
                try{
                    $image = get_files_from_fileholder($request,'flag');
                    $uploadFlag = upload_files_from_path_dynamic($image,'country-flag');

                    // Update Database
                    $country->update([
                        'flag'  => $uploadFlag,
                    ]);
                }catch(Exception $e) {
                    return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
                }
            }

            return back()->with(['success' => [__("Country Saved Successfully!")]]);
        }
        public function updateCountry(Request $request) {

            $target = $request->target ?? $request->currency_code;
            $country = ReceiverCounty::where('code',$target)->first();
            if(!$country) {
                return back()->with(['warning' => [__("Country not found!")]]);
            }
            $request->merge(['old_flag' =>$country->flag]);

            $validator = Validator::make($request->all(),[
                'currency_country'   => 'required|string',
                'currency_mobile_code'   => 'required',
                'currency_name'      => 'required|string',
                'currency_code'      => ['required','string'],
                'currency_symbol'    => 'required|string',
                'currency_rate'      => 'required|numeric',
                'currency_target'    => 'nullable|string',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','country_edit');
            }
            $validated = $validator->validate();


            $validated = Arr::except($validated,['currency_flag']);

            if($request->hasFile('currency_flag')) {
                try{
                    $image = get_files_from_fileholder($request,'currency_flag');
                    $uploadFlag = upload_files_from_path_dynamic($image,'country-flag',$country->flag);
                    $validated['currency_flag'] = $uploadFlag;
                }catch(Exception $e) {
                    return back()->withErrors($validator)->withInput()->with(['error' => [__("Image file upload failed!")]]);
                }
            }
            $validated = replace_array_key($validated,"currency_");
            try{
            $country->update($validated);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Successfully updated the information.")]]);
        }
        public function deleteCountry(Request $request) {
            $validator = Validator::make($request->all(),[
                'target'        => 'required|string|exists:receiver_counties,code',
            ]);
            $validated = $validator->validate();
            $country = ReceiverCounty::where("code",$validated['target'])->first();

            try{
                $country->delete();
                delete_file(get_files_path('country-flag').'/'.$country->flag);
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Country deleted successfully!")]]);
        }
        public function statusUpdateCountry(Request $request) {
            $validator = Validator::make($request->all(),[
                'status'                    => 'required|boolean',
                'data_target'               => 'required|string',
            ]);
            if ($validator->stopOnFirstFailure()->fails()) {
                $error = ['error' => $validator->errors()];
                return Response::error($error,null,400);
            }
            $validated = $validator->safe()->all();
            $currency_code = $validated['data_target'];

            $Country = ReceiverCounty::where('code',$currency_code)->first();
            if(!$Country) {
                $error = ['error' => [__("Country record not found in our system.")]];
                return Response::error($error,null,404);
            }

            try{
                $Country->update([
                    'status' => ($validated['status'] == true) ? false : true,
                ]);
            }catch(Exception $e) {
                $error = ['error' => [__("Something went wrong! Please try again.")]];
                return Response::error($error,null,500);
            }

            $success = ['success' => [__("Country status updated successfully!")]];
            return Response::success($success,null,200);
        }
        public function searchCountry(Request $request) {
            $validator = Validator::make($request->all(),[
                'text'  => 'required|string',
            ]);

            if($validator->fails()) {
                $error = ['error' => $validator->errors()];
                return Response::error($error,null,400);
            }

            $validated = $validator->validate();
            $allCountries = ReceiverCounty::search($validated['text'])->select()->limit(10)->get();
            return view('admin.components.search.country-search',compact(
                'allCountries',
            ));
        }

    ///========================Receiver countries end===============================================
    ///========================Bank Deposits end===============================================
        public function bankDeposits(){
            $page_title = __("Bank Deposit Type");
            $banks = RemitanceBankDeposit::latest()->paginate(20);
            return view('admin.sections.remitance.banks.index', compact(
                'page_title','banks'
            ));
        }
        public function storeBankDeposit(Request $request){

            $validator = Validator::make($request->all(),[
                'name'      => 'required|string|max:200|unique:remitance_bank_deposits,name',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','bank-deposit-add');
            }
            $validated = $validator->validate();
            $slugData = Str::slug($request->name);
            $makeUnique = RemitanceBankDeposit::where('alias',  $slugData)->first();
            if($makeUnique){
                return back()->with(['error' => [__("Bank Already Exists!")]]);
            }
            $admin = Auth::user();

            $validated['admin_id']      = $admin->id;
            $validated['name']          = $request->name;
            $validated['alias']          = $slugData;
            try{
                RemitanceBankDeposit::create($validated);
                return back()->with(['success' => [__("Bank Saved Successfully!")]]);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }
        public function bankDepositUpdate(Request $request){
            $target = $request->target;
            $bank = RemitanceBankDeposit::where('id',$target)->first();
            $validator = Validator::make($request->all(),[
                'name'      => 'required|string|max:200',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','edit-bank');
            }
            $validated = $validator->validate();

            $slugData = Str::slug($request->name);
            $makeUnique = RemitanceBankDeposit::where('id',"!=",$bank->id)->where('alias',  $slugData)->first();
            if($makeUnique){
                return back()->with(['error' => [__("Bank Already Exists!")]]);
            }
            $admin = Auth::user();
            $validated['admin_id']      = $admin->id;
            $validated['name']          = $request->name;
            $validated['alias']          = $slugData;

            try{
                $bank->fill($validated)->save();
                return back()->with(['success' => [__("Bank Updated Successfully!")]]);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }

        public function bankDepositStatusUpdate(Request $request) {
            $validator = Validator::make($request->all(),[
                'status'                    => 'required|boolean',
                'data_target'               => 'required|string',
            ]);
            if ($validator->stopOnFirstFailure()->fails()) {
                $error = ['error' => $validator->errors()];
                return Response::error($error,null,400);
            }
            $validated = $validator->safe()->all();
            $bank_id = $validated['data_target'];

            $bank = RemitanceBankDeposit::where('id',$bank_id)->first();
            if(!$bank) {
                $error = ['error' => [__("Bank record not found in our system.")]];
                return Response::error($error,null,404);
            }

            try{
                $bank->update([
                    'status' => ($validated['status'] == true) ? false : true,
                ]);
            }catch(Exception $e) {
                $error = ['error' => [__("Something went wrong! Please try again.")]];
                return Response::error($error,null,500);
            }

            $success = ['success' => [__("Bank status updated successfully!")]];
            return Response::success($success,null,200);
        }
        public function bankDepositDelete(Request $request) {
            $validator = Validator::make($request->all(),[
                'target'        => 'required|string|exists:remitance_bank_deposits,id',
            ]);
            $validated = $validator->validate();
            $bank = RemitanceBankDeposit::where("id",$validated['target'])->first();

            try{
                $bank->delete();
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Bank deleted successfully!")]]);
        }
        public function bankDepositSearch(Request $request) {
            $validator = Validator::make($request->all(),[
                'text'  => 'required|string',
            ]);

            if($validator->fails()) {
                $error = ['error' => $validator->errors()];
                return Response::error($error,null,400);
            }

            $validated = $validator->validate();

            $banks = RemitanceBankDeposit::search($validated['text'])->select()->limit(10)->get();
            return view('admin.components.search.bank-deposit-search',compact(
                'banks',
            ));
    }
    ///========================Bank Deposits end===============================================
    ///========================Cash Pickup end===============================================
        public function cashPickup(){
            $page_title = __("Cash Pickup");
            $cashPickups = RemitanceCashPickup::latest()->paginate(20);
            return view('admin.sections.remitance.cash-pickup.index', compact(
                'page_title','cashPickups'
            ));
        }
        public function storeCashPickup(Request $request){

            $validator = Validator::make($request->all(),[
                'name'      => 'required|string|max:200|unique:remitance_cash_pickups,name',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','cash-pickup-add');
            }
            $validated = $validator->validate();
            $slugData = Str::slug($request->name);
            $makeUnique = RemitanceCashPickup::where('alias',  $slugData)->first();
            if($makeUnique){
                return back()->with(['error' => [__("Cash Pickup Already Exists!")]]);
            }
            $admin = Auth::user();

            $validated['admin_id']      = $admin->id;
            $validated['name']          = $request->name;
            $validated['alias']          = $slugData;
            try{
                RemitanceCashPickup::create($validated);
                return back()->with(['success' => [__("Cash Pickup Saved Successfully!")]]);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }
        public function cashPickupUpdate(Request $request){
            $target = $request->target;
            $cashPickup = RemitanceCashPickup::where('id',$target)->first();
            $validator = Validator::make($request->all(),[
                'name'      => 'required|string|max:200',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','edit-cash-pickup');
            }
            $validated = $validator->validate();

            $slugData = Str::slug($request->name);
            $makeUnique = RemitanceCashPickup::where('id',"!=",$cashPickup->id)->where('alias',  $slugData)->first();
            if($makeUnique){
                return back()->with(['error' => [__("Cash Pickup Already Exists!")]]);
            }
            $admin = Auth::user();
            $validated['admin_id']      = $admin->id;
            $validated['name']          = $request->name;
            $validated['alias']          = $slugData;

            try{
                $cashPickup->fill($validated)->save();
                return back()->with(['success' => [__("Cash Pickup Updated Successfully!")]]);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }

        public function cashPickuptatusUpdate(Request $request) {
            $validator = Validator::make($request->all(),[
                'status'                    => 'required|boolean',
                'data_target'               => 'required|string',
            ]);
            if ($validator->stopOnFirstFailure()->fails()) {
                $error = ['error' => $validator->errors()];
                return Response::error($error,null,400);
            }
            $validated = $validator->safe()->all();
            $pickup_id = $validated['data_target'];

            $pickup = RemitanceCashPickup::where('id',$pickup_id)->first();
            if(!$pickup) {
                $error = ['error' => [__("Cash Pickup record not found in our system.")]];
                return Response::error($error,null,404);
            }

            try{
                $pickup->update([
                    'status' => ($validated['status'] == true) ? false : true,
                ]);
            }catch(Exception $e) {
                $error = ['error' => [__("Something went wrong! Please try again.")]];
                return Response::error($error,null,500);
            }

            $success = ['success' => [__("Status updated successfully!")]];
            return Response::success($success,null,200);
        }
        public function cashPickuptDelete(Request $request) {
            $validator = Validator::make($request->all(),[
                'target'        => 'required|string|exists:remitance_cash_pickups,id',
            ]);
            $validated = $validator->validate();
            $cashPickup = RemitanceCashPickup::where("id",$validated['target'])->first();

            try{
                $cashPickup->delete();
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return back()->with(['success' => [__("Cash Pickup deleted successfully!")]]);
        }
        public function cashPickupSearch(Request $request) {
            $validator = Validator::make($request->all(),[
                'text'  => 'required|string',
            ]);

            if($validator->fails()) {
                $error = ['error' => $validator->errors()];
                return Response::error($error,null,400);
            }

            $validated = $validator->validate();

            $cashPickups = RemitanceCashPickup::search($validated['text'])->select()->limit(10)->get();
            return view('admin.components.search.cash-pick-search',compact(
                'cashPickups',
            ));
    }
    ///========================Cash Pickup end===============================================
    ///========================Remittance logs start=============================================
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("All Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'REMITTANCE')->where('attribute',"SEND")->latest()->paginate(20);

        return view('admin.sections.remitance.index', compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Pending Add Money Logs View.
     * @return view $pending-remitance-logs
     */
    public function pending()
    {
        $page_title =__("Pending Logs");
        $transactions = Transaction::with(
         'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'REMITTANCE')->where('attribute',"SEND")->where('status', 2)->latest()->paginate(20);
        return view('admin.sections.remitance.index', compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Complete Add Money Logs View.
     * @return view $complete-remitance-logs
     */
    public function complete()
    {
        $page_title = __("Complete Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'REMITTANCE')->where('attribute',"SEND")->where('status', 1)->latest()->paginate(20);
        return view('admin.sections.remitance.index', compact(
            'page_title',
            'transactions'
        ));
    }
    /**
     * Canceled Add Money Logs View.
     * @return view $canceled-remitance-logs
     */
    public function canceled()
    {
        $page_title = __("Canceled Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'REMITTANCE')->where('attribute',"SEND")->where('status',4)->latest()->paginate(20);
        return view('admin.sections.remitance.index', compact(
            'page_title',
            'transactions'
        ));
    }
    public function addMoneyDetails($id){

        $data = Transaction::where('id',$id)->with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name,alias,payment_gateway_id,currency_code,rate',
        )->where('type', 'REMITTANCE')->where('attribute',"SEND")->first();
        $pre_title = __("Remittance details for");
        $page_title = $pre_title.'  '.$data->trx_id;
        return view('admin.sections.remitance.details', compact(
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
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', 'REMITTANCE')->first();

        try{
            $notification_content = [
                'title'         => __("Remittance"),
                'message'       => "Your Remittance request approved by admin " .getAmount($data->request_amount,2).' '.get_default_currency_code(),
                'image'         => files_asset_path('profile-default'),
            ];
            if($data->user_id != null) {
                $receipient = $data->details->receiver;
                $notifyData = [
                    'trx_id'  => $data->trx_id,
                    'title'  => __("Send Remittance to")." @" . $receipient->firstname.' '.@$receipient->lastname." (".@$receipient->email.")",
                    'request_amount'  => getAmount($data->request_amount,4).' '.get_default_currency_code(),
                    'exchange_rate'  => "1 " .get_default_currency_code().' = '.get_amount($data->details->to_country->rate,$data->details->to_country->code),
                    'charges'   => getAmount( $data->charge->total_charge, 2).' ' .get_default_currency_code(),
                    'payable'   =>  getAmount($data->payable,4).' ' .get_default_currency_code(),
                    'sending_country'   => @$data->details->form_country,
                    'receiving_country'   => @$data->details->to_country->country,
                    'receiver_name'  =>  @$receipient->firstname.' '.@$receipient->lastname,
                    'alias'  =>  ucwords(str_replace('-', ' ', @$receipient->alias)),
                    'transaction_type'  =>  @$data->details->remitance_type,
                    'receiver_get'   =>  getAmount($data->details->recipient_amount,4).' ' .$data->details->to_country->code,
                    'status'  => __("success"),
                ];
                //update transaction
                UserNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'user_id'  =>  $data->user_id,
                    'message'   => $notification_content,
                ]);
                $data->status = 1;
                $data->save();
                try{
                    if( $this->basic_settings->email_notification == true){
                        $data->user->notify(new Approved($data->user,(object)$notifyData));
                    }
                }catch(Exception $e){

                }
            }elseif($data->agent_id != null){
                $receipient = $data->details->receiver_recipient;
                $notifyData = [
                    'trx_id'  => $data->trx_id,
                    'title'  => __("Send Remittance to")." @" . $receipient->firstname.' '.@$receipient->lastname." (".@$receipient->email.")",
                    'request_amount'  => getAmount($data->request_amount,4).' '.get_default_currency_code(),
                    'exchange_rate'  => "1 " .get_default_currency_code().' = '.get_amount($data->details->to_country->rate,$data->details->to_country->code),
                    'charges'   => getAmount( $data->charge->total_charge, 2).' ' .get_default_currency_code(),
                    'payable'   =>  getAmount($data->payable,4).' ' .get_default_currency_code(),
                    'sending_country'   => @$data->details->form_country,
                    'receiving_country'   => @$data->details->to_country->country,
                    'receiver_name'  =>  @$receipient->firstname.' '.@$receipient->lastname,
                    'alias'  =>  ucwords(str_replace('-', ' ', @$receipient->alias)),
                    'transaction_type'  =>  @$data->details->remitance_type,
                    'receiver_get'   =>  getAmount($data->details->recipient_amount,4).' ' .$data->details->to_country->code,
                    'status'  => __("success"),
                ];
                $user = $data->agent;
                $returnWithProfit = ($user->wallet->balance +  $data->details->charges->agent_total_commission);
                $this->updateSenderWalletBalance($user->wallet,$returnWithProfit,$data);
                $this->agentProfitInsert($data->id,$user->wallet,(array)$data->details->charges);
                //update transaction
                AgentNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'agent_id'  =>  $data->agent_id,
                    'message'   => $notification_content,
                ]);
                $data->status = 1;
                $data->save();
                try{
                    if( $this->basic_settings->email_notification == true){
                        $data->agent->notify(new Approved($data->agent,(object)$notifyData));
                    }
                }catch(Exception $e){

                }

            }
            return redirect()->back()->with(['success' => [__("Remittance request approved successfully")]]);
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
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', 'REMITTANCE')->first();


        try{
             $notification_content = [
                'title'         => __("Remittance"),
                'message'       => "Your Remittance request rejected by admin " .getAmount($data->request_amount,2).' '.get_default_currency_code(),
                'image'         => files_asset_path('profile-default'),
            ];

            if($data->user_id != null) {
                $receipient = $data->details->receiver;
                $notifyData = [
                    'trx_id'  => $data->trx_id,
                    'title'  => __("Send Remittance to")." @" . $receipient->firstname.' '.@$receipient->lastname." (".@$receipient->email.")",
                    'request_amount'  => getAmount($data->request_amount,4).' '.get_default_currency_code(),
                    'exchange_rate'  => "1 " .get_default_currency_code().' = '.get_amount($data->details->to_country->rate,$data->details->to_country->code),
                    'charges'   => getAmount( $data->charge->total_charge, 2).' ' .get_default_currency_code(),
                    'payable'   =>  getAmount($data->payable,4).' ' .get_default_currency_code(),
                    'sending_country'   => @$data->details->form_country,
                    'receiving_country'   => @$data->details->to_country->country,
                    'receiver_name'  =>  @$receipient->firstname.' '.@$receipient->lastname,
                    'alias'  =>  ucwords(str_replace('-', ' ', @$receipient->alias)),
                    'transaction_type'  =>  @$data->details->remitance_type,
                    'receiver_get'   =>  getAmount($data->details->recipient_amount,4).' ' .$data->details->to_country->code,
                    'status'  => __("Rejected"),
                    'reason'  => $request->reject_reason,
                ];
                $userWallet = UserWallet::where('user_id',$data->user_id)->first();
                $userWallet->balance +=  $data->payable;
                $userWallet->save();

                $up['status'] = 4;
                $up['reject_reason'] = $request->reject_reason;
                $up['available_balance'] = $userWallet->balance;
                $data->fill($up)->save();

                $user =$data->user;
                try{
                    if( $this->basic_settings->email_notification == true){
                        $user->notify(new Rejected($user,(object)$notifyData));
                    }
                }catch(Exception $e){

                }
                UserNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'user_id'  =>  $data->user_id,
                    'message'   => $notification_content,
                ]);
                DB::commit();
            }else if($data->agent_id != null) {
                $receipient = $data->details->receiver_recipient;
                $notifyData = [
                    'trx_id'  => $data->trx_id,
                    'title'  => __("Send Remittance to")." @" . $receipient->firstname.' '.@$receipient->lastname." (".@$receipient->email.")",
                    'request_amount'  => getAmount($data->request_amount,4).' '.get_default_currency_code(),
                    'exchange_rate'  => "1 " .get_default_currency_code().' = '.get_amount($data->details->to_country->rate,$data->details->to_country->code),
                    'charges'   => getAmount( $data->charge->total_charge, 2).' ' .get_default_currency_code(),
                    'payable'   =>  getAmount($data->payable,4).' ' .get_default_currency_code(),
                    'sending_country'   => @$data->details->form_country,
                    'receiving_country'   => @$data->details->to_country->country,
                    'receiver_name'  =>  @$receipient->firstname.' '.@$receipient->lastname,
                    'alias'  =>  ucwords(str_replace('-', ' ', @$receipient->alias)),
                    'transaction_type'  =>  @$data->details->remitance_type,
                    'receiver_get'   =>  getAmount($data->details->recipient_amount,4).' ' .$data->details->to_country->code,
                    'status'  => __("Rejected"),
                    'reason'  => $request->reject_reason,
                ];
                $userWallet = AgentWallet::where('agent_id',$data->agent_id)->first();
                $userWallet->balance +=  $data->payable;
                $userWallet->save();

                $up['status'] = 4;
                $up['reject_reason'] = $request->reject_reason;
                $up['available_balance'] = $userWallet->balance;
                $data->fill($up)->save();

                $user =$data->agent;
                try{
                    if( $this->basic_settings->agent_email_notification == true){
                        $user->notify(new Rejected($user,(object)$notifyData));
                    }
                }catch(Exception $e){

                }
                AgentNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'agent_id'  =>  $data->agent_id,
                    'message'   => $notification_content,
                ]);
                DB::commit();
            }
            return redirect()->back()->with(['success' => [__("Remittance request rejected successfully")]]);
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
        $file_name = now()->format('Y-m-d_H:i:s') . "_Remittance_Logs".'.xlsx';
        return Excel::download(new RemittanceTransactionExport, $file_name);
    }
    ///========================Remittance logs end===============================================


}
