=== wp-Typography ===
Contributors: pputzer
Tags: typography, hyphenation, smart quotes, formatting, widows, orphans, typogrify, quotes, prettify, small caps, diacritics
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 4.9
Stable tag: 5.3.5

Improve your web typography with: hyphenation, space control, intelligent character replacement, and CSS hooks.

== Description ==

Improve your web typography with:

* Hyphenation &mdash; [over 70 languages supported](https://code.mundschenk.at/wp-typography/frequently-asked-questions/#faq-what-hyphenation-language-patterns-are-included)

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

== Installation ==

= Requirements =

wp‐Typography has the following requirements:

* the host server must run PHP 5.6.0 or later
* your installation of PHP must include the [mbstring extension](http://us3.php.net/manual/en/mbstring.installation.php) (most do)
* text must be encoded UTF‐8


== Frequently Asked Questions ==

FAQs are maintained on the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/).

Two questions come up so frequently, we will republish their answers here:

= Will this plu­gin slow my page load­ing times? =

Yes. Use [WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/).

= This plugin breaks post title links. What gives? =

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

Remember, many more FAQs are are addressed the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/).

= I'm using Advanced Custom Fields and don't want my custom fields to be hyphenated! How can I disable that behavior? =

Please install the [wp-Typography Disable ACF Integration plugin](https://wordpress.org/plugins/wp-typography-disable-acf-integration/) by @sarukku.

== Screenshots ==

1. wp-Typography "General" settings page.
2. wp-Typography "Hyphenation" settings page.
3. wp-Typography "Intelligent Character Replacement" settings page.
4. wp-Typography "Space Control" settings page.
4. wp-Typography "Add CSS Hooks" settings page.

== Upgrade Notice ==

= 5.1.3 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

= 5.1.2 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

= 5.1.0 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

= 5.0.4 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

= 5.0.3 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

= 5.0.2 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

= 5.0.1 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

= 5.0.0 =
The plugin now requires at least PHP 5.6. If you are still running an earlier version,
please upgrade PHP or continue to use version 4.2.2.

== Changelog ==

= 5.3.5 - May 10, 2018 =
* _Bugfix_: 50/50 (and x/x except 1/1) are not treated as fractions anymore.
* _Bugfix_: The French spacing rules were not applied to closing guillemets followed by a comma.

= 5.3.4 - April 22, 2018 =
* _Bugfix_: Update used libraries to the latest versions.

= 5.3.3 - April 08, 2018 =
* _Bugfix_: Correctly match smart fractions even if the are followed by a comma (i.e. `1/4,`).

= 5.3.2 - March 24, 2018 =
* _Bugfix_: Prevent future conflicts with other plugins by updating included libraries.

= 5.3.1 - March 15, 2018 =
* _Bugfix_: Always clear the cache after updates to prevent frontend whitescreens under certain circumstances.

= 5.3.0 - March 13, 2018 =
* _Feature_: True integration with Advanced Custom Fields 5, making the filters adjustable for each field via the settings UI.
* _Feature_: The script to remove soft hyphens from clipboard selections has been refactored to reduce the number of loaded resources.
* _Change_: Some API methods have been deprecated and will be removed in 6.0.0:
  - The static methods `WP_Typography::filter*` should be replaced by static calls to the existing `process*` method family.
  - In general, all instance methods of the new class `WP_Typography\Implementation` can now be called statically on the singleton via the `WP_Typography` superclass.
* _Bugfix_: In rare cases, UTF-8 characters like `Å` caused all content within the same tag to disappear.

= 5.2.4 - February 26, 2018 =
* _Bugfix_: The partial was still packaged in the wrong place, causing backend whitescreens in some cases.

= 5.2.3 - February 22, 2018 =
* _Bugfix_: A partial was missing from the compatibility checking code for older PHP versions.

= 5.2.2 - February 04, 2018 =
* _Bugfix_: Superscripts were not displayed correctly in the settings page.
* _Bugfix_: Standalone `<` and `>` characters (i.e. not part of an HTML tag) could vanish in some circumstances.
* _Bugfix_: Re-activating the plugin no longer overwrites the settings with their defaults.

= 5.2.1 - January 11, 2018 =
* _Bugfix_: Languages were not sorted correctly in the settings page.
* _Bugfix_: Circular references in caches objects have been fixed.
* _Bugfix_: Workaround for Divi theme crash, avoiding `get_body_class()`.

= 5.2.0 - January 05, 2018 =
* _Feature_: WordPress body classes (i.e. the result of `get_body_class()`) are now passed to the text processing methods. This means that you can exclude entire pages from wp-Typography's processing based on the body classes generated by WordPress.
* _Feature_: Support for WooCommerce page descriptions (via the filter hook `woocommerce_format_content`).
* _Feature_: New hyphenation languages
  - Assamese,
  - Belarusian,
  - Bengali,
  - Church Slavonic,
  - Esperanto,
  - Friulan,
  - Gujarati,
  - Kannada,
  - Kurmanji,
  - Malayalam,
  - Norwegian (Bokmål)
  - Norwegian (Nynorsk)
  - Piedmontese,
  - Romansh,
  - Upper Sorbian.
* _Change_: Updated to use version 6.1.0 of the composer package `mundschenk-at/php-typography`.
* _Bugfix_: Numbers are treated like characters for the purpose of wrapping emails.
* _Bugfix_: Better matching between hyphenation languages and WordPress locales.
