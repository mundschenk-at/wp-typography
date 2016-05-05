<?php

/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2016 Peter Putzer.
 *	Copyright 2012-2013 Marie Hogebrandt.
 *	Coypright 2009-2011 KINGdesk, LLC.
 *
 *	This program is free software; you can redistribute it and/or
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
 *  @package wpTypography
 *  @author Jeffrey D. King <jeff@kingdesk.com>
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Autoload parser classes
 */
require_once dirname( __DIR__ ) . '/php-typography/php-typography-autoload.php';

/**
 * Main wp-Typography plugin class. All WordPress specific code goes here.
 */
class WP_Typography {

	/**
	 * The full version string of the plugin.
	 */
	private $version;

	/**
	 * A byte-encoded version number used as part of the key for transient caching
	 */
	private $version_hash;

	/**
	 * The result of plugin_basename() for the main plugin file (relative from plugins folder).
	 */
	private $local_plugin_path;

	/**
	 * A hash containing the various plugin settings.
	 */
	private $settings;

	/**
	 * The PHP_Typography instance doing the actual work.
	 */
	private $php_typo;

	/**
	 * The transients set by the plugin (to clear on update).
	 *
	 * @var array A hash with the transient keys set by the plugin stored as ( $key => true ).
	 */
	private $transients = array();

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
	 * @var number
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
	 * @param string $version  The full plugin version string (e.g. "3.0.0-beta.2")
	 * @param string $basename The result of plugin_basename() for the main plugin file.
	 */
	private function __construct( $version, $basename = 'wp-typography/wp-typography.php' ) {
		// basic set-up
		$this->version           = $version;
		$this->version_hash      = $this->hash_version_string( $version );
		$this->local_plugin_path = $basename;
		$this->transients        = get_option( 'typo_transient_keys', array() );

		// admin handler
		$this->admin             = new WP_Typography_Admin( $basename, $this );
		$this->default_settings  = $this->admin->get_default_settings();
	}

	/**
	 * Create & retrieve the WP_Typography instance. Should not be called outside of plugin set-up.
	 *
	 * @since 3.2.0
	 *
	 * @param string $version  The full plugin version string (e.g. "3.0.0-beta.2")
	 * @param string $basename The result of plugin_basename() for the main plugin file.
	 * @return WP_Typography
	 */
	public static function _get_instance( $version, $basename = 'wp-typography/wp-typography.php' ) {
		if ( ! self::$_instance ) {
			self::$_instance = new self( $version, $basename );
		} else {
			_doing_it_wrong( __FUNCTION__, 'WP_Typography::_get_instance called more than once.', '3.2.0' );
		}

		return self::$_instance;
	}

	/**
	 * Retrieve the plugin instance.
	 *
	 * @since 3.2.0
	 *
	 * @return WP_Typography
	 */
	public static function get_instance() {
    	if ( ! self::$_instance ) {
    		_doing_it_wrong( __FUNCTION__, 'WP_Typography::get_instance called without plugin intialization.', '3.2.0' );
			return self::_get_instance( '0.0.0' ); // fallback with invalid version
		}

		return self::$_instance;
	}

	/**
	 * Start the plugin for real.
	 */
	function run() {
		// ensure that our translations are loaded
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		// load settings
		add_action( 'init', array( $this, 'init') );

		// also run the back- end frontend
		$this->admin->run();
	}

	/**
	 * Load the settings from the option table.
	 */
	function init() {
		// restore defaults if necessary
		if ( get_option( 'typo_restore_defaults' ) ) {  // any truthy value will do
			$this->set_default_options( true );
		}

		// clear cache if necessary
		if ( get_option( 'typo_clear_cache' ) ) {  // any truthy value will do
			$this->clear_cache();
		}

		// load settings
		foreach ( $this->default_settings as $key => $value ) {
			$this->settings[ $key ] = get_option( $key );
		}

		// disable wptexturize filter if it conflicts with our settings
		if ( $this->settings['typo_smart_characters'] && ! is_admin() ) {
			add_filter( 'run_wptexturize', '__return_false' );
		}

		// apply our filters
		if ( ! is_admin() ) {
			/**
			 * Filter the priority used for wp-Typography's text processing filters.
			 *
			 * When NextGen Gallery is detected, the priority is set to PHP_INT_MAX.
			 *
			 * @since 3.2.0
			 *
			 * @param number $priority The filter priority. Default 9999.
			 */
			$priority = apply_filters( 'typo_filter_priority', $this->filter_priority );

			// removed because it caused issues for feeds
			// add_filter( 'bloginfo', array($this, 'processBloginfo'), 9999);
			// add_filter( 'wp_title', 'strip_tags', 9999);
			// add_filter( 'single_post_title', 'strip_tags', 9999);

			// "full" content
			add_filter( 'comment_author',    array( $this, 'process' ),       $priority );
			add_filter( 'comment_text',      array( $this, 'process' ),       $priority );
			add_filter( 'comment_text',      array( $this, 'process' ),       $priority );
			add_filter( 'the_content',       array( $this, 'process' ),       $priority );
			add_filter( 'term_name',         array( $this, 'process' ),       $priority );
			add_filter( 'term_description',  array( $this, 'process' ),       $priority );
			add_filter( 'link_name',         array( $this, 'process' ),       $priority );
			add_filter( 'the_excerpt',       array( $this, 'process' ),       $priority );
			add_filter( 'the_excerpt_embed', array( $this, 'process' ),       $priority );
			add_filter( 'widget_text',       array( $this, 'process' ),       $priority );

			// headings
			add_filter( 'the_title',            array( $this, 'process_title' ), $priority );
			add_filter( 'single_post_title',    array( $this, 'process_title' ), $priority );
			add_filter( 'single_cat_title',     array( $this, 'process_title' ), $priority );
			add_filter( 'single_tag_title',     array( $this, 'process_title' ), $priority );
			add_filter( 'single_month_title',   array( $this, 'process_title' ), $priority );
			add_filter( 'single_month_title',   array( $this, 'process_title' ), $priority );
			add_filter( 'nav_menu_attr_title',  array( $this, 'process_title' ), $priority );
			add_filter( 'nav_menu_description', array( $this, 'process_title' ), $priority );
			add_filter( 'widget_title',         array( $this, 'process_title' ), $priority );
			add_filter( 'list_cats',            array( $this, 'process_title' ), $priority );

			// extra care needs to be taken with the <title> tag
			add_filter( 'wp_title',             array( $this, 'process_feed' ),        $priority ); // WP < 4.4
			add_filter( 'document_title_parts', array( $this, 'process_title_parts' ), $priority );
		}

		// add CSS Hook styling
		add_action( 'wp_head', array( $this, 'add_wp_head' ) );

		// optionally enable clipboard clean-up
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	/**
	 * Process heading text fragment.
	 *
	 * Calls `process( $text, true )`.
	 *
	 * @param string $text
	 */
	function process_title( $text ) {
		return $this->process( $text, true );
	}

	/**
	 * Process text as feed.
	 *
	 * Calls `process( $text, $is_title, true )`.
	 *
	 * @since 3.2.4
	 *
	 * @param string $text
	 * @param boolean $is_title Default false.
	 */
	function process_feed( $text, $is_title = false ) {
		return $this->process( $text, $is_title, true );
	}

	/**
	 * Process title parts and strip &shy; and zero-width space.
	 *
	 * @since 3.2.5
	 *
	 * @param array $title_parts An array of strings.
	 */
	function process_title_parts( $title_parts ) {
		/**
		 * We need a utility function that's not autoloaded.
		 */
		require_once dirname( __DIR__ ) . '/php-typography/php-typography-functions.php'; // @codeCoverageIgnore

		foreach ( $title_parts as $index => $part ) {
			// &shy; and &#8203; after processing title part
			$title_parts[ $index ] = str_replace( array( \PHP_Typography\uchr(173), \PHP_Typography\uchr(8203) ), '', $this->process( $part, true, true ) );
		}

		return $title_parts;
	}

	/**
	 * Process text fragment.
	 *
	 * @since 3.2.4 Parameter $force_feed added.
	 *
	 * @param string $text
	 * @param boolean $is_title Default false.
	 * @param boolean $force_feed Default false.
	 */
	public function process( $text, $is_title = false, $force_feed = false ) {
		$typo = $this->get_php_typo();
		$transient = 'typo_' . base64_encode( md5( $text, true ) . $this->cached_settings_hash );

		/**
		 * Filter the caching duration for processed text fragments.
		 *
		 * @since 3.2.0
		 *
		 * @param number $duration The duration in seconds. Defaults to 1 day.
		 */
		$duration = apply_filters( 'typo_processed_text_caching_duration', DAY_IN_SECONDS );

		if ( is_feed() || $force_feed ) { // feed readers can be pretty stupid
			$transient .= 'f' . ( $is_title ? 't' : 's' ) . $this->version_hash;

			if ( empty( $this->settings['typo_enable_caching'] ) || false === ( $processed_text = get_transient( $transient ) ) ) {
				$processed_text = $typo->process_feed( $text, $is_title );
				$this->set_transient( $transient, $processed_text, $duration );
			}
		} else {
			$transient .= ( $is_title ? 't' : 's' ) . $this->version_hash;

			if ( empty( $this->settings['typo_enable_caching'] ) || false === ( $processed_text = get_transient( $transient ) ) ) {
				$processed_text = $typo->process( $text, $is_title );
				$this->set_transient( $transient, $processed_text, $duration );
			}
		}

		return $processed_text;
	}

	/**
	 * Set a transient and store the key.
	 *
	 * @param string  $transient The transient key. Maximum length depends on WordPress version (for WP < 4.4 it is 45 characters)
	 * @param mixed   $value The value to store.
	 * @param number  $duration The duration in seconds. Optional. Default 1 second.
	 * @param boolean $force Set the transient even if 'Disable Caching' is set to true.
	 * @return boolean True if the transient could be set successfully.
	 */
	public function set_transient( $transient, $value, $duration = 1, $force = false ) {
		if ( ! $force && empty( $this->settings['typo_enable_caching'] ) ) {
			// caching is disabled and not forced for this transient, so we bail
			return false;
		}

		if ( ! empty( $this->settings['typo_caching_limit'] ) && count( $this->transients ) >= $this->settings['typo_caching_limit'] ) {
			// too many cached entries - clean up transients
			$this->clear_cache();
		}

		$result = false;
		if ( $result = set_transient( $transient, $value, $duration ) ) {
			// store $transient as keys to prevent duplicates
			$this->transients[ $transient ] = true;
			update_option( 'typo_transient_keys', $this->transients );
		}

		return $result;
	}

	/**
	 * Retrieve the PHP_Typography instance and ensure just-in-time initialization.
	 */
	private function get_php_typo() {

		if ( empty( $this->php_typo ) ) {
			$this->php_typo = new \PHP_Typography\PHP_Typography( false, 'lazy' );
			$transient = 'typo_php_' . md5( json_encode( $this->settings ) ) . '_' . $this->version_hash;

			if ( ! $this->php_typo->load_state( get_transient( $transient ) ) ) {
				// OK, we have to initialize the PHP_Typography instance manually
				$this->php_typo->init( false );

				// Load our settings into the instance
				$this->init_php_typo();

				/**
				 * Filter the caching duration for the PHP_Typography engine state.
				 *
				 * @since 3.2.0
				 *
				 * @param number $duration The duration in seconds. Defaults to 1 week.
				 */
				$duration = apply_filters( 'typo_php_typography_caching_duration', WEEK_IN_SECONDS );

				// Try again next time
				$this->set_transient( $transient, $this->php_typo->save_state(), $duration, true );
			}

			// Settings won't be touched again, so cache the hash
			$this->cached_settings_hash = $this->php_typo->get_settings_hash( 32 );
		}

		return $this->php_typo;
	}

	/**
	 * Initialize the PHP_Typograpyh instance from our settings.
	 */
	private function init_php_typo() {
		// load configuration variables into our phpTypography class
		$this->php_typo->set_tags_to_ignore( $this->settings['typo_ignore_tags'] );
		$this->php_typo->set_classes_to_ignore( $this->settings['typo_ignore_classes'] );
		$this->php_typo->set_ids_to_ignore( $this->settings['typo_ignore_ids'] );

		if ( $this->settings['typo_smart_characters'] ) {
			$this->php_typo->set_smart_dashes( $this->settings['typo_smart_dashes'] );
			$this->php_typo->set_smart_dashes_style( $this->settings['typo_smart_dashes_style'] );
			$this->php_typo->set_smart_ellipses( $this->settings['typo_smart_ellipses'] );
			$this->php_typo->set_smart_math( $this->settings['typo_smart_math'] );

			// Note: smart_exponents was grouped with smart_math for the WordPress plugin,
			//       but does not have to be done that way for other ports
			$this->php_typo->set_smart_exponents( $this->settings['typo_smart_math'] );
			$this->php_typo->set_smart_fractions( $this->settings['typo_smart_fractions'] );
			$this->php_typo->set_smart_ordinal_suffix( $this->settings['typo_smart_ordinals'] );
			$this->php_typo->set_smart_marks( $this->settings['typo_smart_marks'] );
			$this->php_typo->set_smart_quotes( $this->settings['typo_smart_quotes'] );

			$this->php_typo->set_smart_diacritics( $this->settings['typo_smart_diacritics'] );
			$this->php_typo->set_diacritic_language( $this->settings['typo_diacritic_languages'] );
			$this->php_typo->set_diacritic_custom_replacements( $this->settings['typo_diacritic_custom_replacements'] );

			$this->php_typo->set_smart_quotes_primary( $this->settings['typo_smart_quotes_primary'] );
			$this->php_typo->set_smart_quotes_secondary( $this->settings['typo_smart_quotes_secondary'] );
		} else {
			$this->php_typo->set_smart_dashes( false );
			$this->php_typo->set_smart_ellipses( false );
			$this->php_typo->set_smart_math( false );
			$this->php_typo->set_smart_exponents( false );
			$this->php_typo->set_smart_fractions( false );
			$this->php_typo->set_smart_ordinal_suffix( false );
			$this->php_typo->set_smart_marks( false );
			$this->php_typo->set_smart_quotes( false );
			$this->php_typo->set_smart_diacritics( false );
		}

		$this->php_typo->set_single_character_word_spacing( $this->settings['typo_single_character_word_spacing'] );
		$this->php_typo->set_dash_spacing( $this->settings['typo_dash_spacing'] );
		$this->php_typo->set_fraction_spacing( $this->settings['typo_fraction_spacing'] );
		$this->php_typo->set_unit_spacing( $this->settings['typo_unit_spacing'] );
		$this->php_typo->set_french_punctuation_spacing( $this->settings['typo_french_punctuation_spacing'] );
		$this->php_typo->set_units( $this->settings['typo_units'] );
		$this->php_typo->set_space_collapse( $this->settings['typo_space_collapse'] );
		$this->php_typo->set_dewidow( $this->settings['typo_prevent_widows'] );
		$this->php_typo->set_max_dewidow_length( $this->settings['typo_widow_min_length'] );
		$this->php_typo->set_max_dewidow_pull( $this->settings['typo_widow_max_pull'] );
		$this->php_typo->set_wrap_hard_hyphens( $this->settings['typo_wrap_hyphens'] );
		$this->php_typo->set_email_wrap( $this->settings['typo_wrap_emails'] );
		$this->php_typo->set_url_wrap( $this->settings['typo_wrap_urls'] );
		$this->php_typo->set_min_after_url_wrap( $this->settings['typo_wrap_min_after'] );
		$this->php_typo->set_style_ampersands( $this->settings['typo_style_amps'] );
		$this->php_typo->set_style_caps( $this->settings['typo_style_caps'] );
		$this->php_typo->set_style_numbers( $this->settings['typo_style_numbers'] );
		$this->php_typo->set_style_hanging_punctuation( $this->settings['typo_style_hanging_punctuation'] );
		$this->php_typo->set_style_initial_quotes( $this->settings['typo_style_initial_quotes'] );
		$this->php_typo->set_initial_quote_tags( $this->settings['typo_initial_quote_tags'] );

		if ( $this->settings['typo_enable_hyphenation'] ) {
			$this->php_typo->set_hyphenation( $this->settings['typo_enable_hyphenation'] );
			$this->php_typo->set_hyphenate_headings( $this->settings['typo_hyphenate_headings'] );
			$this->php_typo->set_hyphenate_all_caps( $this->settings['typo_hyphenate_caps'] );
			$this->php_typo->set_hyphenate_title_case( $this->settings['typo_hyphenate_title_case'] );
			$this->php_typo->set_hyphenate_compounds( $this->settings['typo_hyphenate_compounds'] );
			$this->php_typo->set_hyphenation_language( $this->settings['typo_hyphenate_languages'] );
			$this->php_typo->set_min_length_hyphenation( $this->settings['typo_hyphenate_min_length'] );
			$this->php_typo->set_min_before_hyphenation( $this->settings['typo_hyphenate_min_before'] );
			$this->php_typo->set_min_after_hyphenation( $this->settings['typo_hyphenate_min_after'] );
			$this->php_typo->set_hyphenation_exceptions( $this->settings['typo_hyphenate_exceptions'] );
		} else { // save some cycles
			$this->php_typo->set_hyphenation( $this->settings['typo_enable_hyphenation'] );
		}
	}

	/**
	 * Initialize options with default values.
	 *
	 * @param boolean $force_defaults Optional. Default false.
	 */
	function set_default_options( $force_defaults = false ) {
		// grab configuration variables
		foreach ( $this->default_settings as $key => $value ) {
			// set or update the options with the default value if necessary.
			if ( $force_defaults || ! is_string( get_option( $key ) ) ) {
				update_option( $key, $value['default'] );
			}
		}

		if ( $force_defaults ) {
			// reset switch
			update_option( 'typo_restore_defaults', false );
			update_option( 'typo_clear_cache', false );
		}
	}

	/**
	 * Retrieve the plugin's default option values.
	 *
	 * @return array
	 */
	public function get_default_options() {
		return $this->default_settings;
	}

	/**
	 * Clear all transients set by the plugin.
	 */
	 function clear_cache() {
		// delete all our transients
		foreach( array_keys( $this->transients ) as $transient ) {
			delete_transient( $transient );
		}

		$this->transients = array();
		update_option( 'typo_transient_keys', $this->transients );
		update_option( 'typo_clear_cache', false );
	}

	/**
	 * Print CSS and JS depending on plugin options.
	 */
	function add_wp_head() {
		if ( $this->settings['typo_style_css_include'] && trim( $this->settings['typo_style_css'] ) != '' ) {
			echo '<style type="text/css">'."\r\n";
			echo $this->settings['typo_style_css']."\r\n";
			echo "</style>\r\n";
		}

		if ( $this->settings['typo_hyphenate_safari_font_workaround'] ) {
			echo "<style type=\"text/css\">body {-webkit-font-feature-settings: \"liga\";font-feature-settings: \"liga\";}</style>\r\n";
		}
	}

	/**
	 * Load translations and check for other plugins.
	 */
	function plugins_loaded() {
		// Load our translations
		load_plugin_textdomain( 'wp-typography', false, dirname( $this->local_plugin_path ) . '/translations/' );

		// Check for NextGEN Gallery and use insane filter priority if activated
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
	 * @param unknown $version
	 * @return string The hashed version (containing as few bytes as possible);
	 */
	private function hash_version_string( $version ) {
		$hash = '';

		$parts = explode( '.', $version );
		foreach( $parts as $part ) {
			$hash .= chr( 64 + preg_replace('/[^0-9]/', '', $part ) );
		}

		return $hash;
	}

	/**
	 * Retrieve the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the plugin version hash.
	 *
	 * @return string
	 */
	public function get_version_hash() {
		return $this->version_hash;
	}

	/**
	 * Enqueue frontend javascript.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery-selection', plugin_dir_url( $this->local_plugin_path ) . 'js/jquery.selection.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'wp-typography-cleanup-clipboard', plugin_dir_url( $this->local_plugin_path ) . 'js/clean_clipboard.js', array( 'jquery', 'jquery-selection' ), $this->version, true );
	}
}
