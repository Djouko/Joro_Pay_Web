<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\CountryRestriction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\Response;

class CountryRestrictionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $page_title = __("Setup Country Restriction");
        $data = CountryRestriction::all();
        return view('admin.sections.country-restriction.index',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $page_title =__( "Country Restriction Form");
        $data = CountryRestriction::where('slug',$slug)->firstOrfail();
        return view('admin.sections.country-restriction.edit',compact(
            'page_title',
            'data',
        ));
    }
      /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $validated = Validator::make($request->all(), [
            'countries'       =>  "required|array",
        ])->validate();

        $data =  CountryRestriction::where('slug',$slug)->first();
        if(!$data){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        $request_countries = $validated['countries'];
        $all_countries = get_all_countries();

        // Extract country names from the $all_countries array
        $allCountryNames = collect($all_countries)->pluck('name')->toArray();

        // Find missing countries
        $missingCountries = [];
        foreach ($allCountryNames as $country) {
            if (!in_array($country, $request_countries)) {
                $missingCountries[] = $country;
            }
        }
        try{
            $data->update([
                'admin_id' =>Auth::user()->id,
                'data' =>$missingCountries
            ]);
        }catch(Exception $e){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Information updated successfully!")]]);
    }
    /**
     * Function for update KYC status
     * @param  \Illuminate\Http\Request  $request
     */
    public function statusUpdate(Request $request) {

        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric',
            'status'            => 'required|integer',
        ]);
        if($validator->stopOnFirstFailure()->fails()) {
            return Response::error($validator->errors());
        }
        $validated = $validator->validate();

        $status = [
            0 => true,
            1 => false,
        ];

        // find terget Item
        $data = CountryRestriction::find($validated['data_target']);
        if(!$data) {
            $error = ['error' => [__("Something went wrong! Please try again.")]];
            return Response::error($error,null,404);
        }

        try{
            $data->update([
                'status'        => $status[$validated['status']],
            ]);
        }catch(Exception $e) {
            $error = ['error' => [__("Something went wrong! Please try again.")]];
            return Response::error($error,null,500);
        }

        $success = ['success' => [__("Status updated successfully!")]];
        return Response::success($success);

    }
}
