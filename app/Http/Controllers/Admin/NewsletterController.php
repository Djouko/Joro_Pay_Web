<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Newsletter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\SendMail;


class NewsletterController extends Controller
{
    public function index()
    {
        $page_title = __("Newsletter Section");
        $data = Newsletter::orderBy('id',"DESC")->paginate(20);

        return view('admin.sections.newsletter.index',compact(
            'page_title',
            'data',
        ));
    }
    public function delete(Request $request) {
        $validated = Validator::make($request->all(),[
            'target'        => 'required|integer',
        ],[
            'target.exists'     => __("Selected payment newsletter is invalid!"),
        ])->validate();

        try{
            $data = Newsletter::find($validated['target']);
            $data->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Newsletter deleted successfully!")]]);
    }
    public function search(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);

        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        $data = Newsletter::search($validated['text'])->select()->limit(10)->get();
        return view('admin.components.search.newsletter-search',compact(
            'data',
        ));
    }
    public function sendMail(Request $request) {
          $validator = Validator::make($request->all(),[
            'subject'       => "required|string|max:250",
            'message'       => "required|string|max:2000",
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','send-email');
        }
        $data =  Newsletter::get();


        try{
            Notification::send($data,new SendMail((object) $request->all()));
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Email successfully sended")]]);

    }
}
