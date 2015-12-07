<?php

/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2015 Peter Putzer.
 *
 *	This program is free software; you can redistribute it and/or
 *	modify it under the terms of the GNU General Public License,
 *	version 2 as published by the Free Software Foundation.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *  MA 02110-1301, USA.
 *
 *  ***
 *
 *  Copyright 2009, KINGdesk, LLC. Licensed under the GNU General Public
 *  License 2.0. If you use, modify and/or redistribute this software,
 *  you must leave the KINGdesk, LLC copyright information, the request
 *  for a link to http://kingdesk.com, and the web design services
 *  contact information unchanged. If you redistribute this software, or
 *  any derivative, it must be released under the GNU General Public
 *  License 2.0.
 *
 *  This program is distributed without warranty (implied or otherwise) of
 *  suitability for any particular purpose. See the GNU General Public
 *  License for full license terms <http://creativecommons.org/licenses/GPL/2.0/>.
 *
 *  WE DON'T WANT YOUR MONEY: NO TIPS NECESSARY! If you enjoy this plugin,
 *  a link to http://kingdesk.com from your website would be appreciated.
 *  For web design services, please contact jeff@kingdesk.com.
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
	 * Sets up a new wpTypography object.
	 *
	 * @param string $version  The full plugin version string (e.g. "3.0.0-beta.2")
	 * @param string $basename The result of plugin_basename() for the main plugin file.
	 */
	function __construct( $version, $basename = 'wp-typography/wp-typography.php' ) {
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

		// Remove default Texturize filter if it conflicts.
		if ( $this->settings['typoSmartCharacters'] && ! is_admin() ) {
			remove_filter( 'category_description', 'wptexturize' ); // TODO: necessary?
			remove_filter( 'single_post_title',    'wptexturize' ); // TODO: necessary?
			remove_filter( 'comment_author',       'wptexturize' );
			remove_filter( 'comment_text',         'wptexturize' );
			remove_filter( 'the_title',            'wptexturize' );
			remove_filter( 'the_content',          'wptexturize' );
			remove_filter( 'the_excerpt',          'wptexturize' );
			remove_filter( 'widget_text',          'wptexturize' );
			remove_filter( 'widget_title',         'wptexturize' );
		}

		// apply our filters
		if ( ! is_admin() ) {
			// removed because it caused issues for feeds
			// add_filter( 'bloginfo', array($this, 'processBloginfo'), 9999);
			// add_filter( 'wp_title', 'strip_tags', 9999);
			// add_filter( 'single_post_title', 'strip_tags', 9999);
			add_filter( 'comment_author', array( $this, 'process' ),       $this->filter_priority );
			add_filter( 'comment_text',   array( $this, 'process' ),       $this->filter_priority );
			add_filter( 'the_title',      array( $this, 'process_title' ), $this->filter_priority );
			add_filter( 'the_content',    array( $this, 'process' ),       $this->filter_priority );
			add_filter( 'the_excerpt',    array( $this, 'process' ),       $this->filter_priority );
			add_filter( 'widget_text',    array( $this, 'process' ),       $this->filter_priority );
			add_filter( 'widget_title',   array( $this, 'process_title' ), $this->filter_priority );
		}

		// add IE6 zero-width-space removal CSS Hook styling
		add_action( 'wp_head', array( $this, 'add_wp_head' ) );
	}


	/**
	 * Process title text fragment.
	 *
	 * Calls `process( $text, true )`.
	 *
	 * @param string $text
	 */
	function process_title( $text ) {
		return $this->process( $text, true );
	}

	/**
	 * Process text fragment.
	 *
	 * @param string $text
	 * @param boolean $is_title Default false.
	 */
	function process( $text, $is_title = false ) {
		$typo = $this->get_php_typo();
		$transient = 'typo_' . base64_encode( md5( $text, true ) . $this->cached_settings_hash );

		if ( is_feed() ) { // feed readers can be pretty stupid
			$transient .= 'f' . ( $is_title ? 't' : 's' ) . $this->version_hash;

			if ( ! empty( $this->settings['typoDisableCaching'] ) || false === ( $processed_text = get_transient( $transient ) ) ) {
				$processed_text = $typo->process_feed( $text, $is_title );
				$this->set_transient( $transient, $processed_text, DAY_IN_SECONDS );
			}
		} else {
			$transient .= ( $is_title ? 't' : 's' ) . $this->version_hash;

			if ( ! empty( $this->settings['typoDisableCaching'] ) || false === ( $processed_text = get_transient( $transient ) ) ) {
				$processed_text = $typo->process( $text, $is_title );
				$this->set_transient( $transient, $processed_text, DAY_IN_SECONDS );
			}
		}

		return $processed_text;
	}

	/**
	 * Set a transient and store the key.
	 *
	 * @param string $transient The transient key. Maximum length depends on WordPress version (for WP < 4.4 it is 45 characters)
	 * @param mixed  $value The value to store.
	 * @param number $duration The duration in seconds. Optional. Default 1 second.
	 * @return boolean True if the transient could be set successfully.
	 */
	public function set_transient( $transient, $value, $duration = 1 ) {
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

				// Try again next time
				$this->set_transient( $transient, $this->php_typo->save_state(), WEEK_IN_SECONDS );
			}

			// Settings won't be touched again, so cache the hash
			$this->cached_settings_hash = $this->php_typo->get_settings_hash( 11 );
		}

		return $this->php_typo;
	}

	/**
	 * Initialize the PHP_Typograpyh instance from our settings.
	 */
	private function init_php_typo() {
		// load configuration variables into our phpTypography class
		$this->php_typo->set_tags_to_ignore( $this->settings['typoIgnoreTags'] );
		$this->php_typo->set_classes_to_ignore( $this->settings['typoIgnoreClasses'] );
		$this->php_typo->set_ids_to_ignore( $this->settings['typoIgnoreIDs'] );

		if ( $this->settings['typoSmartCharacters'] ) {
			$this->php_typo->set_smart_dashes( $this->settings['typoSmartDashes'] );
			$this->php_typo->set_smart_ellipses( $this->settings['typoSmartEllipses'] );
			$this->php_typo->set_smart_math( $this->settings['typoSmartMath'] );

			// note smart_exponents was grouped with smart_math for the WordPress plugin,
			// but does not have to be done that way for other ports
			$this->php_typo->set_smart_exponents( $this->settings['typoSmartMath'] );
			$this->php_typo->set_smart_fractions( $this->settings['typoSmartFractions'] );
			$this->php_typo->set_smart_ordinal_suffix( $this->settings['typoSmartOrdinals'] );
			$this->php_typo->set_smart_marks( $this->settings['typoSmartMarks'] );
			$this->php_typo->set_smart_quotes( $this->settings['typoSmartQuotes'] );

			$this->php_typo->set_smart_diacritics( $this->settings['typoSmartDiacritics'] );
			$this->php_typo->set_diacritic_language( $this->settings['typoDiacriticLanguages'] );
			$this->php_typo->set_diacritic_custom_replacements( $this->settings['typoDiacriticCustomReplacements'] );

			$this->php_typo->set_smart_quotes_primary( $this->settings['typoSmartQuotesPrimary'] );
			$this->php_typo->set_smart_quotes_secondary( $this->settings['typoSmartQuotesSecondary'] );
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

		$this->php_typo->set_single_character_word_spacing( $this->settings['typoSingleCharacterWordSpacing'] );
		$this->php_typo->set_dash_spacing( $this->settings['typoDashSpacing'] );
		$this->php_typo->set_fraction_spacing( $this->settings['typoFractionSpacing'] );
		$this->php_typo->set_unit_spacing( $this->settings['typoUnitSpacing'] );
		$this->php_typo->set_units( $this->settings['typoUnits'] );
		$this->php_typo->set_space_collapse( $this->settings['typoSpaceCollapse'] );
		$this->php_typo->set_dewidow( $this->settings['typoPreventWidows'] );
		$this->php_typo->set_max_dewidow_length( $this->settings['typoWidowMinLength'] );
		$this->php_typo->set_max_dewidow_pull( $this->settings['typoWidowMaxPull'] );
		$this->php_typo->set_wrap_hard_hyphens( $this->settings['typoWrapHyphens'] );
		$this->php_typo->set_email_wrap( $this->settings['typoWrapEmails'] );
		$this->php_typo->set_url_wrap( $this->settings['typoWrapURLs'] );
		$this->php_typo->set_min_after_url_wrap( $this->settings['typoWrapMinAfter'] );
		$this->php_typo->set_style_ampersands( $this->settings['typoStyleAmps'] );
		$this->php_typo->set_style_caps( $this->settings['typoStyleCaps'] );
		$this->php_typo->set_style_numbers( $this->settings['typoStyleNumbers'] );
		$this->php_typo->set_style_initial_quotes( $this->settings['typoStyleInitialQuotes'] );
		$this->php_typo->set_initial_quote_tags( $this->settings['typoInitialQuoteTags'] );

		if ( $this->settings['typoEnableHyphenation'] ) {
			$this->php_typo->set_hyphenation( $this->settings['typoEnableHyphenation'] );
			$this->php_typo->set_hyphenate_headings( $this->settings['typoHyphenateHeadings'] );
			$this->php_typo->set_hyphenate_all_caps( $this->settings['typoHyphenateCaps'] );
			$this->php_typo->set_hyphenate_title_case( $this->settings['typoHyphenateTitleCase'] );
			$this->php_typo->set_hyphenation_language( $this->settings['typoHyphenateLanguages'] );
			$this->php_typo->set_min_length_hyphenation( $this->settings['typoHyphenateMinLength'] );
			$this->php_typo->set_min_before_hyphenation( $this->settings['typoHyphenateMinBefore'] );
			$this->php_typo->set_min_after_hyphenation( $this->settings['typoHyphenateMinAfter'] );
			$this->php_typo->set_hyphenation_exceptions( $this->settings['typoHyphenateExceptions'] );
		} else { // save some cycles
			$this->php_typo->set_hyphenation( $this->settings['typoEnableHyphenation'] );
		}
	}

	/**
	 * Retrieve the list of valid hyphenation languages.
	 * The language names are translation-ready but not translated yet.
	 *
	 * @return array An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	public function get_hyphenation_languages() {
		return $this->get_php_typo()->get_hyphenation_languages();
	}

	/**
	 * Retrieve the list of valid diacritic replacement languages.
	 * The language names are translation-ready but not translated yet.
	 *
	 * @return array An array in the form of ( LANG_CODE => LANGUAGE ).
	 */
	public function get_diacritic_languages() {
		return $this->get_php_typo()->get_diacritic_languages();
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
		if ( $this->settings['typoStyleCSSInclude'] && trim( $this->settings['typoStyleCSS'] ) != '' ) {
			echo '<style type="text/css">'."\r\n";
			echo $this->settings['typoStyleCSS']."\r\n";
			echo "</style>\r\n";
		}

		if ( $this->settings['typoRemoveIE6'] ) {
			echo "<!--[if lt IE 7]>\r\n";
			echo "<script type='text/javascript'>";
			echo "function stripZWS() { document.body.innerHTML = document.body.innerHTML.replace(/\u200b/gi,''); }";
			echo "window.onload = stripZWS;";
			echo "</script>\r\n";
			echo "<![endif]-->\r\n";
		}

		if ( $this->settings['typoHyphenateSafariFontWorkaround'] ) {
			echo "<style type=\"text/css\">body {-webkit-font-feature-settings: \"liga\", \"dlig\";}</style>\r\n";
		}
	}

	/**
	 * Load translations.
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
}
