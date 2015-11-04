=== Pushover Notifications for WordPress ===
Contributors: cklosows
Tags: pushover, notifications, push notifications, bbpress, easy digital downloads, mobile, iphone, ios, android
Requires at least: 3.0
Tested up to: 4.3.1
Stable tag: 1.9.4
Donate link: https://wp-push.com/donations/
License: GPLv2 or later

Pushover Notifications allows your WordPress site to send push notifications straight to your iOS/Android device.

== Description ==

Using the Pushover Notifications application on your iOS/Android device in conjunction with your WordPress blog and this plugin, you can be notified of events happening on your blog as Push Notifications on your mobile device.

Currently supports notifications for new users, comments, pingback/trackbacks, user specific password reset notifications, plugin and theme upgrades, core upgrades, notifying authors of comments on their posts, and post publication notifications for registered users.

You can expand Pushover Notifications for WordPress further with extensions available at https://wp-push.com/extensions/

This plugin is in no way affiliated with Superblock, LLC (the company behind Pushover.net).

= A big thanks to our Translators =
* German: Gero @ http://literaturcafe.de

If you have an interest in translating this plugin visit https://wp-push.com/support/forum/polyglots/

== Installation ==

1. Install the Pushover Notifications plugin
2. Activate the plugin
3. Create an Application at https://pushover.net/apps
4. Enter your Application API Token/Key
5. Enter your User Key
6. Choose what you want to be notified of
7. To get user specific notifications, also add your Pushover User Key to your Profile
8. Enjoy Pushover Notifications on your Site!

== Upgrade Notice ==
In version 1.9.2, if you previously had notifications for new posts enabled, you'll need to go in and choose what roles get notifications before any notificaitons for New Posts will be sent out.

== Changelog ==
= 1.9.4 =
* TWEAK: Improved filters for the notification process

= 1.9.3.1 =
* FIX: PHP Warnings on new post notifications if there is no one to notify

= 1.9.3 =
* NEW/FIX: Setting for a list of all users with Pushover Keys in their profile (helps with performance, thanks Pippin)

= 1.9.2 =
* NEW: Admin's can now choose which roles get new post notifications. Supports Custom Roles as well.
* NEW: Link to create an application is prefilled with WordPress App Template (Thanks Superblock team!)
* FIX: Only users of a role who can receive post notifications can see the option to get new post notifications in their profile.

= 1.9.1.1 =
* FIX: Corrected an issue where the theme update notification was reading the theme_updates site transient incorrectly.

= 1.9.1 =
* NEW: Added notifications for available core updates - Thanks <a href="http://ryanpletcher.com/" target="_blank">Ryan Pletcher</a>
* FIX: My extreme lack of spelling in docBlocks

= 1.9.0.1 =
* FIX: Fixed an issue where the User Profile option wasn't showing up for new post notifications

= 1.9 =
* NEW: Added post publication notifications for users with keys in their profile. When posts move from any status to published, a notification is sent. Post authors will get a notification only if the post moves from 'future' to 'publish', to notify them of a scheduled post being published.
* NEW: Added a section to the Profile page allowing users to turn on or off Post Publish notifications at an account specific level. This is only visible if the user has a Pushover User Key and the global Post Notification setting is on
* FIX: Corrected spelling error on plugin update notification
* CLEANUP: Moved the functions for contact methods out of the class and into the includes

= 1.8.0.1 =
* UPDATED: German Translation

= 1.8 =
* NEW: Pushover Notification when plugin or theme updates are available. Checks every 12 hours.
* NEW: Allow multiple Applications keys. Useful for sites that send large amounts of notifications or want to use different application icons for different types of notifications.
* FIX: Corrected a wrapping issue on the Extensions tab
* FIX: All functions now have PHPDoc on them
* FIX: Fixed a possible bottleneck in sending notifications due to the request blocking the thread. This means email fail-over will only apply when logging is enabled. (Note that logging may affect performance and should only be used on a non-production environment or when debugging)

= 1.7.6 =
* FIX: Fixed an issue when Logging is turned on, a notice is generated stating an Undefined index was found.

= 1.7.5 =
* NEW: Tabs are now a filter so extensions can add tabs
* NEW: System Info Tab to show settings for support (Thanks to Easy Digital Downloads for some great guidance on info to include)
* NEW: Quick Access to Settings and Pushover.net from Plugins page
* FIX: Converted the settings page to use the checked() function instead of custom if statements
* FIX: Spelling error on settings page
* FIX: Corrected link to Improved Cron plugin

= 1.7.4 =
* NEW: New Tab for managing extension license keys
* NEW: Making the new user notifications 100% filterable, including their arguments
* NEW: Disclaimer about 'cron' features and their reliance on wp-cron
* FIX: Spelling correction in admin
* FIX: Translators having issues with line breaks in translatable text
* FIX: Removed unnecessary translation entry on new comment title

= 1.7.3.1 =
* Fixing an integration bug for getting options

= 1.7.3 =
* Fixing Capitalization of Comment Type in Notification
* Streamlined the error logging with a formatting method
* Added tabs to the administration area to allow easier viewing of logs
* Added Extensions tab so users can find extensions for other plugins
* Fixed a Double URL Encoding bug with Supplementary URLs
* Added 'View Comment' link to comment notifications when a comment is made that's pre-approved

= 1.7.2 =
* Shortening the default messages and titles
* German translation by http://literaturcafe.de
* Making strings for comment type translatable
* Changed plugin site to http://wp-push.com, the new home of Pushover Notifications for WordPress

= 1.7.1 =
* Fixing encoding issue with Pushover integration

= 1.7 =
* Allowing sections for External plugin integrations when using the 'ckpn_notification_checkbox_filter' hook
* Making single instance call for pluign
* Added filter for default options so extensions of Pushover Notifications can add their defaults into the core
* Added a case in the response parsing looking for 500 Errors as well as an explanation in the log entry for this error
* Most updates in this release are related to plugin integration, allowing for better extensibility.

= 1.6.2 =
* General Bug Fixes and version bump for WordPress 3.5

= 1.6.1 =
* Fixing More PHP Notices

= 1.6.1 =
* Fixing PHP Notice for undefined constant flase (typo for 'false')

= 1.6 =
* Code Formatting. Thanks to the WP-PHPTidy extension for Sublime Text 2 - https://github.com/welovewordpress/SublimePhpTidy
* Internalization added

= 1.5 =
* Added wp_parse_args functionality to add in extensibility
* Am now following limits from the API: Message Length, URL Length, URL Title Length
* Password Reset Requests are now a high priority. They are marked red in Pushover
* Fixing a bug on Password Reset Notifications when no data is entered

= 1.4 =
* Adding a filter to the checkboxes for extensibility and upcoming extensions.

= 1.3 =
* Added in password reset notification as an option
* Limited logging to last 100 items to save space and clutter. Reduced the message to only the time, type, key, and subject
* Added in a check to see if the mail attempt failed, added a log line for this as well.
* Reversed the order of the logs to show the most recent first.

= 1.2 =
* Added in Advanced & Debug options for users to help troubleshoot. This iteration has the ability to turn on or off SSL Verification, since the API Request to Pushover is via HTTPS. This should help local installs and installs that do not have Root Certs installed for CURL.
* Added in the ability to turn Logging on and Off

= 1.1 =
* Fixing sensitization to the admin settings area. (Thanks to the feedback from the Pushover Team)

= 1.0 =
* Initial Release

== Frequently Asked Questions ==

= Do I have to buy the app for iOS or Android =

Yes, this plugin requires that you buy the application from the Google Play or Apple App store to connect with the Pushover.net account.

= Why do I have to register an Application with Pushover? =

I have been in contact with the Pushover Team and we agreed that in order to maintain a system that allows more managability and customization for you, the end user, each site should register their own application. This will allow you to set your own icon and not be limited by the monthly limitations of the API that are currently set, amongst other things.

== Future Plans ==

If you have a plugin you'd like to see integrated with Pushover Notifications for WordPress, comment in the forums.

== Screenshots ==
None at this time
