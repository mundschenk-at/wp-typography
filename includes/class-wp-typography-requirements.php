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
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Main wp-Typography plugin class. All WordPress specific code goes here.
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
		'PHP Version' 		=> '5.3.4',
		'WordPress Version'	=> '4.4',
		'Multibyte' 		=> true,
		'UTF-8'				=> true,
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
	function __construct( $name, $basename = 'wp-typography/wp-typography.php' ) {
		$this->plugin_name = $name;
		$this->local_plugin_path = $basename;
	}

	/**
	 * Check if all runtime requirements for the plugin are met.
	 *
	 * @return boolean
	 */
	function check() {
		global $wp_version;
		$requirements_met = true;

		if ( version_compare( $wp_version, $this->install_requirements['WordPress Version'], '<' ) ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'admin_notices_wp_version_incompatible' ) );
			}
			$requirements_met = false;
		} elseif ( version_compare( PHP_VERSION, $this->install_requirements['PHP Version'], '<' ) ) {
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
			/* add_action( 'admin_init', array( $this, 'deactivate_plugin' ) ); */
		}

		return $requirements_met;
	}

	/**
	 * Deactivate the plugin.
	 */
	function deactivate_plugin() {
		deactivate_plugins( plugin_basename( $this->local_plugin_path ) );
		}

	/**
	 * Check if multibyte functions are supported.
	 *
	 * @return boolean
	 */
	private function check_multibyte_support() {
		if ( function_exists( 'mb_strlen' )     &&
			 function_exists( 'mb_strtolower' ) &&
			 function_exists( 'mb_substr' )      &&
			 function_exists( 'mb_detect_encoding' ) ) {
			return true;
		 } else {
		 	return false;
		 }
	}

	/**
	 * Check if the blog charset is set to UTF-8.
	 *
	 * @return boolean
	 */
	private function check_utf8_support() {
		return 'utf-8' === strtolower( get_bloginfo( 'charset' ) );
	}

	/**
	 * Print 'WordPress version incompatible' admin notice
	 */
	function admin_notices_wp_version_incompatible() {
		global $wp_version;

		$this->display_error_notice( __( 'The activated plugin %1$s requires WordPress version %2$s or later. You are running WordPress version %3$s. Please deactivate this plugin, or upgrade your installation of WordPress.', 'wp-typography' ),
									 "<strong>{$this->plugin_name}</strong>",
									 $this->install_requirements['WordPress Version'],
									 $wp_version );
	}

	/**
	 * Print 'PHP version incompatible' admin notice
	 */
	function admin_notices_php_version_incompatible() {
		$this->display_error_notice( __( 'The activated plugin %1$s requires PHP %2$s or later. Your server is running PHP %3$s. Please deactivate this plugin, or upgrade your server\'s installation of PHP.', 'wp-typography' ),
								  	 "<strong>{$this->plugin_name}</strong>",
									 $this->install_requirements['PHP Version'],
									 phpversion() );
	}

	/**
	 * Print 'mbstring extension missing' admin notice
	 */
	function admin_notices_mbstring_incompatible() {
		$this->display_error_notice( __( 'The activated plugin %1$s requires the mbstring PHP extension to be enabled on your server. Please deactivate this plugin, or <a href="%2$s">enable the extension</a>.', 'wp-typography' ),
									 "<strong>{$this->plugin_name}</strong>",
									 'http://www.php.net/manual/en/mbstring.installation.php' );
	}

	/**
	 * Print 'Charset incompatible' admin notice
	 */
	function admin_notices_charset_incompatible() {
		$this->display_error_notice( __( 'The activated plugin %1$s requires your blog use the UTF-8 character encoding. You have set your blogs encoding to %2$s. Please deactivate this plugin, or <a href="%3$s">change your character encoding to UTF-8</a>.', 'wp-typography' ),
									 "<strong>{$this->plugin_name}</strong>",
									 get_bloginfo( 'charset' ),
									 '/wp-admin/options-reading.php' );
	}

	/**
	 * Show an error message in the admin area.
	 *
	 * @param string $format     An `sprintf` format string.
	 * @param mixed  $param1,... An optional number of parameters for sprintf.
	 */
	private function display_error_notice( $format ) {
		if ( func_num_args() < 1 ) {
			return; // abort.
		}

		$args = func_get_args();
		$format = array_shift( $args );

		echo '<div class="error"><p>' . vsprintf( $format, $args ) . '</p></div>'; // WPCS: XSS OK.
	}
}
