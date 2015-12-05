<?php

/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2015 Peter Putzer.
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License,
 *	version 2 as published by the Free Software Foundation.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *  MA 02110-1301, USA.
 *
 *  ***
 *
 *  Copyright 2009, KINGdesk, LLC. Licensed under the GNU General Public
 *  License 2.0. If you use, modify and/or redistribute this software,
 *  you must leave the KINGdesk, LLC copyright information, the request
 *  for a link to http://kingdesk.com, and the web design services
 *  contact information unchanged. If you redistribute this software, or
 *  any derivative, it must be released under the GNU General Public
 *  License 2.0.
 *
 *  This program is distributed without warranty (implied or otherwise) of
 *  suitability for any particular purpose. See the GNU General Public
 *  License for full license terms <http://creativecommons.org/licenses/GPL/2.0/>.
 *
 *  WE DON'T WANT YOUR MONEY: NO TIPS NECESSARY! If you enjoy this plugin,
 *  a link to http://kingdesk.com from your website would be appreciated.
 *  For web design services, please contact jeff@kingdesk.com.
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
	 * @param string $slug
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
		register_activation_hook(   $plugin_file, array( $this, 'activate' ) );
		register_deactivation_hook( $plugin_file, array( $this, 'deactivate' ) );
		register_uninstall_hook(    $plugin_file, __CLASS__ . '::uninstall' );
	}

	/**
	 * Fired during plugin activation.
	 *
	 * @since      3.1.0
	 */
	public function activate() {
		$this->plugin->set_default_options();
		$this->plugin->clear_cache();

		$previous_version = get_option( 'typo_installed_version' );
		if ( ! $previous_version ) {
			// previous version < 3.1.0
			update_option( 'typo_restore_defaults', get_option( 'typoRestoreDefaults' ) );
			delete_option( 'typoRestoreDefaults' );
		}

		update_option( 'typo_installed_version', $this->plugin->get_version() );
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
		foreach( $transient_list as $transient => $true ) {
			delete_transient( $transient );
		}

		update_option( 'typo_transient_keys', array() );
	}
}
