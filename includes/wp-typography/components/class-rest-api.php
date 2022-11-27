<?php
/**
 * This file is part of wp-Typography.
 *
 * Copyright 2020-2022 Peter Putzer.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * ***
 *
 * @package mundschenk-at/wp-typography
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Components;

/**
 * The component integrating with the REST API (necessary for block editor support).
 *
 * @since  5.7.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class REST_API implements Plugin_Component {

	/**
	 * A post meta key for storing the enable/disable status.
	 *
	 * @var string
	 */
	const WP_TYPOGRAPHY_DISABLED_META_KEY = 'wp_typography_post_enhancements_disabled';

	/**
	 * Set up the various hooks for the REST API.
	 */
	public function run() : void {
		// Register and enqueue sidebar.
		\add_action( 'init', [ $this, 'register_meta_fields' ] );
	}

	/**
	 * Registers necessary meta fields the the REST API.
	 */
	public function register_meta_fields() : void {
		\register_post_meta(
			'', // Enable field for all post types.
			self::WP_TYPOGRAPHY_DISABLED_META_KEY,
			[
				'type'         => 'boolean',
				'single'       => true,
				'description'  => "Whether wp-Typography's typographic enhancements should be applied to the post.",
				'show_in_rest' => true,
			]
		);
	}
}
