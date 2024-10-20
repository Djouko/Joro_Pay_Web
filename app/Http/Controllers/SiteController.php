<?php

namespace App\Http\Controllers;

use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PayLinkPaymentGateway;
use App\Http\Helpers\PaymentGateway;
use App\Http\Helpers\PaymentGatewayApi;
use App\Models\Admin\AppSettings;
use App\Models\Admin\BasicSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use App\Models\Admin\Language;
use App\Models\Admin\SetupPage;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Contact;
use App\Models\Newsletter;
use App\Models\PaymentLink;
use App\Models\TemporaryData;
use App\Notifications\Admin\ActivityNotification;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    public function home(){
        $basic_settings = BasicSettings::first();
        $page_title = $basic_settings->site_title??"Home";
        $app_urls = AppSettings::first();
        return view('frontend.index',compact('page_title','app_urls'));
    }
    public function about(){
        $page_title = "About";
        return view('frontend.about',compact('page_title'));
    }
    public function faq(){
        $page_title = "Faq";
        return view('frontend.faq',compact('page_title'));
    }
    public function service(){
        $page_title = "Service";
        return view('frontend.service',compact('page_title'));
    }
    public function blog(){
        $page_title = "Blog";
        $categories = BlogCategory::active()->latest()->get();
        $blogs = Blog::active()->orderBy('id',"DESC")->paginate(8);
        $recentPost = Blog::active()->latest()->limit(3)->get();
        return view('frontend.blog',compact('page_title','blogs','recentPost','categories'));
    }
    public function blogDetails($id,$slug){
        $page_title = "Blog Details";
        $categories = BlogCategory::active()->latest()->get();
        $blog = Blog::where('id',$id)->where('slug',$slug)->first();
        $recentPost = Blog::active()->where('id',"!=",$id)->latest()->limit(3)->get();
        return view('frontend.blogDetails',compact('page_title','blog','recentPost','categories'));
    }
    public function blogByCategory($id,$slug){
        $categories = BlogCategory::active()->latest()->get();
        $category = BlogCategory::findOrfail($id);
        $page_title = __("category");
        $blogs = Blog::active()->where('category_id',$category->id)->latest()->paginate(8);
        $recentPost = Blog::active()->latest()->limit(3)->get();
        return view('frontend.blogByCategory',compact('page_title','blogs','category','categories','recentPost'));
    }
    public function agentInfo(){
        $page_title = "Agent";
        return view('frontend.agent-info',compact('page_title'));
    }
    public function merchant(){
        $page_title = "Merchant";
        return view('frontend.merchant',compact('page_title'));
    }
    public function contact(){
        $page_title = "Contact Us";
        return view('frontend.contact',compact('page_title'));
    }
    public function contactStore(Request $request){

        $validator = Validator::make($request->all(),[
            'name'    => 'required|string',
            'email'   => 'required|email',
            'mobile'  => 'required',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validate();
        try {
            Contact::create($validated);
        } catch (\Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__('Your Message Submitted!')]]);

    }
    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        session()->put('local', $lang);
        return redirect()->back();
    }
    public function usefulPage($slug){
        $defualt = selectedLang();
        $page = SetupPage::where('slug', $slug)->where('status', 1)->first();
        if(empty($page)){
            abort(404);
        }
        $page_title = $page->title->language->$defualt->title;

        return view('frontend.policy_pages',compact('page_title','page','defualt'));
    }
    public function newsletterSubmit(Request $request){
        $validator = Validator::make($request->all(),[
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:newsletters',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $in['fullname'] = $request->fullname;
        $in['email'] = $request->email;
        try{
            Newsletter::create($in);
            return redirect()->back()->with(['success' => [__('Your newsletter information submission successfully')]]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function pagaditoSuccess(){
        $request_data = request()->all();
        //if payment is successful
            $token = $request_data['param1'];
            $checkTempData = TemporaryData::where("type",PaymentGatewayConst::PAGADITO)->where("identifier",$token)->first();
            if(isset($checkTempData->data->type) && $checkTempData->data->type === PaymentGatewayConst::TYPEPAYLINK){
                return $this->pagaditoSuccessPayLink($checkTempData);
            }
            if($checkTempData->data->env_type == 'web'){
                if(!$checkTempData) return redirect()->route('user.add.money.index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
                $checkTempData = $checkTempData->toArray();
                try{
                    PaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }
                return redirect()->route("user.add.money.index")->with(['success' => ['Successfully added money']]);

            }elseif($checkTempData->data->env_type == 'agent'){
                if(!$checkTempData) return redirect()->route('agent.add.money.index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
                $checkTempData = $checkTempData->toArray();
                try{
                    PaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }
                return redirect()->route("agent.add.money.index")->with(['success' => ['Successfully added money']]);

            }elseif($checkTempData->data->env_type == 'api'){
                if(!$checkTempData) {
                    $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
                    return Helpers::error($message);
                }
                $checkTempData = $checkTempData->toArray();
                try{
                    PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    $message = ['error' => [$e->getMessage()]];
                    Helpers::error($message);
                }
                $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
                return Helpers::onlysuccess($message);
            }elseif($checkTempData->data->env_type == 'agent_api'){
                if(!$checkTempData) {
                    $message = ['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]];
                    return Helpers::error($message);
                }
                $checkTempData = $checkTempData->toArray();
                $creator_table = $checkTempData['data']->creator_table ?? null;
                $creator_id = $checkTempData['data']->creator_id ?? null;
                $creator_guard = $checkTempData['data']->creator_guard ?? null;
                $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
                if($creator_table != null && $creator_id != null && $creator_guard != null) {
                    if(!array_key_exists($creator_guard,$api_authenticated_guards)) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
                    $creator = DB::table($creator_table)->where("id",$creator_id)->first();
                    if(!$creator) throw new Exception(__("Request user doesn\'t save properly. Please try again"));
                    $api_user_login_guard = $api_authenticated_guards[$creator_guard];
                    Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
                }

                try{
                    PaymentGatewayApi::init($checkTempData)->type(PaymentGatewayConst::TYPEADDMONEY)->responseReceive('pagadito');
                }catch(Exception $e) {
                    $message = ['error' => [$e->getMessage()]];
                    Helpers::error($message);
                }
                $message = ['success' => [__("Payment Successful, Please Go Back Your App")]];
                return Helpers::onlysuccess($message);
            }else{
                $message = ['error' => [__("Transaction failed")]];
                Helpers::error($message);
            }


    }
    public function pagaditoSuccessPayLink($checkTempData){
        if(!$checkTempData) return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        $checkTempData = $checkTempData->toArray();
        try{
            PayLinkPaymentGateway::init($checkTempData)->type(PaymentGatewayConst::TYPEPAYLINK)->responseReceive('pagadito');
        }catch(Exception $e) {
            return redirect()->route('index')->with(['error' => [__("Transaction Failed. The record didn't save properly. Please try again")]]);
        }
        $payment_link = PaymentLink::find($checkTempData['data']->validated->target);
        return redirect()->route('payment-link.transaction.success', $payment_link->token)->with(['success' => [__('Transaction Successful')]]);
    }


}
