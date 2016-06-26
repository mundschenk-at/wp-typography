<?php
/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2016 Peter Putzer.
 *	Copyright 2012-2013 Marie Hogebrandt.
 *	Coypright 2009-2011 KINGdesk, LLC.
 *
 *	This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 *  @package wpTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 *
 *  @wordpress-plugin
 *  Plugin Name: wp-Typography
 *  Plugin URI: https://code.mundschenk.at/wp-typography/
 *  Description: Improve your web typography with: hyphenation, space control, intelligent character replacement, and CSS hooks.
 *  Author: Peter Putzer
 *  Author URI: https://code.mundschenk.at
 *  Version: 3.3.0
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *  Text Domain: wp-typography
 *  Domain Path: /translations
 *
 *  ***
 *  Portions of this plugin are inspired by:
 *	   Christian Metts - href="http://code.google.com/p/typogrify/
 *     Hamish Macpherson - http://www.hamstu.com/
 */

/**
 * Autoload our classes
 */
require_once dirname( __FILE__ ) . '/includes/wp-typography-autoload.php';

/**
 * Load the plugin after checking for the necessary PHP version.
 *
 * It's necessary to do this here because main class relies on namespaces.
 */
function run_wp_typography() {

	$requirements = new WP_Typography_Requirements( 'wp-Typography', plugin_basename( __FILE__ ) );

	if ( $requirements->check() ) {
		/**
		 * Load version from plugin data
		 */
		if ( ! function_exists( 'get_plugin_data' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__, false, false );
		$version = $plugin_data['Version'];

		// Create the plugin.
		$plugin = WP_Typography::_get_instance( $version, plugin_basename( __FILE__ ) );

		// Register activation & deactivation hooks.
		$setup = new WP_Typography_Setup( 'wp-typography', $plugin );
		$setup->register( __FILE__ );

		// Start the plugin for real.
		$plugin->run();
	}
}
run_wp_typography();
