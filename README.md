<<<<<<<< Update Guide >>>>>>>>>>>

Immediate Older Version: 4.4.0
Current Version: 4.5.0

Feature Update:
1. Added Seo Data.
2. Added GiftCard (Reloadly).
3. Updated Language Keys Values.
4. Exchange Rate Section Added On Admin Panel.
5. Updated Module Settings.
6. Handle Email Sending Errors.
7. Updated Push Notification Functionalities.
8. Updated Tatum Gateway Functionalities For SOL Currency.
9. Automatic Bill Pay Added (Reloadly).
10. Automatic Mobile Top Up Pay Added (Reloadly).
11. Removed Strowallet Virtual Card System.
12. Pay-Link System Updated.
13. Updated Project Installer.
14. Manage Country For Registration System (User,Agent,Merchant).
15. Push Notification Added For App Sections(User,Agent,Merchant).
16. Regenerate Merchant Api Key With Website Name For Payment Gateway.
17. Admin Google 2FA Security.

Please Use This Commands On Your Terminal To Update Full System
1. To Run project Please Run This Command On Your Terminal
    composer update && composer dumpautoload && php artisan migrate:fresh --seed && php artisan passport:install --force
