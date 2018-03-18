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

namespace WP_Typography\Data_Storage;

/**
 * Implements an interface to the transients API for wp-Typography.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Transients extends \Mundschenk\Data_Storage\Transients {

	const PREFIX = 'typo_';

	/**
	 * Create new cache instance.
	 */
	public function __construct() {
		parent::__construct( self::PREFIX );
	}

	/**
	 * Cache the given object under the transient name.
	 *
	 * @param  string $transient Required.
	 * @param  mixed  $object    Required.
	 * @param  string $handle    Optional. A name passed to the filters.
	 */
	public function cache_object( $transient, $object, $handle = '' ) {
		/**
		 * Filters whether the PHP_Typography engine state should be cached.
		 *
		 * @since 4.2.0
		 * @since 5.1.0 $handle parameter added.
		 *
		 * @param bool   $enabled Defaults to true.
		 * @param string $handle  Optional. A name passed to the filters.
		 */
		if ( apply_filters( 'typo_php_typography_caching_enabled', true, $handle ) ) {
			/**
			 * Filters the caching duration for the PHP_Typography engine state.
			 *
			 * @since 3.2.0
			 * @since 5.1.0 $handle parameter added.
			 *
			 * @param int    $duration The duration in seconds. Defaults to 0 (no expiration).
			 * @param string $handle   Optional. A name passed to the filters.
			 */
			$duration = apply_filters( 'typo_php_typography_caching_duration', 0, $handle );

			$this->set_large_object( $transient, $object, $duration );
		}
	}
}
