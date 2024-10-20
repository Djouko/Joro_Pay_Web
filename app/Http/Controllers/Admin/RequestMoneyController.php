<?php

namespace App\Http\Controllers\Admin;

use App\Constants\PaymentGatewayConst;
use App\Exports\RequestMoneyTrxExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Providers\Admin\BasicSettingsProvider;
use Maatwebsite\Excel\Facades\Excel;

class RequestMoneyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index()
    {
        $page_title = __("All Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name,alias,payment_gateway_id,currency_code,rate',
        )->where('type', PaymentGatewayConst::REQUESTMONEY)->where('attribute',PaymentGatewayConst::SEND)->latest()->paginate(20);
        return view('admin.sections.request-money.index',compact(
            'page_title','transactions'
        ));
    }
     /**
     * Display All Pending Logs
     * @return view
     */
    public function pending() {
        $page_title = __("Pending Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name,alias,payment_gateway_id,currency_code,rate',
        )->where('type', PaymentGatewayConst::REQUESTMONEY)->where('attribute',PaymentGatewayConst::SEND)->pending()->latest()->paginate(20);
        return view('admin.sections.request-money.index',compact(
            'page_title','transactions'
        ));
    }
    /**
     * Display All Complete Logs
     * @return view
     */
    public function complete() {
        $page_title = __("Complete Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name,alias,payment_gateway_id,currency_code,rate',
        )->where('type', PaymentGatewayConst::REQUESTMONEY)->where('attribute',PaymentGatewayConst::SEND)->success()->latest()->paginate(20);
        return view('admin.sections.request-money.index',compact(
            'page_title','transactions'
        ));
    }
    /**
     * Display All Canceled Logs
     * @return view
     */
    public function canceled() {
        $page_title =  __("Canceled Logs");
        $transactions = Transaction::with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name,alias,payment_gateway_id,currency_code,rate',
        )->where('type', PaymentGatewayConst::REQUESTMONEY)->where('attribute',PaymentGatewayConst::SEND)->rejected()->latest()->paginate(20);
        return view('admin.sections.request-money.index',compact(
            'page_title','transactions'
        ));
    }
    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_request_money_Logs".'.xlsx';
        return Excel::download(new RequestMoneyTrxExport,$file_name);
    }
}
