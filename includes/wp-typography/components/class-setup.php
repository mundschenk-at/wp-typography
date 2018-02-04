<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
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
use WP_Typography\Settings\Plugin_Configuration as Config;

/**
 * Fired during plugin de-/activation and uninstall.
 *
 * This class defines all code necessary to run during the plugin's setup and teardown.
 *
 * @since 3.1.0
 * @since 5.1.0 Now implements the Plugin_Component interface.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Setup implements Plugin_Component {

	/**
	 * Just in case the Options prefix changes in the future.
	 *
	 * @var string
	 */
	const LEGACY_OPTIONS_PREFIX = 'typo';

	/**
	 * Special option value for detecting non-existing options during upgrades.
	 *
	 * @var string
	 */
	const UPGRADING = 'UPGRADING_WP_TYPOGRAPHY';

	/**
	 * The full path to the main plugin file.
	 *
	 * @since 5.1.0
	 * @var   string
	 */
	private $plugin_file;

	/**
	 * The plugin object.
	 *
	 * @since    3.1.0
	 * @var      \WP_Typography $plugin The main plugin class instance.
	 */
	private $plugin;

	/**
	 * An abstraction of the WordPress Options API.
	 *
	 * @since 5.1.0
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Create a new instace of WP_Typography\Setup.
	 *
	 * @param string  $plugin_path The full path to the main plugin file.
	 * @param Options $options     The Options API handler.
	 */
	public function __construct( $plugin_path, Options $options ) {
		$this->plugin_file = $plugin_path;
		$this->options     = $options;
	}

	/**
	 * Registers the de-/activation/uninstall hooks for the plugin.
	 *
	 * @param \WP_Typography $plugin The plugin instance.
	 */
	public function run( \WP_Typography $plugin ) {
		$this->plugin = $plugin;

		// Register various hooks.
		\register_activation_hook( $this->plugin_file,   [ $this,     'activate' ] );
		\register_deactivation_hook( $this->plugin_file, [ $this,     'deactivate' ] );
		\register_uninstall_hook( $this->plugin_file,    [ __CLASS__, 'uninstall' ] );

		// Run necessary upgrade actions.
		\add_action( 'plugins_loaded', [ $this, 'plugin_update_check' ] );
	}

	/**
	 * Fired during plugin activation.
	 *
	 * @since 3.1.0
	 */
	public function activate() {
		// Load default values for any new options and clear the cache.
		$this->plugin->get_config();
		$this->plugin->set_default_options(); // After get_config, otherwhise previous options are overwritten.
		$this->plugin->clear_cache();
	}

	/**
	 * Run necessary upgrade actions.
	 *
	 * @since 5.1.0
	 */
	public function plugin_update_check() {
		$installed_version = $this->options->get( Options::INSTALLED_VERSION );

		if ( $this->plugin->get_version() !== $installed_version ) {
			$this->plugin_updated( $installed_version );
		}
	}

	/**
	 * Upgrade plugin data.
	 *
	 * @param string $previous_version The version we are upgrading from.
	 */
	protected function plugin_updated( $previous_version ) {

		// Upgrade from version 3.0.0 or lower.
		if ( \version_compare( $previous_version, '3.1.0-beta.2', '<' ) ) {
			$this->upgrade_options_3_1();
		}
		if ( \version_compare( $previous_version, '3.2.0-beta.1', '<' ) ) {
			$this->upgrade_options_3_2();
		}
		if ( \version_compare( $previous_version, '3.3.0-alpha.2', '<' ) ) {
			$this->upgrade_options_3_3();
		}
		if ( \version_compare( $previous_version, '3.5.0-alpha.1', '<' ) ) {
			$this->upgrade_options_3_5();
		}

		// Upgrade from version 5.0.0 or lower.
		if ( \version_compare( $previous_version, '5.1.0-alpha.2', '<' ) ) {
			$this->upgrade_options_5_1();
		}

		// Update installed version information.
		$this->set_installed_version();
	}

	/**
	 * Upgrade routine for installations with versions below 3.1.0 (or unknown versions).
	 *
	 * @since 5.1.0
	 */
	protected function upgrade_options_3_1() {
		foreach ( $this->plugin->get_default_options() as $option_name => $option ) {
			$old_option = $this->get_old_option_name( self::LEGACY_OPTIONS_PREFIX . "_{$option_name}" );
			$old_value  = $this->options->get( $old_option, self::UPGRADING, true );

			if ( self::UPGRADING !== $old_value ) {
				// Change to new option layout (but still using individual options).
				$this->options->set( $option_name, $old_value );
				$this->options->delete( $old_option, true );
			}
		}
	}

	/**
	 * Upgrade routine for installations with versions below 3.2.0.
	 *
	 * @since 5.1.0
	 */
	protected function upgrade_options_3_2() {
		$this->options->delete( 'typo_disable_caching', true );
	}

	/**
	 * Upgrade routine for installations with versions below 3.3.0.
	 *
	 * @since 5.1.0
	 */
	protected function upgrade_options_3_3() {
		$this->options->delete( 'typo_remove_ie6', true );
	}

	/**
	 * Upgrade routine for installations with versions below 3.5.0.
	 *
	 * @since 5.1.0
	 */
	protected function upgrade_options_3_5() {
		$this->options->delete( 'typo_enable_caching', true );
		$this->options->delete( 'typo_caching_limit', true );
	}

	/**
	 * Upgrade routine for installations with versions below 5.1.0.
	 *
	 * @since 5.1.0
	 */
	protected function upgrade_options_5_1() {
		$this->options->delete( 'typo_transient_keys', true );
		$this->options->delete( 'typo_cache_keys', true );

		$this->upgrade_options_to_array();
	}

	/**
	 * Move all old options to the new array.
	 *
	 * @since 5.1.0
	 */
	protected function upgrade_options_to_array() {
		$config = $this->plugin->get_default_options();

		foreach ( $config as $option_name => $default_value ) {
			$old_value = $this->options->get( $option_name, self::UPGRADING );

			if ( self::UPGRADING !== $old_value ) {
				$config[ $option_name ] = $old_value;

				$this->options->delete( $option_name );
			}
		}

		$this->options->set( Options::CONFIGURATION, $config );
	}

	/**
	 * Update installed version option.
	 *
	 * @since 5.1.0
	 */
	protected function set_installed_version() {
		$this->options->set( Options::INSTALLED_VERSION, $this->plugin->get_version() );
	}


	/**
	 * Convert option names in the WordPress style to their legacy form.
	 *
	 * @param string $option The new option name, e.g. 'my_new_option'.
	 * @return string        An old-style option name, e.g. 'MyOldOption'.
	 */
	protected function get_old_option_name( $option ) {
		$parts   = \explode( '_', $option );
		$oldname = \array_shift( $parts );

		// Does not really seem to matter, but try
		// to match the correct case.
		foreach ( $parts as $part ) {
			if ( 'ie6' === $part ) {
				$oldname .= 'IE6';
			} elseif ( 'css' === $part ) {
				$oldname .= 'CSS';
			} elseif ( 'urls' === $part ) {
				$oldname .= 'URLs';
			} elseif ( 'ids' === $part ) {
				$oldname .= 'IDs';
			} else {
				$oldname .= \ucfirst( $part );
			}
		}

		return $oldname;
	}

	/**
	 * Fired during plugin deactivation.
	 *
	 * @since 3.1.0
	 */
	public function deactivate() {
	}

	/**
	 * Fired during uninstall.
	 *
	 * @since 3.1.0
	 */
	public static function uninstall() {

		// Delete all our transients.
		$transients = new \WP_Typography\Data_Storage\Transients();
		$transients->invalidate();
	}
}
