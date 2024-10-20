<?php

namespace App\Http\Controllers\Api\Merchant\Auth;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers as ApiHelpers;
use App\Models\Admin\SetupKyc;
use App\Models\Merchants\Merchant;
use App\Models\Merchants\MerchantQrCode;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\Merchant\LoggedInUsers;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use App\Traits\Merchant\RegisteredUsers;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Traits\AdminNotifications\AuthNotifications;

class LoginController extends Controller
{
    use  AuthenticatesUsers, LoggedInUsers ,RegisteredUsers,ControlDynamicInputFields,AuthNotifications;
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:50',
            'password' => 'required|min:6',
        ]);

        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return ApiHelpers::validation($error);
        }
        $user = Merchant::where('email',$request->email)->first();
        if(!$user){
            $error = ['error'=>[__('Merchant does not exist')]];
            return ApiHelpers::validation($error);
        }
        if (Hash::check($request->password, $user->password)) {
            if($user->status == 0){
                $error = ['error'=>[__('Account Has been Suspended')]];
                return ApiHelpers::validation($error);
            }
            $user->two_factor_verified = false;
            $user->save();
            $this->refreshUserWallets($user);
            $this->createDeveloperApi($user);
            $this->refreshSandboxWallets($user);
            $this->createGatewaySetting($user);
            $this->createLoginLog($user);
            $this->createQr($user);
            $token = $user->createToken('Merchant Token')->accessToken;
            $data = ['token' => $token, 'merchant' => $user, ];
            $message =  ['success'=>[__('Login Successful')]];
            return ApiHelpers::success($data,$message);

        } else {
            $error = ['error'=>[__('Incorrect Password')]];
            return ApiHelpers::error($error);
        }

    }

    public function register(Request $request){
        $basic_settings = $this->basic_settings;
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->merchant_secure_password) {
            $passowrd_rule = ["required","confirmed",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }
        if( $basic_settings->merchant_agree_policy){
            $agree ='required';
        }else{
            $agree ='';
        }

        $validator = Validator::make($request->all(), [
            'firstname'     => 'required|string|max:60',
            'lastname'      => 'required|string|max:60',
            'business_name'      => 'required|string|max:60',
            'email'         => 'required|string|email|max:150|unique:merchants,email',
            'password'      => $passowrd_rule,
            'country'       => 'required|string|max:150',
            'city'       => 'required|string|max:150',
            'phone_code'    => 'required|string|max:10',
            'phone'         => 'required|string|max:20',
            'zip_code'         => 'required|string|max:8',
            'agree'         =>  $agree,

        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return ApiHelpers::validation($error);
        }
        if($basic_settings->merchant_kyc_verification == true){
            $user_kyc_fields = SetupKyc::merchantKyc()->first()->fields ?? [];
            $validation_rules = $this->generateValidationRules($user_kyc_fields);
            $validated = Validator::make($request->all(), $validation_rules);

            if ($validated->fails()) {
                $message =  ['error' => $validated->errors()->all()];
                return ApiHelpers::error($message);
            }
            $validated = $validated->validate();
            $get_values = $this->registerPlaceValueWithFields($user_kyc_fields, $validated);
        }
        $data = $request->all();
        $mobile        = remove_speacial_char($data['phone']);
        $mobile_code   = remove_speacial_char($data['phone_code']);
        $complete_phone             =  $mobile_code . $mobile;

        $user = Merchant::where('mobile',$mobile)->orWhere('full_mobile',$complete_phone)->orWhere('email',$data['email'])->first();
        if($user){
            $error = ['error'=>[__('Merchant already exist')]];
            return ApiHelpers::validation($error);
        }

        $userName = make_username($data['firstname'],$data['lastname']);
        $check_user_name = Merchant::where('username',$userName)->first();
        if($check_user_name){
            $userName = $userName.'-'.rand(123,456);
        }
        //Merchant Create
        $user = new Merchant();
        $user->firstname = isset($data['firstname']) ? $data['firstname'] : null;
        $user->lastname = isset($data['lastname']) ? $data['lastname'] : null;
        $user->business_name = isset($data['business_name']) ? $data['business_name'] : null;
        $user->email = strtolower(trim($data['email']));
        $user->mobile =  $mobile;
        $user->mobile_code =  $mobile_code;
        $user->full_mobile =    $complete_phone;
        $user->password = Hash::make($data['password']);
        $user->username = $userName;
        $user->address = [
            'address' => isset($data['address']) ? $data['address'] : '',
            'city' => isset($data['city']) ? $data['city'] : '',
            'zip' => isset($data['zip_code']) ? $data['zip_code'] : '',
            'country' =>isset($data['country']) ? $data['country'] : '',
            'state' => isset($data['state']) ? $data['state'] : '',
        ];
        $user->status = 1;
        $user->email_verified =  true;
        $user->sms_verified =  ($basic_settings->sms_verification == true) ? true : true;
        $user->kyc_verified =  ($basic_settings->merchant_kyc_verification == true) ? false : true;
        $user->save();
        if( $user && $basic_settings->merchant_kyc_verification == true){
            $create = [
                'merchant_id'       => $user->id,
                'data'          => json_encode($get_values),
                'created_at'    => now(),
            ];

            DB::beginTransaction();
            try{
                DB::table('merchant_kyc_data')->updateOrInsert(["merchant_id" => $user->id],$create);
                $user->update([
                    'kyc_verified'  => GlobalConst::PENDING,
                ]);
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                $user->update([
                    'kyc_verified'  => GlobalConst::DEFAULT,
                ]);
                $error = ['error'=>[__('Something went wrong! Please try again.')]];
                return ApiHelpers::validation($error);
            }

           }
        $token = $user->createToken('merchant_token')->accessToken;
        $this->createUserWallets($user);
        $this->createDeveloperApiReg($user);
        $this->registerNotificationToAdminApi($user,'merchant_api',"MERCHANT");
        $this->createQr($user);

        $data = ['token' => $token, 'merchant' => $user, ];
        $message =  ['success'=>[__('Registration Successful')]];
        return ApiHelpers::success($data,$message);

    }
    public function logout(){
        Auth::user()->token()->revoke();
        $message = ['success'=>[__('Logout Successfully!')]];
        return ApiHelpers::onlysuccess($message);

    }
    public function createQr($user){
		$user = $user;
	    $qrCode = $user->qrCode()->first();
        $in['merchant_id'] = $user->id;;
        $in['qr_code'] =  $user->email;
	    if(!$qrCode){
            MerchantQrCode::create($in);
	    }else{
            $qrCode->fill($in)->save();
        }
	    return $qrCode;
	}
    protected function guard()
    {
        return Auth::guard("merchant_api");
    }

}
