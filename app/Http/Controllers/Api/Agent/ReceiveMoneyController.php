<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Helpers\Api\Helpers;
use App\Http\Controllers\Controller;

class ReceiveMoneyController extends Controller
{
    public function index() {
        $user = authGuardApi()['user'];
        $user->createQr();
        $userQrCode = $user->qrCode()->first();
        $uniqueCode = $userQrCode->qr_code??'';
        $qrCode = generateQr($uniqueCode);
        $data = [
            'uniqueCode' => @$uniqueCode,
            'qrCode' => @$qrCode,
        ];
        $message = ['success' => [__('Receive Money')]];
        return Helpers::success($data, $message);

    }
}
