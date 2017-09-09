=== wp-Typography ===
Contributors: pputzer
Tags: typography, hyphenation, smart quotes, quote marks, formatting, typogrify, quotes, prettify, widows, orphans, small caps, diacritics
Requires at least: 4.4
Requires PHP: 5.6
Tested up to: 4.8
Stable tag: 5.0.4

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
* _Change:_ Updated list of valid top-level domains.
* _Bugfix_: French punctuation spacing after links (and other inline tags) fixed.
* _Bugfix_: Lone ampersands are treated as single-character words.
* _Bugfix_: Hyphenated words are properly de-widowed.

= 4.2.2 - June 18, 2017 =
* _Bugfix_: Properly clear cache on upgrade.
* _Change_: Various internal performance improvements.

= 4.2.1 - June 9, 2017 =
* _Bugfix_: Prevent crash on PHP 5.x when building the hyphenation trie.

= 4.2.0 - June 8, 2017 =
* _Feature_: Prevent line-breaks in numbered abbreviations (e.g. `ISO 9001`).
* _Feature_: Added new hook `typo_php_typography_caching_enabled` to disable object caching for very resource-starved environments.
* _Change_: Core API refactored and minimum PHP version increased to 5.4.0.
* _Change_: Updated hyphenation patterns:
  - German
  - German (Traditional)
  - Latin
  - Latin (Liturgical)
* _Change:_ Updated list of valid top-level domains.
