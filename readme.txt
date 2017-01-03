=== wp-Typography ===
Contributors: pputzer
Tags: typography, hyphenation, smart quotes, quote marks, smartypants, typogrify, quotes, prettify, widows, orphans, small caps, diacritics
Requires at least: 4.4
Tested up to: 4.7
Stable tag: 3.6.0

Improve your web typography with: hyphenation, space control, intelligent character replacement, and CSS hooks.

== Description ==

Improve your web typography with:

* Hyphenation &mdash; [over 50 languages supported](https://code.mundschenk.at/wp-typography/frequently-asked-questions/#what-hyphenation-language-patterns-are-included)

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

* the host server must run PHP 5.3.4 or later
* your installation of PHP 5.3.4+ must include the [mbstring extension](http://us3.php.net/manual/en/mbstring.installation.php) (most do)
* text must be encoded UTF‐8


== Frequently Asked Questions ==

FAQs are maintained on the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/).

Two questions come up so frequently, we will republish their answers here:

= Will this plu­gin slow my page load­ing times? =

Yes. Use [WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/).

= This plugin breaks post title links.  What gives? =

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

Remember, many more FAQs are are addressed the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/).


== Screenshots ==

1. wp-Typography "General" settings page.
2. wp-Typography "Hyphenation" settings page.
3. wp-Typography "Intelligent Character Replacement" settings page.
4. wp-Typography "Space Control" settings page.
4. wp-Typography "Add CSS Hooks" settings page.

== Changelog ==

= 3.6.0 - December 26, 2016 =
* Feature: Added hook `typo_ignore_parser_errors` to re-enable "parser guessing" as it was before version 3.5.2.
* Feature: Added new hook `typo_disable_filtering` to selectively disable filter groups.

= 3.5.3 - December 17, 2016 =
* Bugfix: Remove ambiguous entries from German diacritics replacement file.

= 3.5.2 - December 14, 2016 =
* Change: Return unmodified HTML if a processed text fragment is not well-formed. This improves compatibility with page builder plugins (and themes) that do weird things with the `the_content` filter.

= 3.5.1 - November 05, 2016 =
* Bugfix: Quotes ending in numbers were sometimes interpreted as primes.

= 3.5.0 - October 21, 2016 =
* Feature: Added "Latin (Liturgical)" as a new hyphenation language.
* Feature: Limited support for ACF Pro.
* Change: Better compatibility with improperly written plugins (ensuring that `wptexturize` is always off).
* Change: Only use the WP Object Cache for caching, not transients, to reduce database usage and prevent clogging in some configurations.
* Change: Updated list of valid top-level domains.
* Change: Updated HTML5 parser (html5-php) to 2.2.2.
* Bugfix: Custom hyphenations with more than one hyphenation point were not working properly.
* Bugfix: The `min_after` hyphenation setting was off by one.
* Bugfix: An IE11 bug on Windows 7 was previously triggered when the Safari workaround is enabled.
* Bugfix: Language names were not translated in the settings screen.
* Bugfix: Fractions did not play nice with prime symbols.

= 3.4.0 - July 10, 2016 =
* Store hyphenation patterns as JSON files instead of PHP to work around a GlotPress bug that prevents timely language pack updates.
* Out-of-the box support for Advanced Custom Fields (specifically for fields of the types `text`, `textarea` and `wysiwyg`).
* Updated list of valid top-level domains.
* Tested as compatible with WPML.

= 3.3.1 - June 27, 2016 =
* The JavaScript files for `Remove hyphenation when copying to clipboard` were missing from the build.
* Fixed a typo in the settings page.

= 3.3.0 - June 27, 2016 =
* Updated HTML parser (html5-php) to 2.2.1.
* Updated list of valid top-level domains.
* Removed IE6 references and workarounds. He's dead, Jim.
* Prevent references to US non-profit organizations like `501(c)(3)` being replaced with the copyright symbol (props randybruder).
* Added optional clean up of text copied to clipboard to prevent stray hyphens from showing on paste.
* Added CSS classes for smart fractions ("numerator", "denominator") and ordinal suffixes ("ordinal").
* Fixed « and » spacing when French punctuation style is enabled.
* Fixed `<title>` tag handling (no more `&shy;` and `<span>`tags, props mpcube).
* [Preliminary API documentation](https://code.mundschenk.at/wp-typography/api/) has been added to the plugin website.

= 3.2.7 - April 14, 2016 =
* "Duplicate ID" warnings should be gone now, regardless of the installed libXML version.

= 3.2.6 - April 05, 2016 =
* Fixed autoloading issue on frontpage. Sorry!

= 3.2.5 - April 05, 2016 =
* Properly handle `<title>` in WordPress 4.4 or higher (props TimThemann).
* Fixed missing parameter that prevented the `Hyphenate headings` setting from working correctly.

= 3.2.4 - April 04, 2016 =
* Fixed filtering of `<title>` tag (do only smart character replacement).

= 3.2.3 - March 28, 2016 =
* Made Safari rendering bug workaround less aggressive by not enabling discretionary ligatures.

= 3.2.2 - March 22, 2016 =
* Fixed Safari rendering bug workaround on Safari 9.1 (Mac OS X 10.11.4).

= 3.2.1 - March 20, 2016 =
* Accidentally, the filter for `the_content` was dropped in the version 3.2.0.

= 3.2.0 - March 20, 2016 =
* Added support for the French punctuation style (thin non-breakable space before `;:?!`).
* Added proper hyphenation of hyphenated compound words (e.g. `editor-in-chief`).
* Added partial support for styling hanging punctuation.
* Added adjustable limit for the number of cached text fragments.
* Changed behavior of caching setting: it needs to be explicitely enabled. Having it on by default caused too many problems on shared hosting environments.
* Started adding filters for programmatic adjustments to the typographic enhancements.
* Made main plugin class a singleton to ensure easier access for theme developers.
* Added the wp-Typography filter to additional WordPress hooks and completely disabled wptexturize (if Smart Character Replacement is enabled).

= 3.1.3 - January 13, 2016 =
* Pre­vent in­cor­rect re­place­ment of straight quotes with primes (e.g. `"number 6"` is not re­placed with `“num­ber 6″` but with `“num­ber 6”`).
* Fixed a bug that pre­vented header tags (`<h1>` … `<h6>`) that were set as “tags to ig­nore” from ac­tu­ally be­ing left alone by the plu­gin.

= 3.1.2 - January 7, 2016 =
* Do not create (most) transients if Disable Caching is set. This prevents unchecked database growth on large installations.

= 3.1.1 - January 5, 2016 =
* Fixed fatal error when running on PHP 5.3 (use of $this in anonymous function).

= 3.1.0 - January 3, 2016 =
* Minimum PHP version updated to 5.3.4 (from 5.3.0) to ensure consistent handling of UTF-8 regular expressions.
* Added workaround for insane NextGEN Gallery filter priority (props Itsacon).
* Added "Clear Cache" button.
* Changed internal option names to conform to WordPress standards (no camel case).
* Performance improvements through lazy initialization and caching of the PHP_Typography object state.
* Fixed diacritics replacement for UTF-8 strings
* Refactored plugin code for easier maintenance.
* Date-like values (e.g. "during the fiscal year 2015/2016") are not converted to smart fractions anymore.
* Added ability to switch between dash styles: both traditional US (em dash without spacing) and international usage (en dash with spaces) can be selected.
* Various white-space fixes related to dash styling.
* Language names in the Settings panel are sorted correctly for all locales.
* Fixed a bug where block-level tags where not detected corrected.
* Added workaround for duplicate ID warnings generated by some versions of libXML.
* Updated all hyphenation files and added the following new languages:
  * Afrikaans,
  * Armenian,
  * Dutch,
  * Georgian,
  * German (Traditional),
  * Latin (Classical),
  * Latvian,
  * Thai, and
  * Turkmen.

= 3.0.4 - December 12, 2015 =
* Prevent accidentally invalid XPath queries from being fatal on the frontend.
* Replaced old FAQ links in the README.

= 3.0.3 - December 8, 2015 =
* Use WordPress languages packs for translations.
* Fixed a bug in the XPath expression for ignoring tags by CSS ID.

= 3.0.2 - December 3, 2015 =
* A typo prevented custom quote styles from working.

= 3.0.1 - December 3, 2015 =
* Prevent drop-down box settings from being accidentally overwritten (props Stefan Engenhorst).
* Earlier check for minimum PHP version to prevent a parsing error on PHP 5.2 (props Javi).

= 3.0.0 - December 2, 2015 =
* DOM-based HTML parsing with HTML5-PHP
* Translation-ready & German translation added
* Added German as a diacritics language (mainly for French words).
* Various optimizations (hyphenation is still slow, though)
* Fixed custom hyphenation patterns.
* Fixed some calls to deprecated functions.
* Adopted semantic versioning for the project.
* Added workaround for Safari font bug.
* Added transient caching to speed things up a bit.
