<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2023 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
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

use WP_Typography\Components\REST_API;

use WP_Typography\Data_Storage\Cache;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Data_Storage\Transients;

use WP_Typography\Settings\Tools as Settings_Tools;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\PHP_Typography;
use PHP_Typography\Settings;
use PHP_Typography\U;
use PHP_Typography\Hyphenator\Cache as Hyphenator_Cache;

/**
 * Implementation behind the `WP_Typography` facade.
 *
 * @since  5.3.0
 * @since  5.9.0 Return type declarations added.
 */
class Implementation extends \WP_Typography {

	/**
	 * The full version string of the plugin.
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * A hash containing the various plugin settings.
	 *
	 * @since 5.8.0 Renamed to $configuration.
	 *
	 * @var array<string,string|int|bool>
	 */
	private array $configuration;

	/**
	 * The PHP_Typography instance doing the actual work.
	 *
	 * @since 5.8.0 Renamed to $typography.
	 *
	 * @var PHP_Typography
	 */
	private PHP_Typography $typography;

	/**
	 * The PHP_Typography\Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $typo_settings;

	/**
	 * The Hyphenator_Cache instance.
	 *
	 * @var Hyphenator_Cache
	 */
	protected Hyphenator_Cache $hyphenator_cache;

	/**
	 * An abstraction of the WordPress transients API.
	 *
	 * @since 5.1.0
	 *
	 * @var Transients
	 */
	private Transients $transients;

	/**
	 * An abstraction of the WordPress object cache.
	 *
	 * @since 5.1.0
	 *
	 * @var Cache
	 */
	private Cache $cache;

	/**
	 * An abstraction of the WordPress Options API.
	 *
	 * @since 5.1.0
	 *
	 * @var Options
	 */
	private Options $options;

	/**
	 * The body classes for the current request.
	 *
	 * @var string[]
	 */
	private array $body_classes = [];

	/**
	 * An array of settings with their default value.
	 *
	 * @var array<string,string|int|bool>
	 */
	private array $default_settings;

	/**
	 * Sets up a new WP_Typography object.
	 *
	 * @since 5.1.0 Optional parameters $transients, $cache, $options, $setup, $public_if added.
	 *
	 * @param string     $version     The full plugin version string (e.g. "3.0.0-beta.2").
	 * @param Transients $transients  Required.
	 * @param Cache      $cache       Required.
	 * @param Options    $options     Required.
	 */
	public function __construct( $version, Transients $transients, Cache $cache, Options $options ) {
		// Basic set-up.
		$this->version = $version;

		// Initialize cache handlers.
		$this->transients = $transients;
		$this->cache      = $cache;

		// Initialize Options API handler.
		$this->options = $options;
	}

	/**
	 * Retrieves and caches the list of valid hyphenation languages.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Language names are translated.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @return string[] An array in the form of ( $language_code => $language ).
	 */
	public function get_hyphenation_languages(): array {
		return $this->load_languages( 'hyphenate_languages', [ PHP_Typography::class, 'get_hyphenation_languages' ], 'hyphenate' );
	}

	/**
	 * Retrieves and caches the list of valid diacritic replacement languages.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Language names are translated.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @return string[] An array in the form of ( $language_code => $language ).
	 */
	public function get_diacritic_languages(): array {
		return $this->load_languages( 'diacritic_languages', [ PHP_Typography::class, 'get_diacritic_languages' ], 'diacritic' );
	}

	/**
	 * Load and cache given language list.
	 *
	 * @param  string   $cache_key         A cache key.
	 * @param  callable $get_language_list Retrieval function for the language list.
	 * @param  string   $type              Either 'diacritic' or 'hyphenate'.
	 *
	 * @return string[]
	 */
	protected function load_languages( $cache_key, callable $get_language_list, $type ): array {
		// Try to load hyphenation language list from cache.
		$languages = $this->cache->get( $cache_key, $found );

		// Dynamically generate the list of hyphenation language patterns.
		if ( false === $found || ! \is_array( $languages ) ) {
			$languages = self::translate_languages( $get_language_list() );

			/**
			 * Filters the caching duration for the language plugin lists.
			 *
			 * @since 3.2.0
			 * @since 5.9.0 Type of parameter `$duration` corrected to `int`.
			 *
			 * @param int    $duration The duration in seconds. Defaults to 1 week.
			 * @param string $list     The name language plugin list.
			 */
			$duration = \apply_filters( 'typo_language_list_caching_duration', WEEK_IN_SECONDS, "{$type}_languages" );

			// Cache translated hyphenation languages.
			$this->cache->set( $cache_key, $languages, $duration );
		}

		return $languages;
	}

	/**
	 * Translate language list.
	 *
	 * @param string[] $languages An array in the form [ LANGUAGE_CODE => LANGUAGE ].
	 *
	 * @return string[] The same array with the language name translated.
	 */
	private static function translate_languages( array $languages ): array {
		\array_walk(
			$languages,
			function( &$lang, $code ) {
				// The language names are made visible to GlotPress via the
				// autogenerated source file `_language_names.php` (which is not
				// actually included anywhere).
				$lang = \_x( $lang, 'language name', 'wp-typography' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			}
		);

		// Re-sort after translation.
		\asort( $languages );

		return $languages;
	}

	/**
	 * Retrieves the plugin configuration.
	 *
	 * @return array<string,string|int|bool>
	 */
	public function get_config(): array {
		if ( ! isset( $this->configuration ) ) {
			$config = $this->options->get( Options::CONFIGURATION );
			if ( \is_array( $config ) ) {
				$config_is_dirty = false;

				// Include new default options.
				foreach ( $this->get_default_options() as $option_name => $default_value ) {
					if ( ! isset( $config[ $option_name ] ) ) {
						$config[ $option_name ] = $default_value;
						$config_is_dirty        = true;
					}
				}

				// Persist the configuration in the DB if it is dirty.
				if ( $config_is_dirty ) {
					$this->options->set( Options::CONFIGURATION, $config );
				}

				// Use the updated configuration.
				$this->configuration = $config;
			} else {
				// The configuration array has been corrupted.
				$this->configuration = [];
				$this->set_default_options( true );
			}
		}

		return $this->configuration;
	}

	/**
	 * Retrieves the internal Settings object for the preferences set via the
	 * plugin options screen.
	 *
	 * @since 5.0.0
	 *
	 * @return Settings
	 */
	public function get_settings(): Settings {

		// Initialize Settings instance.
		if ( empty( $this->typo_settings ) ) {
			$config    = $this->get_config();
			$transient = 'php_settings_' . \md5( (string) \wp_json_encode( $config ) );
			$settings  = $this->transients->get_large_object( $transient );

			if ( ! $settings instanceof Settings ) {
				// OK, we have to initialize the PHP_Typography instance manually.
				$settings = new Settings( false );

				// Load our options into the Settings instance.
				$this->init_settings_from_options( $settings, $config );

				// Try again next time.
				$this->transients->cache_object( $transient, $settings, 'settings' );
			}

			// Make parser errors filterable on an individual level.
			$settings->set_parser_errors_handler( [ $this, 'parser_errors_handler' ] );

			// Settings should be good now, so let's use them.
			$this->typo_settings = $settings;
		}

		return $this->typo_settings;
	}

	/**
	 * Processes a heading text fragment.
	 *
	 * Calls `process( $text, true, $settings )`.
	 *
	 * @since 4.0.0 Parameter $settings added.
	 *
	 * @param string        $text     The HTML fragment to process.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string                 The processed `$text`.
	 */
	public function process_title( $text, Settings $settings = null ): string {
		return $this->process( $text, true, false, $settings );
	}

	/**
	 * Processes a heading text fragment as part of an RSS feed.
	 *
	 * Calls `process_feed( $text, true, $settings )`.
	 *
	 * @since 5.3.0
	 *
	 * @param string        $text     The HTML fragment to process.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string                 The processed `$text`.
	 */
	public function process_feed_title( $text, Settings $settings = null ): string {
		return $this->process_feed( $text, true, $settings );
	}

	/**
	 * Processes a content text fragment as part of an RSS feed.
	 *
	 * Calls `process( $text, $is_title, true )`.
	 *
	 * @since 3.2.4
	 * @since 4.0.0 Parameter $settings added.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @param string        $text     The HTML fragment to process.
	 * @param bool          $is_title Optional. Whether the rules for titles should be applied. Default false.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string                 The processed `$text`.
	 */
	public function process_feed( $text, $is_title = false, Settings $settings = null ): string {
		return $this->process( $text, $is_title, true, $settings );
	}

	/**
	 * Processes title parts and strips &shy; and zero-width space.
	 *
	 * @since 3.2.5
	 * @since 4.0.0 Parameter $settings added.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @param string[]      $title_parts An array of strings.
	 * @param Settings|null $settings    Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string[]
	 */
	public function process_title_parts( array $title_parts, Settings $settings = null ): array {
		foreach ( $title_parts as $index => $part ) {
			// Remove "&shy;" and "&#8203;" after processing title part.
			$title_parts[ $index ] = \wp_strip_all_tags(
				\str_replace( [ U::SOFT_HYPHEN, U::ZERO_WIDTH_SPACE ], '', $this->process( $part, true, true, $settings ) )
			);
		}

		return $title_parts;
	}

	/**
	 * Processes a text fragment.
	 *
	 * @since 3.2.4 Parameter $force_feed added.
	 * @since 4.0.0 Parameter $settings added.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @param string        $text       The HTML fragment to process.
	 * @param bool          $is_title   Optional. Whether the rules for titles should be applied. Default false.
	 * @param bool          $force_feed Optional. Whether to force processing according to feed rules. Default false.
	 * @param Settings|null $settings   Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string                   The processed `$text`.
	 */
	public function process( $text, $is_title = false, $force_feed = false, Settings $settings = null ): string {
		// Check if processing is disabled for this post.
		$post_id = \get_the_ID();
		$disable = ! empty( $post_id ) && \get_post_meta( $post_id, REST_API::WP_TYPOGRAPHY_DISABLED_META_KEY, true );

		/**
		 * Filters whether wp-Typography should be disabled for this post. This
		 * allows themes or plugins to override the post meta setting.
		 *
		 * @since 5.7.0
		 *
		 * @param bool     $disable  Flag for enabling or disabling typographic processing.
		 *                           Default is the value of the post meta toggle (or false for
		 *                           non-post uses).
		 * @param int|false $post_id The ID of the current item in the WordPress Loop. False if $post is not set.
		 */
		$disable = \apply_filters( 'typo_disable_processing_for_post', $disable, $post_id );

		// Return original HTML fragment if processing has been disabled.
		if ( $disable ) {
			return $text;
		}

		// Use default settings if no argument was given.
		if ( null === $settings ) {
			$settings = $this->get_settings();
		}

		/**
		 * Filters the settings object used for processing the text fragment.
		 *
		 * @since 5.0.0
		 *
		 * @param Settings $settings The settings instance.
		 */
		$settings = \apply_filters( 'typo_settings', $settings );

		return $this->maybe_process_fragment( $text, $is_title, $force_feed || \is_feed(), $settings );
	}

	/**
	 * Processes the HTML fragment and caches the result. May return a cached version
	 * if the settings and other parameters match.
	 *
	 * @since 5.7.0
	 *
	 * @param string   $text     The HTML fragment to process.
	 * @param bool     $is_title Whether the rules for titles should be applied.
	 * @param bool     $is_feed  Whether the rules for RSS feeds should be applied.
	 * @param Settings $settings A settings object.
	 *
	 * @return string            The processed `$text`.
	 */
	protected function maybe_process_fragment( $text, $is_title, $is_feed, Settings $settings ): string {
		// Construct cache key.
		$key = 'frag_' . \md5( $text ) . '_' . $settings->get_hash( 32, false ) . '_' . ( $is_feed ? 'f' : '' ) . ( $is_title ? 't' : 's' );

		// Retrieve cached text.
		$found          = false;
		$processed_text = $this->cache->get( $key, $found );

		if ( empty( $found ) || ! \is_string( $processed_text ) ) {
			// Feed readers are strange sometimes.
			$apply_typography = [ $this->get_typography_instance(), $is_feed ? 'process_feed' : 'process' ];
			$processed_text   = $apply_typography( $text, $settings, $is_title, $this->body_classes );

			/**
			 * Filters the caching duration for processed text fragments.
			 *
			 * @since 3.2.0
			 *
			 * @param int $duration The duration in seconds. Defaults to 1 day.
			 */
			$duration = \apply_filters( 'typo_processed_text_caching_duration', DAY_IN_SECONDS );

			// Save text fragment for later.
			$this->cache->set( $key, $processed_text, $duration );
		}

		return $processed_text;
	}

	/**
	 * Grabs the body classes from the filter hook.
	 *
	 * @param  string[] $classes An array of CSS classes.
	 *
	 * @return string[]
	 */
	public function filter_body_class( array $classes ): array {
		$this->body_classes = $classes;

		return $classes;
	}

	/**
	 * Retrieves the PHP_Typography instance and ensure just-in-time initialization.
	 *
	 * @since 5.9.0 Return typ documented.
	 *
	 * @return PHP_Typography
	 */
	protected function get_typography_instance(): PHP_Typography {

		// Retrieve options.
		$config = $this->get_config();

		// Initialize PHP_Typography instance.
		if ( ! isset( $this->typography ) ) {
			$transient = 'php_' . \md5( (string) \wp_json_encode( $config ) );
			$typo      = $this->transients->get_large_object( $transient );

			if ( ! $typo instanceof PHP_Typography ) {
				// OK, we have to initialize the PHP_Typography instance manually.
				$typo = new PHP_Typography( new Typography\Custom_Registry() );

				// Try again next time.
				$this->transients->cache_object( $transient, $typo, 'typography' );
			}

			$this->typography = $typo;
		}

		// Also cache hyphenators (the pattern tries are expensive to build).
		if ( $config[ Config::ENABLE_HYPHENATION ] && ! isset( $this->hyphenator_cache ) ) {
			$transient    = 'php_hyphenator_cache';
			$hyphen_cache = $this->transients->get_large_object( $transient );

			if ( ! $hyphen_cache instanceof Hyphenator_Cache ) {
				$hyphen_cache = $this->typography->get_hyphenator_cache();

				// Try again next time.
				$this->transients->cache_object( $transient, $hyphen_cache, 'hyphenator_cache' );
			}

			// Let's use it!
			$this->hyphenator_cache = $hyphen_cache;
			$this->typography->set_hyphenator_cache( $this->hyphenator_cache );
		}

		return $this->typography;
	}

	/**
	 * Save hyphenator cache for the next request.
	 */
	public function save_hyphenator_cache_on_shutdown(): void {
		if ( isset( $this->hyphenator_cache ) && $this->hyphenator_cache->has_changed() ) {
			$this->transients->cache_object( 'php_hyphenator_cache', $this->hyphenator_cache, 'hyphenator_cache' );
		}
	}

	/**
	 * Initializes the Settings object for PHP_Typography from the plugin options.
	 *
	 * @param Settings $s      The settings instance to initialize.
	 * @param mixed[]  $config The array of configuration entries.
	 */
	protected function init_settings_from_options( Settings $s, array $config ): void {
		// Necessary for full Settings initialization.
		$s->set_smart_dashes_style( $config[ Config::SMART_DASHES_STYLE ] );
		$s->set_smart_quotes_primary( $config[ Config::SMART_QUOTES_PRIMARY ] );
		$s->set_smart_quotes_secondary( $config[ Config::SMART_QUOTES_SECONDARY ] );

		// Load the rest of the configuration variables into our Settings class.
		$s->set_tags_to_ignore( $config[ Config::IGNORE_TAGS ] );
		$s->set_classes_to_ignore( $config[ Config::IGNORE_CLASSES ] );
		$s->set_ids_to_ignore( $config[ Config::IGNORE_IDS ] );

		// Remap problematic Unicode characters.
		$hyphen = ! empty( $config[ Config::REMAP_HYPHEN ] ) ? U::HYPHEN_MINUS : U::HYPHEN;
		$s->remap_character( U::HYPHEN, $hyphen );

		/**
		 * Filters whether to use the NARROW NO-BREAK SPACE character (U+202F, &#8239;)
		 * where appropriate.
		 *
		 * Historically, browser and/or font support for this character has been limited,
		 * so by default it is replaced by the normal (non-narrow) NO-BREAK SPACE (U+00A0, &nbsp;).
		 *
		 * @since 5.1.0
		 *
		 * @deprecated 5.6.0 Use the GUI setting instead or filter the option
		 *                   'remap_narrow_no_break_space' (with inverse meaning
		 *                   of the boolean value).
		 *
		 * @param bool $enable Default true if the UI is set to not remap the character.
		 */
		$narrow_no_break_space = \apply_filters( 'typo_narrow_no_break_space', empty( $config[ Config::REMAP_NARROW_NO_BREAK_SPACE ] ) ) ? U::NO_BREAK_NARROW_SPACE : U::NO_BREAK_SPACE;
		$s->remap_character( U::NO_BREAK_NARROW_SPACE, $narrow_no_break_space );

		if ( $config[ Config::SMART_CHARACTERS ] ) {
			$s->set_smart_dashes( $config[ Config::SMART_DASHES ] );
			$s->set_smart_ellipses( $config[ Config::SMART_ELLIPSES ] );
			$s->set_smart_math( $config[ Config::SMART_MATH ] );

			// Note: smart_exponents was grouped with smart_math for the WordPress plugin,
			// but does not have to be done that way for other ports.
			$s->set_smart_exponents( $config[ Config::SMART_MATH ] );
			$s->set_smart_fractions( $config[ Config::SMART_FRACTIONS ] );
			$s->set_smart_ordinal_suffix( $config[ Config::SMART_ORDINALS ] );
			$s->set_smart_ordinal_suffix_match_roman_numerals( $config[ Config::SMART_ORDINALS_ROMAN_NUMBERS ] );
			$s->set_smart_marks( $config[ Config::SMART_MARKS ] );
			$s->set_smart_area_units( $config[ Config::SMART_AREA_UNITS ] );
			$s->set_smart_quotes( $config[ Config::SMART_QUOTES ] );
			$s->set_smart_quotes_exceptions( $this->prepare_smart_quotes_exceptions( $config[ Config::SMART_QUOTES_EXCEPTIONS ] ) );

			$s->set_smart_diacritics( $config[ Config::SMART_DIACRITICS ] );
			$s->set_diacritic_language( $config[ Config::DIACRITIC_LANGUAGES ] );
			$s->set_diacritic_custom_replacements( $config[ Config::DIACRITIC_CUSTOM_REPLACEMENTS ] );
		} else {
			$s->set_smart_dashes( false );
			$s->set_smart_ellipses( false );
			$s->set_smart_math( false );
			$s->set_smart_exponents( false );
			$s->set_smart_fractions( false );
			$s->set_smart_ordinal_suffix( false );
			$s->set_smart_marks( false );
			$s->set_smart_area_units( false );
			$s->set_smart_quotes( false );
			$s->set_smart_diacritics( false );
		}

		// Space control.
		$s->set_single_character_word_spacing( $config[ Config::SINGLE_CHARACTER_WORD_SPACING ] );
		$s->set_dash_spacing( $config[ Config::DASH_SPACING ] );
		$s->set_fraction_spacing( $config[ Config::FRACTION_SPACING ] );
		$s->set_unit_spacing( $config[ Config::UNIT_SPACING ] );
		$s->set_numbered_abbreviation_spacing( $config[ Config::NUMBERED_ABBREVIATIONS_SPACING ] );
		$s->set_french_punctuation_spacing( $config[ Config::FRENCH_PUNCTUATION_SPACING ] );
		$s->set_units( $config[ Config::UNITS ] );
		$s->set_space_collapse( $config[ Config::SPACE_COLLAPSE ] );
		$s->set_dewidow( $config[ Config::PREVENT_WIDOWS ] );
		$s->set_max_dewidow_length( $config[ Config::WIDOW_MIN_LENGTH ] );
		$s->set_max_dewidow_pull( $config[ Config::WIDOW_MAX_PULL ] );
		$s->set_dewidow_word_number( 1 );

		// Line wrapping.
		$s->set_wrap_hard_hyphens( $config[ Config::WRAP_HYPHENS ] );
		$s->set_email_wrap( $config[ Config::WRAP_EMAILS ] );
		$s->set_url_wrap( $config[ Config::WRAP_URLS ] );
		$s->set_min_after_url_wrap( $config[ Config::WRAP_MIN_AFTER ] );

		// CSS hooks.
		$s->set_style_ampersands( $config[ Config::STYLE_AMPS ] );
		$s->set_style_caps( $config[ Config::STYLE_CAPS ] );
		$s->set_style_numbers( $config[ Config::STYLE_NUMBERS ] );
		$s->set_style_hanging_punctuation( $config[ Config::STYLE_HANGING_PUNCTUATION ] );
		$s->set_style_initial_quotes( $config[ Config::STYLE_INITIAL_QUOTES ] );
		$s->set_initial_quote_tags( $config[ Config::INITIAL_QUOTE_TAGS ] );

		if ( $config[ Config::ENABLE_HYPHENATION ] ) {
			$s->set_hyphenation( true );
			$s->set_hyphenate_headings( $config[ Config::HYPHENATE_HEADINGS ] );
			$s->set_hyphenate_all_caps( $config[ Config::HYPHENATE_CAPS ] );
			$s->set_hyphenate_title_case( $config[ Config::HYPHENATE_TITLE_CASE ] );
			$s->set_hyphenate_compounds( $config[ Config::HYPHENATE_COMPOUNDS ] );
			$s->set_hyphenation_language( $config[ Config::HYPHENATE_LANGUAGES ] );
			$s->set_min_length_hyphenation( $config[ Config::HYPHENATE_MIN_LENGTH ] );
			$s->set_min_before_hyphenation( $config[ Config::HYPHENATE_MIN_BEFORE ] );
			$s->set_min_after_hyphenation( $config[ Config::HYPHENATE_MIN_AFTER ] );
			$s->set_hyphenation_exceptions( $config[ Config::HYPHENATE_EXCEPTIONS ] );
		} else { // save some cycles.
			$s->set_hyphenation( false );
		}

		/**
		 * Filters whether HTML parser errors should be silently ignored.
		 *
		 * The resulting HTML will be a "best guess" by the parser, like it was before version 3.5.2.
		 *
		 * @since 3.6.0
		 *
		 * @param bool $ignore Default false.
		 */
		$s->set_ignore_parser_errors( $config[ Config::IGNORE_PARSER_ERRORS ] || \apply_filters( 'typo_ignore_parser_errors', false ) );
	}

	/**
	 * Prepares a list of smart quotes exceptions from WordPress' "cockney" list
	 * and any custom exceptions configured in the settings, maintaining compatibility
	 * with the `wp_texturize` function.
	 *
	 * @since 5.6.0
	 *
	 * @internal
	 *
	 * @param  string $custom_exceptions Additional exceptions configured via the
	 *                                   settings page (as a comma-separated string).
	 *
	 * @return string[]                  An array of replacements, indexed by the
	 *                                   key and sorted by descending key length.
	 */
	protected function prepare_smart_quotes_exceptions( $custom_exceptions ): array {
		global $wp_cockneyreplace;

		// The combined exceptions list.
		$exceptions = [];

		// If a plugin has provided an autocorrect array, use it.
		if ( ! empty( $wp_cockneyreplace ) && \is_array( $wp_cockneyreplace ) ) {
			$exceptions = $wp_cockneyreplace;
		} else {
			/*
			* translators: This is a comma-separated list of words that defy the syntax of quotations in normal use,
			* for example...  'We do not have enough words yet' ... is a typical quoted phrase.  But when we write
			* lines of code 'til we have enough of 'em, then we need to insert apostrophes instead of quotes.
			*/
			$patterns = \explode(
				',',
				// @codeCoverageIgnoreStart
				\_x( // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- text domain missing to use Core translations.
					"'tain't,'twere,'twas,'tis,'twill,'til,'bout,'nuff,'round,'cause,'em",
					'Comma-separated list of words to texturize in your language'
				)
				// @codeCoverageIgnoreEnd
			);
			$replacements = \explode(
				',',
				\html_entity_decode(
					// @codeCoverageIgnoreStart
					\_x(  // phpcs:ignore WordPress.WP.I18n.MissingArgDomain -- text domain missing to use Core translations.
						'&#8217;tain&#8217;t,&#8217;twere,&#8217;twas,&#8217;tis,&#8217;twill,&#8217;til,&#8217;bout,&#8217;nuff,&#8217;round,&#8217;cause,&#8217;em',
						'Comma-separated list of replacement words in your language'
					),
					// @codeCoverageIgnoreEnd
					\ENT_QUOTES | \ENT_HTML5,
					'UTF-8'
				)
			);

			// @phpstan-ignore-next-line -- Ternary condition is not always true on PHP 7.4.
			$exceptions = \array_combine( $patterns, $replacements ) ?: []; // phpcs:ignore Universal.Operators.DisallowShortTernary -- ensure array type.
		}

		// If necessary, merge custom exceptions.
		$custom_exceptions = Settings_Tools::parse_smart_quote_exceptions_string( $custom_exceptions );
		if ( ! empty( $custom_exceptions ) ) {
			$exceptions = \array_merge( $exceptions, $custom_exceptions );
		}

		// Longest strings first.
		\uksort(
			$exceptions,
			function( $a, $b ) {
				return ( \strlen( $b ) - \strlen( $a ) ) ?: \strcmp( $a, $b ); // phpcs:ignore Universal.Operators.DisallowShortTernary
			}
		);

		return $exceptions;
	}

	/**
	 * Initializes the options with default values.
	 *
	 * @param bool $force_defaults Optional. Default false.
	 */
	public function set_default_options( $force_defaults = false ): void {
		$update = $force_defaults;

		// Grab configuration variables.
		foreach ( $this->get_default_options() as $key => $default ) {
			// Set or update the options with the default value if necessary.
			if ( $force_defaults || ! isset( $this->configuration[ $key ] ) ) {
				$this->configuration[ $key ] = $default;
				$update                      = true;
			}
		}

		// Update stored options.
		if ( $update ) {
			$this->options->set( Options::CONFIGURATION, $this->configuration );
		}

		if ( $force_defaults ) {
			// Push the reset switch.
			$this->options->delete( Options::RESTORE_DEFAULTS );
			$this->options->delete( Options::CLEAR_CACHE );
		}
	}

	/**
	 * Retrieves the plugin's default option values.
	 *
	 * @return array<string,string|int|bool>
	 */
	public function get_default_options(): array {
		if ( empty( $this->default_settings ) ) {
			/**
			 * Filters the default settings used to initialize the plugin.
			 *
			 * @since 5.1.0
			 *
			 * @param array $defaults The default settings indexed by their configuration key.
			 */
			$this->default_settings = (array) \apply_filters( 'typo_plugin_defaults', \wp_list_pluck( Config::get_defaults(), 'default' ) );
		}

		return $this->default_settings;
	}

	/**
	 * Clears all transients set by the plugin.
	 */
	public function clear_cache(): void {
		$this->transients->invalidate();
		$this->cache->invalidate();

		$this->options->delete( Options::CLEAR_CACHE );
	}

	/**
	 * Makes parser errors filterable.
	 *
	 * @param  string[] $errors An array of error messages.
	 *
	 * @return string[]         The filtered array.
	 */
	public function parser_errors_handler( $errors ): array {
		/**
		 * Filters the HTML parser errors (if there are any).
		 *
		 * @since 4.0.0
		 *
		 * @param array $errors An array of error messages.
		 */
		return \apply_filters( 'typo_handle_parser_errors', $errors );
	}

	/**
	 * Retrieves the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
