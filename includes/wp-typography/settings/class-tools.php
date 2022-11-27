<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2019-2022 Peter Putzer.
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

namespace WP_Typography\Settings;

use PHP_Typography\U;

/**
 * An abstract class containing helper methods to convert WordPress settings to a
 * format suitable for consumption by PHP-Typography.
 *
 * @since  5.5.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @internal
 */
abstract class Tools {

	/**
	 * Parses a smart quotes exceptions string into an array. All single straight
	 * quotes (prime marks) are treated as apostrophes.
	 *
	 * @param  string $string A string formatted `word1, word2, ...`.
	 *
	 * @return string[]       An array of replacements, indexed by the key.
	 */
	public static function parse_smart_quote_exceptions_string( $string ) : array {
		return \array_reduce(
			\preg_split( '/,/', $string, -1, \PREG_SPLIT_NO_EMPTY ) ?: [], // phpcs:ignore WordPress.PHP.DisallowShortTernary -- ensure array type.
			function( $result, $replacement ) {
				$key         = \trim( \strip_tags( $replacement ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- no need for WordPress' version here.
				$replacement = \str_replace( "'", U::APOSTROPHE, $key );

				// Matched keys or replacements might have contained only tags. Discard those.
				if ( ! empty( $key ) && ! empty( $replacement ) ) {
					$result[ $key ] = $replacement;
				}

				return $result;
			},
			[]
		);
	}

	/**
	 * Provides an array_map implementation with control over resulting array's keys.
	 *
	 * Based on https://gist.github.com/jasand-pereza/84ecec7907f003564584.
	 *
	 * @deprecated 5.8.0
	 *
	 * @template T
	 *
	 * @param  callable $callback A callback function that needs to return [ $key => $value ] pairs.
	 * @param  array<T> $array    The array.
	 *
	 * @return array<T>
	 */
	public static function array_map_assoc( callable $callback, array $array ) : array {
		\_deprecated_function( __FUNCTION__, '5.8.0' );

		$new = [];

		foreach ( $array as $k => $v ) {
			$u = $callback( $k, $v );

			if ( ! empty( $u ) ) {
				$new[ \key( $u ) ] = \current( $u );
			}
		}

		return $new;
	}
}
