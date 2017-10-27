<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
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

/**
 * This class checks if the required runtime environment is available.
 *
 * Included checks:
 *    - PHP version
 *    - WordPress version
 *    - mb_string extension
 *    - UTF-8 encoding
 *
 * Note: All code must be executable on PHP 5.2.
 */
class WP_Typography_Requirements {

	/**
	 * The user-visible name of the plugin.
	 *
	 * @todo Should the plugin name be translated?
	 * @var string $plugin_name
	 */
	private $plugin_name = 'wp-Typography';

	/**
	 * The minimum requirements for running the plugins. Must contain:
	 *  - 'PHP Version'
	 *  - 'WordPress Version'
	 *  - 'Multibyte'
	 *  - 'UTF-8'
	 *
	 * @var array A Hash containing the version requirements for the plugin.
	 */
	private $install_requirements = array(
		'PHP Version' => '5.6.0',
		'Multibyte'   => true,
		'UTF-8'       => true,
	);

	/**
	 * The result of plugin_basename() for the main plugin file.
	 * (Relative from plugins folder.)
	 *
	 * @var string $local_plugin_path
	 */
	private $local_plugin_path;

	/**
	 * Sets up a new WP_Typography_Requirements object.
	 *
	 * @param string $name     The plugin name.
	 * @param string $basename The result of plugin_basename() for the main plugin file.
	 */
	public function __construct( $name, $basename = 'wp-typography/wp-typography.php' ) {
		$this->plugin_name       = $name;
		$this->local_plugin_path = $basename;
	}

	/**
	 * Check if all runtime requirements for the plugin are met.
	 *
	 * @return boolean
	 */
	public function check() {
		$requirements_met = true;

		if ( version_compare( PHP_VERSION, $this->install_requirements['PHP Version'], '<' ) ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'admin_notices_php_version_incompatible' ) );
			}
			$requirements_met = false;
		} elseif ( $this->install_requirements['Multibyte'] && ! $this->check_multibyte_support() ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'admin_notices_mbstring_incompatible' ) );
			}
			$requirements_met = false;
		} elseif ( $this->install_requirements['UTF-8'] && ! $this->check_utf8_support() ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'admin_notices_charset_incompatible' ) );
			}
			$requirements_met = false;
		}

		if ( ! $requirements_met && is_admin() ) {
			// Load text domain to ensure translated admin notices.
			load_plugin_textdomain( 'wp-typography', false, dirname( $this->local_plugin_path ) . '/translations/' );

			/*
				Not sure if we should actually auto-deactivate the plugin.
				add_action( 'admin_init', array( $this, 'deactivate_plugin' ) );
			 */
		}

		return $requirements_met;
	}

	/**
	 * Deactivate the plugin.
	 */
	public function deactivate_plugin() {
		deactivate_plugins( plugin_basename( $this->local_plugin_path ) );
	}

	/**
	 * Check if multibyte functions are supported.
	 *
	 * @return boolean
	 */
	protected function check_multibyte_support() {
		return
			function_exists( 'mb_strlen' ) &&
			function_exists( 'mb_strtolower' ) &&
			function_exists( 'mb_substr' ) &&
			function_exists( 'mb_detect_encoding' );
	}

	/**
	 * Check if the blog charset is set to UTF-8.
	 *
	 * @return boolean
	 */
	protected function check_utf8_support() {
		return 'utf-8' === strtolower( get_bloginfo( 'charset' ) );
	}

	/**
	 * Print 'PHP version incompatible' admin notice
	 */
	public function admin_notices_php_version_incompatible() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: target PHP version number 3: actual PHP version number */
			__( 'The activated plugin %1$s requires PHP %2$s or later. Your server is running PHP %3$s. Please deactivate this plugin, or upgrade your server\'s installation of PHP.', 'wp-typography' ),
			"<strong>{$this->plugin_name}</strong>",
			$this->install_requirements['PHP Version'],
			phpversion()
		);
	}

	/**
	 * Print 'mbstring extension missing' admin notice
	 */
	public function admin_notices_mbstring_incompatible() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: mbstring documentation URL */
			__( 'The activated plugin %1$s requires the mbstring PHP extension to be enabled on your server. Please deactivate this plugin, or <a href="%2$s">enable the extension</a>.', 'wp-typography' ),
			"<strong>{$this->plugin_name}</strong>",
			/* translators: URL with mbstring PHP extension installation instructions */
			__( 'http://www.php.net/manual/en/mbstring.installation.php', 'wp-typography' )
		);
	}

	/**
	 * Print 'Charset incompatible' admin notice
	 */
	public function admin_notices_charset_incompatible() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: current character encoding 3: options URL */
			__( 'The activated plugin %1$s requires your blog use the UTF-8 character encoding. You have set your blogs encoding to %2$s. Please deactivate this plugin, or <a href="%3$s">change your character encoding to UTF-8</a>.', 'wp-typography' ),
			"<strong>{$this->plugin_name}</strong>",
			get_bloginfo( 'charset' ),
			'/wp-admin/options-reading.php'
		);
	}

	/**
	 * Show an error message in the admin area.
	 *
	 * @param string $format ... An `sprintf` format string, followd by an unspecified number of optional parameters.
	 */
	protected function display_error_notice( $format ) {
		if ( func_num_args() < 1 || empty( $format ) ) {
			return; // abort.
		}

		$args   = func_get_args();
		$format = array_shift( $args );

		echo '<div class="notice notice-error"><p>' . vsprintf( $format, $args ) . '</p></div>'; // WPCS: XSS OK.
	}
}
