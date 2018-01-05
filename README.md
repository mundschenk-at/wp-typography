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

*   the host server must run PHP 5.6.0 or later
*   your installation of PHP must include the [mbstring extension](http://us3.php.net/manual/en/mbstring.installation.php) (most do)
*   text must be encoded UTF‐8

wp-Typography can easily be ported to any other PHP-based content management system. The Composer package [`mundschenk-at/php-typography`](https://github.com/mundschenk-at/php-typography) assembles all typographic functionality (without any WordPress-specific code) in an object oriented format that is ready use.

View the [wp-Typography homepage](https://code.mundschenk.at/wp-typography/ "wp-Typography Homepage") for more information.

## Frequently Asked Questions ##

FAQs are maintained at the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/ "wp-Typography FAQs").

Two questions come up so frequently, we will republish their answers here:

### Will this plu­gin slow my page load­ing times? ###

Yes. Use [WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/).

### This plugin breaks post title links.  What gives? ###

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

## Changelog ##

A detailed release changelog can be found on the [wp-Typography website](https://code.mundschenk.at/wp-typography/changes/).
