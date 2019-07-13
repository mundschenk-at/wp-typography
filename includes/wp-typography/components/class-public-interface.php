<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2019 Peter Putzer.
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

namespace WP_Typography\Components;

use WP_Typography\Data_Storage\Options;
use WP_Typography\UI;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\PHP_Typography;
use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\Settings\Quote_Style;

/**
 * The public (non-admin) functionality of the plugin.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Public_Interface implements Plugin_Component {

	/**
	 * The plugin instance used for setting transients.
	 *
	 * @var \WP_Typography
	 */
	protected $plugin;

	/**
	 * The priority for our filter hooks.
	 *
	 * @var int
	 */
	private $filter_priority = 9999;

	/**
	 * The result of plugin_basename() for the main plugin file (relative from plugins folder).
	 *
	 * @var string $plugin_basename
	 */
	private $plugin_basename;

	/**
	 * The plugin configuration.
	 *
	 * @var array
	 */
	protected $config;

	const CLEAN_CSS_PATTERNS = [
		"`^([\t\s]+)`Ssm",
		'`^\/\*(.+?)\*\/`Ssm',
		"`([\n\A;]+)\/\*(.+?)\*\/`Ssm",
		"`([\n\A;\s]+)//(.+?)[\n\r]`Ssm",
		"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`Ssm",
	];

	const CLEAN_CSS_REPLACEMENTS = [
		'',
		'',
		'$1',
		"\$1\n",
		"\n",
	];

	/**
	 * Creates a new instance of the Public_Interface.
	 *
	 * @param string $plugin_basename The result of plugin_basename() for the main plugin file.
	 */
	public function __construct( $plugin_basename ) {
		$this->plugin_basename = $plugin_basename;
	}

	/**
	 * Set up the various hooks for the admin side.
	 *
	 * @param \WP_Typography $plugin The plugin object.
	 */
	public function run( \WP_Typography $plugin ) {
		$this->plugin = $plugin;

		// Do not run our filters on the admin side or during a WP-CLI command.
		if ( ! \is_admin() && ! \defined( 'WP_CLI' ) ) {
			\add_action( 'init', [ $this, 'init' ] );
		}
	}

	/**
	 * Sets up filters and actions.
	 */
	public function init() {
		$this->config = $this->plugin->get_config();

		// Disable wptexturize filter if it conflicts with our settings.
		if ( $this->config[ Config::SMART_CHARACTERS ] ) {
			\add_filter( 'run_wptexturize', '__return_false' );

			// Ensure that wptexturize is actually off by forcing a re-evaluation (some plugins call it too early).
			\wptexturize( ' ', true ); // Argument must not be empty string!
		}

		// Check for NextGEN Gallery and use insane filter priority if activated.
		if ( \class_exists( 'C_NextGEN_Bootstrap' ) ) {
			$this->filter_priority = PHP_INT_MAX;
		}

		// Apply our filters.
		$this->add_content_filters();

		// Grab body classes via hook.
		\add_filter( 'body_class', [ $this->plugin, 'filter_body_class' ], $this->filter_priority );

		// Add CSS Hook styling.
		\add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

		// Optionally enable clipboard clean-up.
		\add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Save hyphenator cache on exit, if necessary.
		\add_action( 'shutdown', [ $this->plugin, 'save_hyphenator_cache_on_shutdown' ], 10 );
	}

	/**
	 * Adds content filter handlers.
	 */
	public function add_content_filters() {

		// Define the default filters.
		$filters = [
			// Add filters for "full" content.
			'content' => [ $this, 'enable_content_filters' ],
			// Add filters for headings.
			'heading' => [ $this, 'enable_heading_filters' ],
		];

		/**
		 * Filters the content filter enabling functions.
		 *
		 * @internal
		 *
		 * @param callable[string] $filters An array of enabling functions taking
		 *                                  the priority as an argument, indexed by
		 *                                  filter group.
		 */
		$filters = \apply_filters( 'typo_content_filters', $filters );

		/**
		 * Filters the priority used for wp-Typography's text processing filters.
		 *
		 * When NextGen Gallery is detected, the priority is set to PHP_INT_MAX.
		 *
		 * @since 3.2.0
		 *
		 * @param int $priority The filter priority. Default 9999.
		 */
		$priority = \apply_filters( 'typo_filter_priority', $this->filter_priority );

		foreach ( $filters as $tag => $enable ) {
			/**
			 * Disables automatic filtering by wp-Typography.
			 *
			 * @since 3.6.0
			 * @since 5.2.0 WooCommerce support added ($filter_group 'woocommerce').
			 * @since 5.6.0 Filter group `title` removed.
			 *
			 * @param bool   $disable      Whether to disable automatic filtering. Default false.
			 * @param string $filter_group Which filters to disable. Possible values 'content', 'heading', 'acf', 'woocommerce'.
			 */
			if ( ! \apply_filters( 'typo_disable_filtering', false, $tag ) ) {
				$enable( $priority );
			}
		}
	}

	/**
	 * Enable the content (body) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_content_filters( $priority ) {
		\add_filter( 'comment_author',    [ $this->plugin, 'process' ], $priority );
		\add_filter( 'comment_text',      [ $this->plugin, 'process' ], $priority );
		\add_filter( 'comment_text',      [ $this->plugin, 'process' ], $priority );
		\add_filter( 'the_content',       [ $this->plugin, 'process' ], $priority );
		\add_filter( 'term_name',         [ $this->plugin, 'process' ], $priority );
		\add_filter( 'term_description',  [ $this->plugin, 'process' ], $priority );
		\add_filter( 'link_name',         [ $this->plugin, 'process' ], $priority );
		\add_filter( 'the_excerpt',       [ $this->plugin, 'process' ], $priority );
		\add_filter( 'the_excerpt_embed', [ $this->plugin, 'process' ], $priority );
		\add_filter( 'wp_dropdown_cats',  [ $this->plugin, 'process' ], $priority );

		// Preserve shortcode handling on WordPress 4.8+.
		if ( \version_compare( \get_bloginfo( 'version' ), '4.8', '>=' ) ) {
			\add_filter( 'widget_text_content', [ $this->plugin, 'process' ], $priority );
		} else {
			\add_filter( 'widget_text', [ $this->plugin, 'process' ], $priority );
		}
	}

	/**
	 * Enable the heading filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_heading_filters( $priority ) {
		\add_filter( 'the_title',    [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'widget_title', [ $this->plugin, 'process_title' ], $priority );
	}

	/**
	 * Enqueues custom styles.
	 *
	 * @since 5.5.3
	 */
	public function enqueue_styles() {
		// Custom styles set via the CSS Hooks settings page.
		if ( $this->config[ Config::STYLE_CSS_INCLUDE ] && '' !== \trim( $this->config[ Config::STYLE_CSS ] ) ) {
			// Register and enqueue dummy stylesheet.
			\wp_register_style( 'wp-typography-custom', '' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- only inline.
			\wp_enqueue_style( 'wp-typography-custom' );

			// Print the inline styles.
			\wp_add_inline_style( 'wp-typography-custom', $this->clean_styles( $this->config[ Config::STYLE_CSS ] ) );
		}

		// The Safari bug workaround.
		if ( $this->config[ Config::HYPHENATE_SAFARI_FONT_WORKAROUND ] ) {
			// Register and enqueue dummy stylesheet.
			\wp_register_style( 'wp-typography-safari-font-workaround', '' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- only inline.
			\wp_enqueue_style( 'wp-typography-safari-font-workaround' );

			// Print the inline styles.
			\wp_add_inline_style( 'wp-typography-safari-font-workaround', 'body {-webkit-font-feature-settings: "liga";font-feature-settings: "liga";-ms-font-feature-settings: normal;}' );
		}
	}

	/**
	 * Enqueues frontend JavaScript files.
	 */
	public function enqueue_scripts() {
		if ( $this->config[ Config::HYPHENATE_CLEAN_CLIPBOARD ] ) {
			// Set up file suffix and plugin version.
			$suffix     = ( \defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ) ? '' : '.min';
			$version    = $this->plugin->get_version();
			$plugin_dir = \plugin_dir_url( $this->plugin_basename );

			\wp_enqueue_script( 'wp-typography-cleanup-clipboard', "{$plugin_dir}js/clean-clipboard$suffix.js", [ 'jquery' ], $version, true );
		}
	}

	/**
	 * Cleans up the user-supplied CSS rules for output. Removes comments and most
	 * whitespace and filters the rules through `safecss_filter_attr`.
	 *
	 * @since 5.5.3
	 *
	 * @param  string $css A string of CSS styles.
	 *
	 * @return string      Filtered string of CSS styles.
	 */
	protected function clean_styles( $css ) {
		$cleaned = \preg_replace( self::CLEAN_CSS_PATTERNS, self::CLEAN_CSS_REPLACEMENTS, $css );
		$css     = '';

		if ( \preg_match_all( '/\s*(?<selector>[^{}]+?)\s*\{\s*(?<rules>[^{}]+?)\s*\}\s*/sS', $cleaned, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $m ) {
				$selector = \wp_strip_all_tags( $m['selector'] );
				$rules    = \safecss_filter_attr( $m['rules'] );

				if ( ! empty( $selector ) && ! empty( $rules ) ) {
					$css .= "{$selector}{{$rules}}";
				}
			}
		}

		return $css;
	}
}
