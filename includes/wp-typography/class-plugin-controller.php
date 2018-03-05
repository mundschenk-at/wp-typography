<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018 Peter Putzer.
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

use WP_Typography\Components\Admin_Interface;
use WP_Typography\Components\Common;
use WP_Typography\Components\Multilingual_Support;
use WP_Typography\Components\Plugin_Component;
use WP_Typography\Components\Public_Interface;
use WP_Typography\Components\Setup;

use WP_Typography\Data_Storage\Cache;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Data_Storage\Transients;

/**
 * Registers and runs the various plugin components.
 *
 * @since 5.3.0
 */
class Plugin_Controller {

	/**
	 * The plugin API implementation.
	 *
	 * @var \WP_Typography\Implementation
	 */
	private $api;

	/**
	 * The plugin components in order of execution.
	 *
	 * @var Plugin_Component[]
	 */
	private $plugin_components = [];

	/**
	 * Sets up a new plugin controller instance.
	 *
	 * @param Implementation       $api         Required.
	 * @param Setup                $setup       Required.
	 * @param Common               $common      Required.
	 * @param Admin_Interface      $admin       Required.
	 * @param Public_Interface     $public_if   Required.
	 * @param Multilingual_Support $multi       Required.
	 */
	public function __construct( Implementation $api, Setup $setup, Common $common, Admin_Interface $admin, Public_Interface $public_if, Multilingual_Support $multi ) {
		// Basic set-up.
		$this->api = $api;

		// Initialize activation/deactivation handler.
		$this->plugin_components[] = $setup;

		// Initialize common component.
		$this->plugin_components[] = $common;

		// Initialize multilingual support.
		$this->plugin_components[] = $multi;

		// Initialize public interface handler.
		$this->plugin_components[] = $public_if;

		// Initialize admin interface handler.
		$this->plugin_components[] = $admin;
	}

	/**
	 * Starts the plugin for real.
	 */
	public function run() {
		// Set plugin singleton.
		\WP_Typography::set_instance( $this->api );

		// Run all the plugin components.
		foreach ( $this->plugin_components as $component ) {
			$component->run( $this->api );
		}
	}
}
