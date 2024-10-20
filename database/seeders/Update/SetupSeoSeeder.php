<?php

namespace Database\Seeders\Update;

use App\Models\Admin\SetupSeo;
use Illuminate\Database\Seeder;

class SetupSeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seo_data = SetupSeo::first();
        if($seo_data){
            $seo_data->title = 'QRPAY - Money Transfer with QR Code';
            $seo_data->desc = 'QRPay offers a comprehensive solution for seamless money transfers using QR codes, catering to Android and iOS platforms, along with a user-friendly website and efficient admin panels. The system comprises three distinct interfaces: User Panel, Agent Panel, Merchant Panel, and Super Admin Panel. Key features encompass effortless money transfers through QR codes, swift payment processing, mobile top-up services, bill payment functionalities, streamlined remittance solutions, virtual card options, a secure payment checkout page, versatile payment gateway integration, and an accessible Developer API. Our commitment is in delivering exceptional software solutions at a budget-friendly cost, empowering you to capitalize on opportunities and excel in this dynamic industry. Embrace the opportunity to elevate ordinary operations into extraordinary accomplishments with QRPay.';
            $seo_data->tags = ["agent","contactless payment","developer api","digital wallet","ewallet","flutter app","gateway solutions","merchant api","mobile wallet","money transfer","payment gateway","qr code money transfer","qr code payment","qr code wallet","qrpay"];
            $seo_data->image = '94aa72f8-7de4-4968-947a-994eeffdd761.webp';
            $seo_data->save();
        }
    }
}
