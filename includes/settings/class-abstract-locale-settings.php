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

namespace WP_Typography\Settings;

use \PHP_Typography\Settings;

/**
 * An abstract base class for localized settings.
 *
 * @since 5.0.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
abstract class Abstract_Locale_Settings implements Locale_Settings {
	/**
	 * A priority.
	 *
	 * @var int
	 */
	protected $priority;

	/**
	 * A Quote_Style constant.
	 *
	 * @var string
	 */
	protected $primary_quote_style;

	/**
	 * A Quote_Style constant.
	 *
	 * @var string
	 */
	protected $secondary_quote_style;

	/**
	 * A Dash_Style constant.
	 *
	 * @var string
	 */
	protected $dash_style;

	/**
	 * Enable French punctuation spacing?
	 *
	 * @var bool
	 */
	protected $french_punctuation;

	/**
	 * Creates a new instance.
	 *
	 * @param int    $priority           The matching priority.
	 * @param string $dash               A Dash_Style constant.
	 * @param string $primary_quote      A Quote_Style constant.
	 * @param string $secondary_quote    A Quote_Style constant.
	 * @param bool   $french_punctuation True if French punctuation spacing should be enabled.
	 */
	protected function __construct( $priority, $dash, $primary_quote, $secondary_quote, $french_punctuation ) {
		$this->priority              = $priority;
		$this->dash_style            = $dash;
		$this->primary_quote_style   = $primary_quote;
		$this->secondary_quote_style = $secondary_quote;
		$this->french_punctuation    = $french_punctuation;
	}

	/**
	 * Tries to match the language default to a given locale.
	 *
	 * @param  string $language A two- or three-letter language code (e.g. 'de').
	 * @param  string $country  A two-letter upper-case country code (e.g. 'DE').
	 * @param  string $modifier Optional. An modifier for the locale (e.g. 'formal'). Default ''.
	 * @return bool             True if the default is applicable to this locale, false otherwise.
	 */
	abstract public function match( $language, $country, $modifier = '' );

	/**
	 * Retrieves the matching priority. A higher value means earlier matching.
	 *
	 * @return int
	 */
	public function priority() {
		return $this->priority;
	}

	/**
	 * Apply language-specific adjustments to the defaults array.
	 *
	 * @param  array $defaults An array of default values indexed by the option name.
	 *
	 * @return array
	 */
	public function adjust_defaults( array $defaults ) {
		$defaults['typo_smart_quotes_primary']       = $this->primary_quote_style;
		$defaults['typo_smart_quotes_secondary']     = $this->secondary_quote_style;
		$defaults['typo_french_punctuation_spacing'] = $this->french_punctuation;
		$defaults['typo_smart_dashes_style']         = $this->dash_style;

		return $defaults;
	}

	/**
	 * Retrieves the primary quote style for this locale.
	 *
	 * @return string A Quote_Style constant.
	 */
	public function primary_quote_style() {
		return $this->primary_quote_style;
	}

	/**
	 * Retrieves the secondary quote style for this locale.
	 *
	 * @return string A Quote_Style constant.
	 */
	public function secondary_quote_style() {
		return $this->secondary_quote_style;
	}

	/**
	 * Retrieves the dash style for this locale.
	 *
	 * @return string A Dash_Style constant.
	 */
	public function dash_style() {
		return $this->dash_style;
	}

	/**
	 * Whether this locale uses French punctuation spacing.
	 *
	 * @return bool
	 */
	public function use_french_punctuation_spacing() {
		return $this->french_punctuation;
	}
}
