<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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

/**
 * An interface to support language-specific default settings in wp-Typography.
 *
 * @since  5.0.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
interface Locale_Settings {

	/**
	 * Tries to match the language default to a given locale.
	 *
	 * @param  string $language A two- or three-letter language code (e.g. 'de').
	 * @param  string $country  A two-letter upper-case country code (e.g. 'DE').
	 * @param  string $modifier Optional. An modifier for the locale (e.g. 'formal'). Default ''.
	 *
	 * @return bool             True if the default is applicable to this locale, false otherwise.
	 */
	public function match( $language, $country, $modifier = '' ) : bool;

	/**
	 * Retrieves the matching priority. A higher value means earlier matching.
	 *
	 * @return int
	 */
	public function priority() : int;

	/**
	 * Apply language-specific adjustments to the defaults array.
	 *
	 * @param  array $defaults An array of default values indexed by the option name.
	 *
	 * @return array
	 */
	public function adjust_defaults( array $defaults ) : array;

	/**
	 * Retrieves the primary quote style for this locale.
	 *
	 * @return string A Quote_Style constant.
	 */
	public function primary_quote_style() : string;

	/**
	 * Retrieves the secondary quote style for this locale.
	 *
	 * @return string A Quote_Style constant.
	 */
	public function secondary_quote_style() : string;

	/**
	 * Retrieves the dash style for this locale.
	 *
	 * @return string A Dash_Style constant.
	 */
	public function dash_style() : string;

	/**
	 * Whether this locale uses French punctuation spacing.
	 *
	 * @return bool
	 */
	public function use_french_punctuation_spacing() : bool;
}
