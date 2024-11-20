<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2024 Peter Putzer.
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

use PHP_Typography\Settings;

/**
 * A class implementing fast but versatile locale matching.
 *
 * @since  5.0.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Basic_Locale_Settings extends Abstract_Locale_Settings {

	const LANGUAGE_PRIORITY = 10;
	const COUNTRY_PRIORITY  = 100;
	const MODIFIER_PRIORITY = 1000;

	/**
	 * An array with valid languages as indexes.
	 *
	 * @var array<string,int>
	 */
	protected $valid_languages = [];

	/**
	 * An array with valid country codes as indexes.
	 *
	 * @var array<string,int>
	 */
	protected $valid_countries = [];

	/**
	 * An array with valid modifier strings as indexes.
	 *
	 * @var array<string,int>
	 */
	protected $valid_modifiers = [];

	/**
	 * Creates a new instance.
	 *
	 * @param string[] $languages          An array of valid languages. The empty array accepts any language.
	 * @param string[] $countries          An array of valid country codes. The empty array accepts any country code.
	 * @param string[] $modifiers          An array of valid modifier strings. The empty array accepts any modifier.
	 * @param string   $dash               A Dash_Style constant.
	 * @param string   $primary_quote      A Quote_Style constant.
	 * @param string   $secondary_quote    A Quote_Style constant.
	 * @param bool     $french_punctuation True if French punctuation spacing should be enabled.
	 */
	public function __construct( array $languages, array $countries, array $modifiers, string $dash, string $primary_quote, string $secondary_quote, bool $french_punctuation ) {
		parent::__construct(
			self::LANGUAGE_PRIORITY * \count( $languages ) + self::COUNTRY_PRIORITY * \count( $countries ) + self::MODIFIER_PRIORITY * \count( $modifiers ),
			$dash,
			$primary_quote,
			$secondary_quote,
			$french_punctuation
		);

		$this->valid_languages = \array_flip( $languages );
		$this->valid_countries = \array_flip( $countries );
		$this->valid_modifiers = \array_flip( $modifiers );
	}

	/**
	 * Tries to match the language default to a given locale.
	 *
	 * @param  string $language A two- or three-letter language code (e.g. 'de').
	 * @param  string $country  A two-letter upper-case country code (e.g. 'DE').
	 * @param  string $modifier Optional. An modifier for the locale (e.g. 'formal'). Default ''.
	 *
	 * @return bool             True if the default is applicable to this locale, false otherwise.
	 */
	public function match( string $language, string $country, string $modifier = '' ): bool {
		return ( empty( $this->valid_languages ) || isset( $this->valid_languages[ $language ] ) )
			&& ( empty( $this->valid_countries ) || isset( $this->valid_countries[ $country ] ) )
			&& ( empty( $modifier ) || empty( $this->valid_modifiers ) || isset( $this->valid_modifiers[ $modifier ] ) );
	}
}
