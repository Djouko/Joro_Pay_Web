<?php

namespace App\Http\Controllers\Api\Agent;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\ReceiverCounty;
use App\Models\Agent;
use App\Models\AgentRecipient;
use App\Models\RemitanceBankDeposit;
use App\Models\RemitanceCashPickup;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RecipientController extends Controller
{
//=============================Basic Endpoints Recipient Start================================================
    public function dynamicFields(){
        $bank_deposit = [
            [
                'field_name' => "transaction_type",
                'label_name' => "Transaction Type",
            ],
            [
                'field_name' => "recipient_type",
                'label_name' => "Transaction Type",
            ],
            [
                'field_name' => "firstname",
                'label_name' => "First Name",
            ],
            [
                'field_name' => "lastname",
                'label_name' => "Last Name",
            ],
            [
                'field_name' => "email",
                'label_name' => "Email",
            ],

            [
                'field_name' => "country",
                'label_name' => "Country",
            ],
            [
                'field_name' => "address",
                'label_name' => "Address ",
            ],
            [
                'field_name' => "state",
                'label_name' => "State",
            ],
            [
                'field_name' => "city",
                'label_name' => "City",
            ],
            [
                'field_name' => "zip",
                'label_name' => "Zip Code",
            ],
            [
                'field_name' => "mobile_code",
                'label_name' => "Dial Code",
            ],
            [
                'field_name' => "mobile",
                'label_name' => "Phone Number",
            ],
            [
                'field_name' => "bank",
                'label_name' => "Select Bank",
            ],
            [
                'field_name' => "account_number",
                'label_name' => "Account Number",
            ],

        ];
        $bank_deposit = (array) $bank_deposit;

        $wallet_to_wallet = [
            [
                'field_name' => "transaction_type",
                'label_name' => "Transaction Type",
            ],
            [
                'field_name' => "country",
                'label_name' => "Country",
            ],
            [
                'field_name' => "mobile_code",
                'label_name' => "Dial Code",
            ],
            [
                'field_name' => "mobile",
                'label_name' => "Phone Number",
            ],

            [
                'field_name' => "firstname",
                'label_name' => "First Name",
            ],
            [
                'field_name' => "lastname",
                'label_name' => "Last Name",
            ],
            [
                'field_name' => "email",
                'label_name' => "Email",
            ],

            [
                'field_name' => "address",
                'label_name' => "Address ",
            ],
            [
                'field_name' => "state",
                'label_name' => "State",
            ],
            [
                'field_name' => "city",
                'label_name' => "City",
            ],
            [
                'field_name' => "zip",
                'label_name' => "Zip Code",
            ]


        ];
        $wallet_to_wallet = (array) $wallet_to_wallet;

        $cash_pickup = [
            [
                'field_name' => "transaction_type",
                'label_name' => "Transaction Type",
            ],
            [
                'field_name' => "firstname",
                'label_name' => "First Name",
            ],
            [
                'field_name' => "lastname",
                'label_name' => "Last Name",
            ],
            [
                'field_name' => "email",
                'label_name' => "Email",
            ],
            [
                'field_name' => "country",
                'label_name' => "Country",
            ],
            [
                'field_name' => "address",
                'label_name' => "Address ",
            ],
            [
                'field_name' => "state",
                'label_name' => "State",
            ],
            [
                'field_name' => "city",
                'label_name' => "City",
            ],
            [
                'field_name' => "zip",
                'label_name' => "Zip Code",
            ],
            [
                'field_name' => "mobile_code",
                'label_name' => "Dial Code",
            ],
            [
                'field_name' => "mobile",
                'label_name' => "Phone Number",
            ],
            [
                'field_name' => "cash_pickup",
                'label_name' => "Pickup Point",
            ],

        ];
        $cash_pickup = (array) $cash_pickup;
        $message =  ['success'=>[__('Recipient Store/Update Fields Name')]];
        $data = [
            Str::slug(GlobalConst::TRX_BANK_TRANSFER) => $bank_deposit,
            Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) => $wallet_to_wallet,
            Str::slug(GlobalConst::TRX_CASH_PICKUP) => $cash_pickup,
        ];
        return Helpers::success($data,$message);

    }
    public function saveRecipientInfo(){
        $basic_settings = BasicSettings::first();
        $transactionType = [
            [
                'id'    => 1,
                'field_name' => Str::slug(GlobalConst::TRX_BANK_TRANSFER),
                'label_name' => "Bank Transfer",
            ],
            [
                'id'    => 2,
                'field_name' =>Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER),
                'label_name' => $basic_settings->site_name.' Wallet',
            ],

            [
                'id'    => 3,
                'field_name' => Str::slug(GlobalConst::TRX_CASH_PICKUP),
                'label_name' => "Cash Pickup",
            ]
         ];
          $transaction_type = (array) $transactionType;

        $receiverCountries = ReceiverCounty::active()->get()->map(function($data){
            return[
                'id' => $data->id,
                'country' => $data->country,
                'name' => $data->name,
                'code' => $data->code,
                'mobile_code' => $data->mobile_code,
                'symbol' => $data->symbol,
                'flag' => $data->flag,
                'rate' => getAmount( $data->rate,2),
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        $data =[
            'base_curr' => get_default_currency_code(),
            'countryFlugPath'       => 'public/backend/images/country-flag',
            'default_image'         => "public/backend/images/default/default.webp",
            'transactionTypes'      => $transaction_type,
            'receiverCountries'     => $receiverCountries,
            'banks'                 => $banks,
            'cashPickupsPoints'     => $cashPickups,
        ];
        $message =  ['success'=>['Save Recipient Information']];
        return Helpers::success($data,$message);
    }
    public function checkUser(Request $request){
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $field_name = "email";

        try{
            $user = User::where($field_name,$validated['email'])->first();
            if($user != null) {
                if(authGuardApi()['user']->email ===  $user->email){
                    $error = ['error'=>[__("Can't send remittance to your own")]];
                    return Helpers::error($error);
                }
                if(@$user->address->country === null ||  @$user->address->country != get_default_currency_name()) {
                    $error = ['error'=>[__("This User Country doesn't match with default currency country!")]];
                    return Helpers::error($error);
                }
            }
            if(!$user){
                $error = ['error'=>[__("User not found")]];
                return Helpers::error($error);
            }
            $data =[
                'user' => $user,
            ];
            $message =  ['success'=>[__('Successfully get user')]];
            return Helpers::success($data,$message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
//=============================Basic Endpoints Recipient Ended================================================

//=============================Sender Recipient Start================================================
    public function recipientList(){

        $recipients = AgentRecipient::auth()->sender()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => $data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];
            }else{
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => @$data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'account_number' => $data->account_number??'',
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];

            }

        });
        $data =[
            'sender_recipients'   => $recipients,
        ];
        $message =  ['success'=>[__('My Sender Recipients')]];
        return Helpers::success($data,$message);
    }
    public function storeRecipient(Request $request){
        $user = authGuardApi()['user'];
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

        $validator = Validator::make(request()->all(), [
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
            'account_number'            => $account_number,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $checkMobile = AgentRecipient::auth()->where('email',$request->email)->first();
        if($checkMobile){
            $error = ['error'=>[__('This recipient  already exist')]];
            return Helpers::error($error);
        }
        $country = ReceiverCounty::where('id',$request->country)->first();
        if(!$country){
            $error = ['error'=>[__('Please select a valid country')]];
            return Helpers::error($error);
        }
        $countryId = $country->id;

        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid bank')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid cash pickup')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = User::where('email',$request->email)->first();
            if(@$receiver->address->country === null ||  @$receiver->address->country != get_default_currency_name()) {
                $error = ['error'=>[__("This User Country doesn't match with default currency country!")]];
                return Helpers::error($error);
            }
            if( !$receiver){
                $error = ['error'=>[__('User not found')]];
                return Helpers::error($error);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;
        }

        $in['agent_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['recipient_type'] = GlobalConst::SENDER;
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
            AgentRecipient::create($in);
            $message =  ['success'=>[__('Sender recipient save successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function editRecipient(){
        $validator = Validator::make(request()->all(), [
            'id'              =>'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $recipient = AgentRecipient::auth()->sender()->with('agent','receiver_country')->where('id',request()->id)->get()->map(function($item){
            return[
                'id' => $item->id,
                'country' => $item->country,
                'type' => $item->type,
                'recipient_type' => $item->recipient_type,
                'alias' => $item->alias,
                'firstname' => $item->firstname,
                'lastname' => $item->lastname,
                'email' => $item->email,
                'account_number' => $item->account_number??'',
                'mobile_code' => $item->mobile_code,
                'mobile' => $item->mobile,
                'city' => $item->city,
                'address' => $item->address,
                'state' => $item->state,
                'zip_code' => $item->zip_code,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,

            ];
        })->first();
        if( !$recipient){
            $error = ['error'=>[__('Invalid request, sender recipient not found!')]];
            return Helpers::error($error);
        }
        $basic_settings = BasicSettings::first();
        $transactionType = [
            [
                'id'    => 1,
                'field_name' => Str::slug(GlobalConst::TRX_BANK_TRANSFER),
                'label_name' => "Bank Transfer",
            ],
            [
                'id'    => 2,
                'field_name' =>Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER),
                'label_name' => $basic_settings->site_name.' Wallet',
            ],

            [
                'id'    => 3,
                'field_name' => Str::slug(GlobalConst::TRX_CASH_PICKUP),
                'label_name' => "Cash Pickup",
            ]
         ];
          $transaction_type = (array) $transactionType;

        $receiverCountries = ReceiverCounty::active()->get()->map(function($data){
            return[
                'id' => $data->id,
                'country' => $data->country,
                'name' => $data->name,
                'code' => $data->code,
                'mobile_code' => $data->mobile_code,
                'symbol' => $data->symbol,
                'flag' => $data->flag,
                'rate' => getAmount( $data->rate,2),
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        $data =[
            'recipient' => (object)$recipient,
            'base_curr' => get_default_currency_code(),
            'countryFlugPath'   => 'public/backend/images/country-flag',
            'default_image'    => "public/backend/images/default/default.webp",
            'transactionTypes'   => $transaction_type,
            'receiverCountries'   => $receiverCountries,
            'banks'   => $banks,
            'cashPickupsPoints'   => $cashPickups,
        ];
        $message =  ['success'=>[__('Successfully get sender recipient')]];
        return Helpers::success($data,$message);
    }
    public function updateRecipient(Request $request){
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
        $validator = Validator::make(request()->all(), [
            'id'              =>'required|string',
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
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $user = authGuardApi()['user'];
        $data =  AgentRecipient::auth()->sender()->with('agent','receiver_country')->where('id',$request->id)->first();
        if( !$data){
            $error = ['error'=>[__('Invalid request, sender recipient not found')]];
            return Helpers::error($error);
        }
        $checkMobile = AgentRecipient::where('id','!=',$data->id)->auth()->where('email',$request->email)->first();
        if($checkMobile){
            $error = ['error'=>[__("This recipient  already exist")]];
            return Helpers::error($error);
        }

        $country = ReceiverCounty::where('id',$request->country)->first();
        if(!$country){
            $error = ['error'=>[__('Please select a valid country')]];
            return Helpers::error($error);
        }
        $countryId = $country->id;
        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid bank')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid cash pickup')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = User::where('email',$request->email)->first();
            if(@$receiver->address->country === null ||  @$receiver->address->country != get_default_currency_name()) {
                $error = ['error'=>[__("This User Country doesn't match with default currency country!")]];
                return Helpers::error($error);
            }
            if( !$receiver){
                $error = ['error'=>[__('User not found!')]];
                return Helpers::error($error);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;
        }

        $in['agent_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['recipient_type'] = GlobalConst::SENDER;
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
            $message =  ['success'=>[__('Sender recipient updated successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function deleteRecipient(Request $request){
        $validator = Validator::make(request()->all(), [
            'id'              =>'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $recipient = AgentRecipient::auth()->sender()->where('id',$request->id)->first();
        if(!$recipient){
            $error = ['error'=>[__('Invalid request')]];
            return Helpers::error($error);
        }
        try{
            $recipient->delete();
            $message =  ['success'=>[__('Recipient deleted successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
   //=============================Sender Recipient End==================================================

   //=============================Receiver Recipient Start================================================
    public function recipientListReceiver(){
        $recipients = AgentRecipient::auth()->receiver()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => $data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];
            }else{
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => @$data->type,
                    'recipient_type' => $data->recipient_type,
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'email' => $data->email,
                    'account_number' => $data->account_number??'',
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'city' => $data->city,
                    'state' => $data->state,
                    'address' => $data->address,
                    'zip_code' => $data->zip_code,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,

                ];

            }

        });
        $data =[
            'receiver_recipients'   => $recipients,
        ];
        $message =  ['success'=>[__('My Receiver Recipients')]];
        return Helpers::success($data,$message);
    }
    public function storeRecipientReceiver(Request $request){
        $user = authGuardApi()['user'];
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

        $validator = Validator::make(request()->all(), [
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
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $checkRecipient = AgentRecipient::auth()->where('email',$request->email)->first();
        if($checkRecipient){
            $error = ['error'=>[__('This recipient  already exist')]];
            return Helpers::error($error);
        }
        $country = ReceiverCounty::where('id',$request->country)->first();
        if(!$country){
            $error = ['error'=>[__('Please select a valid country')]];
            return Helpers::error($error);
        }
        $countryId = $country->id;

        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid bank')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid cash pickup')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = User::where('email',$request->email)->first();
            if(@$receiver->address->country === null ||  @$receiver->address->country != get_default_currency_name()) {
                $error = ['error'=>[__("This User Country doesn't match with default currency country!")]];
                return Helpers::error($error);
            }
            if( !$receiver){
                $error = ['error'=>[__('User not found!')]];
                return Helpers::error($error);
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
            AgentRecipient::create($in);
            $message =  ['success'=>[__('Receiver recipient save successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function editRecipientReceiver(){
        $validator = Validator::make(request()->all(), [
            'id'              =>'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $recipient = AgentRecipient::auth()->receiver()->with('agent','receiver_country')->where('id',request()->id)->get()->map(function($item){
            return[
                'id' => $item->id,
                'country' => $item->country,
                'type' => $item->type,
                'recipient_type' => $item->recipient_type,
                'alias' => $item->alias,
                'firstname' => $item->firstname,
                'lastname' => $item->lastname,
                'email' => $item->email,
                'account_number' => $item->account_number??'',
                'mobile_code' => $item->mobile_code,
                'mobile' => $item->mobile,
                'city' => $item->city,
                'address' => $item->address,
                'state' => $item->state,
                'zip_code' => $item->zip_code,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,

            ];
        })->first();
        if( !$recipient){
            $error = ['error'=>[__('Invalid request, sender recipient not found!')]];
            return Helpers::error($error);
        }
        $basic_settings = BasicSettings::first();
        $transactionType = [
            [
                'id'    => 1,
                'field_name' => Str::slug(GlobalConst::TRX_BANK_TRANSFER),
                'label_name' => "Bank Transfer",
            ],
            [
                'id'    => 2,
                'field_name' =>Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER),
                'label_name' => $basic_settings->site_name.' Wallet',
            ],

            [
                'id'    => 3,
                'field_name' => Str::slug(GlobalConst::TRX_CASH_PICKUP),
                'label_name' => "Cash Pickup",
            ]
        ];
        $transaction_type = (array) $transactionType;

        $receiverCountries = ReceiverCounty::active()->get()->map(function($data){
            return[
                'id' => $data->id,
                'country' => $data->country,
                'name' => $data->name,
                'code' => $data->code,
                'mobile_code' => $data->mobile_code,
                'symbol' => $data->symbol,
                'flag' => $data->flag,
                'rate' => getAmount( $data->rate,2),
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        $data =[
            'recipient' => (object)$recipient,
            'base_curr' => get_default_currency_code(),
            'countryFlugPath'   => 'public/backend/images/country-flag',
            'default_image'    => "public/backend/images/default/default.webp",
            'transactionTypes'   => $transaction_type,
            'receiverCountries'   => $receiverCountries,
            'banks'   => $banks,
            'cashPickupsPoints'   => $cashPickups,
        ];
        $message =  ['success'=>[__('Successfully get receiver recipient')]];
        return Helpers::success($data,$message);
    }
    public function updateRecipientReceiver(Request $request){
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
        $validator = Validator::make(request()->all(), [
            'id'              =>'required|string',
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
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $user = authGuardApi()['user'];
        $data =  AgentRecipient::auth()->receiver()->with('agent','receiver_country')->where('id',$request->id)->first();
        if( !$data){
            $error = ['error'=>[__('Invalid request, receiver recipient not found!')]];
            return Helpers::error($error);
        }
        $checkRecipient = AgentRecipient::where('id','!=',$data->id)->auth()->where('email',$request->email)->first();
        if($checkRecipient){
            $error = ['error'=>['__(This recipient  already exist.']];
            return Helpers::error($error);
        }

        $country = ReceiverCounty::where('id',$request->country)->first();
        if(!$country){
            $error = ['error'=>[__('Please select a valid country')]];
            return Helpers::error($error);
        }
        $countryId = $country->id;
        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid bank')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid cash pickup')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = User::where('email',$request->email)->first();
            if(@$receiver->address->country === null ||  @$receiver->address->country != get_default_currency_name()) {
                $error = ['error'=>[__("This User Country doesn't match with default currency country!")]];
                return Helpers::error($error);
            }
            if( !$receiver){
                $error = ['error'=>[__('User not found!')]];
                return Helpers::error($error);
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
            $message =  ['success'=>[__('Receiver recipient updated successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function deleteRecipientReceiver(Request $request){
        $validator = Validator::make(request()->all(), [
            'id'              =>'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $recipient = AgentRecipient::auth()->receiver()->where('id',$request->id)->first();
        if(!$recipient){
            $error = ['error'=>['Invalid request!']];
            return Helpers::error($error);
        }
        try{
            $recipient->delete();
            $message =  ['success'=>[__('Recipient deleted successfully')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
//=============================Receiver Recipient End==================================================
}
