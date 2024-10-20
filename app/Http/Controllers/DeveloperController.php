<?php

namespace App\Http\Controllers;

use App\Models\Admin\BasicSettings;
use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    public function index(){
        $basic_settings = BasicSettings::first();
        $page_title = $basic_settings->site_title??"Developer";
        return view('frontend.developer.index',compact('page_title'));
    }
    public function prerequisites(){
        $page_title = "Prerequisites";
        return view('frontend.developer.prerequisites',compact('page_title'));
    }
    public function authentication(){
        $page_title = "Authentication";
        return view('frontend.developer.authentication',compact('page_title'));
    }
    public function baseUrl(){
        $page_title = "Base URL";
        return view('frontend.developer.base-url',compact('page_title'));
    }
    public function accessToken(){
        $page_title = "Access Token";
        return view('frontend.developer.access-token',compact('page_title'));
    }
    public function initiatePayment(){
        $page_title = "Initiate Payment";
        return view('frontend.developer.initiate-payment',compact('page_title'));
    }
    public function checkStatusPayment(){
        $page_title = "Check Payment Status";
        return view('frontend.developer.check-status-payment',compact('page_title'));
    }
    public function responseCode(){
        $page_title = "Response Codes";
        return view('frontend.developer.response-code',compact('page_title'));
    }
    public function errorHandling(){
        $page_title = "Error Handling";
        return view('frontend.developer.error-handling',compact('page_title'));
    }
    public function bestPractices(){
        $page_title = "Best Practices";
        return view('frontend.developer.best-practices',compact('page_title'));
    }
    public function examples(){
        $page_title = "Examples";
        return view('frontend.developer.examples',compact('page_title'));
    }
    public function faq(){
        $page_title = "FAQ";
        return view('frontend.developer.faq',compact('page_title'));
    }
    public function support(){
        $page_title = "Support";
        return view('frontend.developer.support',compact('page_title'));
    }
    
}
