<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin\GatewayAPi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GatewayApiController extends Controller
{
    public function index()
    {
        $page_title = __('PayLink Api');
        $api = GatewayAPi::first();
        return view('admin.sections.gateway-api.index',compact(
            'page_title',
            'api',
        ));
    }
    public function updateCardCredentials(Request $request){

        $validator = Validator::make($request->all(), [
            'secret_key'  => 'required|string',
            'public_key' => 'required|string',
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $validated['admin_id'] = Auth::id();

        try {
            GatewayAPi::updateOrCreate(['id' => 1],$validated);
        } catch (\Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Stripe Api Key Updated Successful")]]);
    }
    public function updateWalletStatus(Request $request)
    {
        $setting = GatewayAPi::first();
        $status = $request->status;
        $setting->wallet_status = $status;
        $setting->save();
        return response()->json(['status' => $status]);
    }
    public function updatePaymentGatewayStatus(Request $request)
    {
        $setting = GatewayAPi::first();
        $status = $request->status;
        $setting->payment_gateway_status = $status;
        $setting->save();
        return response()->json(['status' => $status]);
    }
    public function updateCardStatus(Request $request)
    {
        $setting = GatewayAPi::first();
        $status = $request->status;
        $setting->card_status = $status;
        $setting->save();
        return response()->json(['status' => $status]);
    }

}
