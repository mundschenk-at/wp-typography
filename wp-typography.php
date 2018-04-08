<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2018 Peter Putzer.
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
 *  Version: 5.3.3
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *  Text Domain: wp-typography
 *  Domain Path: /translations
 *
 *  ***
 *
 *  Based on original work by KINGdesk, LLC.
 *
 *  Portions of this plugin are inspired by:
 *     Christian Metts - href="http://code.google.com/p/typogrify/
 *     Hamish Macpherson - http://www.hamstu.com/
 */

/**
 * Load requirements class in a PHP 5.2 compatible manner.
 */
require_once dirname( __FILE__ ) . '/vendor/mundschenk-at/check-wp-requirements/class-mundschenk-wp-requirements.php';

/**
 * Load the plugin after checking for the necessary PHP version.
 *
 * It's necessary to do this here because main class relies on namespaces.
 */
function run_wp_typography() {

	$requirements = new Mundschenk_WP_Requirements( 'wp-Typography', __FILE__, 'wp-typography', array(
		'php'       => '5.6.0',
		'multibyte' => true,
		'utf-8'     => true,
	) );

	if ( $requirements->check() ) {
		// Autoload the rest of our classes.
		require_once __DIR__ . '/vendor/autoload.php';

		// Create the plugin.
		$plugin = WP_Typography_Factory::get( __FILE__ )->create( 'WP_Typography\Plugin_Controller' );

		// Start the plugin for real.
		$plugin->run();
	}
}
run_wp_typography();
