=== wp-Typography ===
Contributors: pputzer
Tags: typography, hyphenation, smart quotes, formatting, widows, orphans, typogrify, quotes, prettify, small caps, diacritics
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 4.9
Stable tag: 5.2.1

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

= 5.2.2 - February 04, 2018 =
* _Bugfix_: Superscripts were not displayed correctly in the settings page.
* _Bugfix_: Standalone `<` and `>` characters (i.e. not part of an HTML tag) could vanish in some circumstances.
* _Bugfix_: Re-activating the plugin no longer overwrites the settings with their defaults.

= 5.2.1 - January 11, 2018 =
* _Bugfix_: Languages were not sorted correctly in the settings page.
* _Bugfix_: Circular references in caches objects have been fixed.
* _Bugfix_: Workaround for Divi theme crash, avoiding `get_body_class()`.

= 5.2.0 - January 05, 2018 =
* _Feature_: WordPress body classes (i.e. the result of `get_body_class()`) are now
  passed to the text processing methods. This means that you can exclude entire pages
  from wp-Typography's processing based on the body classes generated by WordPress.
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

= 5.1.3 - December 03, 2017 =
* _Change_: Updated to use version 5.2.3 of the composer package `mundschenk-at/php-typography`.
* _Bugfix_: Sometimes, the French double quotes style generated spurious ».
* _Bugfix_: Locale-based language files where not properly matched (primarily affecting `en-US` and `en-GB`, props @strasis).

= 5.1.2 - November 25, 2017 =
* _Change_: Updated to use version 5.2.2 of the composer package `mundschenk-at/php-typography`.
* _Bugfix_: Removed some ambiguous diacritics replacements from the German language file.
* _Bugfix_: Prevent of accidental loading of obsolete composer `ClassLoader` implementations from other plugins.

= 5.1.1 - November 16, 2017 =
* _Bugfix_: Shortcodes in the new WordPress 4.8 text widget work again.

= 5.1.0 - November 14, 2017 =
* _Feature_: HTML5 parser performance improved by 20 percent.
* _Feature_: New hyphenation language "Swiss-German (Traditional)" added.
* _Feature_: New filter hook `typo_narrow_no_break_space` to enable the NARROW NO-BREAK SPACE.
* _Change_: Refactored plugin internals. This means that
  - caching should be more friendly to shared hosting environments,
  - options are stored as a single array now (i.e. fewer rows in the `options` table), and
  - filters and actions are only added when actually needed.
* _Change_: Updated to use version 5.2.1 of the composer package `mundschenk-at/php-typography`.
* _Bugfix_: Narrow spaces are honored during de-widowing.

= 5.0.4 - September 09, 2017 =
* _Bugfix_: Ensure proper typing for cached language plugin lists.

= 5.0.3 - September 03, 2017 =
* _Bugfix_: Lower database write load by reducing option updates (props @jerzyk).

= 5.0.2 - September 02, 2017 =
* _Bugfix_: "Clear Cache" and "Restore Defaults" admin notices are now shown again.
* _Bugfix_: Object caching errors don't crash the site anymore.

= 5.0.1 - August 28, 2017 =
* _Bugfix_: Fatal error on PHP 5.6.x (caused by using `__METHOD__` as a variable function) fixed (`mundschenk-at/php-typography` 5.0.2).

= 5.0.0 - August 27, 2017 =
* _Feature_: Proper multilingual support (automatic language switching). Tested with
  - [Polylang](https://wordpress.org/plugins/polylang/),
  - [MultilingualPress](https://wordpress.org/plugins/multilingual-press/), and
  - [WPML](https://wpml.org).
* _Feature_: Language-specific default settings.
* _Feature_: [Several new hooks](https://code.mundschenk.at/wp-typography/api/) added (including `typo_settings` to directly filter the settings).
* _Change_: Updated to use version 5.0.1 of the new standalone composer package `mundschenk-at/php-typography`.
* _Change_: Minimum PHP version increased to 5.6.0
* _Change_: Updated list of valid top-level domains.
* _Bugfix_: French punctuation spacing after links (and other inline tags) fixed.
* _Bugfix_: Lone ampersands are treated as single-character words.
* _Bugfix_: Hyphenated words are properly de-widowed.
