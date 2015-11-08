# wp-Typography #

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
* your installation of PHP 5 must include the [mbstring extension](http://us3.php.net/manual/en/mbstring.installation.php) (most do)
* text must be encoded UTF‐8
* all markup must be valid xHTML syntax, specifically:
    * every element must be closed
    * every attribute must have a value enclosed in quotes
    * tag names and attributes must be lowercase

wp-Typography can easily be ported to any other PHP based content management system. The subdirectory `php-typography` assembles all typographic functionality (without any WordPress specific code) in an object oriented format that is ready for WordPress independent use.

View the [wp-Typography homepage](https://code.mundschenk.at/wp-typography/ "wp-Typography Homepage") for more information.

## Installation ##

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

## Frequently Asked Questions ##

FAQs are maintained at the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/ "wp-Typography FAQs").

Three questions come up so frequently, we will republish their answers here:

### Will this plu­gin slow my page load­ing times? ###

Yes. Use [WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/).

### This plugin breaks post title links.  What gives? ###

More likely than not, your WordPress theme is using an improper function to set the title attribute of your heading's link.  It is probably using the `the_title()` function, which delivers the post title *after* filtering.  It should be using `the_title_attribute()` which delivers the post title *before* filtering.  Change out this function throughout your theme when it is used inside of an HTML tag, and the problem should go away.

If you are uncomfortable editing your theme's code, you may alternatively go to the wp-Typography settings page in your admin panel and add `h1` and `h2` to the "Do not process the content of these HTML elements:" field.  This will disable typographic processing within improperly designed page title links <em>and</em> page titles.

There is an error in the core of WordPress (as of version 2.8.1) that uses the wrong function to provide post titles for secondary RSS feeds (like RSS feeds for single page comments or categories.  This error will return any HTML tags used for CSS Hooks or character styling, and the tags will be visible when someone clicks on the RSS icon in the address bar of their browser.  If this bothers you, your only option (until WordPress corrects this error) is to disable all typographic processing in your page titles as described in the paragraph above. Updates on this WordPress bug may be followed [here](https://core.trac.wordpress.org/ticket/10410).

### Does this plugin work with wp-Typogrify? ###

This plugin is an official replacement for the [wp-Typogrify plugin](http://wordpress.org/extend/plugins/wp-typogrify/).  Please uninstall wp-Typogrify and install wp-Typography in its place.

Remember, many more FAQs are are addressed the [wp-Typography website](https://code.mundschenk.at/wp-typography/frequently-asked-questions/"wp-Typography FAQs").


## Screenshots ##

### wp-Typography Administrative Settings ###
![Imawp-Typography administrative settings](screenshot-1.png)

## Changelog ##

A detailed release changelog can be found on the [wp-Typography website](https://code.mundschenk.at/wp-typography/changes/).