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
 * Main wp-Typography plugin class. All WordPress specific code goes here.
 */
class WP_Typography_Requirements {

	/**
	 * The user-visible name of the plugin.
	 *
	 * // FIXME Translate?
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
		'PHP Version' 		=> '5.3.0',
		'WordPress Version'	=> '4.0',
		'Multibyte' 		=> true,
		'UTF-8'				=> true,
	 );


	/**
	 * The result of plugin_basename() for the main plugin file.
	 * (Relative from plugins folder.)
	 */
	private $local_plugin_path;

	/**
	 * Sets up a new WP_Typography_Requirements object.
	 *
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
			if( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'admin_notices_php_version_incompatible' ) );
			}
			$requirements_met = false;
		} elseif ( $this->install_requirements['Multibyte'] && ! $this->check_multibyte_support() ) {
			if( is_admin() ) {
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
			// load text domain to ensure translated admin notices
			load_plugin_textdomain( 'wp-typography', false, dirname( $this->local_plugin_path ) . '/translations/' );
		}

		return $requirements_met;
	}

	/**
	 * Check if multibyte functions are supported.
	 *
	 * @return boolean
	 */
	private function check_multibyte_support() {
		if ( function_exists( 'mb_strlen' )     &&
			 function_exists( 'mb_strtolower' ) &&
			 function_exists( 'mb_substr')      &&
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
	 * @param string $format    An `sprintf` format string.
	 * @param mixed  $param1... An optional number of parameters for sprintf.
	 */
	private function display_error_notice( $format ) {
		if ( func_num_args() < 1 ) {
			return; // abort
		}

		$args = func_get_args();
		$format = array_shift( $args );

		echo '<div class="error"><p>' . vsprintf( $format, $args ) . '</p></div>';
	}
}
