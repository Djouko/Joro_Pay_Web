<?php

namespace App\Http\Controllers\Admin;

use App\Constants\PaymentGatewayConst;
use App\Exports\MakePaymentTrxExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Providers\Admin\BasicSettingsProvider;
use Maatwebsite\Excel\Facades\Excel;

class MakePaymentController extends Controller
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
        )->where('type', PaymentGatewayConst::TYPEMAKEPAYMENT)->where('attribute',PaymentGatewayConst::SEND)->latest()->paginate(20);
        return view('admin.sections.make-payment.index',compact(
            'page_title','transactions'
        ));
    }
    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_make_payment_Logs".'.xlsx';
        return Excel::download(new MakePaymentTrxExport,$file_name);
    }
}
