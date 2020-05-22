=== wp-Typography ===
Contributors: pputzer, kingjeffrey
Tags: typography, hyphenation, smart quotes, formatting, widows, orphans, typogrify, quotes, prettify, small caps, diacritics
Requires at least: 4.9
Requires PHP: 5.6
Tested up to: 5.4
Stable tag: 5.6.1

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

* The host server must run PHP 5.6.0 or later,
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

= 5.7.0 - unreleased =
* _Feature_: Disable wp-Typography for specific posts/pages (via a sidebar toggle in the block editor or the filter hook `typo_disable_processing_for_post`).
* _Feature_: New block `wp-typography/typography` added to apply typography fixes on nested blocks (e.g. in widgets).
* _Change_: Significantly updated hyphenation patterns for:
  - Amharic,
  - Chinese pinyin (Latin),
  - German,
  - German (Traditional),
  - German (Swiss Traditional),
  - Latin (Classical),
  - Latin (Liturgical),
  - Spanish.
* _Change_: The minimum version has been raised to WordPress 4.9.
* _Change_: The DOM extension is now explicitly required for running the plugin.
* _Bugfix_: Copying from form fields works again when `Remove hyphenation when copying to clipboard` is enabled.

= 5.6.1 - December 24, 2019 =
* _Bugfix_: Some error messages were not getting translated because of a [WP.org infrastructure change](https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/).
* _Bugfix_: No more whitescreens when the underlying DOM parser fails.
* _Bugfix_: The path for script assets is now correctly constructed when wp-Typography is used as MU plugin.

= 5.6.0 - July 21, 2019 =
* _Feature_: Support for new variant of the international dash style without hair spaces for numeric intervals (i.e. `9-17` becomes `9&ndash;17` instead of `9&hairsp;&ndash;&hairsp;17`).
* _Feature_: Smart area and volume units (`5m2` is transformed into `5 m²`).
* _Feature_: The use of narrow no-break spaces and the true Unicode hyphen can now be enabled via the GUI. Consequently, the filter hook `typo_narrow_no_break_space` has been deprecated.
* _Change_: The HTML title handling has been reengineered, and consequently, the `title` variant of the `typo_disable_filtering` hook has been removed.
* _Change_: CSS class injection for ampersands, acronyms, and intial quotes is now disabled by default.
* _Change_: The smart quotes preview in the settings page should now be easier to read.
* _Change_: All external PHP dependencies have been moved to the namespace `WP_Typography\Vendor` to reduce the chance of conflicts with other plugins.
* _Bugfix_: The regular expression for cleaning user-supplied CSS no longer uses invalid syntax. This should fix any errors in PHP 7.3.

= 5.5.4 - March 11, 2019 =
* _Bugfix_: Automatic language detection now also works for locales without a country code (e.g. `fi`).
* _Bugfix_: No PHP notices are shown for missing options anymore.

= 5.5.3 - February 2, 2019 =
* _Bugfix_: Custom styles containing quote characters are now output correctly.

= 5.5.2 - January 29, 2019 =
* _Bugfix_: To prevent common false positives for single-letter Roman ordinals (especially in French and Dutch), Roman numeral matching now has to be explicitly enabled in the settings. In addition, only `I`, `V`, and `X` are accepted as single-letter Roman numbers.

= 5.5.1 - January 27, 2019 =
* _Bugfix_: Parts of hyphenated words should not be detected as Roman numerals anymore.
* _Bugfix_: The Unicode hyphen character (‐) is recognized as a valid word combiner.

= 5.5.0 - January 27, 2019 =
* _Feature_: French (1<sup>ère</sup>) and "Latin" (1<sup>o</sup>) ordinal numbers are now supported by the smart ordinals feature (also with Roman numerals, e.g. XIX<sup>ème</sup>).
* _Feature_: The list of smart quotes exceptions (words beginning with apostrophes) can now be customized.
* _Feature_: HTML5 parser performance hugely improved (up to 11× faster).
* _Bugfix_: Output filtering is now suspended during WP-CLI commands.
* _Bugfix_: Unit spacing is now properly applied to monetary symbols ($, €, etc.).
* _Bugfix_: Certain HTML entities (e.g. `&amp;`) were accidentally dropped in rare cases.
* _Bugfix_: Comply with the new WordPress Coding Standards 2.0.
