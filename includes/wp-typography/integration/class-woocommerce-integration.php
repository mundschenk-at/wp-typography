<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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
 */

namespace WP_Typography\Integration;

/**
 * Admin and frontend integrations for WooCommerce.
 *
 * @since      5.3.0
 * @author     Peter Putzer <github@mundschenk.at>
 */
class WooCommerce_Integration implements Plugin_Integration {

	/**
	 * The plugin API instance.
	 *
	 * @var \WP_Typography
	 */
	private $plugin;

	/**
	 * Check if the ACF integration should be activated.
	 *
	 * @return bool
	 */
	public function check() {
		return \class_exists( 'WooCommerce' );
	}

	/**
	 * Activate the integration.
	 *
	 * @param \WP_Typography $plugin The plugin object.
	 */
	public function run( \WP_Typography $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Retrieves the identifying tag for the frontend content filters.
	 *
	 * @return string
	 */
	public function get_filter_tag() {
		return 'woocommerce';
	}

	/**
	 * Enables frontend content filters.
	 *
	 * @param int $priority The filter priority.
	 */
	public function enable_content_filters( $priority ) {

		// Page descriptions.
		\add_filter( 'woocommerce_format_content', [ $this->plugin, 'process' ], $priority );

		// Shop notices.
		\add_filter( 'woocommerce_add_error',      [ $this->plugin, 'process' ], $priority );
		\add_filter( 'woocommerce_add_success',    [ $this->plugin, 'process' ], $priority );
		\add_filter( 'woocommerce_add_notice',     [ $this->plugin, 'process' ], $priority );

		// Demo store banner.
		\add_filter( 'woocommerce_demo_store',     [ $this->plugin, 'process' ], $priority );
	}
}
