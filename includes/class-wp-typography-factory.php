<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2020 Peter Putzer.
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

use WP_Typography\Implementation;
use WP_Typography\Components;
use WP_Typography\Integration;
use WP_Typography\Plugin_Controller;
use WP_Typography\Integration\Container as Integrations;
use WP_Typography\Settings\Basic_Locale_Settings;
use WP_Typography\Settings\Locale_Settings;

use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\Settings\Quote_Style;

/**
 * A factory for creating WP_Typography instances via dependency injection.
 *
 * @since 5.1.0
 * @since 5.7.0 Class made concrete.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class WP_Typography_Factory extends Dice {

	// Common rules components.
	const SHARED = [ 'shared' => true ];

	/**
	 * The factory instance.
	 *
	 * @var Dice
	 */
	private static $factory;

	/**
	 * Creates a new instance.
	 *
	 * @since 5.7.0
	 */
	protected function __construct() {
		// Add rules.
		foreach ( $this->get_rules() as $classname => $rule ) {
			$this->addRule( $classname, $rule );
		}
	}

	/**
	 * Retrieves a factory set up for creating WP_Typography instances.
	 *
	 * @since 5.6.0 Parameter $full_plugin_path removed.
	 *
	 * @return WP_Typography_Factory
	 */
	public static function get() {
		if ( ! isset( self::$factory ) ) {

			// Create factory.
			self::$factory = new static();
		}

		return self::$factory;
	}

	/**
	 * Retrieves the rules for setting up the plugin.
	 *
	 * @since 5.7.0
	 *
	 * @return array
	 */
	protected function get_rules() {
		return [
			// Shared helpers.
			Cache::class                           => self::SHARED,
			Transients::class                      => self::SHARED,
			Options::class                         => self::SHARED,

			// API implementation.
			'substitutions'                        => [
				\WP_Typography::class => [
					'instance' => \WP_Typography\Implementation::class,
				],
			],
			\WP_Typography::class                  => [
				'constructParams' => [ $this->get_plugin_version( \WP_TYPOGRAPHY_PLUGIN_FILE ) ],
			],

			// The plugin controller.
			Plugin_Controller::class               => [
				'constructParams' => [ $this->get_components() ],
			],

			// Components.
			Components\Plugin_Component::class     => self::SHARED,

			// Plugin integrations are also shared.
			Integrations::class                    => [
				'shared'          => true,
				'constructParams' => [ $this->get_plugin_integrations() ],
			],
			Integration\Plugin_Integration::class  => self::SHARED,

			// Supported locales.
			Components\Multilingual_Support::class => [
				'constructParams' => [ $this->get_supported_locales() ],
			],
			'$LocaleSwitzerland'                   => [
				'instanceOf'      => Basic_Locale_Settings::class,
				'constructParams' => [
					[ 'de', 'it', 'fr' ],           // Languages.
					[ 'CH' ],                       // Countries.
					[],                             // Modifiers.
					Dash_Style::INTERNATIONAL,
					Quote_Style::DOUBLE_GUILLEMETS, // Primary quotes.
					Quote_Style::SINGLE_GUILLEMETS, // Secondary quotes.
					false,                          // French punctuation.
				],
			],
			'$LocaleUnitedStates'                  => [
				'instanceOf'      => Basic_Locale_Settings::class,
				'constructParams' => [
					[ 'en' ],                   // Languages.
					[ 'US' ],                   // Countries.
					[],                         // Modifiers.
					Dash_Style::TRADITIONAL_US,
					Quote_Style::DOUBLE_CURLED, // Primary quotes.
					Quote_Style::SINGLE_CURLED, // Secondary quotes.
					false,                      // French punctuation.
				],
			],
			'$LocaleUnitedKingdom'                 => [
				'instanceOf'      => Basic_Locale_Settings::class,
				'constructParams' => [
					[ 'en' ],                   // Languages.
					[ 'UK' ],                   // Countries.
					[],                         // Modifiers.
					Dash_Style::INTERNATIONAL,
					Quote_Style::SINGLE_CURLED, // Primary quotes.
					Quote_Style::DOUBLE_CURLED, // Secondary quotes.
					false,                      // French punctuation.
				],
			],
			'$LocaleGerman'                        => [
				'instanceOf'      => Basic_Locale_Settings::class,
				'constructParams' => [
					[ 'de' ],                           // Languages.
					[],                                 // Countries.
					[],                                 // Modifiers.
					Dash_Style::INTERNATIONAL,
					Quote_Style::DOUBLE_LOW_9_REVERSED, // Primary quotes.
					Quote_Style::SINGLE_LOW_9_REVERSED, // Secondary quotes.
					false,                              // French punctuation.
				],
			],
			'$LocaleFrench'                        => [
				'instanceOf'      => Basic_Locale_Settings::class,
				'constructParams' => [
					[ 'fr' ],                              // Languages.
					[],                                    // Countries.
					[],                                    // Modifiers.
					Dash_Style::INTERNATIONAL,
					Quote_Style::DOUBLE_GUILLEMETS_FRENCH, // Primary quotes.
					Quote_Style::DOUBLE_CURLED,            // Secondary quotes.
					true,                                  // French punctuation.
				],
			],
			'$LocaleDutch'                         => [
				'instanceOf'      => Basic_Locale_Settings::class,
				'constructParams' => [
					[ 'nl' ],                   // Languages.
					[],                         // Countries.
					[],                         // Modifiers.
					Dash_Style::INTERNATIONAL,
					Quote_Style::DOUBLE_CURLED, // Primary quotes.
					Quote_Style::SINGLE_CURLED, // Secondary quotes.
					false,                      // French punctuation.
				],
			],
			'$LocaleSinoJapanese'                  => [
				'instanceOf'      => Basic_Locale_Settings::class,
				'constructParams' => [
					[ 'ja', 'zh' ],                     // Languages.
					[],                                 // Countries.
					[],                                 // Modifiers.
					Dash_Style::INTERNATIONAL,
					Quote_Style::CORNER_BRACKETS,       // Primary quotes.
					Quote_Style::WHITE_CORNER_BRACKETS, // Secondary quotes.
					false,                              // French punctuation.
				],
			],
		];
	}

	/**
	 * Retrieves the plugin version.
	 *
	 * @since 5.7.0
	 *
	 * @param  string $plugin_file The full plugin path.
	 *
	 * @return string
	 */
	protected function get_plugin_version( $plugin_file ) {
		// Load version from plugin data.
		if ( ! \function_exists( 'get_plugin_data' ) ) {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return \get_plugin_data( $plugin_file, false, false )['Version'];
	}

	/**
	 * Retrieves the list of plugin components run during normal operations
	 * (i.e. not including the Uninstallation component).
	 *
	 * @since 5.7.0
	 *
	 * @return array {
	 *     An array of `Component` instances in `Dice` syntax.
	 *
	 *     @type array {
	 *         @type string $instance The classname.
	 *     }
	 * }
	 */
	protected function get_components() {
		return [
			[ 'instance' => Components\Setup::class ],
			[ 'instance' => Components\Common::class ],
			[ 'instance' => Components\Public_Interface::class ],
			[ 'instance' => Components\Admin_Interface::class ],
			[ 'instance' => Components\Multilingual_Support::class ],
		];
	}

	/**
	 * Retrieves a list of plugin integrations.
	 *
	 * @since 5.7.0
	 *
	 * @return array {
	 *     An array of `Plugin_Integration` instances in `Dice` syntax.
	 *
	 *     @type array {
	 *         @type string $instance The classname.
	 *     }
	 * }
	 */
	protected function get_plugin_integrations() {
		return [
			[ 'instance' => Integration\ACF_Integration::class ],
			[ 'instance' => Integration\WooCommerce_Integration::class ],
		];
	}

	/**
	 * Retrieves a list of plugin integrations.
	 *
	 * @since 5.7.0
	 *
	 * @return array {
	 *     An array of `Locale_Settings` instances in `Dice` syntax.
	 *
	 *     @type array {
	 *         @type string $instance The classname.
	 *     }
	 * }
	 */
	protected function get_supported_locales() {
		return [
			[ 'instance' => '$LocaleSwitzerland' ],
			[ 'instance' => '$LocaleUnitedStates' ],
			[ 'instance' => '$LocaleUnitedKingdom' ],
			[ 'instance' => '$LocaleGerman' ],
			[ 'instance' => '$LocaleFrench' ],
			[ 'instance' => '$LocaleDutch' ],
			[ 'instance' => '$LocaleSinoJapanese' ],
		];
	}
}
