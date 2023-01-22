=== wp-Typography ===
Contributors: pputzer, kingjeffrey
Tags: typography, hyphenation, smart quotes, formatting, widows, orphans, typogrify, quotes, prettify, small caps, diacritics
Tested up to: 6.1
Stable tag: 5.9.1

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

wp-Typography has the following requirements:

* The host server must run PHP 7.4.0 or later,
* your installation of PHP must include the following PHP extensions (most do):
  - [mbstring](https://www.php.net/manual/en/mbstring.installation.php),
  - [DOM](https://www.php.net/manual/en/dom.installation.php), and
* text must be encoded in UTF‐8.


== Frequently Asked Questions ==

FAQs are maintained on the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/).

Three questions come up so frequently, we will republish their answers here:

= Will this plu­gin slow my page load­ing times? =

Maybe. For best performance, use a [persistent object cache](https://wptavern.com/persistent-object-caching) plugin like [WP Redis](https://wordpress.org/plugins/wp-redis/).

= This plugin breaks post title links. What gives? =

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

= What are the privacy implications of using the plugin? =

wp-Typography does not store, transmit or otherwise process personal data as such. It does cache the content of the site's posts. If necessary, you can clear this cache from the plugin's settings page.

Remember, many more FAQs are are addressed the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/).

== Screenshots ==

1. wp-Typography "General" settings page.
2. wp-Typography "Hyphenation" settings page.
3. wp-Typography "Intelligent Character Replacement" settings page.
4. wp-Typography "Space Control" settings page.
4. wp-Typography "Add CSS Hooks" settings page.

== Changelog ==

= 5.9.1 - January 22, 2023 =
* _Bugfix_: Only apply filters to ACF fields returning strings to preserve type expectations for downstream code.

= 5.9.0 - January 21, 2023 =
* _Feature_: wp-Typography is now compatible with PHP 8.1.
* _Feature_: Improved Advanced Custom Fields support:
  - wp-Typography is now compatible with Advanced Custom Fields 6.
  - The return type `array` is now supported for most fields.
* _Change_: PHP minimum version increased to 7.4.
* _Change_: `Prevent widows` is now disabled by default.

= 5.8.1 - January 25, 2022 =
* _Bugfix_: Whitescreen in `Requirements` class due to error in build process fixed.

= 5.8.0 - January 25, 2022 =
* _Feature_: wp-Typography is now compatible with PHP 8.0.
* _Change_: WordPress minimum version increased to 5.3.
* _Change_: PHP minimum version increased to 7.2.
* _Change_: Support for Internet Explorer 11 has been dropped.
* _Change_: A fabulous new plugin icon designed by [Johanna Amann](https://www.instagram.com/_jo_am/).
