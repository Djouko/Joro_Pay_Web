<?php

namespace App\Http\Controllers\Api;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\AppOnboardScreens;
use App\Models\Admin\AppSettings;
use App\Models\Admin\BasicSettings;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;


class AppSettingsController extends Controller
{
    public function appSettings(){
        $app_url = AppSettings::get()->map(function($url){
            return[
                'id' => $url->id,
                'android_url' => $url->android_url,
                'iso_url' => $url->iso_url,
                'created_at' => $url->created_at,
                'updated_at' => $url->updated_at,
            ];
        })->first();
        
        $splash_screen_user = AppSettings::get()->map(function($splash_screen){
            return[
                'id' => $splash_screen->id,
                'splash_screen_image' => $splash_screen->splash_screen_image,
                'version' => $splash_screen->version,
                'created_at' => $splash_screen->created_at,
                'updated_at' => $splash_screen->updated_at,
            ];
        })->first();
        $splash_screen_agent = AppSettings::get()->map(function($splash_screen){
            return[
                'id' => $splash_screen->id,
                'splash_screen_image' => $splash_screen->agent_splash_screen_image,
                'version' => $splash_screen->agent_version,
                'created_at' => $splash_screen->created_at,
                'updated_at' => $splash_screen->updated_at,
            ];
        })->first();
        $splash_screen_merchant = AppSettings::get()->map(function($splash_screen){
            return[
                'id' => $splash_screen->id,
                'splash_screen_image' => $splash_screen->merchant_splash_screen_image,
                'version' => $splash_screen->merchant_version,
                'created_at' => $splash_screen->created_at,
                'updated_at' => $splash_screen->updated_at,
            ];
        })->first();

        $onboard_screen_user = AppOnboardScreens::where('type',GlobalConst::USER)->orderByDesc('id')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'title' => $data->title,
                'sub_title' => $data->sub_title,
                'image' => $data->image,
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ];

        });
        $onboard_screen_agent = AppOnboardScreens::where('type',GlobalConst::AGENT)->orderByDesc('id')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'title' => $data->title,
                'sub_title' => $data->sub_title,
                'image' => $data->image,
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ];

        });
        $onboard_screen_merchant = AppOnboardScreens::where('type',GlobalConst::MERCHANT)->orderByDesc('id')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'title' => $data->title,
                'sub_title' => $data->sub_title,
                'image' => $data->image,
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ];

        });

        $basic_settings = BasicSettings::first();
        $basic_settings_user = [
            "site_name" =>  @$basic_settings->site_name,
            "site_title" =>  @$basic_settings->site_title,
            "site_logo" =>  @$basic_settings->site_logo,
            "site_logo_dark" =>  @$basic_settings->site_logo_dark,
            "base_color" =>  @$basic_settings->base_color,
            "site_fav_dark" =>  @$basic_settings->site_fav_dark,
            "site_fav" =>  @$basic_settings->site_fav,
            "timezone" =>  @$basic_settings->timezone,
        ];
        $basic_settings_agent = [
            "site_name" =>  @$basic_settings->agent_site_name,
            "site_title" =>  @$basic_settings->agent_site_title,
            "base_color" =>  @$basic_settings->agent_base_color,
            "site_logo" =>  @$basic_settings->agent_site_logo,
            "site_logo_dark" =>  @$basic_settings->agent_site_logo_dark,
            "site_fav_dark" =>  @$basic_settings->agent_site_fav_dark,
            "site_fav" =>  @$basic_settings->agent_site_fav,
            "timezone" =>  @$basic_settings->timezone,
        ];
        $basic_settings_merchant = [
            "site_name" =>  @$basic_settings->merchant_site_name,
            "site_title" =>  @$basic_settings->merchant_site_title,
            "base_color" =>  @$basic_settings->merchant_base_color,
            "site_logo" =>  @$basic_settings->merchant_site_logo,
            "site_logo_dark" =>  @$basic_settings->merchant_site_logo_dark,
            "site_fav_dark" =>  @$basic_settings->merchant_site_fav_dark,
            "site_fav" =>  @$basic_settings->merchant_site_fav,
            "timezone" =>  @$basic_settings->timezone,
        ];

        $user_app_settings = [
            'splash_screen'     => $splash_screen_user,
            'onboard_screen'    => $onboard_screen_user,
            'basic_settings'    => $basic_settings_user,
        ];
        $agent_app_settings = [
            'splash_screen'     => $splash_screen_agent,
            'onboard_screen'    => $onboard_screen_agent,
            'basic_settings'    => $basic_settings_agent,
        ];
        $merchant_app_settings = [
            'splash_screen'     => $splash_screen_merchant,
            'onboard_screen'    => $onboard_screen_merchant,
            'basic_settings'    => $basic_settings_merchant,
        ];
        $app_settings = [
            'user'          => (object) $user_app_settings,
            'agent'         => (object) $agent_app_settings,
            'merchant'      => (object) $merchant_app_settings,
        ];

        $data =[
            'base_url'              => url("/"),
            "default_image"         => files_asset_path_basename("default"),
            "screen_image_path"     => files_asset_path_basename("app-images"),
            "logo_image_path"       => files_asset_path_basename("image-assets"),
            'app_url'               => (object)$app_url,
            'app_settings'          => (object)$app_settings,

        ];
        $message =  ['success'=>[__("Data Fetch Successful")]];
        return Helpers::success($data,$message);

    }
    public function languages()
    {
        try{
            $api_languages = get_api_languages();
        }catch(Exception $e) {
            $error = ['error'=>[$e->getMessage()]];
            return Helpers::error($error);
        }
        $data =[
            'languages' => $api_languages,
        ];
        $message =  ['success'=>[__('Language Data Fetch Successfully!')]];
        return Helpers::success($data,$message);
    }
}
