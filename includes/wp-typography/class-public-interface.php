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

namespace WP_Typography;

use \WP_Typography\Options;
use \WP_Typography\UI;
use \WP_Typography\Settings\Plugin_Configuration as Config;

use \PHP_Typography\PHP_Typography;
use \PHP_Typography\Settings\Dash_Style;
use \PHP_Typography\Settings\Quote_Style;
use \PHP_Typography\Arrays;

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

		if ( ! \is_admin() ) {
			// Load settings.
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

		// Add CSS Hook styling.
		\add_action( 'wp_head', [ $this, 'add_wp_head' ] );

		// Optionally enable clipboard clean-up.
		\add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Save hyphenator cache on exit, if necessary.
		\add_action( 'shutdown', [ $this->plugin, 'save_hyphenator_cache_on_shutdown' ], 10 );
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
		$priority = \apply_filters( 'typo_filter_priority', $this->filter_priority );

		// Add filters for "full" content.
		/**
		 * Disables automatic filtering by wp-Typography.
		 *
		 * @since 3.6.0
		 *
		 * @param bool   $disable      Whether to disable automatic filtering. Default false.
		 * @param string $filter_group Which filters to disable. Possible values 'content', 'heading', 'title', 'acf'.
		 */
		if ( ! \apply_filters( 'typo_disable_filtering', false, 'content' ) ) {
			$this->enable_content_filters( $priority );
		}

		// Add filters for headings.
		/** This filter is documented in class-wp-typography.php */
		if ( ! \apply_filters( 'typo_disable_filtering', false, 'heading' ) ) {
			$this->enable_heading_filters( $priority );
		}

		// Extra care needs to be taken with the <title> tag.
		/** This filter is documented in class-wp-typography.php */
		if ( ! \apply_filters( 'typo_disable_filtering', false, 'title' ) ) {
			$this->enable_title_filters( $priority );
		}

		// Add filters for third-party plugins.
		/** This filter is documented in class-wp-typography.php */
		if ( \class_exists( 'acf' ) && \function_exists( 'acf_get_setting' ) && ! \apply_filters( 'typo_disable_filtering', false, 'acf' ) ) {
			$this->enable_acf_filters( $priority );
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
		\add_filter( 'widget_text',       [ $this->plugin, 'process' ], $priority );
	}

	/**
	 * Enable the heading filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_heading_filters( $priority ) {
		\add_filter( 'the_title',            [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_post_title',    [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_cat_title',     [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_tag_title',     [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_month_title',   [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'single_month_title',   [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'nav_menu_attr_title',  [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'nav_menu_description', [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'widget_title',         [ $this->plugin, 'process_title' ], $priority );
		\add_filter( 'list_cats',            [ $this->plugin, 'process_title' ], $priority );
	}

	/**
	 * Enable the title (not heading) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_title_filters( $priority ) {
		\add_filter( 'wp_title',             [ $this->plugin, 'process_feed' ],        $priority ); // WP < 4.4.
		\add_filter( 'document_title_parts', [ $this->plugin, 'process_title_parts' ], $priority );
		\add_filter( 'wp_title_parts',       [ $this->plugin, 'process_title_parts' ], $priority ); // WP < 4.4.
	}

	/**
	 * Enable the Advanced Custom Fields (https://www.advancedcustomfields.com) filters.
	 *
	 * @param int $priority Filter priority.
	 */
	private function enable_acf_filters( $priority ) {
		$acf_version = \intval( acf_get_setting( 'version' ) );

		if ( 5 === $acf_version ) {
			// Advanced Custom Fields Pro (version 5).
			$acf_prefix = 'acf/format_value';
		} elseif ( 4 === $acf_version ) {
			// Advanced Custom Fields (version 4).
			$acf_prefix = 'acf/format_value_for_api';
		}

		// Other ACF versions (i.e. < 4) are not supported.
		if ( ! empty( $acf_prefix ) ) {
			\add_filter( "{$acf_prefix}/type=wysiwyg",  [ $this->plugin, 'process' ],       $priority );
			\add_filter( "{$acf_prefix}/type=textarea", [ $this->plugin, 'process' ],       $priority );
			\add_filter( "{$acf_prefix}/type=text",     [ $this->plugin, 'process_title' ], $priority );
		}
	}

	/**
	 * Prints CSS and JS depending on plugin options.
	 */
	public function add_wp_head() {

		if ( $this->config[ Config::STYLE_CSS_INCLUDE ] && '' !== trim( $this->config[ Config::STYLE_CSS ] ) ) {
			echo '<style type="text/css">' . "\r\n";
			echo \esc_html( $this->config[ Config::STYLE_CSS ] ) . "\r\n";
			echo "</style>\r\n";
		}

		if ( $this->config[ Config::HYPHENATE_SAFARI_FONT_WORKAROUND ] ) {
			echo "<style type=\"text/css\">body {-webkit-font-feature-settings: \"liga\";font-feature-settings: \"liga\";-ms-font-feature-settings: normal;}</style>\r\n";
		}
	}

	/**
	 * Enqueues frontend JavaScript files.
	 */
	public function enqueue_scripts() {
		if ( $this->config[ Config::HYPHENATE_CLEAN_CLIPBOARD ] ) {
			// Set up file suffix and plugin version.
			$suffix     = SCRIPT_DEBUG ? '' : '.min';
			$version    = $this->plugin->get_version();
			$plugin_dir = \plugin_dir_url( $this->plugin_basename );

			\wp_enqueue_script( 'jquery-selection',                "{$plugin_dir}js/jquery.selection$suffix.js", [ 'jquery' ],                     $version, true );
			\wp_enqueue_script( 'wp-typography-cleanup-clipboard', "{$plugin_dir}js/clean_clipboard$suffix.js",  [ 'jquery', 'jquery-selection' ], $version, true );
		}
	}
}
