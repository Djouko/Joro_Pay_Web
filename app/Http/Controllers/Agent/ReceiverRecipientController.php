<?php

namespace App\Http\Controllers\Agent;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Admin\ReceiverCounty;
use App\Models\AgentRecipient;
use App\Models\RemitanceBankDeposit;
use App\Models\RemitanceCashPickup;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReceiverRecipientController extends Controller
{
    public function index()
    {
        $page_title = __("My Receiver Recipients");
        $token = (object)session()->get('receiver_remittance_token');

        if(@$token ->transacion_type != null && @$token ->receiver_country == null){
            $recipients =  AgentRecipient::auth()->receiver()->where('type',$token->transacion_type)->orderByDesc("id")->paginate(12);
        }elseif(@$token ->transacion_type != null && @$token ->receiver_country != null){
            $recipients =  AgentRecipient::auth()->receiver()->where('type',$token->transacion_type)->where('country',@$token->receiver_country)->orderByDesc("id")->paginate(12);
        }else{
            $recipients = AgentRecipient::auth()->receiver()->orderByDesc("id")->paginate(12);
        }

        return view('agent.sections.receiver_recipient.index',compact('page_title','recipients'));
    }
    public function addReceipient(){
        $page_title = __("Add Recipient");
        $receiverCountries = ReceiverCounty::active()->get();
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        return view('agent.sections.receiver_recipient.add',compact('page_title','receiverCountries','banks','cashPickups'));
    }
    public function storeReceipient(Request $request){
        $user = auth()->user();
        if($request->transaction_type == 'bank-transfer') {
            $bankRules = 'required|string';
            $account_number = 'required|string|min:10|max:16';
        }else {
            $bankRules = 'nullable|string';
            $account_number = 'nullable|string';
        }

        if($request->transaction_type == 'cash-pickup') {
            $cashPickupRules = "required|string";
        }else {
            $cashPickupRules = "nullable";
        }
        $request->validate([
            'transaction_type'              =>'required|string',
            'country'                      =>'required',
            'firstname'                      =>'required|string',
            'lastname'                      =>'required|string',
            'mobile'                      =>"required",
            'mobile_code'                      =>'required',
            'city'                      =>'required|string',
            'address'                      =>'required|string',
            'state'                      =>'required|string',
            'zip'                      =>'required|string',
            'bank'                      => $bankRules,
            'cash_pickup'               => $cashPickupRules,
            'account_number'                    => $account_number,

        ]);
            $checkMobile = AgentRecipient::auth()->where('email',$request->email)->first();
            if($checkMobile){
                return back()->with(['error' => [__('This recipient  already exist')]]);
            }
            $country = ReceiverCounty::where('id',$request->country)->first();
            if(!$country){
                return back()->with(['error' => [__('Please select a valid country')]]);
            }
            $countryId = $country->id;

        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => [__('Please select a valid bank')]]);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => [__('Please select a valid cash pickup')]]);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = User::where('email',$request->email)->first();
            if(@$receiver->address->country === null ||  @$receiver->address->country != get_default_currency_name()) {
                return back()->with(['error' => [__("This User Country doesn't match with default currency country!")]]);
            }
            if( !$receiver){
                return back()->with(['error' => [__('User not found')]]);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;

        }

        $in['agent_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['recipient_type'] = GlobalConst::RECEIVER;
        $in['alias'] =   $alias;
        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;
        $in['email'] = $request->email;
        $in['state'] = $request->state;
        $in['mobile_code'] = remove_speacial_char($request->mobile_code);
        $in['mobile'] = remove_speacial_char($request->mobile_code) == "880"?(int)remove_speacial_char($request->mobile):remove_speacial_char($request->mobile) ;
        $in['city'] = $request->city;
        $in['address'] = $request->address;
        $in['zip_code'] = $request->zip;
        $in['account_number'] = $request->account_number??null;
        $in['details'] = json_encode($details);
        try{
            AgentRecipient::create($in);
            return redirect()->route('agent.receiver.recipient.index')->with(['success' => [__('Receiver recipient save successfully')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    public function editReceipient($id){
        $page_title = __("Edit Recipient");
        $countries = ReceiverCounty::active()->get();
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $pickup_points = RemitanceCashPickup::active()->latest()->get();
        $data =  AgentRecipient::auth()->receiver()->with('agent','receiver_country')->where('id',$id)->first();
        if( !$data){
            return back()->with(['error' => [__('Invalid request')]]);
        }
        return view('agent.sections.receiver_recipient.edit',compact('page_title','countries','banks','pickup_points','data'));
    }
    public function updateReceipient(Request $request){
        $user = auth()->user();
        $data = AgentRecipient::auth()->receiver()->with('agent','receiver_country')->where('id',$request->id)->first();
        if($request->transaction_type == 'bank-transfer') {
            $bankRules = 'required|string';
            $account_number = 'required|string|min:10|max:16';
        }else {
            $bankRules = 'nullable|string';
            $account_number = 'nullable|string';
        }

        if($request->transaction_type == 'cash-pickup') {
            $cashPickupRules = "required|string";
        }else {
            $cashPickupRules = "nullable";
        }
        $request->validate([
        'transaction_type'              =>'required|string',
        'country'                      =>'required',
        'firstname'                      =>'required|string',
        'lastname'                      =>'required|string',
        'mobile'                      =>"required",
        'mobile_code'                      =>'required',
        'city'                      =>'required|string',
        'address'                      =>'required|string',
        'state'                      =>'required|string',
        'zip'                      =>'required|string',
        'bank'                      => $bankRules,
        'cash_pickup'               => $cashPickupRules,
        'account_number'             => $account_number,

        ]);
        $checkMobile = AgentRecipient::where('id','!=',$data->id)->auth()->where('email',$request->email)->first();

        if($checkMobile){
            return back()->with(['error' => [__('This recipient  already exist')]]);
        }

        $country = ReceiverCounty::where('id',$request->country)->first();
        if(!$country){
            return back()->with(['error' => [__('Please select a valid country')]]);
        }
        $countryId = $country->id;

        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => [__('Please select a valid bank')]]);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => [__('Please select a valid cash pickup')]]);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = User::where('email',$request->email)->first();
            if(@$receiver->address->country === null ||  @$receiver->address->country != get_default_currency_name()) {
                return back()->with(['error' => [__("This User Country doesn't match with default currency country!")]]);
            }
            if( !$receiver){
                return back()->with(['error' => [__('User not found')]]);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;


        }

        $in['agent_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['recipient_type'] = GlobalConst::RECEIVER;
        $in['alias'] =   $alias;
        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;
        $in['email'] = $request->email;
        $in['state'] = $request->state;
        $in['mobile_code'] = remove_speacial_char($request->mobile_code);
        $in['mobile'] = remove_speacial_char($request->mobile_code) == "880"?(int)remove_speacial_char($request->mobile):remove_speacial_char($request->mobile) ;
        $in['city'] = $request->city;
        $in['address'] = $request->address;
        $in['zip_code'] = $request->zip;
        $in['account_number'] = $request->account_number??null;
        $in['details'] = json_encode($details);
        try{
            $data->fill($in)->save();
            return redirect()->route('agent.receiver.recipient.index')->with(['success' => [__('Receiver recipient updated successfully')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    public function deleteReceipient(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:agent_recipients,id',
        ]);
        $validated = $validator->validate();
        $receipient = AgentRecipient::auth()->receiver()->where("id",$validated['target'])->first();
        try{
            $receipient->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__('Receiver recipient deleted successfully!')]]);
    }
    //get dynamic fields
    public function getTrxTypeInputs(Request $request) {
        $validator = Validator::make($request->all(),[
            'data'          => "required|string"
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors());
        }
        $validated = $validator->validate();
        switch($validated['data']){
            case Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER):
                $countries = ReceiverCounty::active()->get();
                return view('agent.components.receiver_recipient.trx-type-fields.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('agent.components.receiver_recipient.trx-type-fields.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('agent.components.receiver_recipient.trx-type-fields.bank-deposit',compact('countries','banks'));

            default:
                return Response::error([__("Oops! Data not found or section is under maintenance")]);
        }
        return Response::error(['error' => [__("Something went wrong! Please try again.")]]);
    }
    public function getTrxTypeInputsEdit(Request $request) {
        $validator = Validator::make($request->all(),[
            'data'          => "required|string"
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors());
        }
        $validated = $validator->validate();

        switch($validated['data']){
            case Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER):
                $countries = ReceiverCounty::active()->get();
                return view('user.components.receiver_recipient.trx-type-fields.edit.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('user.components.receiver_recipient.trx-type-fields.edit.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('user.components.receiver_recipient.trx-type-fields.edit.bank-deposit',compact('countries','banks'));

            default:
                return Response::error([__("Oops! Data not found or section is under maintenance")]);
        }
        return Response::error(['error' => [__("Something went wrong! Please try again.")]]);
    }

    public function sendRemittance($id){
        $recipient = AgentRecipient::auth()->receiver()->where("id",$id)->first();
        $token = session()->get('receiver_remittance_token');
        $in['receiver_country'] = $recipient->country;
        $in['transacion_type'] = $recipient->type;
        $in['sender_recipient'] = $token['receive_amount']??"";
        $in['sender_recipient'] = $recipient->id;
        $in['receiver_recipient'] =  $recipient->id??'';
        $in['receive_amount'] = $token['receive_amount']??0;
        Session::put('receiver_remittance_token',$in);
        return redirect()->route('agent.remittance.index');

    }
}
