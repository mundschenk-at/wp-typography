<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

use \Dice\Dice;

use \WP_Typography\Admin;
use \WP_Typography\Cache;
use \WP_Typography\Options;
use \WP_Typography\Public_Interface;
use \WP_Typography\Setup;
use \WP_Typography\Transients;
use \WP_Typography\Settings\Multilingual;

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

			// Additional parameters for compoennts.
			$plugin_basename = \plugin_basename( $full_plugin_path );
			self::$factory->addRule( Admin::class, [
				'constructParams' => [ $plugin_basename ],
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
