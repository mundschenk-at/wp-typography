=== wp-Typography ===
Contributors: kingjeffrey
Donate link: http://kingdesk.com/projects/donate/
Tags: typography, typogrify, hyphenation, SmartyPants, widow, widon't, units, wrapping, wrap, URLs, Email, formatting, smart quotes, quote marks, dashes, em dash, en dash, ellipses, trademark, copyright, service mark, fractions, math, math symbols, ordinal suffixes, ordinal, CSS hooks, ampersands, uppercase, numbers, guillemets, text, smartypants, format, style, quotes, prettify, type, font, admin, automatic, comment, comments, content, headings, heading, CSS, custom, excerpt, feed, feeds, RSS, filter, links, page, pages, plugin, post, posts, title, wordpress, XHTML
Requires at least: 2.7
Tested up to: 2.8.4
Stable tag: 1.12


Improve your web typography with: hyphenation, space control, intelligent character replacement, and CSS hooks.

== Description ==

Improve your web typography with:

* Hyphenation &mdash; [over 40 languages supported](http://kingdesk.com/projects/wp-typography-faqs/#what-hyphenation-language-patterns-are-included)

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
* all markup must be valid xHTML syntax, specifically:
    * every element must be closed
    * every attribute must have a value enclosed in quotes
    * tag names and attributes must be lowercase

wp-Typography can easily be ported to any other PHP based content management system.  A sister project &mdash; [PHP Typography](http://kingdesk.com/projects/php-typography/ "PHP Typography") assembles all typographic functionality (without any WordPress specific code) in an object oriented format that is ready for WordPress independent use.

View the [wp-Typography homepage](http://kingdesk.com/projects/wp-typography/ "wp-Typography Homepage") for more information.

== Installation ==

1. Log in to WordPress as an administrator
2. Go to `Plugins > Add New` and search for `wp-Typography`
3. Click `install` next to the wp-Typography plugin description
4. Click the `Install Now` button in the pop-up window
5. After the plugin is installed, click the `Activate Plugin` link
6. Go to `Settings > wp-Typography` to set your preferences

Alternately, you may manually upload the plugin by following the following instructions:

1. Go to [http://wordpress.org/extend/plugins/wp-typography/](http://wordpress.org/extend/plugins/wp-typography/), and click the `Download` button
2. Unzip the `wp-Typography.x.x.zip` file
3. Using your favored FTP client, upload the `wp-typography` folder to the `/wp-content/plugins/` directory
4. Log in to WordPress as an administrator
5. Go to `Plugins > Installed`, locate the plugin and click the related `Activate` link.
6. Go to `Settings > wp-Typography` to set your preferences

== Frequently Asked Questions ==

FAQs are maintained at the [wp-Typography website](http://kingdesk.com/projects/wp-typography-faqs/ "wp-Typography FAQs").

Three questions come up so frequently, we will republish their answers here:

= Will this plu­gin slow my page load­ing times? =

Yes. Use [WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/).

= This plugin breaks post title links.  What gives? =

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

There is an error in the core of WordPress (as of version 2.8.1) that uses the wrong function to provide post titles for secondary RSS feeds (like RSS feeds for single page comments or categories.  This error will return any HTML tags used for CSS Hooks or character styling, and the tags will be visible when someone clicks on the RSS icon in the address bar of their browser.  If this bothers you, your only option (until WordPress corrects this error) is to disable all typographic processing in your page titles as described in the paragraph above. Updates on this WordPress bug may be followed [here](https://core.trac.wordpress.org/ticket/10410).

= Does this plugin work with wp-Typogrify? =

This plugin is an official replacement for the [wp-Typogrify plugin](http://wordpress.org/extend/plugins/wp-typogrify/).  Please uninstall wp-Typogrify and install wp-Typography in its place.

Remember, many more FAQs are are addressed the [wp-Typography website](http://kingdesk.com/projects/wp-typography-faqs/ "wp-Typography FAQs").


== Screenshots ==

1. wp-Typography administrative settings page

== Changelog ==

= 1.13 - August 31, 2009 =

* Added option to collapse adjacent space characters to a single character
* Upgraded to [PHP Typography 1.13](http://kingdesk.com/projects/php-typography/)

= 1.12 - August 17, 2009 =

* Corrected multibyte character handling error that could cause some text to not display properly
* Upgraded to [PHP Typography 1.12](http://kingdesk.com/projects/php-typography/)

= 1.11 - August 14, 2009 =

* Added language specific quote handling (for single quotes, not just double) for English, German and French quotation styles
* Upgraded to [PHP Typography 1.11](http://kingdesk.com/projects/php-typography/)

= 1.10.1 - August 14, 2009 =

* Left a setting in test mode.  That is corrected.

= 1.10 - August 14, 2009 =

* Fixed typo in default CSS styles
* Added language specific quote handling for English, German and French quotation styles
* Corrected multibyte character handling error that could cause some text to not display properly
* Expanded the multibyte character set recognized as valid word characters for improved hyphenation
* Upgraded to [PHP Typography 1.10](http://kingdesk.com/projects/php-typography/)


= 1.9 - August 12, 2009 =

* Added option to force single character words to wrap to new line (unless they are widows).
* Upgraded to [PHP Typography 1.9](http://kingdesk.com/projects/php-typography/)

= 1.8.1 - August 7, 2009 =

* Added optional automatic inclusion of styling of CSS hooks
* Fixed "Restore Defaults" conflict with other plugins

= 1.8 - August 4, 2009 =

* Corrected math and dash handling of dates
* Styling of uppercase words now plays nicely with soft-hyphens
* Upgraded to [PHP Typography 1.8](http://kingdesk.com/projects/php-typography/)

= 1.7.2 - July 29, 2009 =

* Now WordPress MU compatible
* Updated Options Page to new `register_setting()` and `settings_fields()` API

= 1.7.1 - July 29, 2009 =

* Updated thin space handling to be off by default, and updated the description in the admin panel to warn of rare mishandling in Safari and Chrome.

= 1.7 - July 29, 2009 =

* Reformatted language files for increased stability and to bypass a false positive from Avira's free antivirus software
* Upgraded to [PHP Typography 1.7](http://kingdesk.com/projects/php-typography/)

= 1.6 - July 28, 2009 =

* Efficiency Optimizations ( approximately 25% speed increase )
* Upgraded to [PHP Typography 1.6](http://kingdesk.com/projects/php-typography/)

= 1.5 - July 27, 2009 =

* Added the ability to exclude hyphenation of capitalized (title case) words to help protect proper nouns
* Added Hungarian hyphenation patterns
* Upgraded to [PHP Typography 1.5](http://kingdesk.com/projects/php-typography/)

= 1.4 - July 23, 2009 =

* Fixed an instance where pre-hyphenated words were hyphenated again
* Upgraded to [PHP Typography 1.4](http://kingdesk.com/projects/php-typography/)

= 1.3 - July 23, 2009 =

* Removed two uses of create_function() for improved performance
* Corrected many uninitialized variables
* Corrected two variables that were called out of scope
* Upgraded to [PHP Typography 1.3](http://kingdesk.com/projects/php-typography/)

= 1.2 - July 23, 2009 =

* added new 100 character option for max widow length protected
* added new 100 character option for max pull length for widow protection
* moved the processing of widow handling after hyphenation so that max-pull would not be compared to the length of the adjacent word, but rather the length of the adjacent word segment (i.e. that after a soft hyphen)
* Upgraded to [PHP Typography 1.2](http://kingdesk.com/projects/php-typography/)


= 1.1 - July 22, 2009 =

* took advantage of new feature in PHP Typography 1.1 where we could just set user settings without first setting phpTypography defaults for a slight performance improvement.
* Decoded special HTML characters (for feeds only) to avoid invalid character injection (according to XML's specs)
* Upgraded to [PHP Typography 1.1](http://kingdesk.com/projects/php-typography/)

= 1.0.4 - July 20, 2009 =

* Added test for curl to avoid bug where admin panel would not load 

= 1.0.3 - July 17, 2009 =

* Reverted use of the hyphen character to the basic minus-hyphen in words like "mother-in-law" because of poor support in IE6
* Zero-width-space removal for IE6 was broken.  This is corrected.
* Clarified some labels in the admin interface
* Simplified the admin interface URL

= 1.0.2 - July 16, 2009 =

* Fixed smart math handling so it can be turned off.
* Corrected smart math handling to not convert slashes in URLs to division signs
* Corrected issue where some server settings were throwing a warning in the admin panel for use of file_get_contents()


= 1.0.1 - July 15, 2009 =

* Corrected label in admin interface that indicated pretty fractions were part of basic math handling.

= 1.0 - July 15, 2009 =

* Changed default settings from all options being enabled to a minimal set being enabled.
* Added test to phpTypography methods `process()` and `process_feed()` to skip processing if `$isTitle` parameter is `TRUE` and `h1` or `h2` is an excluded HTML tag


= 1.0 beta 9 - July 14, 2009 =

* Added catch-all quote handling, now any quotes that escape previous filters will be assumed to be closing quotes
* A section of resource links were added to the wp-Typography admin settings page.

= 1.0 beta 8 - July 13, 2009 =

* Changed thin space injection behavior so that for text such as "...often-always?-judging...", the second dash will be wrapped in thin spaces
* Corrected error where fractions were not being styled because of a zero-space insertion with the wrap hard hyphens functionality
* Added default class to exclude: `noTypo`
* Changed order of admin page options, moving hyphenation options toward the top

= 1.0 beta 7 - July 10, 2009 =

* Added "/" as a valid word character so we could capture "this/that" as a word for processing (similar to "mother-in-law")
* Corrected error where characters from the Latin 1 Supplement Block were not recognized as word characters
* Corrected smart quote handling for strings of numbers
* Added smart guillemet conversion: `&lt;&lt;` and `&gt;&gt;` to `&laquo;` and `&raquo;`
* Added smart Single Low 9 Quote conversion as part of smart quotes: comma followed by non-space becomes Single Low 9 Quote
* Added Single Low 9 Quote, Double Low 9 Quote and &raquo; to style_initial_character functionality
* Added a new phpTypography method smart_math that assigns proper characters to minus, multiplication and division characters
* Depreciated the phpTypography method smart_multiplication in favor of smart_math
* Cleaned up some smart quote functionality
* Added ability to wrap after "/" if set_wrap_hard_hyphen is TRUE (like "this/that")
* Titles were not being properly processed, this has been corrected

= 1.0 beta 6 - July 9, 2009 =

* Critical bug fix:  RSS feeds were being disabled by previous versions.  This has been corrected.

= 1.0 beta 5 - July 8, 2009 =

* Corrected error where requiring  Em/En dash thin spacing "word-" would become "word &ndash;" instead of "word&ndash;"
* Corrected default settings
* Alphabetically sorted languages returned with get_languages() method
* Added a "Restore Defaults" option to the admin page


= 1.0 beta 4 - July 7, 2009 =

* Added default encoding value to smart_quote handling to avoid PHP warning messages
* Disabled processing of category titles using wp_list_categories()

= 1.0 beta 3 - July 6, 2009 =

* Corrected curling quotes at the end of block level elements
* Disabled processing of page titles (some browsers did not properly handle soft hyphens) reverts to wp-texturize for titles.

= 1.0 beta 2 - July 6, 2009 =

* Corrected multibyte character conflict in smart-quote handling that caused infrequent dropping of text
* Thin space injection included for en-dashes

= 1.0 beta 1 - July 3, 2009 =

* Initial release