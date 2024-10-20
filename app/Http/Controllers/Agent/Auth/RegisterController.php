<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Http\Controllers\Controller;
use App\Constants\GlobalConst;
use App\Models\Admin\SetupKyc;
use App\Models\Agent;
use App\Models\AgentAuthorization;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;
use App\Notifications\User\Auth\SendVerifyCode;
use App\Traits\Agent\RegisteredUsers;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\ControlDynamicInputFields;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use App\Traits\AdminNotifications\AuthNotifications;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, RegisteredUsers, ControlDynamicInputFields,AuthNotifications;

    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm() {
        $client_ip = request()->ip() ?? false;
        $user_country = geoip()->getLocation($client_ip)['country'] ?? "";

        $page_title = __("Agent Registration");
        return view('agent.auth.register',compact(
            'page_title',
            'user_country',
        ));
    }
    //========================before registration======================================

    public function sendVerifyCode(Request $request){
        $basic_settings = $this->basic_settings;
        if($basic_settings->agent_agree_policy){
            $agree = 'required';
        }else{
            $agree = '';
        }
        $validator = Validator::make($request->all(),[
            'email'         => 'required|email',
            'agree'         =>  $agree,

        ]);
        $validated = $validator->validate();

        $field_name = "username";
        if(check_email($validated['email'])) {
            $field_name = "email";
        }

        $exist = Agent::where($field_name,$validated['email'])->first();
        if( $exist) return back()->with(['error' => [__('Agent already  exists, please try with another email')]]);
        $code = generate_random_code();
        $data = [
            'agent_id'       =>  0,
            'email'         => $validated['email'],
            'code'          => $code,
            'token'         => generate_unique_string("agent_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            if($basic_settings->agent_email_verification == false){
                Session::put('register_email',$validated['email']);
                return redirect()->route("agent.register.kyc");
            }
            DB::table("agent_authorizations")->insert($data);
            Session::put('register_email',$validated['email']);
            if($basic_settings->agent_email_notification == true && $basic_settings->agent_email_verification == true){
                try{
                    Notification::route("mail",$validated['email'])->notify(new SendVerifyCode($validated['email'], $code));
                }catch(Exception $e){

                }
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        };
        return redirect()->route('agent.email.verify',$data['token'])->with(['success' => [__('Verification code sended to your email address.')]]);
    }
    public function verifyCode(Request $request,$token){
        $request->merge(['token' => $token]);
        $request->validate([
            'token'     => "required|string|exists:agent_authorizations,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("",$code);
        $otp_exp_sec = BasicSettingsProvider::get()->agent_otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = AgentAuthorization::where("token",$request->token)->where("code",$code)->first();
        if(!$auth_column){
            return back()->with(['error' => [__('The verification code does not match')]]);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $auth_column->delete();
            return redirect()->route('agent.register')->with(['error' => [__('Session expired. Please try again')]]);
        }
        try{
            $auth_column->delete();
        }catch(Exception $e) {
            return redirect()->route('agent.register')->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return redirect()->route("agent.register.kyc")->with(['success' => [__('Otp successfully verified')]]);
    }
    public function resendCode(Request $request){
        $email = session()->get('register_email');
        $resend = AgentAuthorization::where("email",$email)->first();
        if( $resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                throw ValidationException::withMessages([
                    'code'      => __('You can resend the verification code after').' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)). ' '. __('seconds'),
                ]);
            }
        }

        $code = generate_random_code();
        $data = [
            'agent_id'       =>  0,
            'email'         => $email,
            'code'          => $code,
            'token'         => generate_unique_string("agent_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            $oldToken = AgentAuthorization::where("email",$email)->get();
            if($oldToken){
                foreach($oldToken as $token){
                    $token->delete();
                }
            }
            DB::table("agent_authorizations")->insert($data);
            try{
                Notification::route("mail",$email)->notify(new SendVerifyCode($email, $code));
            }catch(Exception $e){

            }

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return redirect()->route('agent.email.verify',$data['token'])->with(['success' => [__('Verification code resend success')]]);
    }
    public function registerKyc(Request $request){
        $basic_settings   = $this->basic_settings;
        $email =   session()->get('register_email');
        if($email == null){
            return redirect()->route('agent.register');
        }
        $kyc_fields =[];
        if($basic_settings->agent_kyc_verification == true){
            $user_kyc = SetupKyc::agentKyc()->first();
            if(!$user_kyc) return back();
            $kyc_data = $user_kyc->fields;
            $kyc_fields = [];
            if($kyc_data) {
                $kyc_fields = array_reverse($kyc_data);
            }
        }
        $page_title = __("Agent Registration KYC");
        return view('agent.auth.register-kyc',compact(
            'page_title','email','kyc_fields'

        ));
    }
    //========================before registration======================================

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $basic_settings             = $this->basic_settings;
        $validated = $this->validator($request->all())->validate();
        if($basic_settings->agent_kyc_verification == true){
            $user_kyc_fields = SetupKyc::agentKyc()->first()->fields ?? [];
            $validation_rules = $this->generateValidationRules($user_kyc_fields);
            $kyc_validated = Validator::make($request->all(),$validation_rules)->validate();
            $get_values = $this->registerPlaceValueWithFields($user_kyc_fields,$kyc_validated);
        }
        try{
            $validated['phone_code'] = get_country_phone_code($validated['country']);
        }catch(Exception $e) {
            return $this->breakAuthentication($e->getMessage());
        }

        $validated['mobile']        = remove_speacial_char($validated['phone']);
        $validated['mobile_code']   = remove_speacial_char($validated['phone_code']);
        $complete_phone             = $validated['mobile_code'] . $validated['mobile'];

        if(Agent::where('full_mobile',$complete_phone)->exists()) {
            throw ValidationException::withMessages([
                'phone'     => __('Phone number is already exists'),
            ]);
        }
        $userName = make_username($validated['firstname'],$validated['lastname'],'agents');
        $check_user_name = Agent::where('username',$userName)->first();
        if($check_user_name){
            $userName = $userName.'-'.rand(123,456);
        }

        $validated['full_mobile']       = $complete_phone;
        $validated = Arr::except($validated,['agree','phone_code','phone']);
        $validated['email_verified']    = true;
        $validated['sms_verified']      = ($basic_settings->sms_verification == true) ? false : true;
        $validated['kyc_verified']      = ($basic_settings->agent_kyc_verification == true) ? false : true;
        $validated['password']          = Hash::make($validated['password']);
        $validated['username']          =  $userName;
        $validated['address']           = [
                                            'country' => $validated['country'],
                                            'city' => $validated['city'],
                                            'zip' => $validated['zip_code'],
                                            'state' => '',
                                            'address' => '',
                                        ];
       $data = event(new Registered($user = $this->create($validated)));
       if( $data  && $basic_settings->agent_kyc_verification == true){
        $create = [
            'agent_id'       => $user->id,
            'data'          => json_encode($get_values),
            'created_at'    => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('agent_kyc_data')->updateOrInsert(["agent_id" => $user->id],$create);
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $user->update([
                'kyc_verified'  => GlobalConst::DEFAULT,
            ]);

            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }


       }
       $request->session()->forget('register_info');
        $this->guard()->login($user);

        return $this->registered($request, $user);
    }
    protected function guard()
    {
        return Auth::guard('agent');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data) {
        $basic_settings = $this->basic_settings;
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->agent_secure_password) {
            $passowrd_rule = ["required","confirmed",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }
        if($basic_settings->agent_agree_policy){
            $agree = 'required';
        }else{
            $agree = '';
        }

        return Validator::make($data,[
            'firstname'     => 'required|string|max:60',
            'lastname'      => 'required|string|max:60',
            'store_name' => 'required|string|max:100',
            'email'         => 'required|string|email|max:150|unique:agents,email',
            'password'      => $passowrd_rule,
            'country'       => 'required|string|max:150',
            'city'          => 'required|string|max:150',
            'phone_code'    => 'required|string|max:10',
            'phone'         => 'required|string|max:20',
            'zip_code'      => 'required|string|max:8',
            'agree'         =>  $agree,
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return Agent::create($data);
    }


    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        $user->createQr();
        $this->createUserWallets($user);
        $this->registerNotificationToAdmin($user);
        return redirect()->intended(route('agent.dashboard'));
    }
}
