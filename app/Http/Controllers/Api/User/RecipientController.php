<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Receipient;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\BasicSettings;
use App\Models\RemitanceCashPickup;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReceiverCounty;
use App\Models\RemitanceBankDeposit;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;

class RecipientController extends Controller
{
    public function recipientList(){
        $recipients = Receipient::auth()->orderByDesc("id")->get()->map(function($data){
            $basic_settings = BasicSettings::first();
            if($data->type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                return[
                    'id' => $data->id,
                    'country' => $data->country,
                    'country_name' => $data->receiver_country->country,
                    'trx_type' => $data->type,
                    'trx_type_name' => $basic_settings->site_name.' Wallet',
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'email'  => $data->email,
                    'account_number' => $data->account_number??'',
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
                    'trx_type_name' => ucwords(str_replace('-', ' ', @$data->type)),
                    'alias' => $data->alias,
                    'firstname' => $data->firstname,
                    'lastname' => $data->lastname,
                    'mobile_code' => $data->mobile_code,
                    'mobile' => $data->mobile,
                    'email'  => $data->email,
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
        $data =[
            'recipients'   => $recipients,
            'countryFlugPath'   => 'public/backend/images/country-flag',
            'default_image'    => "public/backend/images/default/default.webp",
            'receiverCountries'   => $receiverCountries,
        ];
        $message =  ['success'=>[__('All Recipient')]];
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
            'countryFlugPath'   => 'public/backend/images/country-flag',
            'default_image'    => "public/backend/images/default/default.webp",
            'transactionTypes'   => $transaction_type,
            'receiverCountries'   => $receiverCountries,
            'banks'   => $banks,
            'cashPickupsPoints'   => $cashPickups,
        ];
        $message =  ['success'=>[__('Save Recipient Information')]];
        return Helpers::success($data,$message);
    }
    public function dynamicFields(){
        $bank_deposit = [
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
                'field_name' => "email",
                'label_name' => "Email Address",
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
                'field_name' => "email",
                'label_name' => "Email Address",
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
                'field_name' => "email",
                'label_name' => "Email Address",
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
                if(auth()->user()->email ===  $user->email){
                    $error = ['error'=>[__("Can't send remittance to your own")]];
                    return Helpers::error($error);
                }
                if(@$user->address->country === null ||  @$user->address->country != get_default_currency_name()) {
                    $error = ['error'=>[__("This User Country doesn't match with default currency country!")]];
                    return Helpers::error($error);
                }
            }
            if(!$user){
                $error = ['error'=>[__('User not found')]];
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
    public function storeRecipient(Request $request){
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
        $checkMobile = Receipient::where('user_id',$user->id)->where('email',$request->email)->first();
        if($checkMobile){
            $error = ['error'=>[__('This recipient  already exist.!')]];
            return Helpers::error($error);
        }
        $country = ReceiverCounty::where('id',$request->country)->first();
        if(!$country){
            $error = ['error'=>[__('Please select a valid country!')]];
            return Helpers::error($error);
        }
        $countryId = $country->id;



        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid bank!')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                $error = ['error'=>[__('Please select a valid cash pickup!')]];
                return Helpers::error($error);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = User::where('email',$request->email)->first();
            if( !$receiver){
                $error = ['error'=>[__('User not found!')]];
                return Helpers::error($error);
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
            $message =  ['success'=>[__('Receipient save successfully')]];
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
        $recipient =  Receipient::auth()->with('user','receiver_country')->where('id',request()->id)->get()->map(function($item){
            return[
                'id' => $item->id,
                'country' => $item->country,
                'type' => $item->type,
                'alias' => $item->alias,
                'firstname' => $item->firstname,
                'lastname' => $item->lastname,
                'mobile_code' => $item->mobile_code,
                'mobile' => $item->mobile,
                'email'  => $item->email,
                'account_number'  => $item->account_number??'',
                'city' => $item->city,
                'address' => $item->address,
                'state' => $item->state,
                'zip_code' => $item->zip_code,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,

            ];
        })->first();
        if( !$recipient){
            $error = ['error'=>[__('Invalid request, recipient not found!')]];
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
        $message =  ['success'=>[__('Successfully get recipient')]];
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
        $user = auth()->user();
        $data =  Receipient::auth()->with('user','receiver_country')->where('id',$request->id)->first();
        if( !$data){
            $error = ['error'=>[__('Invalid request, recipient not found!')]];
            return Helpers::error($error);
        }
        $checkMobile = Receipient::where('id','!=',$data->id)->where('user_id',$user->id)->where('email',$request->email)->first();
        if($checkMobile){
            $error = ['error'=>[__('This recipient  already exist.')]];
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
            if( !$receiver){
                $error = ['error'=>[__('User not found')]];
                return Helpers::error($error);
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
            $message =  ['success'=>[__('Receipient updated successfully')]];
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
        $recipient = Receipient::where('id',$request->id)->first();
        if(!$recipient){
            $error = ['error'=>[__('Invalid request')]];
            return Helpers::error($error);
        }
        try{
            $recipient->delete();
            $message =  ['success'=>[__('Receipient deleted successfully!')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }

}
