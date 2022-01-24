# wp-Typography #

[![Build Status](https://scrutinizer-ci.com/g/mundschenk-at/wp-typography/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mundschenk-at/wp-typography/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/mundschenk-at/wp-typography/v/stable)](https://packagist.org/packages/mundschenk-at/wp-typography)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mundschenk-at/wp-typography/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mundschenk-at/wp-typography/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mundschenk-at/wp-typography/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mundschenk-at/wp-typography/?branch=master)
[![License](https://poser.pugx.org/mundschenk-at/wp-typography/license)](https://packagist.org/packages/mundschenk-at/wp-typography)

Improve your web typography with:

*   Hyphenation &mdash; [over 70 languages supported](https://code.mundschenk.at/wp-typography/frequently-asked-questions/#faq-what-hyphenation-language-patterns-are-included)

*   Space control, including:
    -   widow protection
    -   gluing values to units
    -   forced internal wrapping of long URLs & email addresses

*   Intelligent character replacement, including smart handling of:
    -   quote marks
    -   dashes
    -   ellipses
    -   trademarks, copyright & service marks
    -   math symbols
    -   fractions
    -   ordinal suffixes

*   CSS hooks for styling:
    -   ampersands,
    -   uppercase words,
    -   numbers,
    -   initial quotes & guillemets.

wp‐Typography has the following requirements:

*   The host server must run PHP 7.2.0 or later,
*   your installation of PHP must include the following PHP extensions (most do):
    -   [mbstring](https://www.php.net/manual/en/mbstring.installation.php),
    -   [DOM](https://www.php.net/manual/en/dom.installation.php), and
*   text must be encoded in UTF‐8.


wp-Typography can easily be ported to any other PHP-based content management system. The Composer package [`mundschenk-at/php-typography`](https://github.com/mundschenk-at/php-typography) assembles all typographic functionality (without any WordPress-specific code) in an object oriented format that is ready use.

View the [wp-Typography homepage](https://code.mundschenk.at/wp-typography/ "wp-Typography Homepage") for more information.


## Frequently Asked Questions ##

FAQs are maintained at the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/ "wp-Typography FAQs").

Three questions come up so frequently, we will republish their answers here:

### Will this plu­gin slow my page load­ing times? ###

Maybe. For best performance, use a [persistent object cache](https://wptavern.com/persistent-object-caching) plugin like [WP Redis](https://wordpress.org/plugins/wp-redis/).

### This plugin breaks post title links.  What gives? ###

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

### What are the privacy implications of using the plugin? ###

wp-Typography does not store, transmit or otherwise process personal data as such. It does cache the content of the site's posts. If necessary, you can clear this cache from the plugin's settings page.


## Changelog ##

A detailed release changelog can be found on the [wp-Typography website](https://code.mundschenk.at/wp-typography/changes/).
