<?php

namespace App\Http\Controllers\Merchant;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\PaymentLink;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\GatewayAPi;
use Illuminate\Support\Facades\Validator;

class PaymentLinkController extends Controller
{
    /**
     * Payment link page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function index(){
        $page_title = __('Payment Links');
        $payment_links = PaymentLink::merchantAuth()->orderBy('id', 'desc')->paginate(12);
        return view('merchant.sections.payment-link.index', compact('page_title', 'payment_links'));
    }


    /**
     * Payment link create page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function create(){
        $page_title = __('Payment Link Create');
        try {
            $currency_data = getCurrencyList();
        } catch (\Exception $th) {
            return back()->with(['error' => [__('Unable to connect with API, Please Contact Support!!')]]);
        }

        return view('merchant.sections.payment-link.create', compact('page_title','currency_data'));
    }

    /**
     * Payment link store
     *
     * @param Illuminate\Http\Request $request
     * @method POST
     * @return Illuminate\Http\Request
     */
    public function store(Request $request){
        $token = generate_unique_string('payment_links', 'token', 60);
        if($request->type == PaymentGatewayConst::LINK_TYPE_PAY){
            $validator = Validator::make($request->all(), [
                'currency'        => 'required|string',
                'currency_symbol' => 'required|string',
                'country'         => 'required|string',
                'currency_name'   => 'required|string',
                'title'           => 'required|string|max:180',
                'type'            => 'required|string',
                'details'         => 'nullable|string',
                'limit'           => 'nullable',
                'min_amount'      => 'nullable|numeric|min:0.1',
                'max_amount'      => 'nullable|numeric|gt:min_amount',
                'image'           => 'nullable|image|mimes:png,jpg,jpeg,svg,webp',
            ]);

            if($validator->stopOnFirstFailure()->fails()){
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
            $validated = Arr::except($validated, ['image']);
            $validated['limit'] = $request->limit ? 1 : 2;
            $validated['token'] = $token;
            $validated['status'] = 1;
            $validated['merchant_id'] = Auth::id();
            try {
                $payment_link = PaymentLink::create($validated);

                if($request->hasFile('image')) {
                    try{
                        $image = get_files_from_fileholder($request,'image');
                        $upload_image = upload_files_from_path_dynamic($image,'payment-link-image');
                        $payment_link->update([
                            'image'  => $upload_image,
                        ]);
                    }catch(Exception $e) {
                        return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
                    }
                }
            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

        }else{
            $validator = Validator::make($request->all(), [
                'sub_currency'    => 'required',
                'currency_symbol' => 'required',
                'currency_name'   => 'required',
                'country'         => 'required',
                'sub_title'       => 'required|max:180',
                'type'            => 'required',
                'price'           => 'nullable:numeric',
                'qty'             => 'nullable:integer',
            ]);


            if($validator->fails()){
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
            $validated['currency'] = $validated['sub_currency'];
            $validated['title'] = $validated['sub_title'];
            $validated['token'] = $token;
            $validated['status'] = 1;
            $validated['merchant_id'] = Auth::id();

            $validated = Arr::except($validated, ['sub_currency','sub_title']);
            try {
                $payment_link = PaymentLink::create($validated);
            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }
        return redirect()->route('merchant.payment-link.share', $payment_link->id)->with(['success' => [__('payment Link Created Successfully')]]);
    }


    /**
     * Payment link eidt page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function edit($id){
        $page_title = __('Payment Link Edit');

        try {
            $currency_data = getCurrencyList();
        } catch (\Exception $th) {
            return back()->with(['error' => [__('Unable to connect with API, Please Contact Support!!')]]);
        }

        $payment_link = PaymentLink::findOrFail($id);
        return view('merchant.sections.payment-link.edit', compact('page_title','currency_data','payment_link'));
    }


    /**
     * Payment link store
     *
     * @param Illuminate\Http\Request $request
     * @method POST
     * @return Illuminate\Http\Request
     */
    public function update(Request $request){
        $paymentLink = PaymentLink::find($request->target);
        if($request->type == PaymentGatewayConst::LINK_TYPE_PAY){
            $validator = Validator::make($request->all(), [
                'currency'        => 'required',
                'currency_symbol' => 'required',
                'currency_name'   => 'required',
                'title'           => 'required|max:180',
                'type'            => 'required',
                'details'         => 'nullable',
                'limit'           => 'nullable',
                'min_amount'      => 'nullable|min:0.1',
                'max_amount'      => 'nullable|gt:min_amount',
                'image'           => 'nullable|image|mimes:png,jpg,jpeg,svg,webp',
            ]);

            if($validator->fails()){
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();

            if($paymentLink->type == PaymentGatewayConst::LINK_TYPE_SUB){
                $validated['price'] = NULL;
                $validated['qty'] = NULL;
            }


            $validated = Arr::except($validated, ['image']);
            $validated['limit'] = $request->limit ? 1 : 2;
            $validated['merchant_id'] = Auth::id();
            try {
                if($request->hasFile('image')) {
                    try{
                        $image = get_files_from_fileholder($request,'image');
                        $upload_image = upload_files_from_path_dynamic($image,'payment-link-image',$paymentLink->image);
                        $validated['image'] = $upload_image;
                    }catch(Exception $e) {
                        return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
                    }
                }

                $paymentLink->update($validated);

            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

        }else{
            $validator = Validator::make($request->all(), [
                'sub_currency'    => 'required',
                'currency_symbol' => 'required',
                'currency_name'   => 'required',
                'sub_title'       => 'required|max:180',
                'type'            => 'required',
                'price'           => 'nullable',
                'qty'             => 'nullable',
            ]);

            $validated = $validator->validated();
            $validated['currency'] = $validated['sub_currency'];
            $validated['title'] = $validated['sub_title'];
            $validated['merchant_id'] = Auth::id();

            if($paymentLink->type == PaymentGatewayConst::LINK_TYPE_PAY){

                $validated['image'] = NULL;
                $validated['details'] = NULL;
                $validated['limit'] = 2;
                $validated['min_amount'] = NULL;
                $validated['max_amount'] = NULL;

                $image_link = get_files_path('payment-link-image') . '/' . $paymentLink->image;
                delete_file($image_link);
            }

            $validated = Arr::except($validated, ['sub_currency','sub_title']);
            try {
                $paymentLink->update($validated);
            } catch (\Exception $th) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }


        return redirect()->route('merchant.payment-link.share', $paymentLink->id)->with(['success' => [__('Payment Link Updated Successful')]]);
    }

    /**
     * Payment link store
     *
     * @param Illuminate\Http\Request $request
     * @method POST
     * @return Illuminate\Http\Request
     */
    public function status(Request $request){


        $validator = Validator::make($request->all(), [
            'target'        => 'required',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $paymentLink = PaymentLink::find($validated['target']);

        try {
            $status = $paymentLink->status == 1 ? 2 : 1;
            $paymentLink->update(['status' => $status]);

        } catch (\Exception $th) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }


        return redirect()->route('merchant.payment-link.index')->with(['success' => [__('Payment Link Status Updated Successful')]]);
    }


    /**
     * Payment link eidt page show
     *
     * @method GET
     * @return Illuminate\Http\Request
     */
    public function share($id){
        $page_title = __('Link Share');
        $payment_link = PaymentLink::findOrFail($id);
        return view('merchant.sections.payment-link.share', compact('page_title','payment_link'));
    }

    /**
     * Payment Link Share
     *
     * @method GET
     * @return Illuminate\Http\Request
     */

    public function paymentLinkShare($token){
        $payment_link = PaymentLink::with('merchant')->where('status', 1)->where('token', $token)->first();

        if(empty($payment_link)){
            return redirect()->route('index')->with(['error' => [__('Invalid Payment Link')]]);
        }

        $credentials = GatewayAPi::first();
        if(empty($credentials)){
            return redirect()->route('index')->with(['error' => [__('Can Not Payment Now, Please Contact Support')]]);
        }
        $public_key = $credentials->public_key;

        $page_title = __('Payment Link');
        return view('frontend.paylink.share', compact('payment_link', 'page_title', 'public_key'));
    }

}
