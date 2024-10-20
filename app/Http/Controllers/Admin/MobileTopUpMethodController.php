<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Models\Admin\ReloadlyApi;
use App\Models\TopupCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Exception;

class MobileTopUpMethodController extends Controller
{
    //==============================================Top Up Method (Manual) Start================================================
        public function topUpcategories(){
            $page_title = __("Mobile Topup Method");
            $allCategory = TopupCategory::orderByDesc('id')->paginate(10);
            return view('admin.sections.mobile-topups.category',compact(
                'page_title',
                'allCategory',
            ));
        }
        public function storeCategory(Request $request){

            $validator = Validator::make($request->all(),[
                'name'      => 'required|string|max:200|unique:topup_categories,name',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','category-add');
            }
            $validated = $validator->validate();
            $slugData = Str::slug($request->name);
            $makeUnique = TopupCategory::where('slug',  $slugData)->first();
            if($makeUnique){
                return back()->with(['error' => [ $request->name.' '.__('Method Already Exists!')]]);
            }
            $admin = Auth::user();

            $validated['admin_id']      = $admin->id;
            $validated['name']          = $request->name;
            $validated['slug']          = $slugData;
            try{
                TopupCategory::create($validated);
                return back()->with(['success' => [__("Method Saved Successfully!")]]);
            }catch(Exception $e) {
                return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
        }
        public function categoryUpdate(Request $request){
            $target = $request->target;
            $category = TopupCategory::where('id',$target)->first();
            $validator = Validator::make($request->all(),[
                'name'      => 'required|string|max:200',
            ]);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with('modal','edit-category');
            }
            $validated = $validator->validate();

            $slugData = Str::slug($request->name);
            $makeUnique = TopupCategory::where('id',"!=",$category->id)->where('slug',  $slugData)->first();
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
                return TopupCategory::error($error,null,400);
            }
            $validated = $validator->safe()->all();
            $category_id = $validated['data_target'];

            $category = TopupCategory::where('id',$category_id)->first();
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
                'target'        => 'required|string|exists:topup_categories,id',
            ]);
            $validated = $validator->validate();
            $category = TopupCategory::where("id",$validated['target'])->first();

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

            $allCategory = TopupCategory::search($validated['text'])->select()->limit(10)->get();
            return view('admin.components.search.topup-category-search',compact(
                'allCategory',
            ));
        }
    //==============================================Top Up Method (Manual) End==================================================

    //==============================================Top Up Method(Automatic) Start===============================================
        public function manageTopUpPayApi()
        {
            $page_title = __("Setup Mobile Top Up Api");
            $api = ReloadlyApi::reloadly()->mobileTopUp()->first();
            return view('admin.sections.mobile-topups.reloadly.api',compact(
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
            $api = ReloadlyApi::reloadly()->mobileTopUp()->first();
            $credentials = array_filter($request->except('_token','env','_method'));
            $data['credentials']=  $credentials;
            $data['env']        = $validated['env'];
            $data['status']     = true;
            $data['provider']   =  ReloadlyApi::PROVIDER_RELOADLY;
            $data['type']       =  ReloadlyApi::MOBILE_TOPUP;
            if(!$api){
                ReloadlyApi::create($data);
            }else{
                $api->fill($data)->save();
            }
            return back()->with(['success' => [__("Mobile TopUp API Has Been Updated.")]]);
        }
    //==============================================Top Up Method(Automatic) End=================================================
}
