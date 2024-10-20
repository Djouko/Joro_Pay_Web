<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use Exception;
use App\Models\User;
use App\Models\Receipient;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\RemitanceCashPickup;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReceiverCounty;
use App\Models\RemitanceBankDeposit;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\PaymentGatewayCurrency;
use Illuminate\Support\Facades\Session;

class ReceipientController extends Controller
{
    public function index() 
    {
        $page_title =__( "All Recipient");
        $token = (object)session()->get('remittance_token');
        $user = auth()->user();
        if(@$token ->transacion_type != null && @$token ->receiver_country == null){
            $receipients = Receipient::auth()->where('type',$token->transacion_type)->orderByDesc("id")->paginate(12);
        }elseif(@$token ->transacion_type != null && @$token ->receiver_country != null){
            $receipients = Receipient::auth()->where('type',$token->transacion_type)->where('country',@$token->receiver_country)->orderByDesc("id")->paginate(12);
        }else{
            $receipients = Receipient::auth()->orderByDesc("id")->paginate(12);
        }

        return view('user.sections.receipient.index',compact('page_title','receipients','token'));
    }
    public function addReceipient(){
        $page_title = __("Add Recipient");
        $receiverCountries = ReceiverCounty::active()->get();
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        return view('user.sections.receipient.add',compact('page_title','receiverCountries','banks','cashPickups'));
    }
    public function checkUser(Request $request){
        $fullMobile = $request->mobile;
        $exist['data'] = User::where('full_mobile',$fullMobile)->orWhere('mobile',$fullMobile)->first();
        $user = auth()->user();
        if(@$exist['data'] && $user->full_mobile == @$exist['data']->full_mobile || $user->mobile == @$exist['data']->mobile){
            return response()->json(['own'=>__("Can't send remittance to your own")]);
        }
        return response($exist);
    }
    public function sendRemittance($id){
        $recipient = Receipient::auth()->where("id",$id)->first();
        $token = session()->get('remittance_token');
        $in['receiver_country'] = $recipient->country;
        $in['transacion_type'] = $recipient->type;
        $in['recipient'] = $recipient->id;
        $in['sender_amount'] = $token['sender_amount']??0;
        $in['receive_amount'] = $token['receive_amount']??0;
        Session::put('remittance_token',$in);
        return redirect()->route('user.remittance.index');

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
            'transaction_type'           =>'required|string',
            'country'                    =>'required',
            'firstname'                  =>'required|string',
            'lastname'                   =>'required|string',
            'email'                      =>"required|email",
            'mobile'                     =>"required",
            'mobile_code'                =>'required',
            'city'                       =>'required|string',
            'address'                    =>'required|string',
            'state'                      =>'required|string',
            'zip'                        =>'required|string',
            'bank'                       => $bankRules,
            'cash_pickup'                => $cashPickupRules,
            'account_number'             => $account_number,

        ]);
            $checkMobile = Receipient::where('user_id',$user->id)->where('email',$request->email)->first();
            if($checkMobile){
                return back()->with(['error' => [__('This recipient  already exist.')]]);
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
            if( !$receiver){
                return back()->with(['error' => [__('User not found')]]);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;

        }

        $in['user_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['alias'] =   $alias;
        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;
        $in['state'] = $request->state;
        $in['email'] = $request->email;
        $in['mobile_code'] = remove_speacial_char($request->mobile_code);
      $in['mobile'] = remove_speacial_char($request->mobile_code) == "880"?(int)remove_speacial_char($request->mobile):remove_speacial_char($request->mobile) ;
        $in['city'] = $request->city;
        $in['address'] = $request->address;
        $in['zip_code'] = $request->zip;
        $in['account_number'] = $request->account_number??null;
        $in['details'] = json_encode($details);
        try{
            Receipient::create($in);
            return redirect()->route('user.receipient.index')->with(['success' => [__('Receipient save successfully')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    public function editReceipient($id){
        $page_title = __("Edit Recipient");
        $countries = ReceiverCounty::active()->get();
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $pickup_points = RemitanceCashPickup::active()->latest()->get();
        $data =  Receipient::auth()->with('user','receiver_country')->where('id',$id)->first();

        if( !$data){
            return back()->with(['error' => [__('Invalid request')]]);
        }
        return view('user.sections.receipient.edit',compact('page_title','countries','banks','pickup_points','data'));
    }
    public function updateReceipient(Request $request){
        $user = auth()->user();
        $data =  Receipient::auth()->with('user','receiver_country')->where('id',$request->id)->first();
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
        'email'                      =>"required|email",
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
        $checkMobile = Receipient::where('id','!=',$data->id)->where('user_id',$user->id)->where('email',$request->email)->first();
        if($checkMobile){
            return back()->with(['error' => [__('This recipient  already exist.')]]);
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
            if( !$receiver){
                return back()->with(['error' => [__('User not found')]]);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;

        }

        $in['user_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['alias'] =   $alias;
        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;
        $in['state'] = $request->state;
        $in['email'] = $request->email;
        $in['mobile_code'] = remove_speacial_char($request->mobile_code);
      $in['mobile'] = remove_speacial_char($request->mobile_code) == "880"?(int)remove_speacial_char($request->mobile):remove_speacial_char($request->mobile) ;
        $in['city'] = $request->city;
        $in['address'] = $request->address;
        $in['zip_code'] = $request->zip;
        $in['account_number'] = $request->account_number??null;
        $in['details'] = json_encode($details);
        try{
            $data->fill($in)->save();
            return redirect()->route('user.receipient.index')->with(['success' => [__('Receipient updated successfully')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

    }
    public function deleteReceipient(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:receipients,id',
        ]);
        $validated = $validator->validate();
        $receipient = Receipient::where("id",$validated['target'])->first();
        try{
            $receipient->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__('Receipient deleted successfully!')]]);
    }
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
                return view('user.components.recipient.trx-type-fields.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.bank-deposit',compact('countries','banks'));

            default:
                return Response::error([__('Oops! Data not found or section is under maintenance')]);
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
                return view('user.components.recipient.trx-type-fields.edit.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.edit.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.edit.bank-deposit',compact('countries','banks'));

            default:
                return Response::error([__('Oops! Data not found or section is under maintenance')]);
        }
        return Response::error(['error' => [__("Something went wrong! Please try again.")]]);
    }
}
