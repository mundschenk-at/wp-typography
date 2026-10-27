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

namespace WP_Typography\Components;

use WP_Typography\Data_Storage\Options;
use WP_Typography\Implementation;
use WP_Typography\Integration\Container as Integrations;

/**
 * Common functionality necessary for both public and admin interfaces.
 *
 * @since  5.1.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Common implements Plugin_Component {

	/**
	 * The plugin API.
	 *
	 * @since 5.7.0 Renamed to $api.
	 * @since 5.10.0 Type changed to \WP_Typography like other Plugin_Components.
	 *
	 * @var \WP_Typography
	 */
	private \WP_Typography $api;

	/**
	 * An abstraction of the WordPress Options API.
	 *
	 * @var Options
	 */
	private Options $options;

	/**
	 * The plugin integrations.

	 * @var Integrations
	 */
	private Integrations $integrations;

	/**
	 * Create a new instance.
	 *
	 * @since 5.7.0 Parameter $api added.
	 *
	 * @param Implementation $api          The core API.
	 * @param Options        $options      The Options API handler.
	 * @param Integrations   $integrations Available plugin integrations.
	 */
	public function __construct( Implementation $api, Options $options, Integrations $integrations ) {
		$this->api          = $api;
		$this->options      = $options;
		$this->integrations = $integrations;
	}

	/**
	 * Registers the de-/activation/uninstall hooks for the plugin.
	 *
	 * @since 5.7.0 Parameter $plugin removed.
	 */
	public function run(): void {
		// Load settings.
		\add_action( 'init', [ $this, 'init' ] );

		// Initialize plugin integrations.
		\add_action( 'plugins_loaded', [ $this->integrations, 'activate' ] );
	}


	/**
	 * Restore default options or clear cache if requested.
	 */
	public function init(): void {

		// Restore defaults if necessary.
		if ( $this->options->get( Options::RESTORE_DEFAULTS ) ) {  // any truthy value will do.
			$this->api->set_default_options( true );
		}

		// Clear cache if necessary.
		if ( $this->options->get( Options::CLEAR_CACHE ) ) {  // any truthy value will do.
			$this->api->clear_cache();
		}
	}
}
