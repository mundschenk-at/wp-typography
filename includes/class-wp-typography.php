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

use \PHP_Typography\PHP_Typography;
use \PHP_Typography\Hyphenator_Cache;
use \PHP_Typography\Settings;

/**
 * Main wp-Typography plugin class. All WordPress specific code goes here.
 */
final class WP_Typography {

	/**
	 * The full version string of the plugin.
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * A byte-encoded version number used as part of the key for transient caching
	 *
	 * @var string $version_hash
	 */
	private $version_hash;

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
	private $settings;

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
	private $hyphenator_cache;

	/**
	 * The transients set by the plugin (to clear on update).
	 *
	 * @var array A hash with the transient keys set by the plugin stored as ( $key => true ).
	 */
	private $transients = [];

	/**
	 * The cache keys set by the plugin (to clear on update).
	 *
	 * @since 3.5.0
	 *
	 * @var array A hash with the cache keys set by the plugin stored as ( $key => true ).
	 */
	private $cache_keys = [];

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
	 * The admin side handler object.
	 *
	 * @var WP_Typography_Admin
	 */
	private $admin;

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
	 * @param string $version  The full plugin version string (e.g. "3.0.0-beta.2").
	 * @param string $basename Optional. The result of plugin_basename() for the main plugin file. Default 'wp-typography/wp-typography.php'.
	 */
	private function __construct( $version, $basename = 'wp-typography/wp-typography.php' ) {
		// Basic set-up.
		$this->version           = $version;
		$this->version_hash      = $this->hash_version_string( $version );
		$this->local_plugin_path = $basename;
		$this->transients        = get_option( 'typo_transient_keys', [] );
		$this->cache_keys        = get_option( 'typo_cache_keys', [] );

		// Initialize admin interface handler.
		$this->admin             = new WP_Typography_Admin( $basename, $this );
		$this->default_settings  = $this->admin->get_default_settings();
	}

	/**
	 * Retrieves (and if necessary creates) the WP_Typography instance. Should not be called outside of plugin set-up.
	 *
	 * @since 3.2.0
	 *
	 * @param string $version  The full plugin version string (e.g. "3.0.0-beta.2").
	 * @param string $basename Optional. The result of plugin_basename() for the main plugin file. Default 'wp-typography/wp-typography.php'.
	 *
	 * @return WP_Typography
	 */
	public static function _get_instance( $version, $basename = 'wp-typography/wp-typography.php' ) {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self( $version, $basename );
		} else {
			_doing_it_wrong( __FUNCTION__, 'WP_Typography::_get_instance called more than once.', '3.2.0' );
		}

		return self::$_instance;
	}

	/**
	 * Retrieves the plugin instance.
	 *
	 * @since 3.2.0
	 *
	 * @return WP_Typography
	 */
	public static function get_instance() {
		if ( empty( self::$_instance ) ) {
			_doing_it_wrong( __FUNCTION__, 'WP_Typography::get_instance called without plugin intialization.', '3.2.0' );
			return self::_get_instance( '0.0.0' ); // fallback with invalid version.
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
	 * The language names are translation-ready but not translated yet.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array in the form of ( $language_code => $language ).
	 */
	public static function get_hyphenation_languages() {
		return PHP_Typography::get_hyphenation_languages();
	}

	/**
	 * Retrieves the list of valid diacritic replacement languages.
	 *
	 * The language names are translation-ready but not translated yet.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array in the form of ( $language_code => $language ).
	 */
	public static function get_diacritic_languages() {
		return PHP_Typography::get_diacritic_languages();
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
	 * Starts the plugin for real.
	 */
	public function run() {
		// Ensure that our translations are loaded.
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );

		// Load settings.
		add_action( 'init', [ $this, 'init' ] );

		// Also run the backend UI.
		$this->admin->run();
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
		foreach ( $this->default_settings as $key => $value ) {
			$this->settings[ $key ] = get_option( $key );
		}

		// Disable wptexturize filter if it conflicts with our settings.
		if ( $this->settings['typo_smart_characters'] && ! is_admin() ) {
			add_filter( 'run_wptexturize', '__return_false' );

			// Ensure that wptexturize is actually off by forcing a re-evaluation (some plugins call it too early).
			wptexturize( ' ', true ); // Argument must not be empty string!
		}

		// Apply our filters.
		if ( ! is_admin() ) {
			$this->add_content_filters();
		}

		// Add CSS Hook styling.
		add_action( 'wp_head', [ $this, 'add_wp_head' ] );

		// Optionally enable clipboard clean-up.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}


	/**
	 * Retrieves the internal Settings object for the preferences set by the user
	 * via the plugin options screen.
	 *
	 * @since 5.0.0
	 *
	 * @return Settings
	 */
	public function get_settings() {
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
		// Needed to initialize settings.
		$typo = $this->get_php_typo();

		if ( null === $settings ) {
			$settings = $this->typo_settings;
			$hash = $this->cached_settings_hash;
		} else {
			$hash = $settings->get_hash();
		}

		$key  = 'typo_' . base64_encode( md5( $text, true ) . $hash );

		/**
		 * Filter the caching duration for processed text fragments.
		 *
		 * @since 3.2.0
		 *
		 * @param int $duration The duration in seconds. Defaults to 1 day.
		 */
		$duration = apply_filters( 'typo_processed_text_caching_duration', DAY_IN_SECONDS );
		$found = false;

		if ( is_feed() || $force_feed ) { // feed readers can be pretty stupid.
			$key .= 'f' . ( $is_title ? 't' : 's' ) . $this->version_hash;
			$processed_text = $this->get_cache( $key, $found );

			if ( ! $found ) {
				$processed_text = $typo->process_feed( $text, $settings, $is_title );
				$this->set_cache( $key, $processed_text, $duration );
			}
		} else {
			$key .= ( $is_title ? 't' : 's' ) . $this->version_hash;
			$processed_text = $this->get_cache( $key, $found );

			if ( ! $found ) {
				$processed_text = $typo->process( $text, $settings, $is_title );
				$this->set_cache( $key, $processed_text, $duration );
			}
		}

		return $processed_text;
	}

	/**
	 * Sets a transient and stores the key for clean-up.
	 *
	 * @param string $transient The transient key. Maximum length depends on WordPress version (for WP < 4.4 it is 45 characters).
	 * @param mixed  $value     The value to store.
	 * @param int    $duration  Optional. The duration in seconds. Default 1 second.
	 *
	 * @return bool True if the transient could be set successfully.
	 */
	public function set_transient( $transient, $value, $duration = 1 ) {
		$result = set_transient( $transient, $value, $duration );

		if ( $result ) {
			// Store $transient as keys to prevent duplicates.
			$this->transients[ $transient ] = true;
			update_option( 'typo_transient_keys', $this->transients );
		}

		return $result;
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
	public function set_cache( $key, $value, $duration = 0 ) {
		$result = wp_cache_set( $key, $value, 'wp-typography', $duration );

		if ( $result ) {
			// Store as keys to prevent duplicates.
			$this->cache_keys[ $key ] = true;
			update_option( 'typo_cache_keys', $this->cache_keys );
		}

		return $result;
	}

	/**
	 * Retrieves a cached value.
	 *
	 * @param string $key   The cache key.
	 * @param bool   $found Whether the key was found in the cache. Disambiguates a return of false as a storable value. Passed by reference.
	 *
	 * @return mixed
	 */
	public function get_cache( $key, &$found ) {
		return wp_cache_get( $key, 'wp-typography', false, $found );
	}

	/**
	 * Cache the given object under the transient name.
	 *
	 * @param  string $transient Required.
	 * @param  mixed  $object    Required.
	 */
	private function cache_object( $transient, $object ) {
		/**
		 * Filters the caching duration for the PHP_Typography engine state.
		 *
		 * @since 3.2.0
		 *
		 * @param int $duration The duration in seconds. Defaults to 0 (no expiration).
		 */
		$duration = apply_filters( 'typo_php_typography_caching_duration', 0 );

		/**
		 * Filters whether the PHP_Typography engine state should be cached.
		 *
		 * @since 4.2.0
		 *
		 * @param bool $enabled Defaults to true.
		 */
		$caching_enabled = apply_filters( 'typo_php_typography_caching_enabled', true );

		// Try again next time.
		if ( $caching_enabled ) {
			$this->set_transient( $transient, $object, $duration );
		}
	}

	/**
	 * Retrieves the PHP_Typography instance and ensure just-in-time initialization.
	 */
	private function get_php_typo() {

		// Initialize PHP_Typography instance.
		if ( empty( $this->typo ) ) {
			$transient = 'typo_php_' . md5( wp_json_encode( $this->settings ) ) . '_' . $this->version_hash;
			$this->typo = $this->_maybe_fix_object( get_transient( $transient ) );

			if ( empty( $this->typo ) ) {
				// OK, we have to initialize the PHP_Typography instance manually.
				$this->typo = new PHP_Typography( PHP_Typography::INIT_NOW );

				// Try again next time.
				$this->cache_object( $transient, $this->typo );
			}
		}

		// Initialize Settings instance.
		if ( empty( $this->typo_settings ) ) {
			$transient = 'typo_php_settings_' . md5( wp_json_encode( $this->settings ) ) . '_' . $this->version_hash;
			$this->typo_settings = $this->_maybe_fix_object( get_transient( $transient ) );

			if ( empty( $this->typo_settings ) ) {
				// OK, we have to initialize the PHP_Typography instance manually.
				$this->typo_settings = new Settings( false );

				// Load our settings into the instance.
				$this->init_settings( $this->typo_settings );

				// Try again next time.
				$this->cache_object( $transient, $this->typo_settings );
			}

			// Settings won't be touched again, so cache the hash.
			$this->cached_settings_hash = $this->typo_settings->get_hash( 32 );
		}

		// Also cache hyphenator (the pattern trie is expensive to build).
		if ( $this->settings['typo_enable_hyphenation'] && empty( $this->hyphenator_cache ) ) {
			$transient = 'typo_php_hyphenator_cache_' . $this->version_hash;
			$this->hyphenator_cache = $this->_maybe_fix_object( get_transient( $transient ) );

			if ( empty( $this->hyphenator_cache ) ) {
				$this->hyphenator_cache = $this->typo->get_hyphenator_cache();

				// Try again next time.
				$this->cache_object( $transient, $this->typo_settings );
			}

			// Let's use it!
			$this->typo->set_hyphenator_cache( $this->hyphenator_cache );
		}

		return $this->typo;
	}

	/**
	 * Initializes the Settings object for PHP_Typography from the plugin settings.
	 *
	 * @param Settings $s Required.
	 */
	private function init_settings( Settings $s ) {
		// Load configuration variables into our PHP_Typography class.
		$s->set_tags_to_ignore( $this->settings['typo_ignore_tags'] );
		$s->set_classes_to_ignore( $this->settings['typo_ignore_classes'] );
		$s->set_ids_to_ignore( $this->settings['typo_ignore_ids'] );

		if ( $this->settings['typo_smart_characters'] ) {
			$s->set_smart_dashes( $this->settings['typo_smart_dashes'] );
			$s->set_smart_dashes_style( $this->settings['typo_smart_dashes_style'] );
			$s->set_smart_ellipses( $this->settings['typo_smart_ellipses'] );
			$s->set_smart_math( $this->settings['typo_smart_math'] );

			// Note: smart_exponents was grouped with smart_math for the WordPress plugin,
			// but does not have to be done that way for other ports.
			$s->set_smart_exponents( $this->settings['typo_smart_math'] );
			$s->set_smart_fractions( $this->settings['typo_smart_fractions'] );
			$s->set_smart_ordinal_suffix( $this->settings['typo_smart_ordinals'] );
			$s->set_smart_marks( $this->settings['typo_smart_marks'] );
			$s->set_smart_quotes( $this->settings['typo_smart_quotes'] );

			$s->set_smart_diacritics( $this->settings['typo_smart_diacritics'] );
			$s->set_diacritic_language( $this->settings['typo_diacritic_languages'] );
			$s->set_diacritic_custom_replacements( $this->settings['typo_diacritic_custom_replacements'] );

			$s->set_smart_quotes_primary( $this->settings['typo_smart_quotes_primary'] );
			$s->set_smart_quotes_secondary( $this->settings['typo_smart_quotes_secondary'] );
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

		$s->set_single_character_word_spacing( $this->settings['typo_single_character_word_spacing'] );
		$s->set_dash_spacing( $this->settings['typo_dash_spacing'] );
		$s->set_fraction_spacing( $this->settings['typo_fraction_spacing'] );
		$s->set_unit_spacing( $this->settings['typo_unit_spacing'] );
		$s->set_numbered_abbreviation_spacing( $this->settings['typo_numbered_abbreviations_spacing'] );
		$s->set_french_punctuation_spacing( $this->settings['typo_french_punctuation_spacing'] );
		$s->set_units( $this->settings['typo_units'] );
		$s->set_space_collapse( $this->settings['typo_space_collapse'] );
		$s->set_dewidow( $this->settings['typo_prevent_widows'] );
		$s->set_max_dewidow_length( $this->settings['typo_widow_min_length'] );
		$s->set_max_dewidow_pull( $this->settings['typo_widow_max_pull'] );
		$s->set_wrap_hard_hyphens( $this->settings['typo_wrap_hyphens'] );
		$s->set_email_wrap( $this->settings['typo_wrap_emails'] );
		$s->set_url_wrap( $this->settings['typo_wrap_urls'] );
		$s->set_min_after_url_wrap( $this->settings['typo_wrap_min_after'] );
		$s->set_style_ampersands( $this->settings['typo_style_amps'] );
		$s->set_style_caps( $this->settings['typo_style_caps'] );
		$s->set_style_numbers( $this->settings['typo_style_numbers'] );
		$s->set_style_hanging_punctuation( $this->settings['typo_style_hanging_punctuation'] );
		$s->set_style_initial_quotes( $this->settings['typo_style_initial_quotes'] );
		$s->set_initial_quote_tags( $this->settings['typo_initial_quote_tags'] );

		if ( $this->settings['typo_enable_hyphenation'] ) {
			$s->set_hyphenation( $this->settings['typo_enable_hyphenation'] );
			$s->set_hyphenate_headings( $this->settings['typo_hyphenate_headings'] );
			$s->set_hyphenate_all_caps( $this->settings['typo_hyphenate_caps'] );
			$s->set_hyphenate_title_case( $this->settings['typo_hyphenate_title_case'] );
			$s->set_hyphenate_compounds( $this->settings['typo_hyphenate_compounds'] );
			$s->set_hyphenation_language( $this->settings['typo_hyphenate_languages'] );
			$s->set_min_length_hyphenation( $this->settings['typo_hyphenate_min_length'] );
			$s->set_min_before_hyphenation( $this->settings['typo_hyphenate_min_before'] );
			$s->set_min_after_hyphenation( $this->settings['typo_hyphenate_min_after'] );
			$s->set_hyphenation_exceptions( $this->settings['typo_hyphenate_exceptions'] );
		} else { // save some cycles.
			$s->set_hyphenation( $this->settings['typo_enable_hyphenation'] );
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
		$s->set_ignore_parser_errors( $this->settings['typo_ignore_parser_errors'] || apply_filters( 'typo_ignore_parser_errors', false ) );

		// Make parser errors filterable on an individual level.
		$s->set_parser_errors_handler( [ $this, 'parser_errors_handler' ] );
	}

	/**
	 * Initializes the options with default values.
	 *
	 * @param bool $force_defaults Optional. Default false.
	 */
	private function set_default_options( $force_defaults = false ) {
		// Grab configuration variables.
		foreach ( $this->default_settings as $key => $default ) {
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
		return $this->default_settings;
	}

	/**
	 * Clears all transients set by the plugin.
	 */
	private function clear_cache() {
		// Delete all our transients.
		foreach ( array_keys( $this->transients ) as $transient ) {
			delete_transient( $transient );
		}
		// ... as well as cache keys.
		foreach ( array_keys( $this->cache_keys ) as $key ) {
			wp_cache_delete( $key, 'wp-typography' );
		}

		$this->transients = [];
		$this->cache_keys = [];
		update_option( 'typo_transient_keys', $this->transients );
		update_option( 'typo_cache_keys', $this->cache_keys );
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
		if ( $this->settings['typo_style_css_include'] && '' !== trim( $this->settings['typo_style_css'] ) ) {
			echo '<style type="text/css">' . "\r\n";
			echo esc_html( $this->settings['typo_style_css'] ) . "\r\n";
			echo "</style>\r\n";
		}

		if ( $this->settings['typo_hyphenate_safari_font_workaround'] ) {
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

		$parts = explode( '.', $version );
		foreach ( $parts as $part ) {
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
	 * @return string
	 */
	public function get_version_hash() {
		return $this->version_hash;
	}

	/**
	 * Enqueues frontend JavaScript files.
	 */
	public function enqueue_scripts() {
		if ( $this->settings['typo_hyphenate_clean_clipboard'] ) {
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
	private function _maybe_fix_object( $object ) {
		if ( ! is_object( $object ) && 'object' === gettype( $object ) ) {
			$object = unserialize( serialize( $object ) ); // @codingStandardsIgnoreLine
		}

		return $object;
	}
}
