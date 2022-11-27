<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2022 Peter Putzer.
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

namespace WP_Typography\Components;

use WP_Typography\Implementation;
use WP_Typography\Data_Storage\Options;
use WP_Typography\UI;
use WP_Typography\Settings\Plugin_Configuration;

use Mundschenk\UI\Control_Factory;
use Mundschenk\UI\Control;
use Mundschenk\UI\Controls\Checkbox_Input;
use Mundschenk\UI\Controls\Select;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since  3.1.0
 * @since  5.6.0 Obsolete property $plugin_file removed.
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @phpstan-import-type Config from Plugin_Configuration
 * @phpstan-import-type Tab from UI\Tabs
 * @phpstan-import-type Section from UI\Sections
 * @phpstan-type Help_Page array{
 *     heading : string,
 *     content : string,
 * }
 */
class Admin_Interface implements Plugin_Component {
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
	 * Links to add the settings page.
	 *
	 * @var array<string,string> $admin_resource_links An array in the form of 'anchor text' => 'URL'.
	 */
	private $admin_resource_links;

	/**
	 * Context sensitive help for the settings page.
	 *
	 * @var Help_Page[] $admin_help_pages
	 */
	private array $admin_help_pages;

	/**
	 * Tabs IDs and headings for the settings page.
	 *
	 * Tabs will be displayed in array order.
	 *
	 * @var Tab[] $admin_form_tabs
	 */
	private array $admin_form_tabs;

	/**
	 * Section IDs and headings for the settings page.
	 *
	 * Sections will be displayed in array order.
	 *
	 * @var Section[] $admin_form_sections
	 */
	private array $admin_form_sections;

	/**
	 * The form controls on the settings page.
	 *
	 * @var Control[] $admin_form_controls
	 */
	private array $admin_form_controls = [];

	/**
	 * The plugin API.
	 *
	 * @since 5.7.0 Renamed to $api.
	 *
	 * @var Implementation
	 */
	private Implementation $api;

	/**
	 * The current active settings tab.
	 *
	 * @var string
	 */
	private $active_tab;

	/**
	 * The plugin configuration defaults (including UI definition).
	 *
	 * @var array<string,Config>
	 */
	private array $defaults;

	/**
	 * An array to keep track of triggered admin notices.
	 *
	 * @var bool[]
	 */
	private array $triggered_notice = [];

	/**
	 * Create a new instace of admin backend.
	 *
	 * @since 5.6.0 Parameters $basename and $plugin_path removed.
	 * @since 5.7.0 Parameter $api added.
	 *
	 * @param Implementation $api     The core API.
	 * @param Options        $options The Options API handler.
	 */
	public function __construct( Implementation $api, Options $options ) {
		$this->api     = $api;
		$this->options = $options;

	}

	/**
	 * Set up the various hooks for the admin side.
	 *
	 * @since 5.7.0 Parameter $plugin removed.
	 */
	public function run() : void {
		if ( \is_admin() ) {

			// Cache the plugin basename.
			$this->plugin_basename = \plugin_basename( \WP_TYPOGRAPHY_PLUGIN_FILE );

			// Set up default options.
			$this->defaults = Plugin_Configuration::get_defaults();

			// Initialize admin form.
			$this->admin_resource_links = $this->initialize_resource_links();
			$this->admin_help_pages     = $this->initialize_help_pages();
			$this->admin_form_tabs      = UI\Tabs::get_tabs();
			$this->admin_form_sections  = UI\Sections::get_sections();
			$this->admin_form_controls  = Control_Factory::initialize( $this->defaults, $this->options, Options::CONFIGURATION );

			// Add action hooks.
			\add_action( 'admin_menu', [ $this, 'add_options_page' ] );
			\add_action( 'admin_init', [ $this, 'register_the_settings' ] );
			\add_action( 'admin_init', [ $this, 'maybe_add_privacy_notice_content' ] );

			// Add filter hooks.
			\add_filter( 'plugin_action_links_' . $this->plugin_basename, [ $this, 'plugin_action_links' ] );
		}
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 *
	 * @return array<string,string> The array keys consist of the translated anchor text, their values of the linked URL.
	 */
	private function initialize_resource_links() : array {
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
	 *
	 * @phpstan-return Help_Page[]
	 */
	private function initialize_help_pages() : array {
		return [
			'help-intro' => [
				'heading' => \__( 'Introduction', 'wp-typography' ),
				'content' =>
					'<p>' .
					\__( 'Improve your web typography with <em>hyphenation</em>, <em>space control</em>, <em>intelligent character replacement</em>, and <em>CSS hooks</em>. These improvements can be enabled separately via the settings tabs provided below. We try to provide reasonable default values, but please check that they are suitable for your site language.', 'wp-typography' ) .
					'</p><p>' .
					\__( 'Please keep in mind that technically, WordPress applies the typographic fixes on the individual content parts used in your site templates, e.g. <code>the_title</code>, <code>the_content</code>, not the page as a whole. For this reason, HTML tags (including classes and IDs) from the theme\'s template files cannot be used to limit the scope of wp-Typography\'s processing.', 'wp-typography' ) .
					'</p>',
			],
		];
	}

	/**
	 * Register admin settings.
	 */
	public function register_the_settings() : void {
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
		\add_filter( "pre_update_option_{$configuration_name}", [ $this, 'filter_update_option' ], 10, 2 );

		// Register control render callbacks.
		foreach ( $this->admin_form_controls as $control ) {
			$control->register( self::OPTION_GROUP );
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
		if (
			 // phpcs:disable WordPress.Security.NonceVerification.Missing -- we are only checking that the button was clicked.
			! empty( $_POST[ $this->options->get_name( Options::RESTORE_DEFAULTS ) ] ) ||
			! empty( $_POST[ $this->options->get_name( Options::CLEAR_CACHE ) ] )
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		) {
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
	protected function get_active_settings_tab() : string {
		if ( empty( $this->active_tab ) ) {
			// Check active tab.
			if ( ! empty( $_REQUEST['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->active_tab = \sanitize_key( $_REQUEST['tab'] );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( ! empty( $_REQUEST['option_page'] ) && 0 === \strpos( \sanitize_key( $_REQUEST['option_page'] ), self::OPTION_GROUP ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->active_tab = \substr( \sanitize_key( $_REQUEST['option_page'] ), \strlen( self::OPTION_GROUP ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} else {
				$this->active_tab = (string) \array_keys( $this->admin_form_tabs )[0];
			}
		}

		return $this->active_tab;
	}

	/**
	 * Add proper notification for Restore Defaults button.
	 *
	 * @param mixed $input Ignored.
	 *
	 * @return bool
	 */
	public function sanitize_restore_defaults( $input ) {
		return $this->trigger_admin_notice( Options::RESTORE_DEFAULTS, 'defaults-restored', \__( 'Settings reset to default values.', 'wp-typography' ), 'updated', $input );
	}

	/**
	 * Add proper notification for Clear Cache button.
	 *
	 * @param mixed $input Ignored.
	 *
	 * @return bool
	 */
	public function sanitize_clear_cache( $input ) : bool {
		return $this->trigger_admin_notice( Options::CLEAR_CACHE, 'cache-cleared', \__( 'Cached post content cleared.', 'wp-typography' ), 'notice-info', $input );
	}

	/**
	 * Sanitize plugin settings array.
	 *
	 * @param  array<string,string|int|bool> $input The plugin settings.

	 * @return array<string,string|int|bool>         The sanitized plugin settings.
	 */
	public function sanitize_settings( $input ) : array {
		$current_tab = $this->get_active_settings_tab();

		foreach ( $this->defaults as $key => $info ) {
			if ( $current_tab === $info['tab_id'] && Checkbox_Input::class === $info['ui'] ) {
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
	 * @return bool The $input parameter cast to a boolean value.
	 */
	protected function trigger_admin_notice( $setting_name, $notice_id, $message, $notice_level, $input ) : bool {
		if (
			 // phpcs:ignore WordPress.Security.NonceVerification.Missing -- we are only checking that the button was clicked.
			! empty( $_POST[ $this->options->get_name( $setting_name ) ] ) &&
			empty( $this->triggered_notice[ $setting_name ] )
		) {
			\add_settings_error( self::OPTION_GROUP . $this->get_active_settings_tab(), $notice_id, $message, $notice_level );

			// Workaround for https://core.trac.wordpress.org/ticket/21989.
			$this->triggered_notice[ $setting_name ] = true;
		}

		return (bool) $input;
	}

	/**
	 * Add an options page for the plugin settings.
	 */
	public function add_options_page() : void {
		$page = \add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', \strtolower( $this->plugin_name ), [ $this, 'get_admin_page_content' ] );

		if ( ! $page ) {
			// User has insufficient capabilities.
			return;
		}

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
	public function add_context_help() : void {
		$screen = \get_current_screen();
		if ( empty( $screen ) ) {
			return; // Guard against calls when no current screen is defined.
		}

		foreach ( $this->admin_help_pages as $help_id => $help ) {
			$tab = [
				'id'      => $help_id,
				'title'   => $help['heading'],
				'content' => $help['content'],
			];

			$screen->add_help_tab( $tab );
		}

		// Create sidebar.
		$sidebar = '<p>' . \__( 'Useful resources:', 'wp-typography' ) . '</p><ul>';
		foreach ( $this->admin_resource_links as $anchor => $url ) {
			// $anchor is already translated.
			$sidebar .= '<li><a href="' . \esc_url( $url ) . '">' . $anchor . '</a></li>';
		}
		$sidebar .= '</ul>';

		$screen->set_help_sidebar( $sidebar );
	}

	/**
	 * Print any additional markup for the given form section.
	 *
	 * @param array $section The section information.
	 *
	 * @phpstan-param array{id : string, title? : string, callback? : callable} $section
	 */
	public function print_settings_section( array $section ) : void {
		$section_id = ! empty( $section['id'] ) ? $section['id'] : '';

		if ( ! empty( $this->admin_form_tabs[ $section_id ]['description'] ) ) {
			$description = $this->admin_form_tabs[ $section_id ]['description'];
		} elseif ( ! empty( $this->admin_form_sections[ $section_id ]['description'] ) ) {
			$description = $this->admin_form_sections[ $section_id ]['description'];
		}

		// Load the settings page HTML.
		require \WP_TYPOGRAPHY_PLUGIN_PATH . '/admin/partials/settings/section.php';
	}

	/**
	 * Enqueue stylesheet for options page.
	 */
	public function print_admin_styles() : void {
		\wp_enqueue_style( 'wp-typography-settings', \plugins_url( 'admin/css/settings.css', $this->plugin_basename ), [], $this->api->get_version(), 'all' );
	}

	/**
	 * Add a 'Settings' link to the wp-Typography entry in the plugins list.
	 *
	 * @param  string[] $links An array of HTML link tags.
	 *
	 * @return string[]        An array of HTML link tags.
	 */
	public function plugin_action_links( array $links ) : array {
		$adminurl = \trailingslashit( \admin_url() );

		// Add link "Settings" to the plugin in /wp-admin/plugins.php.
		$settings_link = '<a href="' . $adminurl . 'options-general.php?page=' . \strtolower( $this->plugin_name ) . '">' . \__( 'Settings', 'wp-typography' ) . '</a>';
		\array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Display the plugin options page.
	 */
	public function get_admin_page_content() : void {
		$this->admin_form_controls[ Config::HYPHENATE_LANGUAGES ]->set_option_values( $this->api->get_hyphenation_languages() );
		$this->admin_form_controls[ Config::DIACRITIC_LANGUAGES ]->set_option_values( $this->api->get_diacritic_languages() );

		// Load the settings page HTML.
		require \WP_TYPOGRAPHY_PLUGIN_PATH . '/admin/partials/settings/settings-page.php';
	}


	/**
	 * Adds a privacy notice snippet if used with WordPress 4.9.6 or greater.
	 *
	 * @since 5.4.0
	 */
	public function maybe_add_privacy_notice_content() : void {
		// Don't crash on older versions of WordPress.
		if ( ! \function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = '<p class="privacy-policy-tutorial">' . \__( "wp-Typography does not store, transmit or otherwise process personal data as such. It does cache the content of the site's posts. If necessary, you can clear this cache from the plugin's settings page.", 'wp-typography' ) . '</p>';

		\wp_add_privacy_policy_content( \__( 'wp-Typography', 'wp-typography' ), $content );
	}
}
