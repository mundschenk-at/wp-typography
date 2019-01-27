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

namespace WP_Typography\Integration;

/**
 * A registry for plugin integrations.
 *
 * @since      5.3.0
 * @author     Peter Putzer <github@mundschenk.at>
 */
class Container {

	/**
	 * The plugin instance used for setting transients.
	 *
	 * @var \WP_Typography
	 */
	private $plugin;

	/**
	 * An array of plugin integration instances.
	 *
	 * @var Plugin_Integration[]
	 */
	private $integrations = [];

	/**
	 * An array of activated plugin integrations.
	 *
	 * @var Plugin_Integration[]
	 */
	private $active_integrations = [];

	/**
	 * Creates a new instance.
	 *
	 * @param Plugin_Integration[] $integrations An array of plugin integration instances.
	 */
	public function __construct( array $integrations ) {
		$this->integrations = $integrations;
	}

	/**
	 * Activate all applicable plugin integrations.
	 */
	public function activate() {
		foreach ( $this->integrations as $integration ) {
			if ( $integration->check() ) {
				$this->active_integrations[] = $integration;
				$integration->run( $this->plugin );
			}
		}

		// No need to restrict these to the frontend, that's already done by Public_Interface.
		\add_filter( 'typo_content_filters', [ $this, 'get_content_filters' ] );
	}

	/**
	 * Start up enabled integrations.
	 *
	 * @param \WP_Typography $plugin The plugin API instance.
	 */
	public function run( \WP_Typography $plugin ) {
		$this->plugin = $plugin;

		\add_action( 'plugins_loaded', [ $this, 'activate' ] );
	}

	/**
	 * Adds the additional content filter enabling functions, indexed by tag.
	 *
	 * @param array $filters The filters in the form $tag => $callable.
	 *
	 * @return array
	 */
	public function get_content_filters( array $filters ) {
		foreach ( $this->active_integrations as $integration ) {
			$filters[ $integration->get_filter_tag() ] = [ $integration, 'enable_content_filters' ];
		}

		return $filters;
	}
}
