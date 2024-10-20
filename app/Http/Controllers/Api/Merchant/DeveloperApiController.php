<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\BasicSettings;
use App\Models\Merchants\DeveloperApiCredential;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeveloperApiController extends Controller
{
    public function index()
    {
        $merchant = auth()->user();
        $keys = DeveloperApiCredential::auth()->active()->latest()->get() ;
        $keys->makeHidden(['merchant_id','updated_at']);
        $data = [
            'keys' =>$keys??[]
        ];
        $message = ['success' => [__('Merchant Developer Api Key')]];
        return Helpers::success($data, $message);
    }
    public function updateMode(Request $request) {
        $validator = Validator::make($request->all(), [
            'target'     => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $merchant_developer_api = DeveloperApiCredential::where('id',$validated['target'])->auth()->first();

        if(!$merchant_developer_api) {
            $error = ['error'=>[__('Developer API not found!')]];
            return Helpers::error($error);
        }
        $update_mode = ($merchant_developer_api->mode == PaymentGatewayConst::ENV_SANDBOX) ? PaymentGatewayConst::ENV_PRODUCTION : PaymentGatewayConst::ENV_SANDBOX;

        try{
            $merchant_developer_api->update([
                'mode'      => $update_mode,
            ]);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message = ['success'=>[__('Developer API mode updated successfully!')]];
        return Helpers::onlysuccess($message);
    }
    public function generateApiKeys(Request $request){
        $validator = Validator::make($request->all(), [
            'name'     => "required|string|max:100",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $merchant =  authGuardApi()['user'];
        $check = DeveloperApiCredential::where('name',$validated['name'])->first();
        if( $check){
            $error = ['error'=>[__("The developer API key with this name has already been created")]];
            return Helpers::error($error);
        }
        try{
            DeveloperApiCredential::create([
                'merchant_id'       => $merchant->id,
                'name'              => $validated['name'],
                'client_id'         => generate_unique_string("developer_api_credentials","client_id",100),
                'client_secret'     => generate_unique_string("developer_api_credentials","client_secret",100),
                'mode'              => PaymentGatewayConst::ENV_SANDBOX,
                'status'            => true,
                'created_at'        => now(),
            ]);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message = ['success'=>[__('Api Keys Created Successfully')]];
        return Helpers::onlysuccess($message);

    }
    public function deleteKys(Request $request) {
        $validator = Validator::make($request->all(), [
            'target'     => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $merchant_developer_api = DeveloperApiCredential::where('id',$validated['target'])->auth()->first();
        if(!$merchant_developer_api) {
            $error = ['error'=>[__('Developer API not found!')]];
            return Helpers::error($error);
        }
        try{
            $merchant_developer_api->delete();
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message = ['success'=>[__('Api Keys Deleted Successfully')]];
        return Helpers::onlysuccess($message);
    }

}
