<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2024 Peter Putzer.
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

use WP_Typography\Implementation;

/**
 * Admin and frontend integrations for WooCommerce.
 *
 * @since  5.3.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class WooCommerce_Integration implements Plugin_Integration {

	/**
	 * The plugin API.
	 *
	 * @since 5.7.0 Renamed to $api.
	 *
	 * @var \WP_Typography
	 */
	private \WP_Typography $api;

	/**
	 * Creates a new integration.
	 *
	 * @since 5.7.0
	 *
	 * @param Implementation $api     The core API.
	 */
	public function __construct( Implementation $api ) {
		$this->api = $api;
	}

	/**
	 * Check if the ACF integration should be activated.
	 *
	 * @return bool
	 */
	public function check(): bool {
		return \class_exists( 'WooCommerce' );
	}

	/**
	 * Activate the integration.
	 *
	 * @since 5.7.0 Parameter $plugin removed.
	 */
	public function run(): void {
		// Nothing to do.
	}

	/**
	 * Retrieves the identifying tag for the frontend content filters.
	 *
	 * @return string
	 */
	public function get_filter_tag(): string {
		return 'woocommerce';
	}

	/**
	 * Enables frontend content filters.
	 *
	 * @param int $priority The filter priority.
	 */
	public function enable_content_filters( int $priority ): void {

		// Page descriptions.
		\add_filter( 'woocommerce_format_content', [ $this->api, 'process' ], $priority );

		// Shop notices.
		\add_filter( 'woocommerce_add_error',      [ $this->api, 'process' ], $priority );
		\add_filter( 'woocommerce_add_success',    [ $this->api, 'process' ], $priority );
		\add_filter( 'woocommerce_add_notice',     [ $this->api, 'process' ], $priority );

		// Demo store banner.
		\add_filter( 'woocommerce_demo_store',     [ $this->api, 'process' ], $priority );
	}
}
