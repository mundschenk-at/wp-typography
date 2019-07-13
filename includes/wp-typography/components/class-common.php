<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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
use WP_Typography\Integration\Container as Integrations;
use WP_Typography\Settings\Plugin_Configuration as Config;

/**
 * Common functionality necessary for both public and admin interfaces.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Common implements Plugin_Component {

	/**
	 * The plugin object.
	 *
	 * @var      \WP_Typography $plugin The main plugin class instance.
	 */
	private $plugin;

	/**
	 * An abstraction of the WordPress Options API.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * The plugin integrations.

	 * @var Integrations
	 */
	private $integrations;

	/**
	 * Create a new instace of WP_Typography\Setup.
	 *
	 * @param Options      $options      The Options API handler.
	 * @param Integrations $integrations Available plugin integrations.
	 */
	public function __construct( Options $options, Integrations $integrations ) {
		$this->options      = $options;
		$this->integrations = $integrations;
	}

	/**
	 * Registers the de-/activation/uninstall hooks for the plugin.
	 *
	 * @param \WP_Typography $plugin The plugin instance.
	 */
	public function run( \WP_Typography $plugin ) {
		$this->plugin = $plugin;

		// Load settings.
		\add_action( 'init', [ $this, 'init' ] );

		// Initialize plugin integrations.
		$this->integrations->run( $plugin );
	}


	/**
	 * Restore default options or clear cache if requested.
	 */
	public function init() {

		// Restore defaults if necessary.
		if ( $this->options->get( Options::RESTORE_DEFAULTS ) ) {  // any truthy value will do.
			$this->plugin->set_default_options( true );
		}

		// Clear cache if necessary.
		if ( $this->options->get( Options::CLEAR_CACHE ) ) {  // any truthy value will do.
			$this->plugin->clear_cache();
		}
	}
}
