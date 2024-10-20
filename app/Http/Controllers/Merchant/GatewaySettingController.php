<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Merchants\GatewaySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GatewaySettingController extends Controller
{
    public function index()
    {
        $merchant = auth()->user();
        $page_title = __('gateway Settings');
        $setting = GatewaySetting::merchantAuth()->first();
        return view('merchant.sections.gateway-settings.index',compact('page_title','setting'));
    }
    public function updateWalletStatus(Request $request)
    {
        $setting = GatewaySetting::merchantAuth()->first();
        $status = $request->status;
        $setting->wallet_status = $status;
        $setting->save();
        return response()->json(['status' => $status]);
    }
    public function updateVirtualCardStatus(Request $request)
    {
        $setting = GatewaySetting::merchantAuth()->first();
        $status = $request->status;
        $setting->virtual_card_status = $status;
        $setting->save();
        return response()->json(['status' => $status]);
    }
    public function updateMasterCardStatus(Request $request)
    {
        $setting = GatewaySetting::merchantAuth()->first();
        $status = $request->status;
        $setting->master_visa_status = $status;
        $setting->save();
        return response()->json(['status' => $status]);
    }
    public function updateMasterCardCredentials(Request $request)
    {
        $validated = Validator::make($request->all(),[
            'primary_key'     => "required|string|max:255",
            'secret_key'      => "required|string|max:255",
        ])->validate();
        $setting = GatewaySetting::merchantAuth()->first();
        $setting->master_visa_status = true;
        $setting->credentials = [
                    'primary_key' => $validated['primary_key']??$setting->primary_key,
                    'secret_key' => $validated['secret_key']??$setting->secret_key
                ];
        $setting->save();
        return back()->with(['success' => [__('Master/Visa card credentials updated successfully!')]]);

    }
}
