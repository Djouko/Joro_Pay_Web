<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Merchants\GatewaySetting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GatewaySettingController extends Controller
{
    public function index()
    {
        $setting = GatewaySetting::merchantAuth()->first();
        $data = [
            'wallet_status' => $setting->wallet_status,
            'virtual_card_status' =>$setting->virtual_card_status,
            'master_visa_status' =>$setting->master_visa_status,
            'credentials' =>[
                'primary_key' => $setting->credentials->primary_key??'',
                'secret_key' => $setting->credentials->secret_key??''
            ],

        ];
        $message = ['success' => [__('gateway Settings')]];
        return Helpers::success($data, $message);
    }
    public function updateWalletStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $setting = GatewaySetting::merchantAuth()->first();
        if($request->status == true &&  $setting->wallet_status == true){
            $error = ['error'=>[__('Your Wallet Balance System Already Enabled')]];
            return Helpers::error($error);
        }
        if($request->status == false &&  $setting->wallet_status == false){
            $error = ['error'=>[__('DYour Wallet Balance System Already Disabled')]];
            return Helpers::error($error);
        }
        try{
            $status = $request->status;
            $setting->wallet_status = $status;
            $setting->save();
        }catch(Exception $e){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        if($request->status == true ){
            $message = ['success' => [__('EWallet Balance System Enabled Successfully')]];
            return Helpers::onlysuccess($message);
        }else{
            $message = ['success' => [__('Wallet Balance System Disabled Successfully')]];
            return Helpers::onlysuccess($message);
        }
    }
    public function updateVirtualCardStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $setting = GatewaySetting::merchantAuth()->first();
        if($request->status == true &&  $setting->virtual_card_status == true){
            $error = ['error'=>[__('EYour Virtual Card System Already Enabled')]];
            return Helpers::error($error);
        }
        if($request->status == false &&  $setting->virtual_card_status == false){
            $error = ['error'=>[__('Your Virtual Card System Already Disabled')]];
            return Helpers::error($error);
        }
        try{
            $status = $request->status;
            $setting->virtual_card_status = $status;
            $setting->save();
        }catch(Exception $e){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        if($request->status == true ){
            $message = ['success' => [__('EVirtual Card System Enabled Successfully')]];
            return Helpers::onlysuccess($message);
        }else{
            $message = ['success' => [__('Virtual Card System Disabled Successfully')]];
            return Helpers::onlysuccess($message);
        }
    }
    public function updateMasterCardStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $setting = GatewaySetting::merchantAuth()->first();
        if($request->status == true &&  $setting->master_visa_status == true){
            $error = ['error'=>[__('EYour Master/Visa System Already Enabled')]];
            return Helpers::error($error);
        }
        if($request->status == false &&  $setting->master_visa_status == false){
            $error = ['error'=>[__('Your Master/Visa System Already Disabled')]];
            return Helpers::error($error);
        }
        try{
            $status = $request->status;
            $setting->master_visa_status = $status;
            $setting->save();
        }catch(Exception $e){
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        if($request->status == true ){
            $message = ['success' => [__('EMaster/Visa System Enabled Successfully')]];
            return Helpers::onlysuccess($message);
        }else{
            $message = ['success' => [__('Master/Visa System Disabled Successfully')]];
            return Helpers::onlysuccess($message);
        }
    }
    public function updateMasterCardCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'primary_key'     => "required|string|max:255",
            'secret_key'      => "required|string|max:255",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $setting = GatewaySetting::merchantAuth()->first();
        $setting->master_visa_status = true;
        $setting->credentials = [
                                    'primary_key' => $validated['primary_key']??$setting->primary_key,
                                    'secret_key' => $validated['secret_key']??$setting->secret_key
                                ];
        $setting->save();
        $message = ['success' => [__('Master/Visa card credentials updated successfully!')]];
        return Helpers::onlysuccess($message);

    }
}
