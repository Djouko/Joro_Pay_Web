<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillPayCategory;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\Response;
use App\Models\Admin\ReloadlyApi;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BillPayMethodController extends Controller
{
   //==============================================Bill Pay Manual Start===============================================
    public function billPayList(){
        $page_title = __("Bill Pay Method")." ( ".__("Manual")." )";
        $allCategory = BillPayCategory::orderByDesc('id')->paginate(10);
        return view('admin.sections.bill-pay.category',compact(
            'page_title',
            'allCategory',
        ));
    }
    public function storeCategory(Request $request){

        $validator = Validator::make($request->all(),[
            'name'      => 'required|string|max:200|unique:bill_pay_categories,name',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','category-add');
        }
        $validated = $validator->validate();
        $slugData = Str::slug($request->name);
        $makeUnique = BillPayCategory::where('slug',  $slugData)->first();
        if($makeUnique){
            return back()->with(['error' => [__("Method Already Exists!")]]);
        }
        $admin = Auth::user();

        $validated['admin_id']      = $admin->id;
        $validated['name']          = $request->name;
        $validated['slug']          = $slugData;
        try{
            BillPayCategory::create($validated);
            return back()->with(['success' => [__("Method Saved Successfully!")]]);
        }catch(Exception $e) {
            return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function categoryUpdate(Request $request){
        $target = $request->target;
        $category = BillPayCategory::where('id',$target)->first();
        $validator = Validator::make($request->all(),[
            'name'      => 'required|string|max:200',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','edit-category');
        }
        $validated = $validator->validate();

        $slugData = Str::slug($request->name);
        $makeUnique = BillPayCategory::where('id',"!=",$category->id)->where('slug',  $slugData)->first();
        if($makeUnique){
            return back()->with(['error' => [__("Method Already Exists!")]]);
        }
        $admin = Auth::user();
        $validated['admin_id']      = $admin->id;
        $validated['name']          = $request->name;
        $validated['slug']          = $slugData;

        try{
            $category->fill($validated)->save();
            return back()->with(['success' => [__("Method Updated Successfully!")]]);
        }catch(Exception $e) {
            return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function categoryStatusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            $error = ['error' => $validator->errors()];
            return BillPayCategory::error($error,null,400);
        }
        $validated = $validator->safe()->all();
        $category_id = $validated['data_target'];

        $category = BillPayCategory::where('id',$category_id)->first();
        if(!$category) {
            $error = ['error' => [__("Method record not found in our system.")]];
            return Response::error($error,null,404);
        }

        try{
            $category->update([
                'status' => ($validated['status'] == true) ? false : true,
            ]);
        }catch(Exception $e) {
            $error = ['error' => [__("Something went wrong! Please try again.")]];
            return Response::error($error,null,500);
        }

        $success = ['success' => [__("Method status updated successfully!")]];
        return Response::success($success,null,200);
    }
    public function categoryDelete(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:bill_pay_categories,id',
        ]);
        $validated = $validator->validate();
        $category = BillPayCategory::where("id",$validated['target'])->first();

        try{
            $category->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Method deleted successfully!")]]);
    }
    public function categorySearch(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);

        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();

        $allCategory = BillPayCategory::search($validated['text'])->select()->limit(10)->get();
        return view('admin.components.search.bill-category-search',compact(
            'allCategory',
        ));
    }
//================================================Bill Pay Manual End======================================================

//================================================Bill Pay Automatic Start=================================================
    public function manageBillPayApi()
    {
        $page_title = __("Setup Bill Pay Api");
        $api = ReloadlyApi::reloadly()->utilityPayment()->first();
        return view('admin.sections.bill-pay.reloadly.api',compact(
            'page_title',
            'api',
        ));
    }
    public function updateCredentials(Request $request){
        $validator = Validator::make($request->all(), [
            'client_id'                 => 'required|string',
            'secret_key'                => 'required|string',
            'production_base_url'       => 'required|url',
            'sandbox_base_url'          => 'required|url',
            'env'                       => 'required|string',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validate();
        $api = ReloadlyApi::reloadly()->utilityPayment()->first();
        $credentials = array_filter($request->except('_token','env','_method'));
        $data['credentials']=  $credentials;
        $data['env']        = $validated['env'];
        $data['status']     = true;
        $data['provider']   =  ReloadlyApi::PROVIDER_RELOADLY;
        $data['type']       =  ReloadlyApi::UTILITY_PAYMENT;
        if(!$api){
            ReloadlyApi::create($data);
        }else{
            $api->fill($data)->save();
        }
        return back()->with(['success' => [__("Bill Pay API Has Been Updated.")]]);
    }
//================================================Bill Pay Automatic End===================================================
}
