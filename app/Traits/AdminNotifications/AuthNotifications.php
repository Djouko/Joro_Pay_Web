<?php

namespace App\Traits\AdminNotifications;

use App\Constants\NotificationConst;
use App\Http\Helpers\NotificationHelper;
use App\Notifications\Admin\ActivityNotification;
use Exception;

trait AuthNotifications {


    protected function registerNotificationToAdmin($user) {
        $notification_content = [
            //email notification
            'subject' =>__("New User Register")."(".userGuard()['type'].")",
            'greeting' =>__("Information Of New User"),
            'email_content' =>__("User Type")." : ".userGuard()['type']."<br>".__("Full Name")." : ".$user->fullname."<br>".__("Username").": @".$user->username."<br>".__("Email").": @".$user->email,
            //push notification
            'push_title' =>__("New User Register")."(".userGuard()['type'].")",
            'push_content' => __('User Type').": ".userGuard()['type'].",".__("Full Name").": ".$user->fullname.",".__("Username").": @".$user->username.",".__("Email")." : @".$user->email,

            //admin db notification
            'notification_type' =>  NotificationConst::REGISTRATION,
            'admin_db_title' => __("New User Register")."(".userGuard()['type'].")",
            'admin_db_message' =>__('User Type').": ".userGuard()['type'].", ".__("Full Name").": ".$user->fullname.", ".__("Username").": @".$user->username." ".__("Email")." : @".$user->email,
        ];
        $user_guard = userGuard()['guard'];
        if($user_guard === "web" || $user_guard === "api"){
            $access_routes =[
                'admin.users.index','admin.users.active','admin.users.banned','admin.users.email.unverified','admin.users.kyc.unverified','admin.users.kyc.details','admin.users.email.users','admin.users.email.users.send','admin.users.details','admin.users.details.update','admin.users.login.logs','admin.users.mail.logs','admin.users.send.mail','admin.users.login.as.member','admin.users.kyc.approve','admin.users.kyc.reject','admin.users.search','admin.users.wallet.balance.update',
            ];
        }elseif($user_guard === "agent" || $user_guard === "agent_api"){
            $access_routes =[
                'admin.agents.index','admin.agents.active','admin.agents.banned','admin.agents.email.unverified','admin.agents.kyc.unverified','admin.agents.kyc.details','admin.agents.email.agents','admin.agents.email.agents.send','admin.agents.details','admin.agents.details.update','admin.agents.login.logs','admin.agents.mail.logs','admin.agents.send.mail','admin.agents.login.as.member','admin.agents.kyc.approve','admin.agents.kyc.reject','admin.agents.search','admin.agents.wallet.balance.update',
            ];
        }elseif($user_guard === "merchant" || $user_guard === "merchant_api"){
            $access_routes =[
                'admin.merchants.index','admin.merchants.active','admin.merchants.banned','admin.merchants.email.unverified','admin.merchants.kyc.unverified','admin.merchants.kyc.details','admin.merchants.email.merchants','admin.merchants.email.merchants.send','admin.merchants.details','admin.merchants.details.update','admin.merchants.login.logs','admin.merchants.mail.logs','admin.merchants.send.mail','admin.merchants.login.as.member','admin.merchants.kyc.approve','admin.merchants.kyc.reject','admin.merchants.search','admin.merchants.wallet.balance.update',
            ];
        }

        try{
            //notification
            (new NotificationHelper())->admin($access_routes)
                                    ->mail(ActivityNotification::class, [
                                            'subject'   => $notification_content['subject'],
                                            'greeting'  => $notification_content['greeting'],
                                            'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                            'user_type' => "admin",
                                            'title' => $notification_content['push_title'],
                                            'desc'  => $notification_content['push_content'],
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    protected function registerNotificationToAdminApi($user,$user_guard,$user_type) {
        $notification_content = [
            //email notification
            'subject' =>__("New User Register")."(".$user_type.")",
            'greeting' =>__("Information Of New User"),
            'email_content' =>__("User Type")." : ".$user_type."<br>".__("Full Name")." : ".$user->fullname."<br>".__("Username").": @".$user->username."<br>".__("Email").": @".$user->email,
            //push notification
            'push_title' =>__("New User Register")."(".$user_type.")",
            'push_content' => __('User Type').": ".$user_type.",".__("Full Name").": ".$user->fullname.",".__("Username").": @".$user->username.",".__("Email")." : @".$user->email,

            //admin db notification
            'notification_type' =>  NotificationConst::REGISTRATION,
            'admin_db_title' => __("New User Register")."(".$user_type.")",
            'admin_db_message' =>__('User Type').": ".$user_type.", ".__("Full Name").": ".$user->fullname.", ".__("Username").": @".$user->username." ".__("Email")." : @".$user->email,
        ];
        $user_guard = $user_guard;
        if($user_guard === "web" || $user_guard === "api"){
            $access_routes =[
                'admin.users.index','admin.users.active','admin.users.banned','admin.users.email.unverified','admin.users.kyc.unverified','admin.users.kyc.details','admin.users.email.users','admin.users.email.users.send','admin.users.details','admin.users.details.update','admin.users.login.logs','admin.users.mail.logs','admin.users.send.mail','admin.users.login.as.member','admin.users.kyc.approve','admin.users.kyc.reject','admin.users.search','admin.users.wallet.balance.update',
            ];
        }elseif($user_guard === "agent" || $user_guard === "agent_api"){
            $access_routes =[
                'admin.agents.index','admin.agents.active','admin.agents.banned','admin.agents.email.unverified','admin.agents.kyc.unverified','admin.agents.kyc.details','admin.agents.email.agents','admin.agents.email.agents.send','admin.agents.details','admin.agents.details.update','admin.agents.login.logs','admin.agents.mail.logs','admin.agents.send.mail','admin.agents.login.as.member','admin.agents.kyc.approve','admin.agents.kyc.reject','admin.agents.search','admin.agents.wallet.balance.update',
            ];
        }elseif($user_guard === "merchant" || $user_guard === "merchant_api"){
            $access_routes =[
                'admin.merchants.index','admin.merchants.active','admin.merchants.banned','admin.merchants.email.unverified','admin.merchants.kyc.unverified','admin.merchants.kyc.details','admin.merchants.email.merchants','admin.merchants.email.merchants.send','admin.merchants.details','admin.merchants.details.update','admin.merchants.login.logs','admin.merchants.mail.logs','admin.merchants.send.mail','admin.merchants.login.as.member','admin.merchants.kyc.approve','admin.merchants.kyc.reject','admin.merchants.search','admin.merchants.wallet.balance.update',
            ];
        }

        try{
            //notification
            (new NotificationHelper())->admin($access_routes)
                                    ->mail(ActivityNotification::class, [
                                            'subject'   => $notification_content['subject'],
                                            'greeting'  => $notification_content['greeting'],
                                            'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                            'user_type' => "admin",
                                            'title' => $notification_content['push_title'],
                                            'desc'  => $notification_content['push_content'],
                                            'unauthorize'  => true,
                                            'user_guard'  => $user_guard,
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    protected function resetNotificationToAdmin($user,$user_type,$guard) {
        $notification_content = [
            //email notification
            'subject' =>__("Password Reset")."(".$user_type.")",
            'greeting' =>__("Information Of Password Reset"),
            'email_content' =>__("User Type")." : ".$user_type."<br>".__("Full Name")." : ".$user->fullname."<br>".__("Username").": @".$user->username."<br>".__("Email").": @".$user->email,
            //push notification
            'push_title' =>__("Password Reset")."(".$user_type.")",
            'push_content' => __('User Type').": ".$user_type.",".__("Full Name").": ".$user->fullname.",".__("Username").": @".$user->username.",".__("Email")." : @".$user->email,

            //admin db notification
            'notification_type' =>  NotificationConst::PASSWORD_RESET,
            'admin_db_title' => __("Password Reset")."(".$user_type.")",
            'admin_db_message' =>__('User Type').": ".$user_type.", ".__("Full Name").": ".$user->fullname.", ".__("Username").": @".$user->username.", ".__("Email")." : @".$user->email,
        ];

        $user_guard = $guard;
        if($user_guard === "web" || $user_guard === "api"){
            $access_routes =[
                'admin.users.index','admin.users.active','admin.users.banned','admin.users.email.unverified','admin.users.kyc.unverified','admin.users.kyc.details','admin.users.email.users','admin.users.email.users.send','admin.users.details','admin.users.details.update','admin.users.login.logs','admin.users.mail.logs','admin.users.send.mail','admin.users.login.as.member','admin.users.kyc.approve','admin.users.kyc.reject','admin.users.search','admin.users.wallet.balance.update',
            ];
        }elseif($user_guard === "agent" || $user_guard === "agent_api"){
            $access_routes =[
                'admin.agents.index','admin.agents.active','admin.agents.banned','admin.agents.email.unverified','admin.agents.kyc.unverified','admin.agents.kyc.details','admin.agents.email.agents','admin.agents.email.agents.send','admin.agents.details','admin.agents.details.update','admin.agents.login.logs','admin.agents.mail.logs','admin.agents.send.mail','admin.agents.login.as.member','admin.agents.kyc.approve','admin.agents.kyc.reject','admin.agents.search','admin.agents.wallet.balance.update',
            ];
        }elseif($user_guard === "merchant" || $user_guard === "merchant_api"){
            $access_routes =[
                'admin.merchants.index','admin.merchants.active','admin.merchants.banned','admin.merchants.email.unverified','admin.merchants.kyc.unverified','admin.merchants.kyc.details','admin.merchants.email.merchants','admin.merchants.email.merchants.send','admin.merchants.details','admin.merchants.details.update','admin.merchants.login.logs','admin.merchants.mail.logs','admin.merchants.send.mail','admin.merchants.login.as.member','admin.merchants.kyc.approve','admin.merchants.kyc.reject','admin.merchants.search','admin.merchants.wallet.balance.update',
            ];
        }

        try{
            //notification
            (new NotificationHelper())->admin($access_routes)
                                    ->mail(ActivityNotification::class, [
                                            'subject'   => $notification_content['subject'],
                                            'greeting'  => $notification_content['greeting'],
                                            'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                            'user_type' => "admin",
                                            'title' => $notification_content['push_title'],
                                            'desc'  => $notification_content['push_content'],
                                            'unauthorize'  => true,
                                            'user_guard'  => $user_guard,
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    //delete account
    protected function deleteUserNotificationToAdmin($user,$user_type,$guard) {
        $notification_content = [
            //email notification
            'subject' =>__("delete Account")."(".$user_type.")",
            'greeting' =>__("Information of User"),
            'email_content' =>__("User Type")." : ".$user_type."<br>".__("Full Name")." : ".$user->fullname."<br>".__("Username").": @".$user->username."<br>".__("Email").": @".$user->email,
            //push notification
            'push_title' =>__("delete Account")."(".$user_type.")",
            'push_content' => __('User Type').": ".$user_type.",".__("Full Name").": ".$user->fullname.",".__("Username").": @".$user->username.",".__("Email")." : @".$user->email,

            //admin db notification
            'notification_type' =>  NotificationConst::DELETE_ACCOUNT,
            'admin_db_title' => __("Delete Account")."(".$user_type.")",
            'admin_db_message' =>__('User Type').": ".$user_type.", ".__("Full Name").": ".$user->fullname.", ".__("Username").": @".$user->username.", ".__("Email")." : @".$user->email,
        ];

        $user_guard = $guard;
        if($user_guard === "web" || $user_guard === "api"){
            $access_routes =[
                'admin.users.index','admin.users.active','admin.users.banned','admin.users.email.unverified','admin.users.kyc.unverified','admin.users.kyc.details','admin.users.email.users','admin.users.email.users.send','admin.users.details','admin.users.details.update','admin.users.login.logs','admin.users.mail.logs','admin.users.send.mail','admin.users.login.as.member','admin.users.kyc.approve','admin.users.kyc.reject','admin.users.search','admin.users.wallet.balance.update',
            ];
        }elseif($user_guard === "agent" || $user_guard === "agent_api"){
            $access_routes =[
                'admin.agents.index','admin.agents.active','admin.agents.banned','admin.agents.email.unverified','admin.agents.kyc.unverified','admin.agents.kyc.details','admin.agents.email.agents','admin.agents.email.agents.send','admin.agents.details','admin.agents.details.update','admin.agents.login.logs','admin.agents.mail.logs','admin.agents.send.mail','admin.agents.login.as.member','admin.agents.kyc.approve','admin.agents.kyc.reject','admin.agents.search','admin.agents.wallet.balance.update',
            ];
        }elseif($user_guard === "merchant" || $user_guard === "merchant_api"){
            $access_routes =[
                'admin.merchants.index','admin.merchants.active','admin.merchants.banned','admin.merchants.email.unverified','admin.merchants.kyc.unverified','admin.merchants.kyc.details','admin.merchants.email.merchants','admin.merchants.email.merchants.send','admin.merchants.details','admin.merchants.details.update','admin.merchants.login.logs','admin.merchants.mail.logs','admin.merchants.send.mail','admin.merchants.login.as.member','admin.merchants.kyc.approve','admin.merchants.kyc.reject','admin.merchants.search','admin.merchants.wallet.balance.update',
            ];
        }

        try{
            //notification
            (new NotificationHelper())->admin($access_routes)
                                    ->mail(ActivityNotification::class, [
                                            'subject'   => $notification_content['subject'],
                                            'greeting'  => $notification_content['greeting'],
                                            'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                            'user_type' => "admin",
                                            'title' => $notification_content['push_title'],
                                            'desc'  => $notification_content['push_content'],
                                            'unauthorize'  => true,
                                            'user_guard'  => $user_guard,
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }

}
