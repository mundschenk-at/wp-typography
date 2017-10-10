<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
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

use \WP_Typography\Admin;
use \WP_Typography\Cache;
use \WP_Typography\Transients;
use \WP_Typography\Settings\Multilingual;

use \PHP_Typography\PHP_Typography;
use \PHP_Typography\Settings;
use \PHP_Typography\Hyphenator\Cache as Hyphenator_Cache;

/**
 * Main wp-Typography plugin class. All WordPress specific code goes here.
 */
class WP_Typography {

	/**
	 * The full version string of the plugin.
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * The result of plugin_basename() for the main plugin file (relative from plugins folder).
	 *
	 * @var string $local_plugin_path
	 */
	private $local_plugin_path;

	/**
	 * A hash containing the various plugin settings.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The PHP_Typography instance doing the actual work.
	 *
	 * @var PHP_Typography
	 */
	private $typo;

	/**
	 * The PHP_Typography\Settings instance.
	 *
	 * @var Settings
	 */
	private $typo_settings;

	/**
	 * The Hyphenator_Cache instance.
	 *
	 * @var Hyphenator_Cache
	 */
	protected $hyphenator_cache;

	/**
	 * An abstraction of the WordPress transients API.
	 *
	 * @since 5.1.0
	 *
	 * @var Transients
	 */
	private $transients;

	/**
	 * An abstraction of the WordPress object cache.
	 *
	 * @since 5.1.0
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * The PHP_Typography configuration is not changed after initialization, so the settings hash can be cached.
	 *
	 * @var string The settings hash for the PHP_Typography instance
	 */
	private $cached_settings_hash;

	/**
	 * An array of settings with their default value.
	 *
	 * @var array
	 */
	private $default_settings;

	/**
	 * Whether the default settings have already been localized.
	 *
	 * @var bool
	 */
	private $default_settings_localized = false;

	/**
	 * The admin side handler object.
	 *
	 * @var Admin
	 */
	private $admin;

	/**
	 * The multlingual support object.
	 *
	 * @var Multilingual
	 */
	private $multilingual;

	/**
	 * The priority for our filter hooks.
	 *
	 * @var int
	 */
	private $filter_priority = 9999;

	/**
	 * The singleton instance.
	 *
	 * @var WP_Typography
	 */
	private static $_instance;

	/**
	 * Sets up a new WP_Typography object.
	 *
	 * @since 5.1.0 Optional parameters $transients and $cache added.
	 *
	 * @param string            $version    The full plugin version string (e.g. "3.0.0-beta.2").
	 * @param string            $basename   Optional. The result of plugin_basename() for the main plugin file. Default 'wp-typography/wp-typography.php'.
	 * @param Admin|null        $admin      Optional. Default null (which means a private instance will be created).
	 * @param Multilingual|null $multi      Optional. Default null (which means a private instance will be created).
	 * @param Transients|null   $transients Optional. Default null (which means a private instance will be created).
	 * @param Cache|null        $cache      Optional. Default null (which means a private instance will be created).
	 */
	public function __construct( $version, $basename = 'wp-typography/wp-typography.php', Admin $admin = null, Multilingual $multi = null, Transients $transients = null, Cache $cache = null ) {
		// Basic set-up.
		$this->version           = $version;
		$this->local_plugin_path = $basename;

		// Initialize cache handlers.
		$this->transients = ( null === $transients ) ? new Transients() : $transients;
		$this->cache      = ( null === $cache ) ? new Cache() : $cache;

		// Initialize admin interface handler.
		$this->admin = ( null === $admin ) ? new Admin( $basename, $this ) : $admin;

		// Initialize multilingual support.
		$this->multilingual = ( null === $multi ) ? new Multilingual( $this ) : $multi;
	}

	/**
	 * Starts the plugin for real.
	 */
	public function run() {
		// Set plugin singleton.
		self::set_instance( $this );

		// Ensure that our translations are loaded.
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );

		// Load settings.
		add_action( 'init', [ $this, 'init' ] );

		// Also run the backend UI.
		$this->admin->run();

		// Enable multilingual support.
		$this->multilingual->run();

		// Keep default settings.
		$this->default_settings = $this->admin->get_default_settings();
	}

	/**
	 * Retrieves (and if necessary creates) the WP_Typography instance. Should not be called outside of plugin set-up.
	 *
	 * @since 5.0.0
	 *
	 * @param WP_Typography|null $instance Only used for plugin initialization. Don't ever pass a value in user code.
	 *
	 * @throws BadMethodCallException Thrown when WP_Typography::set_instance after plugin initialization.
	 */
	private static function set_instance( WP_Typography $instance ) {
		if ( null === self::$_instance ) {
			self::$_instance = $instance;
		} else {
			throw new BadMethodCallException( 'WP_Typography::set_instance called more than once.' );
		}
	}

	/**
	 * Retrieves the plugin instance.
	 *
	 * @since 3.2.0
	 * @since 5.0.0 Errors handled ia exceptions.
	 *
	 * @throws BadMethodCallException Thrown when WP_Typography::get_instance is called before plugin initialization.
	 *
	 * @return WP_Typography
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			throw new BadMethodCallException( 'WP_Typography::get_instance called without prior plugin intialization.' );
		}

		return self::$_instance;
	}

	/**
	 * Retrieves a copy of the preferences set by the user via the plugin settings screen.
	 *
	 * @since 4.0.0
	 *
	 * @return Settings
	 */
	public static function get_user_settings() {
		return clone self::get_instance()->get_settings();
	}

	/**
	 * Retrieves the list of valid hyphenation languages.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Language names are translated.
	 *
	 * @return string[] An array in the form of ( $language_code => $language ).
	 */
	public static function get_hyphenation_languages() {
		return self::get_instance()->load_hyphenation_languages();
	}

	/**
	 * Retrieves and caches the list of valid hyphenation languages.
	 *
	 * @since 5.0.0
	 *
	 * @return string[] An array in the form of ( $language_code => $language ).
	 */
	public function load_hyphenation_languages() {
		return $this->load_languages( 'hyphenate_languages', [ PHP_Typography::class, 'get_hyphenation_languages' ], 'hyphenate' );
	}

	/**
	 * Retrieves the list of valid diacritic replacement languages.
	 *
	 * @since 4.0.0
	 * @since 5.0.0 Language names are translated.
	 *
	 * @return string[] An array in the form of ( $language_code => $language ).
	 */
	public static function get_diacritic_languages() {
		return self::get_instance()->load_diacritic_languages();
	}

	/**
	 * Retrieves and caches the list of valid diacritic replacement languages.
	 *
	 * @since 5.0.0
	 *
	 * @return string[] An array in the form of ( $language_code => $language ).
	 */
	public function load_diacritic_languages() {
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
	protected function load_languages( $cache_key, callable $get_language_list, $type ) {
		// Try to load hyphenation language list from cache.
		$languages = $this->cache->get( $cache_key, $found );

		// Dynamically generate the list of hyphenation language patterns.
		if ( false === $found || ! is_array( $languages ) ) {
			$languages = self::translate_languages( $get_language_list() );

			/**
			 * Filters the caching duration for the language plugin lists.
			 *
			 * @since 3.2.0
			 *
			 * @param number $duration The duration in seconds. Defaults to 1 week.
			 * @param string $list     The name language plugin list.
			 */
			$duration = apply_filters( 'typo_language_list_caching_duration', WEEK_IN_SECONDS, "{$type}_languages" );

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
	private static function translate_languages( array $languages ) {
		array_walk( $languages, function( &$lang, $code ) {
			$lang = _x( $lang, 'language name', 'wp-typography' );  // @codingStandardsIgnoreLine.
		} );

		return $languages;
	}

	/**
	 * Processes a content text fragment.
	 *
	 * @since 4.0.0
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter( $text, Settings $settings = null ) {
		return self::get_instance()->process( $text, false, false, $settings );
	}

	/**
	 * Processes a title text fragment.
	 *
	 * @since 4.0.0
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter_title( $text, Settings $settings = null ) {
		return self::get_instance()->process_title( $text, $settings );
	}

	/**
	 * Processes the title parts and strips &shy; and zero-width space.
	 *
	 * @since 4.0.0
	 *
	 * @param array         $title_parts An array of strings.
	 * @param Settings|null $settings    Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return array
	 */
	public static function filter_title_parts( $title_parts, Settings $settings = null ) {
		return self::get_instance()->process_title_parts( $title_parts, $settings );
	}

	/**
	 * Processes a content text fragment as part of an RSS feed (limiting HTML features to most widely compatible ones).
	 *
	 * @since 4.0.0
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter_feed( $text, Settings $settings = null ) {
		return self::get_instance()->process_feed( $text, false, $settings );
	}

	/**
	 * Processes a title text fragment as part of an RSS feed (limiting HTML features to most widely compatible ones).
	 *
	 * @since 4.0.0
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter_feed_title( $text, Settings $settings = null ) {
		return self::get_instance()->process_feed( $text, true, $settings );
	}

	/**
	 * Loads the settings from the option table.
	 */
	public function init() {

		// Restore defaults if necessary.
		if ( get_option( 'typo_restore_defaults' ) ) {  // any truthy value will do.
			$this->set_default_options( true );
		}

		// Clear cache if necessary.
		if ( get_option( 'typo_clear_cache' ) ) {  // any truthy value will do.
			$this->clear_cache();
		}

		// Load settings.
		foreach ( array_keys( $this->default_settings ) as $key ) {
			$this->options[ $key ] = get_option( $key );
		}

		// Enable multilingual support.
		if ( $this->options['typo_enable_multilingual_support'] ) {
			add_filter( 'typo_settings', [ $this->multilingual, 'automatic_language_settings' ] );
		}

		// Disable wptexturize filter if it conflicts with our settings.
		if ( $this->options['typo_smart_characters'] && ! is_admin() ) {
			add_filter( 'run_wptexturize', '__return_false' );

			// Ensure that wptexturize is actually off by forcing a re-evaluation (some plugins call it too early).
			wptexturize( ' ', true ); // Argument must not be empty string!
		}

		// Apply our filters.
		if ( ! is_admin() ) {
			$this->add_content_filters();

			// Save hyphenator cache on exit, if necessary.
			add_action( 'shutdown', [ $this, 'save_hyphenator_cache_on_shutdown' ], 10 );
		}

		// Add CSS Hook styling.
		add_action( 'wp_head', [ $this, 'add_wp_head' ] );

		// Optionally enable clipboard clean-up.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}


	/**
	 * Retrieves the internal Settings object for the preferences set via the
	 * plugin options screen.
	 *
	 * @since 5.0.0
	 *
	 * @return Settings
	 */
	public function get_settings() {

		// Initialize Settings instance.
		if ( empty( $this->typo_settings ) ) {
			$transient           = 'php_settings_' . md5( wp_json_encode( $this->options ) );
			$this->typo_settings = $this->maybe_fix_object( $this->transients->get_large_object( $transient ) );

			if ( ! $this->typo_settings instanceof Settings ) {
				// OK, we have to initialize the PHP_Typography instance manually.
				$this->typo_settings = new Settings( false );

				// Load our options into the Settings instance.
				$this->init_settings_from_options( $this->typo_settings );

				// Try again next time.
				$this->cache_object( $transient, $this->typo_settings, 'settings' );
			}

			// Settings won't be touched again, so cache the hash.
			$this->cached_settings_hash = $this->typo_settings->get_hash( 32, false );
		}

		return $this->typo_settings;
	}

	/**
	 * Adds content filter handlers.
	 */
	public function add_content_filters() {
		/**
		 * Filters the priority used for wp-Typography's text processing filters.
		 *
		 * When NextGen Gallery is detected, the priority is set to PHP_INT_MAX.
		 *
		 * @since 3.2.0
		 *
		 * @param int $priority The filter priority. Default 9999.
		 */
		$priority = apply_filters( 'typo_filter_priority', $this->filter_priority );

		// Add filters for "full" content.
		/**
		 * Disables automatic filtering by wp-Typography.
		 *
		 * @since 3.6.0
		 *
		 * @param bool   $disable      Whether to disable automatic filtering. Default false.
		 * @param string $filter_group Which filters to disable. Possible values 'content', 'heading', 'title', 'acf'.
		 */
		if ( ! apply_filters( 'typo_disable_filtering', false, 'content' ) ) {
			$this->enable_content_filters( $priority );
		}

		// Add filters for headings.
		/** This filter is documented in class-wp-typography.php */
		if ( ! apply_filters( 'typo_disable_filtering', false, 'heading' ) ) {
			$this->enable_heading_filters( $priority );
		}

		// Extra care needs to be taken with the <title> tag.
		/** This filter is documented in class-wp-typography.php */
		if ( ! apply_filters( 'typo_disable_filtering', false, 'title' ) ) {
			$this->enable_title_filters( $priority );
		}

		// Add filters for third-party plugins.
		/** This filter is documented in class-wp-typography.php */
		if ( class_exists( 'acf' ) && function_exists( 'acf_get_setting' ) && ! apply_filters( 'typo_disable_filtering', false, 'acf' ) ) {
			$this->enable_acf_filters( $priority );
		}
	}

	/**
	 * Enable the content (body) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_content_filters( $priority ) {
		add_filter( 'comment_author',    [ $this, 'process' ], $priority );
		add_filter( 'comment_text',      [ $this, 'process' ], $priority );
		add_filter( 'comment_text',      [ $this, 'process' ], $priority );
		add_filter( 'the_content',       [ $this, 'process' ], $priority );
		add_filter( 'term_name',         [ $this, 'process' ], $priority );
		add_filter( 'term_description',  [ $this, 'process' ], $priority );
		add_filter( 'link_name',         [ $this, 'process' ], $priority );
		add_filter( 'the_excerpt',       [ $this, 'process' ], $priority );
		add_filter( 'the_excerpt_embed', [ $this, 'process' ], $priority );
		add_filter( 'widget_text',       [ $this, 'process' ], $priority );
	}

	/**
	 * Enable the heading filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_heading_filters( $priority ) {
		add_filter( 'the_title',            [ $this, 'process_title' ], $priority );
		add_filter( 'single_post_title',    [ $this, 'process_title' ], $priority );
		add_filter( 'single_cat_title',     [ $this, 'process_title' ], $priority );
		add_filter( 'single_tag_title',     [ $this, 'process_title' ], $priority );
		add_filter( 'single_month_title',   [ $this, 'process_title' ], $priority );
		add_filter( 'single_month_title',   [ $this, 'process_title' ], $priority );
		add_filter( 'nav_menu_attr_title',  [ $this, 'process_title' ], $priority );
		add_filter( 'nav_menu_description', [ $this, 'process_title' ], $priority );
		add_filter( 'widget_title',         [ $this, 'process_title' ], $priority );
		add_filter( 'list_cats',            [ $this, 'process_title' ], $priority );
	}

	/**
	 * Enable the title (not heading) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_title_filters( $priority ) {
		add_filter( 'wp_title',             [ $this, 'process_feed' ],        $priority ); // WP < 4.4.
		add_filter( 'document_title_parts', [ $this, 'process_title_parts' ], $priority );
		add_filter( 'wp_title_parts',       [ $this, 'process_title_parts' ], $priority ); // WP < 4.4.
	}

	/**
	 * Enable the Advanced Custom Fields (https://www.advancedcustomfields.com) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_acf_filters( $priority ) {
		$acf_version = intval( acf_get_setting( 'version' ) );

		if ( 5 === $acf_version ) {
			// Advanced Custom Fields Pro (version 5).
			$acf_prefix = 'acf/format_value';
		} elseif ( 4 === $acf_version ) {
			// Advanced Custom Fields (version 4).
			$acf_prefix = 'acf/format_value_for_api';
		}

		// Other ACF versions (i.e. < 4) are not supported.
		if ( ! empty( $acf_prefix ) ) {
			add_filter( "{$acf_prefix}/type=wysiwyg",  [ $this, 'process' ],       $priority );
			add_filter( "{$acf_prefix}/type=textarea", [ $this, 'process' ],       $priority );
			add_filter( "{$acf_prefix}/type=text",     [ $this, 'process_title' ], $priority );
		}
	}

	/**
	 * Processes a heading text fragment.
	 *
	 * Calls `process( $text, true, $settings )`.
	 *
	 * @since 4.0.0 Parameter $settings added.
	 *
	 * @param string        $text Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 */
	public function process_title( $text, Settings $settings = null ) {
		return $this->process( $text, true, false, $settings );
	}

	/**
	 * Processes a content text fragment as part of an RSS feed.
	 *
	 * Calls `process( $text, $is_title, true )`.
	 *
	 * @since 3.2.4
	 * @since 4.0.0 Parameter $settings added.
	 *
	 * @param string        $text     Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 */
	public function process_feed( $text, $is_title = false, Settings $settings = null ) {
		return $this->process( $text, $is_title, true, $settings );
	}

	/**
	 * Processes title parts and strips &shy; and zero-width space.
	 *
	 * @since 3.2.5
	 * @since 4.0.0 Parameter $settings added.
	 *
	 * @param array         $title_parts An array of strings.
	 * @param Settings|null $settings    Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return array
	 */
	public function process_title_parts( $title_parts, Settings $settings = null ) {
		foreach ( $title_parts as $index => $part ) {
			// Remove "&shy;" and "&#8203;" after processing title part.
			$title_parts[ $index ] = strip_tags(
				str_replace( [ \PHP_Typography\U::SOFT_HYPHEN, \PHP_Typography\U::ZERO_WIDTH_SPACE ], '', $this->process( $part, true, true, $settings ) )
			);
		}

		return $title_parts;
	}

	/**
	 * Processes a text fragment.
	 *
	 * @since 3.2.4 Parameter $force_feed added.
	 * @since 4.0.0 Parameter $settings added.
	 *
	 * @param string        $text       Required.
	 * @param bool          $is_title   Optional. Default false.
	 * @param bool          $force_feed Optional. Default false.
	 * @param Settings|null $settings   Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public function process( $text, $is_title = false, $force_feed = false, Settings $settings = null ) {
		// Use default settings if no argument was given.
		if ( null === $settings ) {
			$settings = $this->get_settings();
			$hash     = $this->cached_settings_hash;
		}

		/**
		 * Filters the settings object used for processing the text fragment.
		 *
		 * @since 5.0.0
		 *
		 * @param Settings $settings The settings instance.
		 */
		$settings = apply_filters( 'typo_settings', $settings );

		// Caclulate hash if necessary.
		$hash = isset( $hash ) ? $hash : $settings->get_hash( 32, false );

		// Enable feed mode?
		$feed = $force_feed || is_feed();

		// Construct cache key.
		$key = 'frag_' . md5( $text ) . '_' . $hash . '_' . ( $feed ? 'f' : '' ) . ( $is_title ? 't' : 's' );

		// Retrieve cached text.
		$found          = false;
		$processed_text = $this->cache->get( $key, $found );

		if ( empty( $found ) ) {
			$typo = $this->get_typography_instance();

			if ( $feed ) { // Feed readers are strange sometimes.
				$processed_text = $typo->process_feed( $text, $settings, $is_title );
			} else {
				$processed_text = $typo->process( $text, $settings, $is_title );
			}

			/**
			 * Filters the caching duration for processed text fragments.
			 *
			 * @since 3.2.0
			 *
			 * @param int $duration The duration in seconds. Defaults to 1 day.
			 */
			$duration = apply_filters( 'typo_processed_text_caching_duration', DAY_IN_SECONDS );

			// Save text fragment for later.
			$this->cache->set( $key, $processed_text, $duration );
		}

		return $processed_text;
	}

	/**
	 * Cache the given object under the transient name.
	 *
	 * @since 5.1.0 $handle parameter added.
	 *
	 * @param  string $transient Required.
	 * @param  mixed  $object    Required.
	 * @param  string $handle    Optional. A name passed to the filters.
	 */
	protected function cache_object( $transient, $object, $handle = '' ) {
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

			$this->transients->set_large_object( $transient, $object, $duration );
		}
	}

	/**
	 * Retrieves the PHP_Typography instance and ensure just-in-time initialization.
	 */
	protected function get_typography_instance() {

		// Initialize PHP_Typography instance.
		if ( empty( $this->typo ) ) {
			$transient  = 'php_' . md5( wp_json_encode( $this->options ) );
			$this->typo = $this->maybe_fix_object( $this->transients->get_large_object( $transient ) );

			if ( ! $this->typo instanceof PHP_Typography ) {
				// OK, we have to initialize the PHP_Typography instance manually.
				$this->typo = new PHP_Typography( PHP_Typography::INIT_NOW );

				// Try again next time.
				$this->cache_object( $transient, $this->typo, 'typography' );
			}
		}

		// Also cache hyphenators (the pattern tries are expensive to build).
		if ( $this->options['typo_enable_hyphenation'] && empty( $this->hyphenator_cache ) ) {
			$transient              = 'php_hyphenator_cache';
			$this->hyphenator_cache = $this->maybe_fix_object( $this->transients->get_large_object( $transient ) );

			if ( ! $this->hyphenator_cache instanceof Hyphenator_Cache ) {
				$this->hyphenator_cache = $this->typo->get_hyphenator_cache();

				// Try again next time.
				$this->cache_object( $transient, $this->hyphenator_cache, 'hyphenator_cache' );
			}

			// Let's use it!
			$this->typo->set_hyphenator_cache( $this->hyphenator_cache );
		}

		return $this->typo;
	}

	/**
	 * Save hyphenator cache for the next request.
	 */
	public function save_hyphenator_cache_on_shutdown() {
		if ( $this->options['typo_enable_hyphenation'] && ! empty( $this->hyphenator_cache ) && $this->hyphenator_cache->has_changed() ) {
			$this->cache_object( 'php_hyphenator_cache', $this->hyphenator_cache, 'hyphenator_cache' );
		}
	}

	/**
	 * Initializes the Settings object for PHP_Typography from the plugin options.
	 *
	 * @param Settings $s Required.
	 */
	protected function init_settings_from_options( Settings $s ) {
		// Load configuration variables into our PHP_Typography class.
		$s->set_tags_to_ignore( $this->options['typo_ignore_tags'] );
		$s->set_classes_to_ignore( $this->options['typo_ignore_classes'] );
		$s->set_ids_to_ignore( $this->options['typo_ignore_ids'] );

		if ( $this->options['typo_smart_characters'] ) {
			$s->set_smart_dashes( $this->options['typo_smart_dashes'] );
			$s->set_smart_dashes_style( $this->options['typo_smart_dashes_style'] );
			$s->set_smart_ellipses( $this->options['typo_smart_ellipses'] );
			$s->set_smart_math( $this->options['typo_smart_math'] );

			// Note: smart_exponents was grouped with smart_math for the WordPress plugin,
			// but does not have to be done that way for other ports.
			$s->set_smart_exponents( $this->options['typo_smart_math'] );
			$s->set_smart_fractions( $this->options['typo_smart_fractions'] );
			$s->set_smart_ordinal_suffix( $this->options['typo_smart_ordinals'] );
			$s->set_smart_marks( $this->options['typo_smart_marks'] );
			$s->set_smart_quotes( $this->options['typo_smart_quotes'] );

			$s->set_smart_diacritics( $this->options['typo_smart_diacritics'] );
			$s->set_diacritic_language( $this->options['typo_diacritic_languages'] );
			$s->set_diacritic_custom_replacements( $this->options['typo_diacritic_custom_replacements'] );

			$s->set_smart_quotes_primary( $this->options['typo_smart_quotes_primary'] );
			$s->set_smart_quotes_secondary( $this->options['typo_smart_quotes_secondary'] );
		} else {
			$s->set_smart_dashes( false );
			$s->set_smart_ellipses( false );
			$s->set_smart_math( false );
			$s->set_smart_exponents( false );
			$s->set_smart_fractions( false );
			$s->set_smart_ordinal_suffix( false );
			$s->set_smart_marks( false );
			$s->set_smart_quotes( false );
			$s->set_smart_diacritics( false );
		}

		$s->set_single_character_word_spacing( $this->options['typo_single_character_word_spacing'] );
		$s->set_dash_spacing( $this->options['typo_dash_spacing'] );
		$s->set_fraction_spacing( $this->options['typo_fraction_spacing'] );
		$s->set_unit_spacing( $this->options['typo_unit_spacing'] );
		$s->set_numbered_abbreviation_spacing( $this->options['typo_numbered_abbreviations_spacing'] );
		$s->set_french_punctuation_spacing( $this->options['typo_french_punctuation_spacing'] );
		$s->set_units( $this->options['typo_units'] );
		$s->set_space_collapse( $this->options['typo_space_collapse'] );
		$s->set_dewidow( $this->options['typo_prevent_widows'] );
		$s->set_max_dewidow_length( $this->options['typo_widow_min_length'] );
		$s->set_max_dewidow_pull( $this->options['typo_widow_max_pull'] );
		$s->set_wrap_hard_hyphens( $this->options['typo_wrap_hyphens'] );
		$s->set_email_wrap( $this->options['typo_wrap_emails'] );
		$s->set_url_wrap( $this->options['typo_wrap_urls'] );
		$s->set_min_after_url_wrap( $this->options['typo_wrap_min_after'] );
		$s->set_style_ampersands( $this->options['typo_style_amps'] );
		$s->set_style_caps( $this->options['typo_style_caps'] );
		$s->set_style_numbers( $this->options['typo_style_numbers'] );
		$s->set_style_hanging_punctuation( $this->options['typo_style_hanging_punctuation'] );
		$s->set_style_initial_quotes( $this->options['typo_style_initial_quotes'] );
		$s->set_initial_quote_tags( $this->options['typo_initial_quote_tags'] );

		if ( $this->options['typo_enable_hyphenation'] ) {
			$s->set_hyphenation( $this->options['typo_enable_hyphenation'] );
			$s->set_hyphenate_headings( $this->options['typo_hyphenate_headings'] );
			$s->set_hyphenate_all_caps( $this->options['typo_hyphenate_caps'] );
			$s->set_hyphenate_title_case( $this->options['typo_hyphenate_title_case'] );
			$s->set_hyphenate_compounds( $this->options['typo_hyphenate_compounds'] );
			$s->set_hyphenation_language( $this->options['typo_hyphenate_languages'] );
			$s->set_min_length_hyphenation( $this->options['typo_hyphenate_min_length'] );
			$s->set_min_before_hyphenation( $this->options['typo_hyphenate_min_before'] );
			$s->set_min_after_hyphenation( $this->options['typo_hyphenate_min_after'] );
			$s->set_hyphenation_exceptions( $this->options['typo_hyphenate_exceptions'] );
		} else { // save some cycles.
			$s->set_hyphenation( $this->options['typo_enable_hyphenation'] );
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
		$s->set_ignore_parser_errors( $this->options['typo_ignore_parser_errors'] || apply_filters( 'typo_ignore_parser_errors', false ) );

		// Make parser errors filterable on an individual level.
		$s->set_parser_errors_handler( [ $this, 'parser_errors_handler' ] );
	}

	/**
	 * Initializes the options with default values.
	 *
	 * @param bool $force_defaults Optional. Default false.
	 */
	public function set_default_options( $force_defaults = false ) {
		// Grab configuration variables.
		foreach ( $this->get_default_options() as $key => $default ) {
			// Set or update the options with the default value if necessary.
			if ( $force_defaults || ! is_string( get_option( $key ) ) ) {
				update_option( $key, $default );
			}
		}

		if ( $force_defaults ) {
			// Push the reset switch.
			update_option( 'typo_restore_defaults', false );
			update_option( 'typo_clear_cache', false );
		}
	}

	/**
	 * Retrieves the plugin's default option values.
	 *
	 * @return array
	 */
	public function get_default_options() {
		if ( ! $this->default_settings_localized ) {
			$this->default_settings           = $this->multilingual->filter_defaults( $this->default_settings );
			$this->default_settings_localized = true;
		}

		return $this->default_settings;
	}

	/**
	 * Clears all transients set by the plugin.
	 */
	public function clear_cache() {
		$this->transients->invalidate();
		$this->cache->invalidate();

		update_option( 'typo_clear_cache', false );
	}

	/**
	 * Makes parser errors filterable.
	 *
	 * @param array $errors An array of error messages.
	 *
	 * @return array The filtered array.
	 */
	public function parser_errors_handler( $errors ) {
		/**
		 * Filters the HTML parser errors (if there are any).
		 *
		 * @since 4.0.0
		 *
		 * @param array $errors An array of error messages.
		 */
		return apply_filters( 'typo_handle_parser_errors', $errors );
	}

	/**
	 * Prints CSS and JS depending on plugin options.
	 */
	public function add_wp_head() {
		if ( $this->options['typo_style_css_include'] && '' !== trim( $this->options['typo_style_css'] ) ) {
			echo '<style type="text/css">' . "\r\n";
			echo esc_html( $this->options['typo_style_css'] ) . "\r\n";
			echo "</style>\r\n";
		}

		if ( $this->options['typo_hyphenate_safari_font_workaround'] ) {
			echo "<style type=\"text/css\">body {-webkit-font-feature-settings: \"liga\";font-feature-settings: \"liga\";-ms-font-feature-settings: normal;}</style>\r\n";
		}
	}

	/**
	 * Loads translations and checks for other plugins.
	 */
	public function plugins_loaded() {
		// Load our translations.
		load_plugin_textdomain( 'wp-typography', false, dirname( $this->local_plugin_path ) . '/translations/' );

		// Check for NextGEN Gallery and use insane filter priority if activated.
		if ( class_exists( 'C_NextGEN_Bootstrap' ) ) {
			$this->filter_priority = PHP_INT_MAX;
		}
	}

	/**
	 * Encodes the given version string (in the form "3.0.0-beta.1") to a representation suitable for hashing.
	 *
	 * The current implementation works as follows:
	 * 1. The version is broken into tokens at each ".".
	 * 2. Each token is stripped of all characters except numbers.
	 * 3. Each number is added to decimal 64 to arrive at an ASCII code.
	 * 4. The character representation of that ASCII code is added to the result.
	 *
	 * This means that textual qualifiers like "alpha" and "beta" are ignored, so "3.0.0-alpha.1" and
	 * "3.0.0-beta.1" result in the same hash. Since those are not regular release names, this is deemed
	 * acceptable to make the algorithm simpler.
	 *
	 * @param string $version A version string.
	 *
	 * @return string The hashed version (containing as few bytes as possible);
	 */
	private function hash_version_string( $version ) {
		$hash = '';

		foreach ( explode( '.', $version ) as $part ) {
			$hash .= chr( 64 + preg_replace( '/[^0-9]/', '', $part ) );
		}

		return $hash;
	}

	/**
	 * Retrieves the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieves the plugin version hash.
	 *
	 * @deprecated 5.2.0
	 *
	 * @return string
	 */
	public function get_version_hash() {
		return $this->hash_version_string( $this->version );
	}

	/**
	 * Enqueues frontend JavaScript files.
	 */
	public function enqueue_scripts() {
		if ( $this->options['typo_hyphenate_clean_clipboard'] ) {
			// Set up file suffix.
			$suffix = SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'jquery-selection',                plugin_dir_url( $this->local_plugin_path ) . "js/jquery.selection$suffix.js", [ 'jquery' ],                     $this->version, true );
			wp_enqueue_script( 'wp-typography-cleanup-clipboard', plugin_dir_url( $this->local_plugin_path ) . "js/clean_clipboard$suffix.js",  [ 'jquery', 'jquery-selection' ], $this->version, true );
		}
	}

	/**
	 * Fix object cache implementations sumetimes returning __PHP_Incomplete_Class.
	 *
	 * Based on http://stackoverflow.com/a/1173769/6646342.
	 *
	 * @param  object $object An object that should have been unserialized, but may be of __PHP_Incomplete_Class.
	 *
	 * @return object         The object with its real class.
	 */
	protected function maybe_fix_object( $object ) {
		if ( ! is_object( $object ) && 'object' === gettype( $object ) ) {
			$object = unserialize( serialize( $object ) ); // @codingStandardsIgnoreLine
		}

		return $object;
	}
}
