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

use \WP_Typography\UI;

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
 * @package    wpTypography
 * @subpackage wpTypography/includes
 * @author     Peter Putzer <github@mundschenk.at>
 */
class WP_Typography_Admin {
	/**
	 * The group name used for registering the plugin options.
	 *
	 * @var string
	 */
	const OPTION_GROUP = 'typo_options_';

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
	 * @var string $local_plugin_path
	 */
	private $local_plugin_path;

	/**
	 * The absolute path to top-level directory for the plugin.
	 *
	 * @var string $plugin_path
	 */
	private $plugin_path;

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
	 * A lookup table for cache keys.
	 *
	 * @since 3.5.0
	 *
	 * @var array
	 */
	private $cache_key_names = [];

	/**
	 * The plugin instance used for setting transients.
	 *
	 * @var WP_Typography
	 */
	private $plugin;

	/**
	 * Create a new instace of WP_Typography_Setup.
	 *
	 * @param string        $basename The plugin slug.
	 * @param WP_Typography $plugin   The plugin object.
	 */
	public function __construct( $basename, WP_Typography $plugin ) {
		$this->local_plugin_path = $basename;
		$this->plugin_path       = plugin_dir_path( __DIR__ ) . basename( $this->local_plugin_path );
		$this->version           = $plugin->get_version();
		$this->plugin            = $plugin;

		// Store cache key names.
		$this->cache_key_names['hyphenate_languages'] = 'typo_hyphenate_languages_' . $plugin->get_version_hash();
		$this->cache_key_names['diacritic_languages'] = 'typo_diacritic_languages_' . $plugin->get_version_hash();
	}

	/**
	 * Set up the various hooks for the admin side.
	 */
	public function run() {
		// Initialize admin form.
		$this->admin_resource_links = $this->initialize_resource_links();
		$this->admin_help_pages     = $this->initialize_help_pages();
		$this->admin_form_tabs      = $this->initialize_form_tabs();
		$this->admin_form_sections  = $this->initialize_form_sections();
		$this->admin_form_controls  = $this->initialize_controls();

		// Add action hooks.
		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'admin_init', [ $this, 'register_the_settings' ] );

		// Add filter hooks.
		add_filter( 'plugin_action_links_' . $this->local_plugin_path, [ $this, 'plugin_action_links' ] );
	}

	/**
	 * Return the list of form controls that double as the default settings.
	 *
	 * @return array {
	 *      @type array $id {
	 *          Contents and meta data for the control $id.
	 *
	 *          @type string $section Section ID. Required.
	 *          @type string $fieldset Fieldset ID. Optional.
	 *          @type string $label Label content with the position of the control marked as %1$s. Optional.
	 *          @type string $help_text Help text. Optional.
	 *          @type string $control The HTML control element. Required.
	 *          @type string $input_type The input type for 'input' controls. Optional.
	 *          @type array  $option_values Array in the form ($value => $text). Optional (i.e. only for 'select' controls).
	 *          @type string $default The default value. Required, but may be an empty string.
	 *      }
	 * }
	 */
	public function get_default_settings() {
		return Arrays::array_map_assoc( function( $control_id, $control ) {
			return [ $control_id, $control->get_default() ];
		}, $this->admin_form_controls );
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
			__( 'Plugin Home', 'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/',
			__( 'FAQs',        'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/frequently-asked-questions/',
			__( 'Changelog',   'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/changes/',
			__( 'License',     'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/license/',
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
				'heading' => __( 'Introduction', 'wp-typography' ),
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
	 *      @type array $id {
	 *            The tab ID.
	 *
	 *            @type string $heading     Tab heading (translated).
	 *            @type string $description Tab description (translated).
	 *      }
	 * }
	 */
	private function initialize_form_tabs() {

		// Sections will be displayed in the order included.
		return [
			'general-scope'         => [
				'heading'       => __( 'General Scope', 'wp-typography' ),
				'description'   => __( 'By default, wp-Typography processes all post content and titles (but not the whole page). Certain HTML elements within your content can be exempted to prevent conflicts with your theme or other plugins.', 'wp-typography' ),
			],
			'hyphenation'           => [
				'heading'       => __( 'Hyphenation', 'wp-typography' ),
				'description'   => __( 'Hyphenation rules are based on pre-computed dictionaries, but can be fine tuned. Custom hyphenations always override the patterns from the dictionary.', 'wp-typography' ),
			],
			'character-replacement' => [
				'heading'       => __( 'Intelligent Character Replacement', 'wp-typography' ),
				'description'   => __( 'Modern keyboards are still based on the limited character range of typewriters. This section allows you to selectively replace typewriter characters with better alternatives.', 'wp-typography' ),
			],
			'space-control'         => [
				'heading'       => __( 'Space Control', 'wp-typography' ),
				'description'   => __( 'Take control of space. At least in your WordPress posts.', 'wp-typography' ),
			],
			'css-hooks'             => [
				'heading'       => __( 'CSS Hooks', 'wp-typography' ),
				'description'   => __( 'To help with styling your posts, some additional CSS classes can be added automatically.', 'wp-typography' ),
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
				'heading'       => __( 'Math & Numbers', 'wp-typography' ),
				'description'   => __( 'Not all number formattings are appropriate for all languages.', 'wp-typography' ),
				'tab_id'        => 'character-replacement',
			],
			'enable-wrapping'   => [
				'heading'       => __( 'Enable Wrapping', 'wp-typography' ),
				'description'   => __( 'Sometimes you want to enable certain long words to wrap to a new line, while at other times you want to prevent wrapping.', 'wp-typography' ),
				'tab_id'        => 'space-control',
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
		$controls = [
			new UI\Textarea( self::OPTION_GROUP, 'typo_ignore_tags', [
				'tab_id'        => 'general-scope',
				'short'         => __( 'Ignore HTML elements', 'wp-typography' ),
				'help_text'     => __( 'Separate tag names with spaces; do not include the <code>&lt;</code> or <code>&gt;</code>. The content of these HTML elements will not be processed.', 'wp-typography' ),
				'default'       => 'code head kbd object option pre samp script style textarea title var math',
			] ),
			new UI\Textarea( self::OPTION_GROUP, 'typo_ignore_classes', [
				'tab_id'        => 'general-scope',
				'short'         => __( 'Ignore CSS classes', 'wp-typography' ),
				'help_text'     => __( 'Separate class names with spaces. Elements with these classes will not be processed.', 'wp-typography' ),
				'default'       => 'vcard noTypo',
			] ),
			new UI\Textarea( self::OPTION_GROUP, 'typo_ignore_ids', [
				'tab_id'        => 'general-scope',
				'short'         => __( 'Ignore IDs', 'wp-typography' ),
				'help_text'     => __( 'Separate ID names with spaces. Elements with these IDs will not be processed.', 'wp-typography' ),
				'control'       => 'textarea',
				'default'       => '',
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_ignore_parser_errors', [
				'tab_id'        => 'general-scope',
				'short'         => __( 'Parser errors', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Ignore errors in parsed HTML.', 'wp-typography' ),
				'help_text'     => __( 'Unchecking will prevent processing completely if the HTML parser produces any errors for a given content part. You should only need to do this in case your site layout changes with wp-Typography enabled.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_enable_hyphenation', [
				'tab_id'        => 'hyphenation',
				'short'         => __( 'Hyphenation', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Enable hyphenation.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_hyphenate_languages', [
				'tab_id'        => 'hyphenation',
				/* translators: 1: language dropdown */
				'label'         => __( 'Language for hyphenation rules: %1$s', 'wp-typography' ),
				'option_values' => [], // Automatically detected and listed in __construct.
				'default'       => 'en-US',
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_hyphenate_headings', [
				'tab_id'        => 'hyphenation',
				'short'         => __( 'Special cases', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Hyphenate headings.', 'wp-typography' ),
				'help_text'     => __( 'Unchecking will disallow hyphenation of headings, even if allowed in the general scope.', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_hyphenate_title_case', [
				'tab_id'        => 'hyphenation',
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Allow hyphenation of words that begin with a capital letter.', 'wp-typography' ),
				'help_text'     => __( 'Uncheck to avoid hyphenation of proper nouns.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_hyphenate_compounds', [
				'tab_id'        => 'hyphenation',
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Allow hyphenation of the components of hyphenated compound words.', 'wp-typography' ),
				'help_text'     => __( 'Uncheck to disallow the hyphenation of the words making up a hyphenated compound (e.g. <code>editor-in-chief</code>).', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_hyphenate_caps', [
				'tab_id'        => 'hyphenation',
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Hyphenate words in ALL CAPS.', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_hyphenate_min_length', [
				'tab_id'        => 'hyphenation',
				'short'         => __( 'Character limits', 'wp-typography' ),
				/* translators: 1: number dropdown */
				'label'         => __( 'Do not hyphenate words with less than %1$s letters.', 'wp-typography' ),
				'option_values' => self::get_numeric_option_values( [ 4, 5, 6, 7, 8, 9, 10 ] ),
				'default'       => 5,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_hyphenate_min_before', [
				'tab_id'        => 'hyphenation',
				/* translators: 1: number dropdown */
				'label'         => __( 'Keep at least %1$s letters before hyphenation.', 'wp-typography' ),
				'option_values' => self::get_numeric_option_values( [ 2, 3, 4, 5 ] ),
				'default'       => 3,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_hyphenate_min_after', [
				'tab_id'        => 'hyphenation',
				/* translators: 1: number dropdown */
				'label'         => __( 'Keep at least %1$s letters after hyphenation.', 'wp-typography' ),
				'option_values' => self::get_numeric_option_values( [ 2, 3, 4, 5 ] ),
				'default'       => 2,
			] ),
			new UI\Textarea( self::OPTION_GROUP, 'typo_hyphenate_exceptions', [
				'tab_id'        => 'hyphenation',
				'short'         => __( 'Exception list', 'wp-typography' ),
				'help_text'     => __( 'Mark allowed hyphenations with "-"; separate words with spaces.', 'wp-typography' ),
				'attributes'    => [
					'rows' => '8',
				],
				'default'       => 'Mund-schenk',
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_hyphenate_clean_clipboard', [
				'tab_id'        => 'hyphenation',
				'short'         => __( 'Browser support', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Remove hyphenation when copying to clipboard', 'wp-typography' ),
				'help_text'     => __( 'To prevent legacy applications from displaying inappropriate hyphens, all soft hyphens and zero-width spaces are removed from the clipboard selection. Requires JavaScript.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_hyphenate_safari_font_workaround', [
				'tab_id'        => 'hyphenation',
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Add workaround for Safari hyphenation bug', 'wp-typography' ),
				'help_text'     => __( 'Safari displays weird ligature-like characters with some fonts (like Open Sans) when hyhpenation is enabled. Inserts <code>-webkit-font-feature-settings: "liga", "dlig";</code> as inline CSS workaround.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_characters', [
				'tab_id'        => 'character-replacement',
				'short'         => __( 'Intelligent character replacement', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Override WordPress\' automatic character handling with your preferences here.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_quotes', [
				'tab_id'        => 'character-replacement',
				'short'         => __( 'Smart quotes', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Transform straight quotes [ <code>\'</code> <code>"</code> ] to typographically correct characters as detailed below.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_smart_quotes_primary', [
				'tab_id'        => 'character-replacement',
				/* translators: 1: style dropdown */
				'label'         => __( 'Primary quotation style: Convert <code>"foo"</code> to %1$s.', 'wp-typography' ),
				'option_values' => [
					Quote_Style::DOUBLE_CURLED              => '&ldquo;foo&rdquo;',
					Quote_Style::DOUBLE_CURLED_REVERSED     => '&rdquo;foo&rdquo;',
					Quote_Style::DOUBLE_LOW_9               => '&bdquo;foo&rdquo;',
					Quote_Style::DOUBLE_LOW_9_REVERSED      => '&bdquo;foo&ldquo;',
					Quote_Style::SINGLE_CURLED              => '&lsquo;foo&rsquo;',
					Quote_Style::SINGLE_CURLED_REVERSED     => '&rsquo;foo&rsquo;',
					Quote_Style::SINGLE_LOW_9               => '&sbquo;foo&rsquo;',
					Quote_Style::SINGLE_LOW_9_REVERSED      => '&sbquo;foo&lsquo;',
					Quote_Style::DOUBLE_GUILLEMETS_FRENCH   => '&laquo;&nbsp;foo&nbsp;&raquo;',
					Quote_Style::DOUBLE_GUILLEMETS          => '&laquo;foo&raquo;',
					Quote_Style::DOUBLE_GUILLEMETS_REVERSED => '&raquo;foo&laquo;',
					Quote_Style::SINGLE_GUILLEMETS          => '&lsaquo;foo&rsaquo;',
					Quote_Style::SINGLE_GUILLEMETS_REVERSED => '&rsaquo;foo&lsaquo;',
					Quote_Style::CORNER_BRACKETS            => '&#x300c;foo&#x300d;',
					Quote_Style::WHITE_CORNER_BRACKETS      => '&#x300e;foo&#x300f;',
				],
				'default'       => Quote_Style::DOUBLE_CURLED,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_smart_quotes_secondary', [
				'tab_id'        => 'character-replacement',
				/* translators: 1: style dropdown */
				'label'         => __( "Secondary quotation style: Convert <code>'foo'</code> to %1\$s.", 'wp-typography' ),
				'option_values' => [
					Quote_Style::DOUBLE_CURLED              => '&ldquo;foo&rdquo;',
					Quote_Style::DOUBLE_CURLED_REVERSED     => '&rdquo;foo&rdquo;',
					Quote_Style::DOUBLE_LOW_9               => '&bdquo;foo&rdquo;',
					Quote_Style::DOUBLE_LOW_9_REVERSED      => '&bdquo;foo&ldquo;',
					Quote_Style::SINGLE_CURLED              => '&lsquo;foo&rsquo;',
					Quote_Style::SINGLE_CURLED_REVERSED     => '&rsquo;foo&rsquo;',
					Quote_Style::SINGLE_LOW_9               => '&sbquo;foo&rsquo;',
					Quote_Style::SINGLE_LOW_9_REVERSED      => '&sbquo;foo&lsquo;',
					Quote_Style::DOUBLE_GUILLEMETS_FRENCH   => '&laquo;&nbsp;foo&nbsp;&raquo;',
					Quote_Style::DOUBLE_GUILLEMETS          => '&laquo;foo&raquo;',
					Quote_Style::DOUBLE_GUILLEMETS_REVERSED => '&raquo;foo&laquo;',
					Quote_Style::SINGLE_GUILLEMETS          => '&lsaquo;foo&rsaquo;',
					Quote_Style::SINGLE_GUILLEMETS_REVERSED => '&rsaquo;foo&lsaquo;',
					Quote_Style::CORNER_BRACKETS            => '&#x300c;foo&#x300d;',
					Quote_Style::WHITE_CORNER_BRACKETS      => '&#x300e;foo&#x300f;',
				],
				'default'       => Quote_Style::SINGLE_CURLED,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_dashes', [
				'tab_id'        => 'character-replacement',
				'short'         => __( 'Smart dashes', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Transform minus-hyphens [ <code>-</code> <code>--</code> ] to contextually appropriate dashes, minus signs, and hyphens [ <code>&ndash;</code> <code>&mdash;</code> <code>&#8722;</code> <code>&#8208;</code> ].', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_smart_dashes_style', [
				'tab_id'        => 'character-replacement',
				/* translators: 1: style dropdown */
				'label'         => __( 'Use the %1$s style for dashes.', 'wp-typography' ),
				'help_text'     => __( 'In the US, the em dash&#8202;&mdash;&#8202;with no or very little spacing&#8202;&mdash;&#8202;is used for parenthetical expressions, while internationally, the en dash &ndash; with spaces &ndash; is more prevalent.', 'wp-typography' ),
				'option_values' => [
					Dash_Style::TRADITIONAL_US => __( 'Traditional US', 'wp-typography' ),
					Dash_Style::INTERNATIONAL  => __( 'International', 'wp-typography' ),
				],
				'default'       => Dash_Style::TRADITIONAL_US,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_diacritics', [
				'tab_id'        => 'character-replacement',
				'short'         => __( 'Smart diacritics', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Force diacritics where appropriate.', 'wp-typography' ),
				'help_text'     => __( 'For example, <code>creme brulee</code> becomes <code>crème brûlée</code>.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_diacritic_languages', [
				'tab_id'        => 'character-replacement',
				/* translators: 1: language dropdown */
				'label'         => __( 'Language for diacritic replacements: %1$s', 'wp-typography' ),
				'help_text'     => __( 'Language definitions will purposefully not process words that have alternate meaning without diacritics like <code>resume</code>/<code>résumé</code>, <code>divorce</code>/<code>divorcé</code>, and <code>expose</code>/<code>exposé</code>.', 'wp-typography' ),
				'option_values' => [], // Automatically detected and listed in __construct.
				'default'       => 'en-US',
			] ),
			new UI\Textarea( self::OPTION_GROUP, 'typo_diacritic_custom_replacements', [
				'tab_id'        => 'character-replacement',
				'label'         => __( 'Custom diacritic word replacements:', 'wp-typography' ),
				'help_text'     => __( 'Must be formatted <code>"word to replace"=>"replacement word",</code>. The entries are case-sensitive.', 'wp-typography' ),
				'attributes'    => [
					'rows' => '8',
				],
				'default'       => '"cooperate"=>"coöperate", "Cooperate"=>"Coöperate", "cooperation"=>"coöperation", "Cooperation"=>"Coöperation", "cooperative"=>"coöperative", "Cooperative"=>"Coöperative", "coordinate"=>"coördinate", "Coordinate"=>"Coördinate", "coordinated"=>"coördinated", "Coordinated"=>"Coördinated", "coordinating"=>"coördinating", "Coordinating"=>"Coördinating", "coordination"=>"coördination", "Coordination"=>"Coördination", "coordinator"=>"coördinator", "Coordinator"=>"Coördinator", "coordinators"=>"coördinators", "Coordinators"=>"Coördinators", "continuum"=>"continuüm", "Continuum"=>"Continuüm", "debacle"=>"débâcle", "Debacle"=>"Débâcle", "elite"=>"élite", "Elite"=>"Élite",',
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_ellipses', [
				'tab_id'        => 'character-replacement',
				'short'         => __( 'Ellipses', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Transform three periods [ <code>...</code> ] to  ellipses [ <code>&hellip;</code> ].', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_marks', [
				'tab_id'        => 'character-replacement',
				'short'         => __( 'Registration marks', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Transform registration marks [ <code>(c)</code> <code>(r)</code> <code>(tm)</code> <code>(sm)</code> <code>(p)</code> ] to  proper characters [ <code>©</code> <code>®</code> <code>™</code> <code>℠</code> <code>℗</code> ].', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_math', [
				'tab_id'        => 'character-replacement',
				'section'       => 'math-replacements',
				'short'         => __( 'Math symbols', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Transform exponents [ <code>3^2</code> ] to pretty exponents [ <code>3<sup>2</sup></code> ] and math symbols [ <code>(2x6)/3=4</code> ] to correct symbols [ <code>(2&#215;6)&#247;3=4</code> ].', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_fractions', [
				'tab_id'        => 'character-replacement',
				'section'       => 'math-replacements',
				'short'         => __( 'Fractions', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Transform fractions [ <code>1/2</code> ] to  pretty fractions [ <code><sup>1</sup>&#8260;<sub>2</sub></code> ].', 'wp-typography' ),
				'help_text'     => __( 'Warning: If you use a font (like Lucida Grande) that does not have a fraction-slash character, this may cause a missing line between the numerator and denominator.', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_smart_ordinals', [
				'tab_id'        => 'character-replacement',
				'section'       => 'math-replacements',
				'short'         => __( 'Ordinal numbers', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Transform ordinal suffixes [ <code>1st</code> ] to  pretty ordinals [ <code>1<sup>st</sup></code> ].', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_single_character_word_spacing', [
				'tab_id'        => 'space-control',
				'short'         => __( 'Single character words', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Prevent single character words from residing at the end of a line of text (unless it is a widow).', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_dash_spacing', [
				'tab_id'        => 'space-control',
				'short'         => __( 'Dashes', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Force thin spaces between em &amp; en dashes and adjoining words.', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_fraction_spacing', [
				'tab_id'        => 'space-control',
				'short'         => __( 'Fractions', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Keep integers with adjoining fractions.', 'wp-typography' ),
				'help_text'     => __( 'Examples: <code>1 1/2</code> or <code>1 <sup>1</sup>&#8260;<sub>2</sub></code>.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_space_collapse', [
				'tab_id'        => 'space-control',
				'short'         => __( 'Adjacent spacing', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Collapse adjacent spacing to a single character.', 'wp-typography' ),
				'help_text'     => __( 'Normal HTML processing collapses basic spaces. This option will additionally collapse no-break spaces, zero-width spaces, figure spaces, etc.', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_french_punctuation_spacing', [
				'tab_id'        => 'space-control',
				'short'         => __( 'French punctuation', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Apply French punctuation rules.', 'wp-typography' ),
				'help_text'     => __( 'This option adds a thin non-breakable space before <code>?!:;</code>.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_numbered_abbreviations_spacing', [
				'tab_id'        => 'space-control',
				'short'         => __( 'Numbered abbreviations', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Keep abbreviations containing numbers together.', 'wp-typography' ),
				'help_text'     => __( 'Examples: <code>ISO 9001</code> or <code>E 100</code>.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_unit_spacing', [
				'tab_id'        => 'space-control',
				'short'         => __( 'Values &amp; Units', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Keep values and units together.', 'wp-typography' ),
				'help_text'     => __( 'Examples: <code>1 in.</code> or <code>10 m<sup>2</sup></code>.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Textarea( self::OPTION_GROUP, 'typo_units', [
				'tab_id'        => 'space-control',
				'label'         => __( 'Additional unit names:', 'wp-typography' ),
				'help_text'     => __( 'Separate unit names with spaces. We already look for a large list; fill in any holes here.', 'wp-typography' ),
				'default'       => 'hectare fortnight',
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_wrap_hyphens', [
				'tab_id'        => 'space-control',
				'section'       => 'enable-wrapping',
				'short'         => __( 'Hyphens', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Enable wrapping after hard hyphens.', 'wp-typography' ),
				'help_text'     => __( 'Adds zero-width spaces after hard hyphens (like in &ldquo;zero-width&rdquo;).', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_wrap_emails', [
				'tab_id'        => 'space-control',
				'section'       => 'enable-wrapping',
				'short'         => __( 'Email addresses', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Enable wrapping of long email addresses.', 'wp-typography' ),
				'help_text'     => __( 'Adds zero-width spaces throughout the email address.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_wrap_urls', [
				'tab_id'        => 'space-control',
				'section'       => 'enable-wrapping',
				'short'         => __( 'URLs', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Enable wrapping of long URLs.', 'wp-typography' ),
				'help_text'     => __( 'Adds zero-width spaces throughout the URL.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 0,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_wrap_min_after', [
				'tab_id'        => 'space-control',
				'section'       => 'enable-wrapping',
				/* translators: 1: number dropdown */
				'label'         => __( 'Keep at least the last %1$s characters of a URL together.', 'wp-typography' ),
				'option_values' => self::get_numeric_option_values( [ 3, 4, 5, 6, 7, 8, 9, 10 ] ),
				'default'       => 3,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_prevent_widows', [
				'tab_id'        => 'space-control',
				'section'       => 'enable-wrapping',
				'short'         => __( 'Widows', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Prevent widows.', 'wp-typography' ),
				'help_text'     => __( 'Widows are the last word in a block of text that wraps to its own line.', 'wp-typography' ),
				'help_inline'   => true,
				'default'       => 1,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_widow_min_length', [
				'tab_id'        => 'space-control',
				'section'       => 'enable-wrapping',
				/* translators: 1: number dropdown */
				'label'         => __( 'Only protect widows with %1$s or fewer letters.', 'wp-typography' ),
				'option_values' => self::get_numeric_option_values( [ 4, 5, 6, 7, 8, 9, 10, 100 ] ),
				'default'       => 5,
			] ),
			new UI\Select( self::OPTION_GROUP, 'typo_widow_max_pull', [
				'tab_id'        => 'space-control',
				'section'       => 'enable-wrapping',
				/* translators: 1: number dropdown */
				'label'         => __( 'Pull at most %1$s letters from the previous line to keep the widow company.', 'wp-typography' ),
				'option_values' => self::get_numeric_option_values( [ 4, 5, 6, 7, 8, 9, 10, 100 ] ),
				'default'       => 5,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_style_amps', [
				'tab_id'        => 'css-hooks',
				'short'         => __( 'Ampersands', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Wrap ampersands [ <code>&amp;</code> ] with <code>&lt;span class="amp"&gt;</code>.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_style_caps', [
				'tab_id'        => 'css-hooks',
				'short'         => __( 'Caps', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Wrap acronyms (all capitals) with <code>&lt;span class="caps"&gt;</code>.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_style_numbers', [
				'tab_id'        => 'css-hooks',
				'short'         => __( 'Numbers', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Wrap digits [ <code>0123456789</code> ] with <code>&lt;span class="numbers"&gt;</code>.', 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_style_hanging_punctuation', [
				'tab_id'        => 'css-hooks',
				'short'         => __( 'Hanging punctuation', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Wrap small punctuation marks.', 'wp-typography' ),
				'help_text'     => __( "The amount of push/pull should be adjusted for your selected font in the stylesheet. <br />Single quote-like marks [ <code>&#8218;&lsquo;&apos;&prime;'</code> ] are wrapped with <code>&lt;span class=\"pull-single\"&gt;</code>. <br />Double quote-like marks [ <code>&#8222;&ldquo;&Prime;\"</code> ] are wrapped with <code>&lt;span class=\"pull-double\"&gt;</code>. <br/>For punctuation marks that do not begin a block of text, a corresponding empty <code>&lt;span class=\"push-&hellip;\"&gt;</code> ensures proper alignment within the line.", 'wp-typography' ),
				'default'       => 0,
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_style_initial_quotes', [
				'tab_id'        => 'css-hooks',
				'short'         => __( 'Initial quotes', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Wrap initial quotes.', 'wp-typography' ),
				'help_text'     => __( 'Matches quotemarks at the beginning of blocks of text, not all opening quotemarks. <br />Single quotes [ <code>&lsquo;</code> <code>&#8218;</code> ] are wrapped with <code>&lt;span class="quo"&gt;</code>. <br />Double quotes [ <code>&ldquo;</code> <code>&#8222;</code> ] are wrapped with <code>&lt;span class="dquo"&gt;</code>. <br />Guillemets [ <code>&laquo;</code> <code>&raquo;</code> ] are wrapped with <code>&lt;span class="dquo"&gt;</code>.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Textarea( self::OPTION_GROUP, 'typo_initial_quote_tags', [
				'tab_id'        => 'css-hooks',
				'label'         => __( 'Limit styling of initial quotes to these <strong>HTML elements</strong>:', 'wp-typography' ),
				'help_text'     => __( 'Separate tag names with spaces; do not include the <code>&lt;</code> or <code>&gt;</code>.', 'wp-typography' ),
				'default'       => 'p h1 h2 h3 h4 h5 h6 blockquote li dd dt',
			] ),
			new UI\Checkbox_Input( self::OPTION_GROUP, 'typo_style_css_include', [
				'tab_id'        => 'css-hooks',
				'short'         => __( 'Styles', 'wp-typography' ),
				/* translators: 1: checkbox HTML */
				'label'         => __( '%1$s Include styling for CSS hooks.', 'wp-typography' ),
				'help_text'     => __( 'Attempts to inject the CSS specified below.  If you are familiar with CSS, it is recommended you not use this option, and maintain all styles in your main stylesheet.', 'wp-typography' ),
				'default'       => 1,
			] ),
			new UI\Textarea( self::OPTION_GROUP, 'typo_style_css', [
				'tab_id'        => 'css-hooks',
				'label'         => __( 'Styling for CSS hooks:', 'wp-typography' ),
				'help_text'     => __( 'This will only be applied if explicitly selected with the preceding option.', 'wp-typography' ),
				'attributes'    => [
					'rows' => '10',
				],
				'default'       => file_get_contents( dirname( __DIR__ ) . '/admin/css/default-styles.css' ),
			] ),
		];

		// Add ID as index.
		$controls = Arrays::array_map_assoc( function( $key, $control ) {
			return [ $control->get_id(), $control ];
		}, $controls );

		// Group controls.
		$controls['typo_enable_hyphenation']->add_grouped_control( $controls['typo_hyphenate_languages'] );

		$controls['typo_hyphenate_headings']->add_grouped_control( $controls['typo_hyphenate_title_case'] );
		$controls['typo_hyphenate_headings']->add_grouped_control( $controls['typo_hyphenate_compounds'] );
		$controls['typo_hyphenate_headings']->add_grouped_control( $controls['typo_hyphenate_caps'] );

		$controls['typo_hyphenate_min_length']->add_grouped_control( $controls['typo_hyphenate_min_before'] );
		$controls['typo_hyphenate_min_length']->add_grouped_control( $controls['typo_hyphenate_min_after'] );

		$controls['typo_hyphenate_clean_clipboard']->add_grouped_control( $controls['typo_hyphenate_safari_font_workaround'] );

		$controls['typo_smart_quotes']->add_grouped_control( $controls['typo_smart_quotes_primary'] );
		$controls['typo_smart_quotes']->add_grouped_control( $controls['typo_smart_quotes_secondary'] );

		$controls['typo_smart_dashes']->add_grouped_control( $controls['typo_smart_dashes_style'] );

		$controls['typo_smart_diacritics']->add_grouped_control( $controls['typo_diacritic_languages'] );
		$controls['typo_smart_diacritics']->add_grouped_control( $controls['typo_diacritic_custom_replacements'] );

		$controls['typo_unit_spacing']->add_grouped_control( $controls['typo_units'] );

		$controls['typo_wrap_urls']->add_grouped_control( $controls['typo_wrap_min_after'] );

		$controls['typo_prevent_widows']->add_grouped_control( $controls['typo_widow_min_length'] );
		$controls['typo_prevent_widows']->add_grouped_control( $controls['typo_widow_max_pull'] );

		$controls['typo_style_initial_quotes']->add_grouped_control( $controls['typo_initial_quote_tags'] );

		$controls['typo_style_css_include']->add_grouped_control( $controls['typo_style_css'] );

		return $controls;
	}

	/**
	 * Register admin settings.
	 */
	public function register_the_settings() {
		foreach ( $this->admin_form_controls as $control_id => $control ) {
			// Register setting.
			$control->register();

			// Prevent spurious saves.
			add_filter( 'pre_update_option_' . $control_id , [ $this, 'filter_update_option' ], 10, 2 );
		}

		foreach ( $this->admin_form_tabs as $tab_id => $tab ) {
			register_setting( self::OPTION_GROUP . $tab_id, 'typo_restore_defaults', [ $this, 'sanitize_restore_defaults' ] );
			register_setting( self::OPTION_GROUP . $tab_id, 'typo_clear_cache',      [ $this, 'sanitize_clear_cache' ] );
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
		if ( ! empty( $_POST['typo_restore_defaults'] ) || ! empty( $_POST['typo_clear_cache'] ) ) { // WPCS: CSRF ok. Input var okay.
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
	private function get_active_settings_tab() {
		// Check active tab.
		$all_tabs = array_keys( $this->admin_form_tabs ); // PHP 5.3 workaround.

		return ! empty( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : $all_tabs[0]; // WPCS: CSRF ok. Input var okay.
	}

	/**
	 * Return numeric values as in associative form $value => $value.
	 *
	 * @param array $values Option values.
	 *
	 * @return array
	 */
	private static function get_numeric_option_values( array $values ) {
		return array_combine( $values, $values );
	}

	/**
	 * Add proper notification for Restore Defaults button.
	 *
	 * @param mixed $input Ignored.
	 *
	 * @return mixed
	 */
	public function sanitize_restore_defaults( $input ) {
		return $this->trigger_admin_notice( 'typo_restore_defaults', 'defaults-restored', __( 'Settings reset to default values.', 'wp-typography' ), 'updated', $input );
	}

	/**
	 * Add proper notification for Clear Cache button.
	 *
	 * @param mixed $input Ignored.
	 *
	 * @return mixed
	 */
	public function sanitize_clear_cache( $input ) {
		return $this->trigger_admin_notice( 'typo_clear_cache', 'cache-cleared', __( 'Cached post content cleared.', 'wp-typography' ), 'notice-info', $input );
	}

	/**
	 * Use sanitization callback to trigger an admin notice.
	 *
	 * @param  string $setting_name The setting used to trigger the notice.
	 * @param  string $notice_id    HTML ID attribute for the notice.
	 * @param  string $message      Translated message string.
	 * @param  string $notice_level 'updated', 'notice-info', etc.
	 * @param  mixed  $input        Passed back.
	 *
	 * @return mixed The $input parameter.
	 */
	private function trigger_admin_notice( $setting_name, $notice_id, $message, $notice_level, $input ) {
		if ( ! empty( $_POST[ $setting_name ] ) ) { // WPCS: CSRF ok. Input var okay.
			add_settings_error( self::OPTION_GROUP . $this->get_active_settings_tab(), $notice_id, $message, $notice_level );
		}

		return $input;
	}

	/**
	 * Add an options page for the plugin settings.
	 */
	public function add_options_page() {
		$page = add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', strtolower( $this->plugin_name ), [ $this, 'get_admin_page_content' ] );

		// General sections for each tab.
		foreach ( $this->admin_form_tabs as $tab_id => $tab ) {
			add_settings_section( $tab_id, '', [ $this, 'print_settings_section' ], self::OPTION_GROUP . $tab_id );
		}

		// Additional sections.
		foreach ( $this->admin_form_sections as $section_id => $section ) {
			add_settings_section( $section_id, $section['heading'], [ $this, 'print_settings_section' ], self::OPTION_GROUP . $section['tab_id'] );
		}

		// Add help tab.
		add_action( 'load-' . $page, [ $this, 'add_context_help' ] );

		// Using registered $page handle to hook stylesheet loading.
		add_action( 'admin_print_styles-' . $page, [ $this, 'print_admin_styles' ] );
	}

	/**
	 * Add context-sensitive help to the settings page.
	 */
	public function add_context_help() {
		$screen = get_current_screen();

		foreach ( $this->admin_help_pages as $help_id => $help ) {
			$screen->add_help_tab( [
				'id'      => $help_id,
				'title'   => $help['heading'],
				'content' => $help['content'],
			] );
		}

		// Create sidebar.
		$sidebar = '<p>' . __( 'Useful resources:', 'wp-typography' ) . '</p><ul>';
		foreach ( $this->admin_resource_links as $anchor => $url ) {
			$sidebar .= '<li><a href="' . esc_url( $url ) . '">' . __( $anchor, 'wp-typography' ) . '</a></li>';  // @codingStandardsIgnoreLine.
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
			echo '<p>' . esc_html( $this->admin_form_tabs[ $section_id ]['description'] ) . '</p>';
		} elseif ( ! empty( $this->admin_form_sections[ $section_id ]['description'] ) ) {
			echo '<p>' . esc_html( $this->admin_form_sections[ $section_id ]['description'] ) . '</p>';
		}
	}

	/**
	 * Enqueue stylesheet for options page.
	 */
	public function print_admin_styles() {
		wp_enqueue_style( 'wp-typography-settings', plugins_url( 'admin/css/settings.css', $this->local_plugin_path ), [], $this->version, 'all' );
	}

	/**
	 * Add a 'Settings' link to the wp-Typography entry in the plugins list.
	 *
	 * @param array $links An array of links.
	 * @return array An array of links.
	 */
	public function plugin_action_links( $links ) {
		$adminurl = trailingslashit( admin_url() );

		// Add link "Settings" to the plugin in /wp-admin/plugins.php.
		$settings_link = '<a href="' . $adminurl . 'options-general.php?page=' . strtolower( $this->plugin_name ) . '">' . __( 'Settings' , 'wp-typography' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Display the plugin options page.
	 */
	public function get_admin_page_content() {
		$found = false;

		// Try to load hyphenation language list from cache.
		$languages = $this->plugin->get_cache( $this->cache_key_names['hyphenate_languages'], $found );

		// Dynamically generate the list of hyphenation language patterns.
		if ( false === $found ) {
			$languages = self::translate_languages( PHP_Typography::get_hyphenation_languages() );

			/**
			 * Filter the caching duration for the language plugin lists.
			 *
			 * @since 3.2.0
			 *
			 * @param number $duration The duration in seconds. Defaults to 1 week.
			 * @param string $list     The name language plugin list.
			 */
			$duration = apply_filters( 'typo_language_list_caching_duration', WEEK_IN_SECONDS, 'hyphenate_languages' );

			// Cache translated hyphenation languages.
			$this->plugin->set_cache( $this->cache_key_names['hyphenate_languages'], $languages, $duration );
		}
		$this->admin_form_controls['typo_hyphenate_languages']->set_options( $languages );

		// Try to load diacritics language list from cache.
		$languages = $this->plugin->get_cache( $this->cache_key_names['diacritic_languages'], $found );

		// Dynamically generate the list of diacritics replacement languages.
		if ( false === $found ) {
			$languages = self::translate_languages( PHP_Typography::get_diacritic_languages() );

			/** This filter is documented in class-wp-typography-admin.php */
			$duration = apply_filters( 'typo_language_list_caching_duration', WEEK_IN_SECONDS, 'diacritic_languages' );

			// Cache translated diactrics languages.
			$this->plugin->set_cache( $this->cache_key_names['diacritic_languages'], $languages, $duration );
		}
		$this->admin_form_controls['typo_diacritic_languages']->set_options( $languages );

		// Load the settings page HTML.
		include_once dirname( __DIR__ ) . '/admin/partials/settings.php';
	}

	/**
	 * Translate language list.
	 *
	 * @param array $languages An array in the form [ LANGUAGE_CODE => LANGUAGE ].
	 *
	 * @return array The same array with the language name translated.
	 */
	private static function translate_languages( array $languages ) {
		array_walk( $languages, function( &$lang, $code ) {
			$lang = _x( $lang, 'language name', 'wp-typography' );  // @codingStandardsIgnoreLine.
		} );

		return $languages;
	}
}
