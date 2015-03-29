=== WP Spam Fighter ===
Contributors: benohead, amazingweb-gmbh
Donate link: http://benohead.com/donate/
Tags: anti spam plugin,anti spam protection,anti-spam,antispam,block,block spam,blog spam,bot,comment,comment spam,comment spam plugin,comment spam prevention,comments,comments spam,how to prevent spam,how to stop spam,mark as spam,plugin,plugin spam,plugins,plugins spam,prevent spam,security,spam,spam comment,spam comments,spam counter,spam filter,spam filter plugin,Spam Free,spam free wordpress,spam plugin,spam plugins,spam prevention,spam prevention wordpress,spam-bot,spam-bots,spambot,spamfree,spammer,spammers,spammy,stop spam,wordpress,wordpress spam block plugin
Requires at least: 3.5
Tested up to: 4.1.1
Stable tag: 0.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Comment spam prevention without moderation, captchas or questions

== Description ==

This plugins prevents comment spam without requiring you to moderate all comments or your users to use user-unfriendly captchas or answer stupid arithmetic questions.

It works using two methods (which can be combined). It boils down to the following behavioral patterns normal comment authors (not spammers) will have:

* Normal users actually do read your post, which takes time. So someone sending a comment only a few seconds after having loaded the post has to be a spammer.
* Normal users do not fill fields which are not visible.

The first mechanism basically notes down when the page was loaded and when the comment was posted. When the comment is posted, if the timestamps are missing or if the user didn't spend enough time on the page, the comment posting will fail.
The great thing about this approach is that it not only stops bots but also human spammers.

The second mechanism based on a hidden field not being filled is what's usually called a honeypot-based mechanism. Spam bots will usually go through all fields in the form and try to put in some value. Normal users won't do that since they cannot see the fields.

Note that this plugin can be used in addition to other plugins using different spam detection mechanism e.g. Akismet.

== Installation ==

1. Upload the folder `wp-spam-fighter` to the `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Update the settings as required

== Frequently Asked Questions ==

= How can I contact you with a complaint, a question or a suggestion? =
Send an email to henri.benoit@gmail.com

= Why is the honeypot not being added to my comment form? =
This plugin uses the comment_form hook to add the honeypot. This only works if your theme displays the comment form using the comment_form() function (and that's the way it should do it). If your theme doesn't do it this way, you will not be able to use the honeypot based form protection.

= Does this plugin support WordPress Multi-Site ? =
Yes, it does. This plugins detects a network activation and will allow you to set it up on the network level.

== Screenshots ==

1. Settings

== Changelog ==

= 0.5.1 =

* Using reCaptcha was preventing logged in user from writing comments.

= 0.5 =

* Added support for Google's No Captcha reCaptcha.

= 0.4 =

* Added option to discard spam comments (without inserting them into the WordPress database) instead of just marking them as spam.

= 0.3 =

* Added option to delete spam comments instead of just marking them as spam.

= 0.2.1 =

* Fixed error registering action links in non-multisite mode.

= 0.2 =

* Multi-site support.

= 0.1 =

* First version.

== Upgrade Notice ==

n.a.
