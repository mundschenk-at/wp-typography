=== wp-Typography ===
Contributors: kingjeffrey
Tags: typography, typogrify, hyphenation, SmartyPants, widow, widon't, units, wrapping, wrap, URLs, Email, formatting, smart quotes, quote marks, dashes, em dash, en dash, ellipses, trademark, copyright, service mark, fractions, math, math symbols, ordinal suffixes, ordinal, CSS hooks, ampersands, uppercase, numbers, guillemets, text, smartypants, format, style, quotes, prettify, type, font
Requires at least: 2.7
Tested up to: 2.8.1
Stable tag: trunk

Improve your web typography with: hyphenation, space control, intelligent character replacement, and (4) CSS hooks.

== Description ==

Improve your web typography with:

* Hyphenation &mdash; over 40 languages supported
* Space control, including:
    * widow protection
    * gluing values to units
    * forced internal wrapping of long URLs & email addresses
* Intelligent character replacement, including smart handling of:
    * quote marks
    * dashes
    * ellipses
    * trademarks, copyright & service marks
    * math symbols
    * fractions
    * ordinal suffixes
* CSS hooks for styling:
    * ampersands,
    * uppercase words,
    * numbers,
    * initial quotes & guillemets.

wp‐Typography has the following requirements:

* the host server must run PHP 5 or later
* text must be encoded UTF‐8
* all markup must be valid xHTML, specifically:
    * every element must be closed
    * every attribute must have a value enclosed in quotes
    * tag names and attributes must be lowercase

wp-Typography can easily be ported to any other PHP based content management system.  A sister project &mdash; [PHP Typography](http://kingdesk.com/projects/php-typography/ "PHP Typography") assembles all typographic functionality (without any WordPress specific code) in an object oriented format that is ready for WordPress independent use.

View the [wp-Typography homepage](http://kingdesk.com/projects/wp-typography/ "wp-Typography Homepage") for more information.

== Installation ==

1. Upload the `wp-typography` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the wp-Typography settings page (`/wp-admin/options-general.php?page=wp-typography/wp-typography.php`), and set your preferences

== Frequently Asked Questions ==

FAQs are maintained at the [wp-Typography website](http://kingdesk.com/projects/wp-typography-faqs/ "wp-Typography FAQs").

This issue comes up so frequently, we will republish it here:

= This plugin breaks post title links.  What gives? =

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

There is an error in the core of WordPress (as of version 2.8.1) that uses the wrong function to provide post titles for secondary RSS feeds (like RSS feeds for single page comments or categories.  This error will return any HTML tags used for CSS Hooks or character styling, and the tags will be visible when someone clicks on the RSS icon in the address bar of their browser.  If this bothers you, your only option (until WordPress corrects this error) is to disable all typographic processing in your page titles as described in the paragraph above. Updates on this WordPress bug may be followed [here](https://core.trac.wordpress.org/ticket/10410).

Remember, many more FAQs are are addressed the [wp-Typography website](http://kingdesk.com/projects/wp-typography-faqs/ "wp-Typography FAQs").


== Screenshots ==

1. wp-Typography administrative settings page

== Changelog ==

= 1.0 =
initial release