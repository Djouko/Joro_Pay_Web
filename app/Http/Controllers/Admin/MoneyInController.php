<?php

namespace App\Http\Controllers\Admin;

use App\Constants\PaymentGatewayConst;
use App\Exports\MoneyInTrxExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Providers\Admin\BasicSettingsProvider;
use Maatwebsite\Excel\Facades\Excel;

class MoneyInController extends Controller
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
        )->where('type', PaymentGatewayConst::MONEYIN)->where('attribute',PaymentGatewayConst::SEND)->latest()->paginate(20);
        return view('admin.sections.money-in.index',compact(
            'page_title','transactions'
        ));
    }
    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_money_in_Logs".'.xlsx';
        return Excel::download(new MoneyInTrxExport,$file_name);
    }
}
