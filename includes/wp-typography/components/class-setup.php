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

use \WP_Typography\Options;
use \WP_Typography\Settings\Plugin_Configuration as Config;

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
	 * Create a new instace of WP_Typography\Setup.
	 *
	 * @param string $plugin_path The full path to the main plugin file.
	 */
	public function __construct( $plugin_path ) {
		$this->plugin_file = $plugin_path;
	}

	/**
	 * Registers the de-/activation/uninstall hooks for the plugin.
	 *
	 * @param \WP_Typography $plugin The plugin instance.
	 */
	public function run( \WP_Typography $plugin ) {
		$this->plugin = $plugin;

		\register_activation_hook( $this->plugin_file,   [ $this,     'activate' ] );
		\register_deactivation_hook( $this->plugin_file, [ $this,     'deactivate' ] );
		\register_uninstall_hook( $this->plugin_file,    [ __CLASS__, 'uninstall' ] );
	}

	/**
	 * Fired during plugin activation.
	 *
	 * @since      3.1.0
	 */
	public function activate() {
		// Update option values & other stuff if necessary.
		$this->plugin_updated( \get_option( Options::PREFIX . '_' . Options::INSTALLED_VERSION ) );

		// Load default options and clear the cache.
		$this->plugin->set_default_options();
		$this->plugin->clear_cache();
	}

	/**
	 * Upgrade plugin data.
	 *
	 * @param string $previous_version The version we are upgrading from.
	 */
	protected function plugin_updated( $previous_version ) {

		// Each version should get it's own if-block.
		if ( \version_compare( $previous_version, '3.1.0-beta.2', '<' ) ) {
			\error_log( 'Upgrading wp-Typography from ' . ( $previous_version ? $previous_version : '< 3.1.0') ); // @codingStandardsIgnoreLine

			foreach ( $this->plugin->get_default_options() as $option_name => $option ) {
				$old_option = $this->get_old_option_name( Options::PREFIX . "_{$option_name}" );
				$old_value  = \get_option( $old_option, self::UPGRADING );

				if ( self::UPGRADING !== $old_value ) {
					$result_update = \update_option( $option_name, $old_value );
					$result_delete = \delete_option( $old_option );

					if ( ! $result_update || ! $result_delete ) {
						\error_log("Error while upgrading $old_option: " . ( $result_update ? '' : 'Update failed. ' .     // @codingStandardsIgnoreLine
																		   ( $result_delete ? '' : 'Delete failed.') ) );
					}
				}
			}
		}
		if ( \version_compare( $previous_version, '3.2.0-beta.1', '<' ) ) {
			\delete_option( 'typo_disable_caching' );
		}
		if ( \version_compare( $previous_version, '3.3.0-alpha.2', '<' ) ) {
			\delete_option( 'typo_remove_ie6' );
		}

		if ( \version_compare( $previous_version, '3.5.0-alpha.1', '<' ) ) {
			\delete_option( 'typo_enable_caching' );
			\delete_option( 'typo_caching_limit' );
		}

		if ( \version_compare( $previous_version, '5.1.0', '<' ) ) {
			\delete_option( 'typo_transient_keys' );
			\delete_option( 'typo_cache_keys' );

			$this->upgrade_options_to_array();
		}

		$this->set_installed_version();
	}

	/**
	 * Move all old options to the new array.
	 *
	 * @since 5.2.0
	 */
	protected function upgrade_options_to_array() {
		$config = $this->plugin->get_default_options();

		foreach ( $config as $option_name => $default_value ) {
			$old_option = Options::PREFIX . "_{$option_name}";
			$old_value  = \get_option( $old_option, self::UPGRADING );

			if ( self::UPGRADING !== $old_value ) {
				$config[ $option_name ] = $old_value;

				\delete_option( $old_option );
			}
		}

		\update_option( Option::CONFIGURATION, $config );
	}

	/**
	 * Update installed version option.
	 *
	 * @since 5.2.0
	 */
	protected function set_installed_version() {
		\update_option( Options::PREFIX . '_' . Options::INSTALLED_VERSION, $this->plugin->get_version() );
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
	 * @since    3.1.0
	 */
	public function deactivate() {
	}

	/**
	 * Fired during uninstall.
	 *
	 * @since    3.1.0
	 */
	public static function uninstall() {

		// Delete all our transients.
		$transients = new \WP_Typography\Transients();
		$transients->invalidate();
	}
}
