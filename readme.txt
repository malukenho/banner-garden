=== Banner Garden Plugin for Wordpress ===
Contributors: Ferenc Vasóczki
Donate link: http://vaso.sma.hu/blog/banner-garden-plugin-for-wordpress/
Tags: ad, banner, commercial, advertising, adverts, ads, widget, plugin, sidebar, banner rotation, campaign, flash, image
Requires at least: 2.9.2
Tested up to: 2.9.2
Stable tag: 0.1.2


This plugin is a campaign and banner manager for wordpress.

== Description ==

Banner Garden Plugin for Wordpress is (as the name is) a very usefull banner / ad  rotator script.

You can use it **ANYWHERE** on your blog. With a simple shortcode, you can insert your flash, image, or html code (like google adsense) into your posts, sidebar through widgets, header, or anywhere you want. Read the usage section later.

You have to do nothing else, just install this plugin, go to the Banner Garden settings page, create a campaign, add banners to your campaign, and place a little code like this [bannergarden id="3"] into anywhere in your blog.

This is the lite version of Banner Garden, it's free to use. I plan, i will write a pro version with much more options, but this is the future.

Features and Benefits:

* Create any campaigns
* Optionally you can set the start and/or the end time of a campaign
* Flash, image and ad code banners
* Rotate banners randomly
* Default banner if a campaign expired or file or campaign not exists. (You canturn this option on/off).
* Uses the WordPress Media Library for uploading or selecting flash/image
* You can add campaign to sidebar through widgets.
* Place campaign anywhere in your blog.
* Include a test flash and a test image banner
* Localization in .po/.mo files.  (Hungarian localization included).



== Installation ==

*Installation:*

1. Download the bannergarden.zip from here. [no link, i am waiting for the wordpress plugin page activation].
1. Unzip it into your wp-content/plugins/ directory.
1. Go to the Plugins page at Dashboard.
1. Activate the plugin.

*Usage:*

1. Go to the Dashboard -> Settings -> BannerGarden
1. Create a new campaign, and set it to Active
1. Add new banner(s) to your campaign
1. Insert anywhere this shortcode with your Campaign ID: [bannergarden id="xxx"] where xxx is the ID of campaign.
1. If you want to translate it, use PoEdit to translate.

You can read more, how Banner Garden works, at the Banner Garden home page:
http://vaso.sma.hu/blog/banner-garden-plugin-for-wordpress/

*Notes:*

I used WordPress 2.9.2 for developing this plugin, i don't know, is it works with previous versions of it.
I tested it with these browsers under Windows XP Pro SP3 (32-bit):

* Firefox 3.6 rv.1.9.2,
* Google Chrome 4.1.249.1045 (42898)
* Opera 10.10 Build 1893
* Safari 4.0.4 (531.21.10)
* Internet Explorer 8.0.6001.18702

Please write me if you have other configuration, and it works.

*Localization:*

I translated it to Hungarian. If you translate it to other language(s), please send me the .mo/.po files, and i will add it to the package.

*How Banner Garden works [detailed usage]:*

Place a shortcode anywhere in your page (see below): [bannergarden id="xxx"]

The xxx is the ID of your campaign what you found at list of campaigns page. The quotes are important!
You have several options to insert this shortcode into your page.

* You can add it to your posts or pages through the WordPress editor.
* You can use the Banner Garden widget, and place it to your sidebar(s).
* Edit the .php file what you want, and insert this code.

So, before your WordPress engine start to render the page, the plugin is search for this shortcode. If it founds, it try to process it.

About the process method. The processor get out the id of the campaign. Check, is this campaign exists, if yes, check is this campaign active, and if it is active, check, is the time settings are right. If everything is all right, it check is there any banner in this campaign. If there is/are banner(s), then select one randomly, and replace the shortcode with your media.

If any of above condition fails, it will show the default banner what is placed in the plugin dirs media subdirectory named “default_banner.jpg”.
This happens only, if you did not checked out the “Show default banner if anything fails” option.

I included a test falsh and a test image banner for you in this media subdirectory.

That's it.
You can read more about this plugin, updates, users comments at http://vaso.sma.hu/blog/banner-garden-plugin-for-wordpress/

I did work very hard on this very first plugin, it tooks me 4 days, because i know wordpress only since a week.

Thank You!

== Frequently Asked Questions ==

= Can this Banner Garden plugin give me some statistic about my banners or campaigns? =
No, this lite version of plugin has no feature like this. I am plan to improve the plugin in future.

= Why my banners doesn't show =
Maybe your campaign is expired, plugin doesn't find your media, and you turned of the default banner at the main page of the Banner Garden.

== Screenshots ==

1. Main page of Banner Garden.
screenshot-1.png

2. Create a campaign.
screenshot-2.png

3. Select start/end date of campaign width datetime picker.
screenshot-3.png

4. Add banner.
screenshot-4.png

5. List of banner.
screenshot-5.png

6. How the banner is at frontend.
screenshot-6.png

== Upgrade Notice ==
No upgrade yet

== Changelog ==

0.1.2
- Fix the path of plugin.