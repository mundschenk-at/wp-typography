<?php
/*
	Plugin Name: wp-Typography
	Plugin URI: http://kingdesk.com/projects/wp-typography
	Description: Improve your web typography with: (1) hyphenation &mdash; over 40 languages supported, (2) Space control, includes: widow protection, gluing values to units, and forced internal wrapping of long URLs & email addresses, (3) Intelligent character replacement, including smart handling of: quote marks, dashes, ellipses, trademarks, math symbols, fractions, and ordinal suffixes, and (4) CSS hooks for styling: ampersands, uppercase words, numbers,  initial quotes &amp; guillemets.
	Author: Jeffrey D. King
	Author URI: http://kingdesk.com/
	Version: 1.22
	
		Copyright 2009, KINGdesk, LLC. Licensed under the GNU General Public License 2.0. If you use, modify and/or redistribute this software, you must leave the KINGdesk, LLC copyright information, the request for a link to http://kingdesk.com, and the web design services contact information unchanged. If you redistribute this software, or any derivative, it must be released under the GNU General Public License 2.0. This program is distributed without warranty (implied or otherwise) of suitability for any particular purpose. See the GNU General Public License for full license terms <http://creativecommons.org/licenses/GPL/2.0/>.

		WE DON'T WANT YOUR MONEY: NO TIPS NECESSARY!  If you enjoy this plugin, a link to http://kingdesk.com from your website would be appreciated.

		For web design services, please contact jeff@kingdesk.com.
*/


/*
Portions of this plugin are inspired by:
 	Christian Metts - href="http://code.google.com/p/typogrify/
	Hamish Macpherson - http://www.hamstu.com/
*/

require_once(WP_PLUGIN_DIR.'/wp-typography/class-wpTypography.php');
new wpTypography;