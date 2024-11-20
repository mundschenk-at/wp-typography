<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2023 Peter Putzer.
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

namespace WP_Typography;

use WP_Typography\Implementation;
use WP_Typography\Components\Plugin_Component;

/**
 * Registers and runs the various plugin components.
 *
 * @since  5.3.0
 * @since  5.9.0 Return type declarations added.
 */
class Plugin_Controller {

	/**
	 * The plugin API implementation.
	 *
	 * @var \WP_Typography
	 */
	private \WP_Typography $api;

	/**
	 * The plugin components in order of execution.
	 *
	 * @var Plugin_Component[]
	 */
	private array $plugin_components = [];

	/**
	 * Sets up a new plugin controller instance.
	 *
	 * @since 5.7.0 Component parameters replaced with factory-cofigured array.
	 *
	 * @param Implementation     $api        The core API.
	 * @param Plugin_Component[] $components An array of plugin components.
	 */
	public function __construct( Implementation $api, array $components ) {
		$this->api               = $api;
		$this->plugin_components = $components;
	}

	/**
	 * Starts the plugin for real.
	 */
	public function run(): void {
		// Set plugin singleton.
		\WP_Typography::set_instance( $this->api );

		// Run all the plugin components.
		foreach ( $this->plugin_components as $component ) {
			$component->run();
		}
	}
}
