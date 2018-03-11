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

use Dice\Dice;

use WP_Typography\Data_Storage\Cache;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Data_Storage\Transients;

use WP_Typography\Components\Admin_Interface;
use WP_Typography\Components\Public_Interface;
use WP_Typography\Components\Setup;

use WP_Typography\Integration\Container as Integrations;
use WP_Typography\Integration\ACF_Integration;
use WP_Typography\Integration\WooCommerce_Integration;

/**
 * A factory for creating WP_Typography instances via dependency injection.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
abstract class WP_Typography_Factory {

	/**
	 * The factory instance.
	 *
	 * @var Dice
	 */
	private static $factory;

	/**
	 * Retrieves a factory set up for creating WP_Typography instances.
	 *
	 * @param string $full_plugin_path The full path to the main plugin file (i.e. __FILE__).
	 *
	 * @return Dice
	 */
	public static function get( $full_plugin_path ) {
		if ( ! isset( self::$factory ) ) {
			self::$factory = new Dice();

			// Shared helpers.
			self::$factory->addRule( Cache::class, [
				'shared' => true,
			] );
			self::$factory->addRule( Transients::class, [
				'shared' => true,
			] );
			self::$factory->addRule( Options::class, [
				'shared' => true,
			] );

			// Plugin integrations are also shared.
			self::$factory->addRule( Integrations::class, [
				'shared'          => true,
				'constructParams' => [
					[
						new ACF_Integration(),
						new WooCommerce_Integration(),
					],
				],
			] );

			// API implementation.
			self::$factory->addRule( 'substitutions', [
				\WP_Typography::class => [
					'instance' => \WP_Typography\Implementation::class,
				],
			] );

			// Load version from plugin data.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			self::$factory->addRule( WP_Typography::class, [
				'constructParams' => [ get_plugin_data( $full_plugin_path, false, false )['Version'] ],
			] );

			// Additional parameters for components.
			$plugin_basename = \plugin_basename( $full_plugin_path );
			self::$factory->addRule( Admin_Interface::class, [
				'constructParams' => [ $plugin_basename, $full_plugin_path ],
			] );
			self::$factory->addRule( Public_Interface::class, [
				'constructParams' => [ $plugin_basename ],
			] );
			self::$factory->addRule( Setup::class, [
				'constructParams' => [ $full_plugin_path ],
			] );
		}

		return self::$factory;
	}
}
