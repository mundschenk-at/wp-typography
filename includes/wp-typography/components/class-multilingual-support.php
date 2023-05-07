<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2023 Peter Putzer.
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

namespace WP_Typography\Components;

use WP_Typography\Implementation;
use WP_Typography\Settings\Basic_Locale_Settings;
use WP_Typography\Settings\Locale_Settings;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\Settings;
use PHP_Typography\Settings\Dashes;
use PHP_Typography\Settings\Quotes;

/**
 * Multilingual_Support support for wp-Typography.
 *
 * @since  5.0.0
 * @since  5.7.0 Method `initialize_locale_settings` removed in favor of dependency injection.
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @phpstan-type Locale array{
 *     locale   : string,
 *     language : string,
 *     country  : string,
 *     modifier : string,
 * }
 */
class Multilingual_Support implements Plugin_Component {

	const MATCH_TYPE_HYPHENATION = 'hyphenation';
	const MATCH_TYPE_DIACRITIC   = 'diacritic';

	const LANGUAGE_ALIASES = [
		'bal' => 'ca',
		'oci' => 'oc',
		'ory' => 'or',
		'tuk' => 'tk',
	];
	const LOCALE_ALIASES   = [
		'el'    => 'el-Mono',
		'el-po' => 'el-Poly',
	];

	/**
	 * An array of Locale_Settings.
	 *
	 * @var Locale_Settings[]
	 */
	protected $locales = [];

	/**
	 * The list of available hyhphenation languages.
	 *
	 * @var string[]
	 */
	protected $hyphenation_languages;

	/**
	 * The list of available diacritics replacement languages.
	 *
	 * @var string[]
	 */
	protected $diacritic_languages;

	/**
	 * The plugin API.
	 *
	 * @since 5.7.0 Renamed to $api.
	 *
	 * @var \WP_Typography
	 */
	protected $api;

	/**
	 * Create a new instace.
	 *
	 * @since 5.7.0
	 *
	 * @param Implementation    $api     The core API.
	 * @param Locale_Settings[] $locales An array of locales. Will be re-sorted
	 *                                   according to `Locale_Settings::priority()`.
	 */
	public function __construct( Implementation $api, array $locales ) {
		$this->api = $api;

		// Ensure proper priority order for locales.
		usort( $locales, [ $this, 'locale_settings_sort' ] );
		$this->locales = $locales;
	}

	/**
	 * Set up the various hooks for multilingual support.
	 *
	 * @since 5.7.0 Parameter $plugin removed.
	 */
	public function run(): void {
		// Enable multilingual support.
		\add_action( 'plugins_loaded', [ $this, 'add_plugin_defaults_filter' ] );
		\add_action( 'init',           [ $this, 'enable_automatic_language_settings' ] );
	}

	/**
	 * Adds a filter for the plugin defaults.
	 */
	public function add_plugin_defaults_filter(): void {
		// Translation of language names is irrelevant here.
		$this->hyphenation_languages = $this->api->get_hyphenation_languages();
		$this->diacritic_languages   = $this->api->get_diacritic_languages();

		// Filter the defaults.
		\add_filter( 'typo_plugin_defaults', [ $this, 'filter_defaults' ] );
	}

	/**
	 * Enable multilingual settings.
	 */
	public function enable_automatic_language_settings(): void {
		if ( $this->api->get_config()[ Config::ENABLE_MULTILINGUAL_SUPPORT ] ) {
			\add_filter( 'typo_settings', [ $this, 'automatic_language_settings' ] );
		}
	}

	/**
	 * Compares two Locale_Settings by their priority. Basically, this is a replacement
	 * for the PHP 7 spaceship operator `<=>`.
	 *
	 * @param  Locale_Settings $s1 First operand.
	 * @param  Locale_Settings $s2 Second operand.
	 *
	 * @return int             Returns 0 if both operands are equal, -1 if the
	 *                         first operand is greater, 1 if the second one is greater.
	 */
	protected function locale_settings_sort( Locale_Settings $s1, Locale_Settings $s2 ): int {
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
	}

	/**
	 * Adjusts the settings based on the current post language.
	 *
	 * @param Settings $settings The settings.
	 *
	 * @return Settings
	 */
	public function automatic_language_settings( Settings $settings ): Settings {

		// Ensure that default settings stay unmodified.
		$settings = clone $settings;

		// Determine locale.
		[ 'locale' => $locale, 'language' => $language, 'country' => $country, 'modifier' => $modifier ] = $this->get_current_locale();

		// Adjust hyphenation language.
		$hyphenation_language_match = $this->match_language( $this->hyphenation_languages, "$language-$country", $language, self::MATCH_TYPE_HYPHENATION );
		if ( ! empty( $hyphenation_language_match ) ) {
			$settings->set_hyphenation_language( $hyphenation_language_match );
		} else {
			$settings->set_hyphenation( false );
		}

		// Adjust diacritics replacement language.
		$diacritics_language_match = $this->match_language( $this->diacritic_languages, "$language-$country", $language, self::MATCH_TYPE_DIACRITIC );
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
	 * @param  array<string,string|int|bool> $defaults An array of default values indexed by the option name.
	 *
	 * @return array<string,string|int|bool>
	 */
	public function filter_defaults( array $defaults ): array {
		[ 'language' => $language, 'country' => $country, 'modifier' => $modifier ] = $this->get_current_locale();

		// Standard adjustments.
		$adjustment = $this->match_locale( $language, $country, $modifier );

		if ( null !== $adjustment ) {
			$defaults = $adjustment->adjust_defaults( $defaults );
		}

		// Adjust hyphenation language.
		$hyphenation_language_match = $this->match_language( $this->hyphenation_languages, "$language-$country", $language, self::MATCH_TYPE_HYPHENATION );
		if ( ! empty( $hyphenation_language_match ) ) {
			$defaults[ Config::HYPHENATE_LANGUAGES ] = $hyphenation_language_match;
		} else {
			$defaults[ Config::ENABLE_HYPHENATION ] = false;
		}

		// Adjust diacritics replacement language.
		$diacritics_language_match = $this->match_language( $this->diacritic_languages, "$language-$country", $language, self::MATCH_TYPE_DIACRITIC );
		if ( ! empty( $diacritics_language_match ) ) {
			$defaults[ Config::DIACRITIC_LANGUAGES ] = $diacritics_language_match;
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
	protected function adjust_french_punctuation_spacing( Settings $settings, $locale, Locale_Settings $adjustment = null ): void {

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
	protected function adjust_dash_style( Settings $settings, $locale, Locale_Settings $adjustment = null ): void {

		if ( null === $adjustment ) {
			$dash_style = $settings->dash_style();
		} else {
			$dash_style = $adjustment->dash_style();
		}

		/**
		 * Filters the dash style for the current locale.
		 *
		 * The returned value has to be a valid style constant from \PHP_Typography\Settings\Dash_Style or a Dashes instance.
		 *
		 * @since 5.9.0 Type hint for parameter `$dash_style` fixed.
		 *
		 * @param string|Dashes $dash_style A style constant (Dash_Style::TRADITIONAL_US or Dash_Style::INTERNATIONAL) or object.
		 * @param string        $locale     The current locale with '-' as the separating character (e.g. 'en-US').
		 */
		$settings->set_smart_dashes_style( \apply_filters( 'typo_dash_style_for_locale', $dash_style, $locale ) );
	}

	/**
	 * Adjust the primary and secondary quote styles based on locale and language.
	 *
	 * @param  Settings             $settings   The settings instance to adjust.
	 * @param  string               $locale     A locale (e.g. 'en-US').
	 * @param  Locale_Settings|null $adjustment Locale-specific settings.
	 */
	protected function adjust_quote_styles( Settings $settings, $locale, Locale_Settings $adjustment = null ): void {

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
		 * The returned value has to be a valid style constant from \PHP_Typography\Settings\Quote_Style or a Quotes instance.
		 *
		 * @param string|Quotes $primary A quote style constant or object.
		 * @param string        $locale  The current locale with '-' as the separating character (e.g. 'en-US').
		 */
		$primary = \apply_filters( 'typo_primary_quote_style_for_locale', $primary, $locale );
		if ( ! empty( $primary ) ) {
			$settings->set_smart_quotes_primary( $primary );
		}

		/**
		 * Filters the secondary quote style for the current locale.
		 *
		 * The returned value has to be a valid style constant from \PHP_Typography\Settings\Quote_Style or a Quotes instance.
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
	 *
	 * @return Locale_Settings|null
	 */
	protected function match_locale( $language, $country, $modifier = '' ): ?Locale_Settings {

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
	 *         @type string $locale   A locale string like de-DE-formal.
	 *         @type string $language A two- or three-letter language code (e.g. 'de').
	 *         @type string $country  A two-letter country code (e.g. 'DE').
	 *         @type string $modifier An optional modifier string (e.g. 'formal'). Default ''.
	 * }
	 *
	 * @phpstan-return Locale
	 */
	protected function get_current_locale(): array {
		/**
		 * Filters the current locale for wp-Typography.
		 *
		 * Return a non-empty string to short-circuit the automatic locale detection.
		 *
		 * @since 5.0.0
		 *
		 * @param string $locale Default ''.
		 */
		$locale = \apply_filters( 'typo_current_locale', '' );
		if ( '' === $locale ) {
			$locale = \get_locale();
		}

		// Split locale into its parts.
		$first_dash = \strpos( $locale, '_' );
		if ( $first_dash ) {
			// The language is the part until the first underscore/dash (2 or 3 letters).
			$language = \substr( $locale, 0, $first_dash );

			// The country code always consists of the 2 letters after the underscore/dash.
			$country = (string) \substr( $locale, $first_dash + 1, 2 );

			// Some locales also have another underscore/dash, followed by an arbitrary modifer.
			$second_dash = \strpos( $locale, '_', $first_dash + 1 );
			$modifier    = $second_dash ? (string) \substr( $locale, $second_dash + 1 ) : '';
		} else {
			$language = $locale;
			$country  = '';
			$modifier = '';
		}

		return [
			'locale'   => \str_replace( '_', '-', $locale ),
			'language' => $language,
			'country'  => $country,
			'modifier' => $modifier,
		];
	}

	/**
	 * Match a 2-letter language code to an index in our languages list.
	 *
	 * @param  array<string,string> $languages An array of languages ( CODE => NAME ).
	 * @param  string               $locale    A locale string in the form en-US (i.e. with - instead of _).
	 * @param  string               $language  A 2-letter language code.
	 * @param  string               $type      Either "hyphenation" or "diacritic".
	 *
	 * @return String                          An index in the languages array (or '' if not match was possible).
	 */
	protected function match_language( array $languages, $locale, $language, $type ): string {
		/**
		 * Filters the matched language.
		 *
		 * @since 5.0.0
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

		// Normalize language & locale.
		$language = self::normalize( $language, self::LANGUAGE_ALIASES );
		$locale   = self::normalize( $locale, self::LOCALE_ALIASES );

		// Short-circuit if there are direct matches.
		if ( isset( $languages[ $locale ] ) ) {
			return $locale;
		} elseif ( isset( $languages[ $language ] ) ) {
			return $language;
		}

		// Try some heuristics..
		$matches     = \preg_grep( "/^{$language}-/", array_keys( $languages ) ) ?: []; // phpcs:ignore Universal.Operators.DisallowShortTernary -- ensure array type.
		$match_count = \count( $matches );

		if ( 1 === $match_count ) {
			$result = \array_pop( $matches );
		} elseif ( $match_count > 1 ) {
			// Narrow the search further.
			$matches     = \preg_grep( "/^{$locale}/", $matches ) ?: []; // phpcs:ignore Universal.Operators.DisallowShortTernary -- ensure array type.
			$match_count = \count( $matches );

			if ( 1 === $match_count ) {
				$result = \array_pop( $matches );
			}
		}

		return $result;
	}

	/**
	 * Normalizes the a language or locale given an alias array.
	 *
	 * @param  string   $key     Either a language or a locale.
	 * @param  string[] $aliases A mapping array.
	 *
	 * @return string
	 */
	protected static function normalize( $key, array $aliases ): string {
		return isset( $aliases[ $key ] ) ? $aliases[ $key ] : $key;
	}
}
