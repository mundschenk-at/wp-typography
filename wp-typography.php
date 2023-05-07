<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2023 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
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
 *  @package mundschenk-at/wp-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 *
 *  @wordpress-plugin
 *  Plugin Name: wp-Typography
 *  Plugin URI: https://code.mundschenk.at/wp-typography/
 *  Description: Improve your web typography with: hyphenation, space control, intelligent character replacement, and CSS hooks.
 *  Author: Peter Putzer
 *  Author URI: https://code.mundschenk.at
 *  Version: 5.9.2-alpha.1
 *  Requires at least: 5.3
 *  Requires PHP: 7.4
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *  Text Domain: wp-typography
 *
 *  ***
 *
 *  Based on original work by KINGdesk, LLC.
 *
 *  Portions of this plugin are inspired by:
 *     Christian Metts - href="http://code.google.com/p/typogrify/
 *     Hamish Macpherson - http://www.hamstu.com/
 */

namespace WP_Typography;

// Don't do anything if called directly.
if ( ! \defined( 'ABSPATH' ) || ! \defined( 'WPINC' ) ) {
	die();
}

// Make plugin file path available globally.
if ( ! \defined( 'WP_TYPOGRAPHY_PLUGIN_FILE' ) ) {
	\define( 'WP_TYPOGRAPHY_PLUGIN_FILE', __FILE__ );
}
if ( ! \defined( 'WP_TYPOGRAPHY_PLUGIN_PATH' ) ) {
	\define( 'WP_TYPOGRAPHY_PLUGIN_PATH', __DIR__ );
}

// Initialize autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Load the plugin after checking for the necessary PHP version.
 *
 * It's necessary to do this here because main class relies on namespaces.
 */
function wp_typography_run() {
	// Validate the requirements.
	if ( ( new Requirements() )->check() ) {
		// Create the plugin.
		$plugin = Factory::get()->create( Plugin_Controller::class );

		// Start the plugin for real.
		$plugin->run();
	}
}
wp_typography_run();
