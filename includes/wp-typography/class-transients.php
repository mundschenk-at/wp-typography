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
 * Implements an interface to the transients API for wp-Typography.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Transients extends Abstract_Cache {

	const INCREMENTOR_KEY = self::PREFIX . 'transients_incrementor';
	const BACKLOG_KEY     = self::PREFIX . 'transients_keys';

	/**
	 * The transients set since the last call to `invalidate`.
	 *
	 * @var string[]
	 */
	protected $transient_keys = [];

	/**
	 * Create new cache instance.
	 */
	public function __construct() {
		$this->incrementor    = \get_transient( self::INCREMENTOR_KEY );
		$this->transient_keys = \get_option( self::BACKLOG_KEY, [] );

		parent::__construct();
	}

	/**
	 * Invalidate all cached elements by reseting the incrementor.
	 */
	public function invalidate() {
		// Clean up old transients.
		foreach ( $this->transient_keys as $key => $ignored ) {
			\delete_transient( $this->get_key( $key ) );
		}

		// Update key storage.
		$this->transient_keys = [];
		\update_option( self::BACKLOG_KEY, $this->transient_keys );

		// Update incrementor.
		$this->incrementor = time();
		\set_transient( self::INCREMENTOR_KEY, $this->incrementor );
	}

	/**
	 * Retrieves a cached value.
	 *
	 * @param string $key The cache key.
	 *
	 * @return mixed
	 */
	public function get( $key ) {
		return \get_transient( $this->get_key( $key ) );
	}

	/**
	 * Retrieves a cached large object.
	 *
	 * @param string $key The cache key.
	 *
	 * @return mixed
	 */
	public function get_large_object( $key ) {
		$encoded = $this->get( $key );
		if ( false === $encoded ) {
			return false;
		}

		$uncompressed = @\gzdecode( \base64_decode( $encoded ) ); // @codingStandardsIgnoreLine
		if ( false === $uncompressed ) {
			return false;
		}

		return \unserialize( $uncompressed ); // @codingStandardsIgnoreLine
	}

	/**
	 * Sets an entry in the cache and stores the key.
	 *
	 * @param string $key       The cache key.
	 * @param mixed  $value     The value to store.
	 * @param int    $duration  Optional. The duration in seconds. Default 0 (no expiration).
	 *
	 * @return bool True if the cache could be set successfully.
	 */
	public function set( $key, $value, $duration = 0 ) {
		$this->store_transient_key( $key );

		return \set_transient( $this->get_key( $key ), $value, $duration );
	}

	/**
	 * Sets a transient for a large PHP object. The object will be stored in
	 * serialized and gzip encoded form using Base64 encoding to ensure binary safety.
	 *
	 * @param string $key       The cache key.
	 * @param mixed  $value     The value to store.
	 * @param int    $duration  Optional. The duration in seconds. Default 0 (no expiration).
	 *
	 * @return bool True if the cache could be set successfully.
	 */
	public function set_large_object( $key, $value, $duration = 0 ) {
		$compressed = \gzencode( \serialize( $value ) ); // @codingStandardsIgnoreLine

		if ( false === $compressed ) {
			return false; // @codeCoverageIgnore
		}

		return $this->set( $key, \base64_encode( $compressed ), $duration );
	}

	/**
	 * Store transient key for latter deletion.
	 *
	 * @param  string $key A transient key root (without prefix and incrementor).
	 */
	protected function store_transient_key( $key ) {
		if ( ! isset( $this->transient_keys[ $key ] ) ) {
			$this->transient_keys[ $key ] = true;
			\update_option( self::BACKLOG_KEY, $this->transient_keys );
		}
	}
}
