<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceiveMoneyController extends Controller
{
    public function index() {
        $page_title = __("Receive Money");
        $user = auth()->user();
        $user->createQr();
        $userQrCode = $user->qrCode()->first();
        $uniqueCode = $userQrCode->qr_code??'';
        $qrCode = generateQr($uniqueCode);
        return view('agent.sections.receive-money.index',compact("page_title","uniqueCode","qrCode",'user'));
    }

}
