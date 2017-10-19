<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2012-2013 Marie Hogebrandt.
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
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      3.1.0
 * @author     Peter Putzer <github@mundschenk.at>
 */
class Admin {
	/**
	 * The group name used for registering the plugin options.
	 *
	 * @var string
	 */
	const OPTION_GROUP = 'typo_options_';

	/**
	 * The Option API handler.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * The user-visible name of the plugin.
	 *
	 * @todo Maybe we should translate the name?
	 * @var string $plugin_name
	 */
	private $plugin_name = 'wp-Typography';

	/**
	 * The result of plugin_basename() for the main plugin file (relative from plugins folder).
	 *
	 * @var string
	 */
	private $plugin_basename;

	/**
	 * The full version string of the plugin.
	 *
	 * @var string $version;
	 */
	private $version;

	/**
	 * Links to add the settings page.
	 *
	 * @var array $admin_resource_links An array in the form of 'anchor text' => 'URL'.
	 */
	private $admin_resource_links;

	/**
	 * Context sensitive help for the settings page.
	 *
	 * @var array $admin_help_pages
	 */
	private $admin_help_pages;

	/**
	 * Section IDs and headings for the settings page.
	 *
	 * Sections will be displayed in the order included.
	 *
	 * @var array $adminFormSections An array in the form of 'id' => 'heading'.
	 */
	private $admin_form_tabs;

	/**
	 * Fieldsets in the admin settings.
	 *
	 * The fieldsets will be displayed in the order of inclusion.
	 *
	 * @var array $admin_form_section_fieldsets {
	 *     @type array $id {
	 *         @type string $heading Fieldset name.
	 *         @type string $sectionID Parent section ID.
	 *     }
	 * }
	 */
	private $admin_form_sections;

	/**
	 * The form controls on the settings page.
	 *
	 * @var array $admin_form_controls {
	 *      @type Control $id
	 * }
	 */
	private $admin_form_controls = [];

	/**
	 * The plugin instance used for setting transients.
	 *
	 * @var \WP_Typography
	 */
	private $plugin;

	/**
	 * The current active settings tab.
	 *
	 * @var string
	 */
	private $active_tab;

	/**
	 * The plugin configuration defaults (including UI definition).
	 *
	 * @var array
	 */
	private $defaults;

	/**
	 * Create a new instace of admin backend.
	 *
	 * @param string  $basename The plugin slug.
	 * @param Options $options  The Options API handler.
	 */
	public function __construct( $basename, Options $options ) {
		$this->plugin_basename = $basename;
		$this->options         = $options;
	}

	/**
	 * Set up the various hooks for the admin side.
	 *
	 * @param \WP_Typography $plugin The plugin object.
	 */
	public function run( \WP_Typography $plugin ) {
		// Save the main plugin for later use.
		$this->version = $plugin->get_version();
		$this->plugin  = $plugin;

		// Set up default options.
		$this->defaults = Config::get_defaults();

		// Initialize admin form.
		$this->admin_resource_links = $this->initialize_resource_links();
		$this->admin_help_pages     = $this->initialize_help_pages();
		$this->admin_form_tabs      = UI\Tabs::get_tabs();
		$this->admin_form_sections  = $this->initialize_form_sections();
		$this->admin_form_controls  = $this->initialize_controls();

		// Add action hooks.
		\add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		\add_action( 'admin_init', [ $this, 'register_the_settings' ] );

		// Add filter hooks.
		\add_filter( 'plugin_action_links_' . $this->plugin_basename, [ $this, 'plugin_action_links' ] );
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 *
	 * @return array {
	 *     @type string $translated_anchor_text A URL.
	 * }
	 */
	private function initialize_resource_links() {
		return [
			\__( 'Plugin Home', 'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/',
			\__( 'FAQs',        'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/frequently-asked-questions/',
			\__( 'Changelog',   'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/changes/',
			\__( 'License',     'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/license/',
		];
	}

	/**
	 * Initialize displayable strings for the settings page help.
	 *
	 * @return array {
	 *      @type array $id {
	 *          @type string $heading The displayed tab name.
	 *          @type string $content The help content.
	 *      }
	 * }
	 */
	private function initialize_help_pages() {
		return [
			'help-intro' => [
				'heading' => \__( 'Introduction', 'wp-typography' ),
				'content' =>
					'<p>' .
					__( 'Improve your web typography with <em>hyphenation</em>, <em>space control</em>, <em>intelligent character replacement</em>, and <em>CSS hooks</em>. These improvements can be enabled separately via the settings tabs provided below. We try to provide reasonable default values, but please check that they are suitable for your site language.', 'wp-typography' ) .
					'</p><p>' .
					__( 'Please keep in mind that technically, WordPress applies the typographic fixes on the individual content parts used in your site templates, e.g. <code>the_title</code>, <code>the_content</code>, not the page as a whole. For this reason, HTML tags (including classes and IDs) from the theme\'s template files cannot be used to limit the scope of wp-Typography\'s processing.', 'wp-typography' ) .
					'</p>',
			],
		];
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 *
	 * @return array {
	 *         @type array $id {
	 *               The form ID.
	 *
	 *               @type string $heading     Section name (translated).
	 *               @type string $description Section description (translated).
	 *               @type string $tab_id      Tab ID.
	 *         }
	 * }
	 */
	private function initialize_form_sections() {

		// Fieldsets will be displayed in the order included.
		return [
			'math-replacements' => [
				'heading'     => \__( 'Math & Numbers', 'wp-typography' ),
				'description' => \__( 'Not all number formattings are appropriate for all languages.', 'wp-typography' ),
				'tab_id'      => UI\Tab::CHARACTER_REPLACEMENT,
			],
			'enable-wrapping'   => [
				'heading'     => \__( 'Enable Wrapping', 'wp-typography' ),
				'description' => \__( 'Sometimes you want to enable certain long words to wrap to a new line, while at other times you want to prevent wrapping.', 'wp-typography' ),
				'tab_id'      => UI\Tab::SPACE_CONTROL,
			],
		];
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 *
	 * @return array {
	 *         @type Control $id A control object.
	 * }
	 */
	private function initialize_controls() {

		// Create controls from default configuration.
		$controls = [];
		foreach ( $this->defaults as $control_id => $control_info ) {
			$controls[ $control_id ] = new $control_info['ui']( $this->options, self::OPTION_GROUP, $control_id, $control_info );
		}

		// Group controls.
		$controls[ Config::ENABLE_HYPHENATION ]->add_grouped_control( $controls[ Config::HYPHENATE_LANGUAGES ] );
		$controls[ Config::HYPHENATE_HEADINGS ]->add_grouped_control( $controls[ Config::HYPHENATE_TITLE_CASE ] );
		$controls[ Config::HYPHENATE_HEADINGS ]->add_grouped_control( $controls[ Config::HYPHENATE_COMPOUNDS ] );
		$controls[ Config::HYPHENATE_HEADINGS ]->add_grouped_control( $controls[ Config::HYPHENATE_CAPS ] );

		$controls[ Config::HYPHENATE_MIN_LENGTH ]->add_grouped_control( $controls[ Config::HYPHENATE_MIN_BEFORE ] );
		$controls[ Config::HYPHENATE_MIN_LENGTH ]->add_grouped_control( $controls[ Config::HYPHENATE_MIN_AFTER ] );

		$controls[ Config::HYPHENATE_CLEAN_CLIPBOARD ]->add_grouped_control( $controls[ Config::HYPHENATE_SAFARI_FONT_WORKAROUND ] );

		$controls[ Config::SMART_QUOTES ]->add_grouped_control( $controls[ Config::SMART_QUOTES_PRIMARY ] );
		$controls[ Config::SMART_QUOTES ]->add_grouped_control( $controls[ Config::SMART_QUOTES_SECONDARY ] );

		$controls[ Config::SMART_DASHES ]->add_grouped_control( $controls[ Config::SMART_DASHES_STYLE ] );

		$controls[ Config::SMART_DIACRITICS ]->add_grouped_control( $controls[ Config::DIACRITIC_LANGUAGES ] );
		$controls[ Config::SMART_DIACRITICS ]->add_grouped_control( $controls[ Config::DIACRITIC_CUSTOM_REPLACEMENTS ] );

		$controls[ Config::UNIT_SPACING ]->add_grouped_control( $controls[ Config::UNITS ] );

		$controls[ Config::WRAP_URLS ]->add_grouped_control( $controls[ Config::WRAP_MIN_AFTER ] );

		$controls[ Config::PREVENT_WIDOWS ]->add_grouped_control( $controls[ Config::WIDOW_MIN_LENGTH ] );
		$controls[ Config::PREVENT_WIDOWS ]->add_grouped_control( $controls[ Config::WIDOW_MAX_PULL ] );

		$controls[ Config::STYLE_INITIAL_QUOTES ]->add_grouped_control( $controls[ Config::INITIAL_QUOTE_TAGS ] );

		$controls[ Config::STYLE_CSS_INCLUDE ]->add_grouped_control( $controls[ Config::STYLE_CSS ] );

		return $controls;
	}

	/**
	 * Register admin settings.
	 */
	public function register_the_settings() {
		$configuration_name    = $this->options->get_name( Options::CONFIGURATION );
		$restore_defaults_name = $this->options->get_name( Options::RESTORE_DEFAULTS );
		$clear_cache_name      = $this->options->get_name( Options::CLEAR_CACHE );

		// Register settings (for each tab).
		foreach ( $this->admin_form_tabs as $tab_id => $tab ) {
			\register_setting( self::OPTION_GROUP . $tab_id, $configuration_name,    [ $this, 'sanitize_settings' ] );
			\register_setting( self::OPTION_GROUP . $tab_id, $restore_defaults_name, [ $this, 'sanitize_restore_defaults' ] );
			\register_setting( self::OPTION_GROUP . $tab_id, $clear_cache_name,      [ $this, 'sanitize_clear_cache' ] );
		}

		// Prevent spurious saves.
		\add_filter( 'pre_update_option_' . $configuration_name, [ $this, 'filter_update_option' ], 10, 2 );

		// Register controls.
		foreach ( $this->admin_form_controls as $control ) {
			$control->register();
		}
	}

	/**
	 * Prevent settings from being saved if we are clearing the cache or restoring defaults.
	 *
	 * @param mixed $value     The new value.
	 * @param mixed $old_value The old value.
	 *
	 * @return mixed
	 */
	public function filter_update_option( $value, $old_value ) {

		// Ensure a complete set of settings.
		$value = \wp_parse_args( $value, $old_value );

		// Check if one of the auxiliary buttons was clicked and ignore changes in that case.
		if ( ! empty( $_POST[ $this->options->get_name( Options::RESTORE_DEFAULTS ) ] ) || ! empty( $_POST[ $this->options->get_name( Options::CLEAR_CACHE ) ] ) ) { // WPCS: CSRF ok. Input var okay.
			return $old_value;
		} else {
			return $value;
		}
	}

	/**
	 * Retrieve the active tab on the settings page.
	 *
	 * @return string
	 */
	protected function get_active_settings_tab() {
		if ( empty( $this->active_tab ) ) {
			// Check active tab.
			if ( ! empty( $_REQUEST['tab'] ) ) {
				$this->active_tab = \sanitize_key( $_REQUEST['tab'] ); // WPCS: CSRF ok. Input var okay.
			} elseif ( ! empty( $_REQUEST['option_page'] ) && 0 === \strpos( \sanitize_key( $_REQUEST['option_page'] ), self::OPTION_GROUP ) ) { // WPCS: CSRF ok. Input var okay.
				$this->active_tab = \substr( \sanitize_key( $_REQUEST['option_page'] ), \strlen( self::OPTION_GROUP ) ); // WPCS: CSRF ok. Input var okay.
			} else {
				$all_tabs         = \array_keys( $this->admin_form_tabs ); // PHP 5.3 workaround.
				$this->active_tab = $all_tabs[0];
			}
		}

		return $this->active_tab;
	}

	/**
	 * Add proper notification for Restore Defaults button.
	 *
	 * @param mixed $input Ignored.
	 *
	 * @return mixed
	 */
	public function sanitize_restore_defaults( $input ) {
		return $this->trigger_admin_notice( Options::RESTORE_DEFAULTS, 'defaults-restored', \__( 'Settings reset to default values.', 'wp-typography' ), 'updated', $input );
	}

	/**
	 * Add proper notification for Clear Cache button.
	 *
	 * @param mixed $input Ignored.
	 *
	 * @return mixed
	 */
	public function sanitize_clear_cache( $input ) {
		return $this->trigger_admin_notice( Options::CLEAR_CACHE, 'cache-cleared', \__( 'Cached post content cleared.', 'wp-typography' ), 'notice-info', $input );
	}

	/**
	 * Sanitize plugin settings array.
	 *
	 * @param  array $input The plugin settings.
	 *
	 * @return array The sanitized plugin settings.
	 */
	public function sanitize_settings( $input ) {
		$active_tab = $this->get_active_settings_tab();

		foreach ( $this->defaults as $key => $info ) {
			if ( $active_tab === $info['tab_id'] && UI\Checkbox_Input::class === $info['ui'] ) {
				$input[ $key ] = ! empty( $input[ $key ] );
			}
		}

		return $input;
	}

	/**
	 * Use sanitization callback to trigger an admin notice.
	 *
	 * @param  string $setting_name The setting used to trigger the notice (without the prefix).
	 * @param  string $notice_id    HTML ID attribute for the notice.
	 * @param  string $message      Translated message string.
	 * @param  string $notice_level 'updated', 'notice-info', etc.
	 * @param  mixed  $input        Passed back.
	 *
	 * @return mixed The $input parameter.
	 */
	protected function trigger_admin_notice( $setting_name, $notice_id, $message, $notice_level, $input ) {
		if ( ! empty( $_POST[ $this->options->get_name( $setting_name ) ] ) ) { // WPCS: CSRF ok. Input var okay.
			\add_settings_error( self::OPTION_GROUP . $this->get_active_settings_tab(), $notice_id, $message, $notice_level );
		}

		return $input;
	}

	/**
	 * Add an options page for the plugin settings.
	 */
	public function add_options_page() {
		$page = \add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', \strtolower( $this->plugin_name ), [ $this, 'get_admin_page_content' ] );

		// General sections for each tab.
		foreach ( $this->admin_form_tabs as $tab_id => $tab ) {
			\add_settings_section( $tab_id, '', [ $this, 'print_settings_section' ], self::OPTION_GROUP . $tab_id );
		}

		// Additional sections.
		foreach ( $this->admin_form_sections as $section_id => $section ) {
			\add_settings_section( $section_id, $section['heading'], [ $this, 'print_settings_section' ], self::OPTION_GROUP . $section['tab_id'] );
		}

		// Add help tab.
		\add_action( 'load-' . $page, [ $this, 'add_context_help' ] );

		// Using registered $page handle to hook stylesheet loading.
		\add_action( 'admin_print_styles-' . $page, [ $this, 'print_admin_styles' ] );
	}

	/**
	 * Add context-sensitive help to the settings page.
	 */
	public function add_context_help() {
		$screen = \get_current_screen();

		foreach ( $this->admin_help_pages as $help_id => $help ) {
			$screen->add_help_tab( [
				'id'      => $help_id,
				'title'   => $help['heading'],
				'content' => $help['content'],
			] );
		}

		// Create sidebar.
		$sidebar = '<p>' . \__( 'Useful resources:', 'wp-typography' ) . '</p><ul>';
		foreach ( $this->admin_resource_links as $anchor => $url ) {
			$sidebar .= '<li><a href="' . \esc_url( $url ) . '">' . \__( $anchor, 'wp-typography' ) . '</a></li>';  // @codingStandardsIgnoreLine.
		}
		$sidebar .= '</ul>';

		$screen->set_help_sidebar( $sidebar );
	}

	/**
	 * Print any additional markup for the given form section.
	 *
	 * @param array $section The section information.
	 */
	public function print_settings_section( $section ) {
		$section_id = ! empty( $section['id'] ) ? $section['id'] : '';

		if ( ! empty( $this->admin_form_tabs[ $section_id ]['description'] ) ) {
			echo '<p>' . \esc_html( $this->admin_form_tabs[ $section_id ]['description'] ) . '</p>';
		} elseif ( ! empty( $this->admin_form_sections[ $section_id ]['description'] ) ) {
			echo '<p>' . \esc_html( $this->admin_form_sections[ $section_id ]['description'] ) . '</p>';
		}
	}

	/**
	 * Enqueue stylesheet for options page.
	 */
	public function print_admin_styles() {
		\wp_enqueue_style( 'wp-typography-settings', \plugins_url( 'admin/css/settings.css', $this->plugin_basename ), [], $this->version, 'all' );
	}

	/**
	 * Add a 'Settings' link to the wp-Typography entry in the plugins list.
	 *
	 * @param array $links An array of links.
	 * @return array An array of links.
	 */
	public function plugin_action_links( $links ) {
		$adminurl = \trailingslashit( \admin_url() );

		// Add link "Settings" to the plugin in /wp-admin/plugins.php.
		$settings_link = '<a href="' . $adminurl . 'options-general.php?page=' . \strtolower( $this->plugin_name ) . '">' . \__( 'Settings', 'wp-typography' ) . '</a>';
		\array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Display the plugin options page.
	 */
	public function get_admin_page_content() {
		$this->admin_form_controls[ Config::HYPHENATE_LANGUAGES ]->set_option_values( $this->plugin->load_hyphenation_languages() );
		$this->admin_form_controls[ Config::DIACRITIC_LANGUAGES ]->set_option_values( $this->plugin->load_diacritic_languages() );

		// Load the settings page HTML.
		include_once \dirname( \dirname( __DIR__ ) ) . '/admin/partials/settings.php';
	}
}
