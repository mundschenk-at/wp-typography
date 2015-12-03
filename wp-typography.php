<?php

/*
	Plugin Name: wp-Typography
	Plugin URI: https://code.mundschenk.at/wp-typography/
	Description: Improve your web typography with: (1) hyphenation &mdash; over 40 languages supported, (2) Space control, includes: widow protection, gluing values to units, and forced internal wrapping of long URLs & email addresses, (3) Intelligent character replacement, including smart handling of: quote marks, dashes, ellipses, trademarks, math symbols, fractions, and ordinal suffixes, and (4) CSS hooks for styling: ampersands, uppercase words, numbers,  initial quotes &amp; guillemets.
	Author: Peter Putzer
	Author URI: https://code.mundschenk.at
	Version: 3.0.1
	License: GNU General Public License v2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
	Text Domain: wp-typography
	Domain Path: /translations

 	Copyright 2014-2015 Peter Putzer.

 	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License,
	version 2 as published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

	***

	Copyright 2009, KINGdesk, LLC. Licensed under the GNU General Public License 2.0.
	If you use, modify and/or redistribute this software, you must leave the KINGdesk,
	LLC copyright information, the request for a link to http://kingdesk.com, and the
	web design services contact information unchanged. If you redistribute this software,
	or any derivative, it must be released under the GNU General Public License 2.0.
	This program is distributed without warranty (implied or otherwise) of suitability
	for any particular purpose. See the GNU General Public License for full license
	terms <http://creativecommons.org/licenses/GPL/2.0/>.

	WE DON'T WANT YOUR MONEY: NO TIPS NECESSARY!  If you enjoy this plugin, a link to
	http://kingdesk.com from your website would be appreciated. For web design services,
	please contact jeff@kingdesk.com.

	Portions of this plugin are inspired by:
	 	Christian Metts - href="http://code.google.com/p/typogrify/
		Hamish Macpherson - http://www.hamstu.com/
*/

/**
 * Load the plugin after checking for the necessary PHP version.
 *
 * It's necessary to do this here because main class relies on namespaces.
 */
function run_wp_typography() {
	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
		load_plugin_textdomain( 'wp-typography', false, dirname( plugin_basename( __FILE__ ) ) . '/translations/' );
		add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . __('wp-Typography requires PHP 5.3 or later. Please upgrade your installation of PHP or deactivate wp-Typography.', 'wp-typography') ."</p></div>';" ) );
		return; // abort
	} else {
		require_once( plugin_dir_path( __FILE__ ) . 'class-wp-typography.php' );
		new WP_Typography( plugin_basename( __FILE__ ) );
	}
}
run_wp_typography();
