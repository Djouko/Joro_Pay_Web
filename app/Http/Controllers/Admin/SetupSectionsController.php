<?php

namespace App\Http\Controllers\Admin;

use App\Constants\LanguageConst;
use App\Constants\SiteSectionConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Admin\Language;
use App\Models\Admin\SiteSections;
use App\Models\Blog;
use App\Models\BlogCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SetupSectionsController extends Controller
{
    protected $languages;

    public function __construct()
    {
        $this->languages = Language::whereNot('code',LanguageConst::NOT_REMOVABLE)->get();
    }

    /**
     * Register Sections with their slug
     * @param string $slug
     * @param string $type
     * @return string
     */
    public function section($slug,$type) {
        $sections = [
            'auth-section'    => [
                'view'      => "authView",
                'update'    => "authUpdate",
            ],
            'app-section'    => [
                'view'      => "appView",
                'update'    => "appUpdate",
            ],
            'agent-app'    => [
                'view'      => "agentAppView",
                'update'    => "agentAppUpdate",
            ],
            'merchant-app'    => [
                'view'      => "merchantAppView",
                'update'    => "merchantAppUpdate",
            ],
            'banner'    => [
                'view'      => "bannerView",
                'update'    => "bannerUpdate",
            ],
            'agent-section'    => [
                'view'          => "agentSectionView",
                'update'        => "agentSectionUpdate",
                'itemStore'     => "agentSectionItemStore",
                'itemUpdate'    => "agentSectionItemUpdate",
                'itemDelete'    => "agentSectionItemDelete",
            ],
            'merchant-section'    => [
                'view'      => "merchantView",
                'update'    => "merchantUpdate",
                'itemStore'     => "merchantItemStore",
                'itemUpdate'    => "merchantItemUpdate",
                'itemDelete'    => "merchantItemDelete",
            ],
            'developer-introduction'    => [
                'view'      => "developerIntroView",
                'update'    => "developerIntroUpdate"
            ],
            'banner-floting'  => [
                'view'      => "bannerFlotingView",
                'update'    => "bannerFlotingUpdate",
                'itemStore'     => "bannerFlotingItemStore",
                'itemUpdate'    => "bannerFlotingItemUpdate",
                'itemDelete'    => "bannerFlotingItemDelete",
            ],
            'about-section'  => [
                'view'      => "aboutView",
                'update'    => "aboutUpdate",
                'itemStore'     => "aboutItemStore",
                'itemUpdate'    => "aboutItemUpdate",
                'itemDelete'    => "aboutItemDelete",
            ],
            'work-section'  => [
                'view'      => "workView",
                'update'    => "workUpdate",
                'itemStore'     => "workItemStore",
                'itemUpdate'    => "workItemUpdate",
                'itemDelete'    => "workItemDelete",
            ],
            'security-section'  => [
                'view'      => "securityView",
                'update'    => "securityUpdate",
                'itemStore'     => "securityItemStore",
                'itemUpdate'    => "securityItemUpdate",
                'itemDelete'    => "securityItemDelete",
            ],
            'overview-section'    => [
                'view'      => "overviewView",
                'update'    => "overviewUpdate",
            ],
            'why-choose-section'  => [
                'view'      => "chooseView",
                'update'    => "chooseUpdate",
                'itemStore'     => "chooseItemStore",
                'itemUpdate'    => "chooseItemUpdate",
                'itemDelete'    => "chooseItemDelete",
            ],
            'brand-section'  => [
                'view'          => "brandView",
                'update'        => "brandUpdate",
                'itemStore'     => "brandItemStore",
                'itemUpdate'    => "brandItemUpdate",
                'itemDelete'    => "brandItemDelete",
            ],
            'service-section'  => [
                'view'      => "serviceView",
                'update'    => "serviceUpdate",
                'itemStore'     => "serviceItemStore",
                'itemUpdate'    => "serviceItemUpdate",
                'itemDelete'    => "serviceItemDelete",
            ],
            'faq-section'  => [
                'view'      => "faqView",
                'update'    => "faqUpdate",
                'itemStore'     => "faqItemStore",
                'itemUpdate'    => "faqItemUpdate",
                'itemDelete'    => "faqItemDelete",
            ],
            'developer-faq'  => [
                'view'      => "developerFaqView",
                'update'    => "developerFaqUpdate",
                'itemStore'     => "developerFaqItemStore",
                'itemUpdate'    => "developerFaqItemUpdate",
                'itemDelete'    => "developerFaqItemDelete",
            ],
            'testimonials-section'  => [
                'view'      => "testimonialView",
                'update'    => "testimonialUpdate",
                'itemStore'     => "testimonialItemStore",
                'itemUpdate'    => "testimonialItemUpdate",
                'itemDelete'    => "testimonialItemDelete",
            ],
            'contact-us-section'  => [
                'view'      => "contactView",
                'update'    => "contactUpdate"
            ],
            'footer-section'  => [
                'view'      => "footerView",
                'update'    => "footerUpdate",
                'itemStore'     => "footerItemStore",
                'itemUpdate'    => "footerItemUpdate",
                'itemDelete'    => "footerItemDelete",
            ],
            'category'    => [
                'view'      => "categoryView",
            ],
            'blog-section'    => [
                'view'      => "blogView",
                'update'    => "blogUpdate",
            ],

        ];

        if(!array_key_exists($slug,$sections)) abort(404);
        if(!isset($sections[$slug][$type])) abort(404);
        $next_step = $sections[$slug][$type];
        return $next_step;
    }

    /**
     * Method for getting specific step based on incomming request
     * @param string $slug
     * @return method
     */
    public function sectionView($slug) {
        $section = $this->section($slug,'view');
        return $this->$section($slug);
    }

    /**
     * Method for distribute store method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionItemStore(Request $request, $slug) {
        $section = $this->section($slug,'itemStore');
        return $this->$section($request,$slug);
    }

    /**
     * Method for distribute update method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionItemUpdate(Request $request, $slug) {
        $section = $this->section($slug,'itemUpdate');
        return $this->$section($request,$slug);
    }

    /**
     * Method for distribute delete method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionItemDelete(Request $request,$slug) {
        $section = $this->section($slug,'itemDelete');
        return $this->$section($request,$slug);
    }

    /**
     * Method for distribute update method for any section by using slug
     * @param string $slug
     * @param \Illuminate\Http\Request  $request
     * @return method
     */
    public function sectionUpdate(Request $request,$slug) {
        $section = $this->section($slug,'update');
        return $this->$section($request,$slug);
    }


//=======================================Auth section Start =======================================
    public function authView($slug) {
        $page_title = __("Auth Section");
        $section_slug = Str::slug(SiteSectionConst::AUTH_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.auth-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function authUpdate(Request $request,$slug) {
        $basic_field_name = [
            'login_text' => "required|string",
            'register_text' => "required|string",
            'forget_text' => "required|string",
        ];
        $slug = Str::slug(SiteSectionConst::AUTH_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;
        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("Section updated successfully!")]]);
    }

//=======================================Auth section End ==========================================
//=======================================App section Start =======================================
    public function appView($slug) {
        $page_title = __("App Section");
        $section_slug = Str::slug(SiteSectionConst::APP_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.app-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function appUpdate(Request $request,$slug) {
        $basic_field_name = ['google_link' => "required|string|max:200|url",'apple_link' => "required|string|max:200|url"];

        $slug = Str::slug(SiteSectionConst::APP_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        $data['images']['google_play'] = $section->value->images->google_play ?? "";
        if($request->hasFile("google_play")) {
            $data['images']['google_play']      = $this->imageValidate($request,"google_play",$section->value->images->google_play ?? null);
        }
        $data['images']['appple_store'] = $section->value->images->appple_store ?? "";
        if($request->hasFile("appple_store")) {
            $data['images']['appple_store']      = $this->imageValidate($request,"appple_store",$section->value->images->appple_store ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
//=======================================App section End ==========================================

//=======================================Merchant App section Start =======================================
    public function agentAppView($slug) {
        $page_title = __("Agent App Section");
        $section_slug = Str::slug(SiteSectionConst::AGENT_APP_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.agent-app-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function agentAppUpdate(Request $request,$slug) {
        $basic_field_name = [
            'title' => "required|string|max:255",
            'details' => "required|string",
            'google_link' => "required|string|max:200|url",
            'apple_link' => "required|string|max:200|url"
        ];

        $slug = Str::slug(SiteSectionConst::AGENT_APP_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        $data['images']['site_image'] = $section->value->images->site_image ?? "";
        if($request->hasFile("site_image")) {
            $data['images']['site_image']      = $this->imageValidate($request,"site_image",$section->value->images->site_image ?? null);
        }
        $data['images']['google_play'] = $section->value->images->google_play ?? "";
        if($request->hasFile("google_play")) {
            $data['images']['google_play']      = $this->imageValidate($request,"google_play",$section->value->images->google_play ?? null);
        }
        $data['images']['appple_store'] = $section->value->images->appple_store ?? "";
        if($request->hasFile("appple_store")) {
            $data['images']['appple_store']      = $this->imageValidate($request,"appple_store",$section->value->images->appple_store ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
//=======================================Merchant App section End ==========================================
//=======================================Merchant App section Start =======================================
    public function merchantAppView($slug) {
        $page_title = __("Merchant App Section");
        $section_slug = Str::slug(SiteSectionConst::MERCHANT_APP_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.merchant-app-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function merchantAppUpdate(Request $request,$slug) {
        $basic_field_name = [
                            'title' => "required|string|max:255",
                            'details' => "required|string",
                            'google_link' => "required|string|max:200|url",
                            'apple_link' => "required|string|max:200|url"
                            ];

        $slug = Str::slug(SiteSectionConst::MERCHANT_APP_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        $data['images']['site_image'] = $section->value->images->site_image ?? "";
        if($request->hasFile("site_image")) {
            $data['images']['site_image']      = $this->imageValidate($request,"site_image",$section->value->images->site_image ?? null);
        }
        $data['images']['google_play'] = $section->value->images->google_play ?? "";
        if($request->hasFile("google_play")) {
            $data['images']['google_play']      = $this->imageValidate($request,"google_play",$section->value->images->google_play ?? null);
        }
        $data['images']['appple_store'] = $section->value->images->appple_store ?? "";
        if($request->hasFile("appple_store")) {
            $data['images']['appple_store']      = $this->imageValidate($request,"appple_store",$section->value->images->appple_store ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
//=======================================Merchant App section End ==========================================

//=======================================Banner section Start =====================================
    public function bannerView($slug) {
        $page_title = __("Banner Section");
        $section_slug = Str::slug(SiteSectionConst::BANNER_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.banner-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function bannerUpdate(Request $request,$slug) {
        $basic_field_name = ['title' => "required|string|max:100",'heading' => "required|string|max:100",'sub_heading' => "required|string|max:400"];

        $slug = Str::slug(SiteSectionConst::BANNER_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        $data['images']['banner_image'] = $section->value->images->banner_image ?? "";
        if($request->hasFile("banner_image")) {
            $data['images']['banner_image']      = $this->imageValidate($request,"banner_image",$section->value->images->banner_image ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
//=======================================Banner section End ==========================================
//=======================================agent section Start =====================================
    public function agentSectionView($slug) {
        $page_title = __("Agent Section");
        $section_slug = Str::slug(SiteSectionConst::AGENT_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.agent-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function agentSectionUpdate(Request $request,$slug) {
        $basic_field_name = [
            'heading'           => "required|string|max:100",
            'sub_heading'       => "required|string",
            'details'           => "required|string",
            'bottom_heading'    => "required|string|max:100",
            'bottom_sub_heading'=> "required|string",
            'bottom_details'    => "required|string",
        ];

        $slug = Str::slug(SiteSectionConst::AGENT_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        if($section != null) {
            $data = json_decode(json_encode($section->value),true);
        }else {
            $data = [];
        }
        $data['images']['banner_image'] = $section->value->images->banner_image ?? "";
        if($request->hasFile("banner_image")) {
            $data['images']['banner_image']      = $this->imageValidate($request,"banner_image",$section->value->images->banner_image ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
    public function agentSectionItemStore(Request $request,$slug) {
        $basic_field_name = [
            'title'     => "required|string|max:100",
            'sub_title'     => "required|string",
            'icon'     => "required|string|max:100",
        ];

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"merchant-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug = Str::slug(SiteSectionConst::AGENT_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id = uniqid();

        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['id'] = $unique_id;

        $update_data['key'] = $slug;
        $update_data['value']   = $section_data;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section item added successfully")]]);
    }
    public function agentSectionItemUpdate(Request $request,$slug) {
        $request->validate([
            'target'    => "required|string",
        ]);

        $basic_field_name = [
            'title_edit'     => "required|string|max:100",
            'sub_title_edit'     => "required|string",
            'icon_edit'     => "required|string|max:100"
        ];

        $slug = Str::slug(SiteSectionConst::AGENT_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


        $language_wise_data = $this->contentValidate($request,$basic_field_name,"merchant-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

        $language_wise_data = array_map(function($language) {
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $section_values['items'][$request->target]['language'] = $language_wise_data;
        try{
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Information updated successfully!")]]);
    }
    public function agentSectionItemDelete(Request $request,$slug) {
        $request->validate([
            'target'    => 'required|string',
        ]);
        $slug = Str::slug(SiteSectionConst::AGENT_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value'     => $section_values,
            ]);
        }catch(Exception $e) {
            return  $e->getMessage();
        }

        return back()->with(['success' => [__("Section item delete successfully!")]]);
    }
//=======================================agent section End ==========================================

//=======================================merchant section Start =====================================
    public function merchantView($slug) {
        $page_title = __("Merchant Section");
        $section_slug = Str::slug(SiteSectionConst::MERCHANT_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.merchant-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function merchantUpdate(Request $request,$slug) {
        $basic_field_name = [
            'heading' => "required|string|max:100",
            'sub_heading' => "required|string",
            'details' => "required|string",
        ];

        $slug = Str::slug(SiteSectionConst::MERCHANT_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        if($section != null) {
            $data = json_decode(json_encode($section->value),true);
        }else {
            $data = [];
        }
        $data['images']['banner_image'] = $section->value->images->banner_image ?? "";
        if($request->hasFile("banner_image")) {
            $data['images']['banner_image']      = $this->imageValidate($request,"banner_image",$section->value->images->banner_image ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
    public function merchantItemStore(Request $request,$slug) {
        $basic_field_name = [
            'title'     => "required|string|max:100",
            'sub_title'     => "required|string",
            'icon'     => "required|string|max:100",
        ];

        $language_wise_data = $this->contentValidate($request,$basic_field_name,"merchant-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug = Str::slug(SiteSectionConst::MERCHANT_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id = uniqid();

        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['id'] = $unique_id;

        $update_data['key'] = $slug;
        $update_data['value']   = $section_data;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section item added successfully")]]);
    }
    public function merchantItemUpdate(Request $request,$slug) {
        $request->validate([
            'target'    => "required|string",
        ]);

        $basic_field_name = [
            'title_edit'     => "required|string|max:100",
            'sub_title_edit'     => "required|string",
            'icon_edit'     => "required|string|max:100"
        ];

        $slug = Str::slug(SiteSectionConst::MERCHANT_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


        $language_wise_data = $this->contentValidate($request,$basic_field_name,"merchant-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

        $language_wise_data = array_map(function($language) {
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $section_values['items'][$request->target]['language'] = $language_wise_data;
        try{
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Information updated successfully!")]]);
    }
    public function merchantItemDelete(Request $request,$slug) {
        $request->validate([
            'target'    => 'required|string',
        ]);
        $slug = Str::slug(SiteSectionConst::MERCHANT_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value'     => $section_values,
            ]);
        }catch(Exception $e) {
            return  $e->getMessage();
        }

        return back()->with(['success' => [__("Section item delete successfully!")]]);
    }
//=======================================merchant section End ==========================================
//=======================================developer introduction section Start =====================================
    public function developerIntroView($slug) {
        $page_title = __("Developer Introduction");
        $section_slug = Str::slug(SiteSectionConst::DEVELOPER_INTRO);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.developer-intro-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function developerIntroUpdate(Request $request,$slug) {
        $basic_field_name = [
            'heading' => "required|string|max:100",
            'details' => "required|string",
            'intro_details' => "required|string",
        ];

        $slug = Str::slug(SiteSectionConst::DEVELOPER_INTRO);
        $section = SiteSections::where("key",$slug)->first();
        if($section != null) {
            $data = json_decode(json_encode($section->value),true);
        }else {
            $data = [];
        }
        $data['images']['intro_image'] = $section->value->images->intro_image ?? "";
        if($request->hasFile("intro_image")) {
            $data['images']['intro_image']      = $this->imageValidate($request,"intro_image",$section->value->images->intro_image ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
//=======================================developer introduction section End ==========================================
//=======================================overview section Start =====================================
    public function overviewView($slug) {
        $page_title = __("Overview Section");
        $section_slug = Str::slug(SiteSectionConst::OVERVIEW_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.overview-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function overviewUpdate(Request $request,$slug) {
        $basic_field_name = [
            'title' => "required|string|max:100",
            'heading' => "required|string|max:100",
            'sub_heading' => "required|string|max:255",
            'botton_text' => "required|string|max:255",
            'button_name' => "required|string",
            'button_link' => "required|string",
        ];

        $slug = Str::slug(SiteSectionConst::OVERVIEW_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        $data['images']['map_image'] = $section->value->images->map_image ?? "";
        if($request->hasFile("map_image")) {
            $data['images']['map_image']      = $this->imageValidate($request,"map_image",$section->value->images->map_image ?? null);
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);
        $update_data['value']  = $data;
        $update_data['key']    = $slug;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }

//=======================================overview section End ==========================================
//=======================================Banner Floting section Start ================================
public function bannerFlotingView($slug) {
    $page_title = __("Banner Floating Section");
    $section_slug = Str::slug(SiteSectionConst::BANNER_FLOTING);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.banner-floting',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}
public function bannerFlotingUpdate(Request $request,$slug) {
    $basic_field_name = [
        'title' => "required|string|max:100",
        'sub_title' => "required|string",
        'button_name' => "required|string",
        'button_link' => "required|string",
    ];

    $slug = Str::slug(SiteSectionConst::BANNER_FLOTING);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }
    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}
public function bannerFlotingItemStore(Request $request,$slug) {
    $basic_field_name = [
        'name'     => "required|string|max:100"
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"floting-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::BANNER_FLOTING);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}
public function bannerFlotingItemUpdate(Request $request,$slug) {
    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'name_edit'     => "required|string|max:100"
    ];

    $slug = Str::slug(SiteSectionConst::BANNER_FLOTING);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


    $language_wise_data = $this->contentValidate($request,$basic_field_name,"floting-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;
    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}
 public function bannerFlotingItemDelete(Request $request,$slug) {
        $request->validate([
            'target'    => 'required|string',
        ]);
        $slug = Str::slug(SiteSectionConst::BANNER_FLOTING);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value'     => $section_values,
            ]);
        }catch(Exception $e) {
            return  $e->getMessage();
        }

        return back()->with(['success' => [__("Section item delete successfully!")]]);
}
//=======================================Banner Floting section End =======================================
//=======================================About section Start ==============================================
public function aboutView($slug) {
    $page_title = __("About Section");
    $section_slug = Str::slug(SiteSectionConst::ABOUT_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.about-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}

public function aboutUpdate(Request $request,$slug) {
    $basic_field_name = [
        'heading' => "required|string|max:100",
        'sub_heading' => "required|string|max:200",
        'details' => "required|string",

    ];
    $slug = Str::slug(SiteSectionConst::ABOUT_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }
    $data['images']['image'] = $section->value->images->image ?? "";
    if($request->hasFile("image")) {
        $data['images']['image']      = $this->imageValidate($request,"image",$section->value->images->image ?? null);
    }
    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}

public function aboutItemStore(Request $request,$slug) {
    $basic_field_name = [
        'title'     => "required|string|max:100",
        'sub_title'     => "required|string",
        'icon'     => "required|string|max:100",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"about-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::ABOUT_SECTION);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}


public function aboutItemUpdate(Request $request,$slug) {
    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'title_edit'     => "required|string|max:100",
        'sub_title_edit'     => "required|string",
        'icon_edit'     => "required|string|max:100"
    ];

    $slug = Str::slug(SiteSectionConst::ABOUT_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


    $language_wise_data = $this->contentValidate($request,$basic_field_name,"about-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;
    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function aboutItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::ABOUT_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
    try{
        unset($section_values['items'][$request->target]);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return  $e->getMessage();
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);
}
//=======================About  Section End===================================
//=======================Work section Start ==================================
public function workView($slug) {
    $page_title = __("Works Section");
    $section_slug = Str::slug(SiteSectionConst::WORK_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.work-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}

public function workUpdate(Request $request,$slug) {
    $basic_field_name = [
        'title' => "required|string|max:50",
        'heading' => "required|string|max:100",
        'sub_heading' => "required|string",

    ];

    $slug = Str::slug(SiteSectionConst::WORK_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }

    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}

public function workItemStore(Request $request,$slug) {
    $basic_field_name = [
        'name'     => "required|string|max:100",
        'icon'     => "required|string|max:100",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"work-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::WORK_SECTION);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Item added successfully!")]]);
}


public function workItemUpdate(Request $request,$slug) {
    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'name_edit'     => "required|string|max:100",
        'icon_edit'     => "required|string|max:100"
    ];

    $slug = Str::slug(SiteSectionConst::WORK_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


    $language_wise_data = $this->contentValidate($request,$basic_field_name,"work-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;
    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function workItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::WORK_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
    try{
        unset($section_values['items'][$request->target]);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return  $e->getMessage();
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);
}
//=======================Work  Section End===================================

//======================Security section Start ===============================
public function securityView($slug) {
    $page_title = __("Security Section");
    $section_slug = Str::slug(SiteSectionConst::SECURITY_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.security-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}

public function securityUpdate(Request $request,$slug) {
    $basic_field_name = [
        'heading' => "required|string|max:100",
        'sub_heading' => "required|string|max:200",
        'details' => "required|string",

    ];

    $slug = Str::slug(SiteSectionConst::SECURITY_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }

    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}

public function securityItemStore(Request $request,$slug) {
    $basic_field_name = [
        'title'     => "required|string|max:100",
        'sub_title'     => "required|string",
        'icon'     => "required|string|max:100",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"security-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::SECURITY_SECTION);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}


public function securityItemUpdate(Request $request,$slug) {
    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'title_edit'     => "required|string|max:100",
        'sub_title_edit'     => "required|string",
        'icon_edit'     => "required|string|max:100"
    ];

    $slug = Str::slug(SiteSectionConst::SECURITY_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


    $language_wise_data = $this->contentValidate($request,$basic_field_name,"service-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;
    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function securityItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::SECURITY_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
    try{
        unset($section_values['items'][$request->target]);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return  $e->getMessage();
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);
}
//=======================Security  Section End===================================
//======================Service section Start ===============================
public function chooseView($slug) {
    $page_title = __("Why Choose Us Section");
    $section_slug = Str::slug(SiteSectionConst::CHOOSE_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.choose-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}

public function chooseUpdate(Request $request,$slug) {
    $basic_field_name = [
        'heading' => "required|string|max:100",
        'sub_heading' => "required|string|max:200",
        'details' => "required|string",

    ];

    $slug = Str::slug(SiteSectionConst::CHOOSE_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }

    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}

public function chooseItemStore(Request $request,$slug) {
    $basic_field_name = [
        'title'     => "required|string|max:100",
        'sub_title'     => "required|string",
        'icon'     => "required|string|max:100",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"choose-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::CHOOSE_SECTION);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}


public function chooseItemUpdate(Request $request,$slug) {
    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'title_edit'     => "required|string|max:100",
        'sub_title_edit'     => "required|string",
        'icon_edit'     => "required|string|max:100"
    ];

    $slug = Str::slug(SiteSectionConst::CHOOSE_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


    $language_wise_data = $this->contentValidate($request,$basic_field_name,"choose-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;
    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function chooseItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::CHOOSE_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
    try{
        unset($section_values['items'][$request->target]);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return  $e->getMessage();
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);
}
//=======================Choose us  Section End===================================
//======================Service section Start ===============================
public function serviceView($slug) {
    $page_title = __("Service Section");
    $section_slug = Str::slug(SiteSectionConst::SERVICE_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.service-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}

public function serviceUpdate(Request $request,$slug) {
    $basic_field_name = [
        'heading' => "required|string|max:100",
        'sub_heading' => "required|string|max:200",
        'details' => "required|string",

    ];

    $slug = Str::slug(SiteSectionConst::SERVICE_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }

    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}

public function serviceItemStore(Request $request,$slug) {
    $basic_field_name = [
        'title'     => "required|string|max:100",
        'sub_title'     => "required|string",
        'icon'     => "required|string|max:100",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"service-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::SERVICE_SECTION);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}


public function serviceItemUpdate(Request $request,$slug) {
    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'title_edit'     => "required|string|max:100",
        'sub_title_edit'     => "required|string",
        'icon_edit'     => "required|string|max:100"
    ];

    $slug = Str::slug(SiteSectionConst::SERVICE_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


    $language_wise_data = $this->contentValidate($request,$basic_field_name,"service-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;
    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function serviceItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::SERVICE_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
    try{
        unset($section_values['items'][$request->target]);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return  $e->getMessage();
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);

}
//=======================Service  Section End===================================
//======================Faq section Start =================================
    public function faqView($slug) {
        $page_title = __("FAQ Section");
        $section_slug = Str::slug(SiteSectionConst::FAQ_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.faq-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function faqUpdate(Request $request,$slug) {
        $basic_field_name = [
            'heading' => "required|string|max:100",
            'sub_heading' => "required|string|max:200",
            'details' => "required|string",

        ];

        $slug = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        if($section != null) {
            $data = json_decode(json_encode($section->value),true);
        }else {
            $data = [];
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);

        $update_data['key']    = $slug;
        $update_data['value']  = $data;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
    public function faqItemStore(Request $request,$slug) {
        $basic_field_name = [
            'question' => "required|string|max:200",
            'answer' => "required|string",
        ];


        $language_wise_data = $this->contentValidate($request,$basic_field_name,"faq-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id = uniqid();

        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['id'] = $unique_id;

        $update_data['key'] = $slug;
        $update_data['value']   = $section_data;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Item added successfully!")]]);
    }
    public function faqItemUpdate(Request $request,$slug) {
        $request->validate([
            'target'    => "required|string",
        ]);

        $basic_field_name = [
            'question_edit'     => "required|string|max:100",
            'answer_edit'     => "required|string",
        ];

        $slug = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


        $language_wise_data = $this->contentValidate($request,$basic_field_name,"faq-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

        $language_wise_data = array_map(function($language) {
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $section_values['items'][$request->target]['language'] = $language_wise_data;
        try{
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Information updated successfully!")]]);
    }
    public function faqItemDelete(Request $request,$slug) {
        $request->validate([
            'target'    => 'required|string',
        ]);
        $slug = Str::slug(SiteSectionConst::FAQ_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value'     => $section_values,
            ]);
        }catch(Exception $e) {
            return  $e->getMessage();
        }

        return back()->with(['success' => [__("Item delete successfully!")]]);
    }
//=======================Faq  Section End===================================
//======================Developer Faq section Start =================================
    public function developerFaqView($slug) {
        $page_title = __("Developer FAQ Section");
        $section_slug = Str::slug(SiteSectionConst::DEVELOPER_FAQ_SECTION);
        $data = SiteSections::getData($section_slug)->first();
        $languages = $this->languages;

        return view('admin.sections.setup-sections.developer-faq-section',compact(
            'page_title',
            'data',
            'languages',
            'slug',
        ));
    }
    public function developerFaqUpdate(Request $request,$slug) {
        $basic_field_name = [
            'heading' => "required|string|max:100",
            'bottom_text' => "required|string",
        ];
        $slug = Str::slug(SiteSectionConst::DEVELOPER_FAQ_SECTION);
        $section = SiteSections::where("key",$slug)->first();
        if($section != null) {
            $data = json_decode(json_encode($section->value),true);
        }else {
            $data = [];
        }

        $data['language']  = $this->contentValidate($request,$basic_field_name);

        $update_data['key']    = $slug;
        $update_data['value']  = $data;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Section updated successfully!")]]);
    }
    public function developerFaqItemStore(Request $request,$slug) {
        $basic_field_name = [
            'question' => "required|string|max:200",
            'answer' => "required|string",
        ];
        $language_wise_data = $this->contentValidate($request,$basic_field_name,"faq-add");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
        $slug = Str::slug(SiteSectionConst::DEVELOPER_FAQ_SECTION);
        $section = SiteSections::where("key",$slug)->first();

        if($section != null) {
            $section_data = json_decode(json_encode($section->value),true);
        }else {
            $section_data = [];
        }
        $unique_id = uniqid();

        $section_data['items'][$unique_id]['language'] = $language_wise_data;
        $section_data['items'][$unique_id]['id'] = $unique_id;

        $update_data['key'] = $slug;
        $update_data['value']   = $section_data;

        try{
            SiteSections::updateOrCreate(['key' => $slug],$update_data);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Item added successfully!")]]);
    }
    public function developerFaqItemUpdate(Request $request,$slug) {
        $request->validate([
            'target'    => "required|string",
        ]);

        $basic_field_name = [
            'question_edit'     => "required|string|max:100",
            'answer_edit'     => "required|string",
        ];

        $slug = Str::slug(SiteSectionConst::DEVELOPER_FAQ_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);


        $language_wise_data = $this->contentValidate($request,$basic_field_name,"faq-edit");
        if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

        $language_wise_data = array_map(function($language) {
            return replace_array_key($language,"_edit");
        },$language_wise_data);

        $section_values['items'][$request->target]['language'] = $language_wise_data;
        try{
            $section->update([
                'value' => $section_values,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Information updated successfully!")]]);
    }
    public function developerFaqItemDelete(Request $request,$slug) {
        $request->validate([
            'target'    => 'required|string',
        ]);
        $slug = Str::slug(SiteSectionConst::DEVELOPER_FAQ_SECTION);
        $section = SiteSections::getData($slug)->first();
        if(!$section) return back()->with(['error' => [__("Section not found!")]]);
        $section_values = json_decode(json_encode($section->value),true);
        if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
        if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
        try{
            unset($section_values['items'][$request->target]);
            $section->update([
                'value'     => $section_values,
            ]);
        }catch(Exception $e) {
            return  $e->getMessage();
        }

        return back()->with(['success' => [__("Item delete successfully!")]]);
    }
//=======================Developer Faq  Section End===================================
//=======================testimonial Section End===============================

 public function brandView($slug) {
    $page_title =__("Brand Section");
    $section_slug = Str::slug(SiteSectionConst::BRAND_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.brand-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}
public function brandUpdate(Request $request,$slug) {
    $basic_field_name = [
        'title' => "required|string|max:255"
    ];

    $slug = Str::slug(SiteSectionConst::BRAND_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }
    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}
public function brandItemStore(Request $request,$slug) {
    $basic_field_name = [
        'name'     => "null|string|max:100",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"brand-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::BRAND_SECTION);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;
    $section_data['items'][$unique_id]['image'] = "";

    if($request->hasFile("image")) {
        $section_data['items'][$unique_id]['image'] = $this->imageValidate($request,"image",$section->value->items->image ?? null);
    }

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}
public function brandItemUpdate(Request $request,$slug) {

    $request->validate([
        'target'    => "required|string",
    ]);

    $slug = Str::slug(SiteSectionConst::BRAND_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);
    $request->merge(['old_image' => $section_values['items'][$request->target]['image'] ?? null]);

    if($request->hasFile("image")) {
        $section_values['items'][$request->target]['image']    = $this->imageValidate($request,"image",$section_values['items'][$request->target]['image'] ?? null);
    }

    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function brandItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::BRAND_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);

    try{
        $image_link = get_files_path('site-section') . '/' . $section_values['items'][$request->target]['image'];
        unset($section_values['items'][$request->target]);
        delete_file($image_link);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);
}
//=======================testimonial Section End===========================
 //=======================testimonial Section End===============================

 public function testimonialView($slug) {
    $page_title = __("Testimonial Section");
    $section_slug = Str::slug(SiteSectionConst::TESTIMONIAL_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.testimonial-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}
public function testimonialUpdate(Request $request,$slug) {
    $basic_field_name = [
        'title' => "required|string|max:50",
        'heading' => "required|string|max:100",
        'sub_heading' => "required|string",
    ];

    $slug = Str::slug(SiteSectionConst::TESTIMONIAL_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }
    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}
public function testimonialItemStore(Request $request,$slug) {
    $basic_field_name = [
        'name'     => "required|string|max:100",
        'designation'     => "required|string|max:100",
        'header'     => "required|string|max:100",
        'rating'     => "required|string|max:100",
        'details'   => "required|string",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"testimonial-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::TESTIMONIAL_SECTION);
    $section = SiteSections::where("key",$slug)->first();

    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;
    $section_data['items'][$unique_id]['image'] = "";

    if($request->hasFile("image")) {
        $section_data['items'][$unique_id]['image'] = $this->imageValidate($request,"image",$section->value->items->image ?? null);
    }

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}
public function testimonialItemUpdate(Request $request,$slug) {

    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'name_edit'     => "required|string|max:100",
        'designation_edit'     => "required|string|max:100",
        'header_edit'     => "required|string|max:100",
        'rating_edit'     => "required|string|max:100",
        'details_edit'   => "required|string|max:255",
    ];

    $slug = Str::slug(SiteSectionConst::TESTIMONIAL_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);

    $request->merge(['old_image' => $section_values['items'][$request->target]['image'] ?? null]);

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"testimonial-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;

    if($request->hasFile("image")) {
        $section_values['items'][$request->target]['image']    = $this->imageValidate($request,"image",$section_values['items'][$request->target]['image'] ?? null);
    }

    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function testimonialItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::TESTIMONIAL_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);

    try{
        $image_link = get_files_path('site-section') . '/' . $section_values['items'][$request->target]['image'];
        unset($section_values['items'][$request->target]);
        delete_file($image_link);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);
}
//=======================testimonial Section End===========================
//=======================contact Section End===============================
public function contactView($slug) {
    $page_title = "Contact Us Section";
    $section_slug = Str::slug(SiteSectionConst::CONTACT_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;

    return view('admin.sections.setup-sections.contact-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}
public function contactUpdate(Request $request,$slug) {
    $basic_field_name = [
        'heading' => "required|string|max:100",
        'sub_heading' => "required|string|max:255",
        'location' => "required|string",
        'mobile' => "required|string",
        'office_hours' => "required|string",
        'email' => "required|string",
    ];

    $slug = Str::slug(SiteSectionConst::CONTACT_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    $data['language']  = $this->contentValidate($request,$basic_field_name);
    $update_data['value']  = $data;
    $update_data['key']    = $slug;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}
//=======================contact Section End===============================
//=======================footer Section End===============================

public function  footerView($slug) {
    $page_title = __("Footer Section");
    $section_slug = Str::slug(SiteSectionConst::FOOTER_SECTION);
    $data = SiteSections::getData($section_slug)->first();

    $languages = $this->languages;

    return view('admin.sections.setup-sections.footer-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
    ));
}
public function  footerUpdate(Request $request,$slug) {
    $basic_field_name = [
        'footer_text' => "required|string|max:100",
        'app_text' => "required|string|max:100",
        'details' => "required|string",
        'newsltter_details' => "required|string"
    ];

    $slug = Str::slug(SiteSectionConst::FOOTER_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    if($section != null) {
        $data = json_decode(json_encode($section->value),true);
    }else {
        $data = [];
    }
    $data['images']['bg_image'] = $section->value->images->bg_image ?? "";
    if($request->hasFile("bg_image")) {
        $data['images']['bg_image']      = $this->imageValidate($request,"bg_image",$section->value->images->bg_image ?? null);
    }
    $data['language']  = $this->contentValidate($request,$basic_field_name);

    $update_data['key']    = $slug;
    $update_data['value']  = $data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}
public function  footerItemStore(Request $request,$slug) {
    $basic_field_name = [
        'name'     => "required|string|max:100",
        'social_icon'   => "required|string|max:255",
        'link'   => "required|string|url|max:255",
    ];

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"icon-add");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;
    $slug = Str::slug(SiteSectionConst::FOOTER_SECTION);
    $section = SiteSections::where("key",$slug)->first();


    if($section != null) {
        $section_data = json_decode(json_encode($section->value),true);
    }else {
        $section_data = [];
    }
    $unique_id = uniqid();

    $section_data['items'][$unique_id]['language'] = $language_wise_data;
    $section_data['items'][$unique_id]['id'] = $unique_id;

    $update_data['key'] = $slug;
    $update_data['value']   = $section_data;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item added successfully")]]);
}
public function  footerItemUpdate(Request $request,$slug) {

    $request->validate([
        'target'    => "required|string",
    ]);

    $basic_field_name = [
        'name_edit'     => "required|string|max:100",
        'social_icon_edit'   => "required|string|max:255",
        'link_edit'   => "required|string|url|max:255",
    ];

    $slug = Str::slug(SiteSectionConst::FOOTER_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"icon-edit");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $section_values['items'][$request->target]['language'] = $language_wise_data;
    try{
        $section->update([
            'value' => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Information updated successfully!")]]);
}

public function footerItemDelete(Request $request,$slug) {
    $request->validate([
        'target'    => 'required|string',
    ]);
    $slug = Str::slug(SiteSectionConst::FOOTER_SECTION);
    $section = SiteSections::getData($slug)->first();
    if(!$section) return back()->with(['error' => [__("Section not found!")]]);
    $section_values = json_decode(json_encode($section->value),true);
    if(!isset($section_values['items'])) return back()->with(['error' => [__("Section item not found!")]]);
    if(!array_key_exists($request->target,$section_values['items'])) return back()->with(['error' => ['__("Section item is invalid!")']]);

    try{
        unset($section_values['items'][$request->target]);
        $section->update([
            'value'     => $section_values,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section item delete successfully!")]]);
}

//=======================footer Section End==============================
//=======================Category  Section Start=======================
public function categoryView(){
    $page_title = __("Setup Blog Category");
    $allCategory = BlogCategory::orderByDesc('id')->paginate(10);
    $languages = Language::get();
    return view('admin.sections.blog-category.index',compact(
        'page_title',
        'allCategory',
        'languages'
    ));
}
public function storeCategory(Request $request){
    $basic_field_name = [
        'name'          => "required|string|max:150",
    ];

    $data['language']  = $this->contentValidate($request,$basic_field_name);
    $slugData = Str::slug($data['language']['en']['name']);
    try{
        $admin = Auth::user();
        BlogCategory::create([
            'admin_id'      => $admin->id,
            'name'          => $data['language']['en']['name'],
            'data'          => $data,
            'slug'          => $slugData,
            'created_at'    => now(),
            'status'        => true,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Category Saved Successfully!")]]);
}
public function categoryUpdate(Request $request){
    $validated = $request->validate([
        'target'    => "required|numeric|exists:blog_categories,id",
    ]);

    $basic_field_name = [
        'name_edit'          => "required|string|max:250",
    ];

    $category = BlogCategory::find($validated['target']);

    $language_wise_data = $this->contentValidate($request,$basic_field_name,"category-update");
    if($language_wise_data instanceof RedirectResponse) return $language_wise_data;

    $language_wise_data = array_map(function($language) {
        return replace_array_key($language,"_edit");
    },$language_wise_data);

    $data['language']  = $language_wise_data;
    $slugData = Str::slug($data['language']['en']['name']);

    try{
        $category->update([
            'name'          => $data['language']['en']['name'],
            'data'      => $data,
            'slug'          => $slugData,
        ]);
    }catch(Exception $e) {
        return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
    }

    return back()->with(['success' => [__("Category Updated Successfully!")]]);
}
public function categoryStatusUpdate(Request $request) {
    $validator = Validator::make($request->all(),[
        'status'                    => 'required|boolean',
        'data_target'               => 'required|string',
    ]);
    if ($validator->stopOnFirstFailure()->fails()) {
        $error = ['error' => $validator->errors()];
        return Response::error($error,null,400);
    }
    $validated = $validator->safe()->all();
    $category_id = $validated['data_target'];

    $category = BlogCategory::where('id',$category_id)->first();
    if(!$category) {
        $error = ['error' => [__("Category record not found in our system.")]];
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

    $success = ['success' => [__("Category status updated successfully!")]];
    return Response::success($success,null,200);
}
public function categoryDelete(Request $request) {
    $validator = Validator::make($request->all(),[
        'target'        => 'required|string|exists:blog_categories,id',
    ]);
    $validated = $validator->validate();
    $category = BlogCategory::where("id",$validated['target'])->first();

    try{
        $category->delete();
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Category deleted successfully!")]]);
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

    $allCategory = BlogCategory::search($validated['text'])->select()->limit(10)->get();
    return view('admin.components.search.category-search',compact(
        'allCategory',
    ));
}
//=======================Category  Section End=======================
//=======================================Banner section Start =====================================
public function blogView($slug) {
    $page_title = __("Blog Section");
    $section_slug = Str::slug(SiteSectionConst::BLOG_SECTION);
    $data = SiteSections::getData($section_slug)->first();
    $languages = $this->languages;
    $categories = BlogCategory::where('status',1)->latest()->get();
    $blogs = Blog::latest()->paginate(10);

    return view('admin.sections.setup-sections.blog-section',compact(
        'page_title',
        'data',
        'languages',
        'slug',
        'categories',
        'blogs'
    ));
}
public function blogUpdate(Request $request,$slug) {
    $basic_field_name = ['title' => "required|string|max:100",'heading' => "required|string|max:100",'sub_heading' => "required|string|max:255"];

    $slug = Str::slug(SiteSectionConst::BLOG_SECTION);
    $section = SiteSections::where("key",$slug)->first();
    $data['language']  = $this->contentValidate($request,$basic_field_name);
    $update_data['value']  = $data;
    $update_data['key']    = $slug;

    try{
        SiteSections::updateOrCreate(['key' => $slug],$update_data);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Section updated successfully!")]]);
}
public function blogItemStore(Request $request){
    $validator = Validator::make($request->all(),[
        'category_id'      => 'required|integer',
        'image'         => 'required|image|mimes:png,jpg,jpeg,svg,webp',
    ]);
    $name_filed = [
        'name'     => "required|string",
    ];
    $details_filed = [
        'details'     => "required|string",
    ];
    $tags_filed = [
        'tags'     => "required|array",
    ];


    if($validator->fails()) {
        return back()->withErrors($validator)->withInput()->with('modal','blog-add');
    }
    $validated = $validator->validate();

    // Multiple language data set

    $language_wise_name = $this->contentValidate($request,$name_filed);
    $language_wise_details = $this->contentValidate($request,$details_filed);
    $language_wise_tags = $this->contentValidate($request,$tags_filed);


    $name_data['language'] = $language_wise_name;
    $details_data['language'] = $language_wise_details;
    $tags_data['language'] = $language_wise_tags;

    $validated['category_id']       = $request->category_id;
    $validated['admin_id']          = Auth::user()->id;
    $validated['name']              = $name_data;
    $validated['details']           = $details_data;
    $validated['slug']              = Str::slug($name_data['language']['en']['name']);
    $validated['lan_tags']          = $tags_data;
    $validated['created_at']        = now();


    // Check Image File is Available or not
    if($request->hasFile('image')) {
        $image = get_files_from_fileholder($request,'image');
        $upload = upload_files_from_path_dynamic($image,'blog');
        $validated['image'] = $upload;
    }
    try{
        Blog::create($validated);
    }catch(Exception $e) {

        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Blog item added successfully!")]]);

}
public function blogEdit($id)
    {
        $page_title = __("Blog Edit");
        $languages = $this->languages;
        $data = Blog::findOrFail($id);
        $categories = BlogCategory::where('status',1)->latest()->get();

        return view('admin.sections.setup-sections.blog-section-edit', compact(
            'page_title',
            'languages',
            'data',
            'categories',
        ));
    }
public function blogItemUpdate(Request $request) {


    $validator = Validator::make($request->all(),[
        'category_id'      => 'required|integer',
        'image'         => 'nullable|image|mimes:png,jpg,jpeg,svg,webp',
        'target'        => 'required|integer',
    ]);

    $name_filed = [
        'name'     => "required|string",
    ];
    $details_filed = [
        'details'     => "required|string",
    ];
    $tags_filed = [
        'tags'     => "required|array",
    ];

    if($validator->fails()) {
        return back()->withErrors($validator)->withInput()->with('modal','blog-edit');
    }
    $validated = $validator->validate();
    $blog = Blog::findOrFail($validated['target']);

    // Multiple language data set
    $language_wise_name = $this->contentValidate($request,$name_filed);
    $language_wise_details = $this->contentValidate($request,$details_filed);
    $language_wise_tags = $this->contentValidate($request,$tags_filed);


    $name_data['language'] = $language_wise_name;
    $details_data['language'] = $language_wise_details;
    $tags_data['language'] = $language_wise_tags;

    $validated['category_id']        = $request->category_id;
    $validated['admin_id']        = Auth::user()->id;
    $validated['name']            = $name_data;
    $validated['details']           = $details_data;
    $validated['slug']            = Str::slug($name_data['language']['en']['name']);
    $validated['lan_tags']          = $tags_data;
    $validated['created_at']      = now();

       // Check Image File is Available or not
       if($request->hasFile('image')) {
            $image = get_files_from_fileholder($request,'image');
            $upload = upload_files_from_path_dynamic($image,'blog',$blog->image);
            $validated['image'] = $upload;
        }
    try{
        $blog->update($validated);
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("Blog item updated successfully!")]]);
}

public function blogItemDelete(Request $request) {
    $request->validate([
        'target'    => 'required|string',
    ]);

    $blog = Blog::findOrFail($request->target);

    try{
        $image_link = get_files_path('blog') . '/' . $blog->image;
        delete_file($image_link);
        $blog->delete();
    }catch(Exception $e) {
        return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
    }

    return back()->with(['success' => [__("BLog delete successfully!")]]);
}
public function blogStatusUpdate(Request $request) {
    $validator = Validator::make($request->all(),[
        'status'                    => 'required|boolean',
        'data_target'               => 'required|string',
    ]);
    if ($validator->stopOnFirstFailure()->fails()) {
        $error = ['error' => $validator->errors()];
        return Response::error($error,null,400);
    }
    $validated = $validator->safe()->all();
    $blog_id = $validated['data_target'];

    $blog = Blog::where('id',$blog_id)->first();
    if(!$blog) {
        $error = ['error' => [__("Blog record not found in our system.")]];
        return Response::error($error,null,404);
    }

    try{
        $blog->update([
            'status' => ($validated['status'] == true) ? false : true,
        ]);
    }catch(Exception $e) {
        $error = ['error' => [__("Something went wrong! Please try again.")]];
        return Response::error($error,null,500);
    }

    $success = ['success' => [__("Blog status updated successfully!")]];
    return Response::success($success,null,200);
}
//=======================================Banner section End ==========================================
    /**
     * Method for get languages form record with little modification for using only this class
     * @return array $languages
     */
    public function languages() {
        $languages = Language::whereNot('code',LanguageConst::NOT_REMOVABLE)->select("code","name")->get()->toArray();
        $languages[] = [
            'name'      => LanguageConst::NOT_REMOVABLE_CODE,
            'code'      => LanguageConst::NOT_REMOVABLE,
        ];
        return $languages;
    }

    /**
     * Method for validate request data and re-decorate language wise data
     * @param object $request
     * @param array $basic_field_name
     * @return array $language_wise_data
     */
    public function contentValidate($request,$basic_field_name,$modal = null) {
        $languages = Language::get();

        $current_local = get_default_language_code();
        $validation_rules = [];
        $language_wise_data = [];
        foreach($request->all() as $input_name => $input_value) {
            foreach($languages as $language) {
                $input_name_check = explode("_",$input_name);
                $input_lang_code = array_shift($input_name_check);
                $input_name_check = implode("_",$input_name_check);
                if($input_lang_code == $language['code']) {
                    if(array_key_exists($input_name_check,$basic_field_name)) {
                        $langCode = $language['code'];
                        if($current_local == $langCode) {
                            $validation_rules[$input_name] = $basic_field_name[$input_name_check];
                        }else {
                            $validation_rules[$input_name] = str_replace("required","nullable",$basic_field_name[$input_name_check]);
                        }
                        $language_wise_data[$langCode][$input_name_check] = $input_value;
                    }
                    break;
                }
            }
        }
        if($modal == null) {
            $validated = Validator::make($request->all(),$validation_rules)->validate();
        }else {
            $validator = Validator::make($request->all(),$validation_rules);
            if($validator->fails()) {
                return back()->withErrors($validator)->withInput()->with("modal",$modal);
            }
            $validated = $validator->validate();
        }

        return $language_wise_data;
    }

    /**
     * Method for validate request image if have
     * @param object $request
     * @param string $input_name
     * @param string $old_image
     * @return boolean|string $upload
     */
    public function imageValidate($request,$input_name,$old_image) {
        if($request->hasFile($input_name)) {
            $image_validated = Validator::make($request->only($input_name),[
                $input_name         => "image|mimes:png,jpg,webp,jpeg,svg",
            ])->validate();

            $image = get_files_from_fileholder($request,$input_name);
            $upload = upload_files_from_path_dynamic($image,'site-section',$old_image);
            return $upload;
        }

        return false;
    }
}
