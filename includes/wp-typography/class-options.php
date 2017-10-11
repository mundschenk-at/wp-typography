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

namespace WP_Typography;

/**
 * Implements an interface to the Options API for wp-Typography.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Options {

	const PREFIX = 'typo';

	const RESTORE_DEFAULTS = 'restore_defaults';
	const CLEAR_CACHE      = 'clear_cache';
	const CONFIGURATION    = 'configuration';

	/**
	 * Create new Options instance.
	 */
	public function __construct() {
	}

	/**
	 * Retrieves an option value.
	 *
	 * @param string $option  The option name (without the plugin-specific prefix).
	 * @param mixed  $default Optional. Default value to return if the option does not exist. Default null.
	 *
	 * @return mixed Value set for the option.
	 */
	public function get( $option, $default = null ) {
		$value = \get_option( $this->get_name( $option ), $default );

		if ( is_array( $default ) && '' === $value ) {
			$value = [];
		}

		return $value;
	}

	/**
	 * Sets or updates an option.
	 *
	 * @param string $option   The option name (without the plugin-specific prefix).
	 * @param mixed  $value    The value to store.
	 * @param bool   $autoload Optional. Whether to load the option when WordPress
	 *                         starts up. For existing options, $autoload can only
	 *                         be updated using update_option() if $value is also
	 *                         changed. Default true.
	 *
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function set( $option, $value, $autoload = true ) {
		return \update_option( $this->get_name( $option ), $value, $autoload );
	}

	/**
	 * Deletes an option.
	 *
	 * @param string $option The option name (without the plugin-specific prefix).
	 *
	 * @return bool True, if option is successfully deleted. False on failure.
	 */
	public function delete( $option ) {
		return \delete_option( $this->get_name( $option ) );
	}

	/**
	 * Retrieves the complete option name to use.
	 *
	 * @param  string $option The option name (without the plugin-specific prefix).
	 *
	 * @return string
	 */
	public function get_name( $option ) {
		return self::PREFIX . "_{$option}";
	}
}
