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

use \WP_Typography;
use \PHP_Typography\Settings;
use \PHP_Typography\Settings\Dash_Style;
use \PHP_Typography\Settings\Quote_Style;

/**
 * Multilingual support for wp-Typography.
 *
 * @since 5.0.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Multilingual {

	/**
	 * The plugin instance.
	 *
	 * @var WP_Typography
	 */
	protected $plugin;

	/**
	 * An array of Locale_Settings.
	 *
	 * @var Locale_Settings[]
	 */
	protected $locales = [];

	/**
	 * Creates a new instance.
	 *
	 * @param WP_Typography $plugin The main plugin instance.
	 */
	public function __construct( WP_Typography $plugin ) {
		$this->plugin = $plugin;

		$this->locales = $this->initialize_locale_settings();
	}

	/**
	 * Initialize the locale settings.
	 *
	 * @return Locale_Settings[]
	 */
	protected function initialize_locale_settings() {
		$locales = [
			new Basic_Locale_Settings( [ 'de', 'it', 'fr' ], [ 'CH' ], [], Dash_Style::INTERNATIONAL,  Quote_Style::DOUBLE_GUILLEMETS,        Quote_Style::SINGLE_GUILLEMETS,     false ),
			new Basic_Locale_Settings( [ 'en' ],             [ 'US' ], [], Dash_Style::TRADITIONAL_US, Quote_Style::DOUBLE_CURLED,            Quote_Style::SINGLE_CURLED,         false ),
			new Basic_Locale_Settings( [ 'en' ],             [ 'UK' ], [], Dash_Style::INTERNATIONAL,  Quote_Style::SINGLE_CURLED,            Quote_Style::DOUBLE_CURLED,         false ),
			new Basic_Locale_Settings( [ 'de' ],             [],       [], Dash_Style::INTERNATIONAL,  Quote_Style::DOUBLE_LOW_9_REVERSED,    Quote_Style::SINGLE_LOW_9_REVERSED, false ),
			new Basic_Locale_Settings( [ 'fr' ],             [],       [], Dash_Style::INTERNATIONAL,  Quote_Style::DOUBLE_GUILLEMETS_FRENCH, Quote_Style::DOUBLE_CURLED,         true ),
			new Basic_Locale_Settings( [ 'nl' ],             [],       [], Dash_Style::INTERNATIONAL,  Quote_Style::DOUBLE_CURLED,            Quote_Style::SINGLE_CURLED,         false ),
			new Basic_Locale_Settings( [ 'ja', 'zh' ],       [],       [], Dash_Style::INTERNATIONAL,  Quote_Style::CORNER_BRACKETS,          Quote_Style::WHITE_CORNER_BRACKETS, false ),
		];

		usort( $locales, function( Locale_Settings $s1, Locale_Settings $s2 ) {
			$prio1 = $s1->priority();
			$prio2 = $s2->priority();

			// Longer strings come first.
			if ( $prio1 > $prio2 ) {
				return -1;
			} elseif ( $prio2 > $prio1 ) {
				return 1;
			} else {
				return 0;
			}
		} );

		return $locales;
	}

	/**
	 * Adjusts the settings based on the current post language.
	 *
	 * @param Settings $settings The settings.
	 *
	 * @return Settings
	 */
	public function automatic_language_settings( Settings $settings ) {

		// Ensure that default settings stay unmodified.
		$settings = clone $settings;

		// Determine locale.
		list( $locale, $language, $country, $modifier ) = $this->get_current_locale();

		// Adjust hyphenation language.
		$hyphenation_language_match = $this->match_language( $this->plugin->load_hyphenation_languages(), $locale, $language, 'hyphenation' );
		if ( ! empty( $hyphenation_language_match ) ) {
			$settings->set_hyphenation_language( $hyphenation_language_match );
		} else {
			$settings->set_hyphenation( false );
		}

		// Adjust diacritics replacement language.
		$diacritics_language_match  = $this->match_language( $this->plugin->load_diacritic_languages(), $locale, $language, 'diacritics' );
		if ( ! empty( $diacritics_language_match ) ) {
			$settings->set_diacritic_language( $diacritics_language_match );
		} else {
			$settings->set_smart_diacritics( false );
		}

		// Some additional adjustments might be necessary.
		$adjustment = $this->match_locale( $language, $country, $modifier );

		// Apply the adjustments and run filters.
		$this->adjust_french_punctuation_spacing( $settings, $locale, $adjustment );
		$this->adjust_dash_style( $settings, $locale, $adjustment );
		$this->adjust_quote_styles( $settings, $locale, $adjustment );

		// It is finished.
		return $settings;
	}

	/**
	 * Apply language-specific adjustments to the defaults array.
	 *
	 * @param  array $defaults An array of default values indexed by the option name.
	 *
	 * @return array
	 */
	public function filter_defaults( array $defaults ) {
		list(, $language, $country, $modifier ) = $this->get_current_locale();

		// Standard adjustments.
		$adjustment = $this->match_locale( $language, $country, $modifier );

		if ( null !== $adjustment ) {
			$defaults = $adjustment->adjust_defaults( $defaults );
		}

		// Adjust hyphenation language.
		$hyphenation_language_match = $this->match_language( $this->plugin->load_hyphenation_languages(), "$language-$country", $language, 'hyphenation' );
		if ( ! empty( $hyphenation_language_match ) ) {
			$defaults['typo_hyphenate_languages'] = $hyphenation_language_match;
		} else {
			$defaults['typo_enable_hyphenation'] = false;
		}

		// Adjust diacritics replacement language.
		$diacritics_language_match  = $this->match_language( $this->plugin->load_diacritic_languages(), "$language-$country", $language, 'diacritics' );
		if ( ! empty( $diacritics_language_match ) ) {
			$defaults['typo_diacritic_languages'] = $diacritics_language_match;
		}

		return $defaults;
	}

	/**
	 * Adjust the setting for French punctuation spacing based on locale and language.
	 *
	 * @param  Settings             $settings   The settings instance to adjust.
	 * @param  string               $locale     A locale (e.g. 'en-US').
	 * @param  Locale_Settings|null $adjustment Locale-specific settings.
	 */
	protected function adjust_french_punctuation_spacing( Settings $settings, $locale, Locale_Settings $adjustment = null ) {

		if ( null === $adjustment ) {
			$french_spacing = false;
		} else {
			$french_spacing = $adjustment->use_french_punctuation_spacing();
		}

		/**
		 * Filters the setting for French punctuation spacing for the current locale.
		 *
		 * @param bool   $enable Whether French punctuation spacing should be enabled.
		 * @param string $locale The current locale with '-' as the separating character (e.g. 'en-US').
		 */
		$settings->set_french_punctuation_spacing( apply_filters( 'typo_enable_french_punctuation_spacing_for_locale', $french_spacing, $locale ) );
	}

	/**
	 * Adjust dash style based on locale and language.
	 *
	 * @param  Settings             $settings   The settings instance to adjust.
	 * @param  string               $locale     A locale (e.g. 'en-US').
	 * @param  Locale_Settings|null $adjustment Locale-specific settings.
	 */
	protected function adjust_dash_style( Settings $settings, $locale, Locale_Settings $adjustment = null ) {

		if ( null === $adjustment ) {
			$dash_style = $settings->dash_style();
		} else {
			$dash_style = $adjustment->dash_style();
		}

		/**
		 * Filters the dash style for the current locale.
		 *
		 * The returned value has to be a valid style constant from \PHP_Typography\Settings\Dash_Style.
		 *
		 * @param string|Dash $dash_style A style constant (Dash_Style::TRADITIONAL_US or Dash_Style::INTERNATIONAL) or object.
		 * @param string      $locale     The current locale with '-' as the separating character (e.g. 'en-US').
		 */
		$settings->set_smart_dashes_style( apply_filters( 'typo_dash_style_for_locale', $dash_style, $locale ) );
	}

	/**
	 * Adjust the primary and secondary quote styles based on locale and language.
	 *
	 * @param  Settings             $settings   The settings instance to adjust.
	 * @param  string               $locale     A locale (e.g. 'en-US').
	 * @param  Locale_Settings|null $adjustment Locale-specific settings.
	 */
	protected function adjust_quote_styles( Settings $settings, $locale, Locale_Settings $adjustment = null ) {

		if ( null === $adjustment ) {
			$primary   = $settings->primary_quote_style();
			$secondary = $settings->secondary_quote_style();
		} else {
			$primary   = $adjustment->primary_quote_style();
			$secondary = $adjustment->secondary_quote_style();
		}

		/**
		 * Filters the primary quote style for the current locale.
		 *
		 * The returned value has to be a valid style constant from \PHP_Typography\Settings\Quote_Style or a \PHP_Typography\Settings\Quotes instance.
		 *
		 * @param string|Quotes $primary A quote style constant or object.
		 * @param string        $locale  The current locale with '-' as the separating character (e.g. 'en-US').
		 */
		$primary = apply_filters( 'typo_primary_quote_style_for_locale', $primary, $locale );
		if ( ! empty( $primary ) ) {
			$settings->set_smart_quotes_primary( $primary );
		}

		/**
		 * Filters the secondary quote style for the current locale.
		 *
		 * The returned value has to be a valid style constant from \PHP_Typography\Settings\Quote_Style or a \PHP_Typography\Settings\Quotes instance.
		 *
		 * @param string|Quotes $secondary A quote style constant or object.
		 * @param string        $locale    The current locale with '-' as the separating character (e.g. 'en-US').
		 */
		$secondary = apply_filters( 'typo_secondary_quote_style_for_locale', $secondary, $locale );
		if ( ! empty( $secondary ) ) {
			$settings->set_smart_quotes_secondary( $secondary );
		}
	}

	/**
	 * Returns a matching Locale_Settings object, if possible.
	 *
	 * @param  string $language A two- or three-letter language code (e.g. 'de').
	 * @param  string $country  A two-letter upper-case country code (e.g. 'DE').
	 * @param  string $modifier Optional. An modifier for the locale (e.g. 'formal'). Default ''.

	 * @return Locale_Settings|null
	 */
	protected function match_locale( $language, $country, $modifier = '' ) {

		foreach ( $this->locales as $locale_settings ) {
			if ( $locale_settings->match( $language, $country, $modifier ) ) {
				return $locale_settings;
			}
		}

		return null;
	}

	/**
	 * Retrieve the current post locale (and default to the global locale if necessary).
	 *
	 * Underscores are automatically replaced with hyphens.
	 *
	 * @return array {
	 *         The locale and its components.
	 *
	 *         @type string $locale   A locale string like de_DE_formal.
	 *         @type string $language A two- or three-letter language code (e.g. 'de').
	 *         @type string $country  A two-letter country code (e.g. 'DE').
	 *         @type string $modifier An optional modifier string (e.g. 'formal'). Default ''.
	 * }
	 */
	protected function get_current_locale() {
		/**
		 * Filters the current locale for wp-Typography.
		 *
		 * Return a non-empty string to short-circuit the automatic locale detection.
		 *
		 * @param string $locale Default ''.
		 */
		$locale = apply_filters( 'typo_current_locale', '' );
		if ( '' === $locale ) {
			$locale = get_locale();
		}

		// Split locale into its parts.
		$first_dash  = strpos( $locale, '_' );
		$language    = substr( $locale, 0, $first_dash );
		$country     = substr( $locale, $first_dash + 1, 2 );
		$second_dash = strpos( $locale, '_', $first_dash + 1 );
		$modifier    = false !== $second_dash ? substr( $locale, $second_dash + 1 ) : '';

		return [ $locale, $language, $country, $modifier ];
	}

	/**
	 * Match a 2-letter language code to an index in our languages list.
	 *
	 * @param  array  $languages An array of languages ( CODE => NAME ).
	 * @param  string $locale    A locale string in the form en-US (i.e. with - instead of _).
	 * @param  string $language  A 2-letter language code.
	 * @param  string $type      Either "hyphenation" or "diacritics".
	 *
	 * @return String            An index in the languages array (or '' if not match was possible).
	 */
	protected function match_language( array $languages, $locale, $language, $type ) {
		/**
		 * Filters the matched language.
		 *
		 * @param string $match     The matched PHP-Typography language code. Default ''.
		 * @param array  $languages {
		 *    The list of available langugaes.
		 *
		 *    @type string $code Language name.
		 * }
		 * @param string $locale    The current locale, separated with a dash (e.g. 'en-US').
		 * @param string $language  The current two-letter language code (e.g. 'en').
		 */
		$result = apply_filters( "typo_match_{$type}_language", '', $languages, $locale, $language );
		if ( '' !== $result ) {
			return $result;
		}

		// Short-circuit if there are direct matches.
		if ( isset( $languages[ $locale ] ) ) {
			return $locale;
		} elseif ( isset( $languages[ $language ] ) ) {
			return $language;
		}

		// Try some heuristics..
		$matches     = preg_grep( "/^{$language}-/", array_keys( $languages ) );
		$match_count = count( $matches );

		if ( 1 === $match_count ) {
			$result = array_pop( $matches );
		} elseif ( $match_count > 1 ) {
			// Narrow the search further.
			$matches     = preg_grep( "/^{$locale}/", $matches );
			$match_count = count( $matches );

			if ( 1 === $match_count ) {
				$result = array_pop( $matches );
			}
		}

		return $result;
	}
}
