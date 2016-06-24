<?php

/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2016 Peter Putzer.
 *	Copyright 2012-2013 Marie Hogebrandt.
 *	Coypright 2009-2011 KINGdesk, LLC.
 *
 *	This program is free software; you can redistribute it and/or
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
 *  @package wpTypography
 *  @author Jeffrey D. King <jeff@kingdesk.com>
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Fired during plugin de-/activation and uninstall.
 *
 * This class defines all code necessary to run during the plugin's setup and teardown.
 *
 * @since      3.1.0
 * @package    wpTypography
 * @subpackage wpTypography/includes
 * @author     Peter Putzer <github@mundschenk.at>
 */
class WP_Typography_Setup {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    3.1.0
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	private $plugin_slug;

	/**
	 * The plugin object.
	 *
	 * @since    3.1.0
	 * @var      WP_Typography $plugin The main plugin class instance.
	 */
	private $plugin;

	/**
	 * Create a new instace of WP_Typography_Setup.
	 *
	 * @param string        $slug
	 * @param WP_Typography $plugin
	 */
	function __construct( $slug, WP_Typography $plugin ) {
		$this->plugin_slug = $slug;
		$this->plugin = $plugin;
	}

	/**
	 * Register the de-/activation/uninstall hooks for the plugin.
	 *
	 * @param string $plugin_file The full path and filename to the main plugin file.
	 */
	public function register( $plugin_file ) {
		register_activation_hook( $plugin_file, array( $this, 'activate' ) );
		register_deactivation_hook( $plugin_file, array( $this, 'deactivate' ) );
		register_uninstall_hook( $plugin_file, __CLASS__ . '::uninstall' );
	}

	/**
	 * Fired during plugin activation.
	 *
	 * @since      3.1.0
	 */
	public function activate() {
		// update option values & other stuff if necessary
		$this->plugin_updated( get_option( 'typo_installed_version' ) );

		// load default options and clear the cache
		$this->plugin->set_default_options();
		$this->plugin->clear_cache();
	}

	/**
	 * Upgrade plugin data.
	 *
	 * @param string $previous_version The version we are upgrading from.
	 */
	private function plugin_updated( $previous_version ) {

		// Each version should get it's own if-block
		if ( version_compare( $previous_version, '3.1.0-beta.2', '<' ) ) {
			error_log( 'Upgrading wp-Typography from ' . ( $previous_version ? $previous_version : '< 3.1.0') );

			foreach ( $this->plugin->get_default_options() as $option_name => $option ) {
				$old_option = $this->get_old_option_name( $option_name );
				$old_value = get_option( $old_option, 'UPGRADING_WP_TYPOGRAPHY' );

				if ( 'UPGRADING_WP_TYPOGRAPHY' !== $old_value ) {
					$result_update = update_option( $option_name, $old_value );
					$result_delete = delete_option( $old_option );

					if ( ! $result_update || ! $result_delete ) {
						error_log("Error while upgrading $old_option: " . ( $result_update ? '' : 'Update failed. ' .
																		  ( $result_delete ? '' : 'Delete failed.') ) );
					}
				}
			}
		}
		if ( version_compare( $previous_version, '3.2.0-beta.1', '<' ) ) {
			delete_option( 'typo_disable_caching' );
		}
		if ( version_compare( $previous_version, '3.3.0-alpha.2', '<' ) ) {
			delete_option( 'typo_remove_ie6' );
		}

		update_option( 'typo_installed_version', $this->plugin->get_version() );
	}

	private function get_old_option_name( $option ) {
		$parts = explode( '_', $option );
		$oldname = array_shift( $parts );

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
				$oldname .= ucfirst( $part );
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
		// $this->plugin->clear_cache();
	}

	/**
	 * Fired during uninstall.
	 *
	 * @since    3.1.0
	 */
	static function uninstall() {
		$transient_list = get_option( 'typo_transient_keys' );

		// delete all our transients
		foreach ( $transient_list as $transient => $true ) {
			delete_transient( $transient );
		}

		update_option( 'typo_transient_keys', array() );
	}
}
