<?php

/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2014-2015 Peter Putzer.
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
	 * The user-visible name of the plugin.
	 *
	 * // FIXME Translate?
	 * @var string $plugin_name
	 */
	private $plugin_name = 'wp-Typography';

	/**
	 * The group name used for registering the plugin options.
	 */
	private $option_group = 'typo_options';

	/**
	 * The result of plugin_basename() for the main plugin file (relative from plugins folder).
	 */
	private $local_plugin_path;

	/**
	 * The absolute path to top-level directory for the plugin.
	 */
	private $plugin_path;

	/**
	 * The full version string of the plugin.
	 */
	private $version;

	/**
	 * Links to add the settings page.
	 * @var array $adminResourceLinks An array in the form of 'anchor text' => 'URL'.
	 */
	private $admin_resource_links;

	/**
	 * Section IDs and headings for the settings page.
	 *
	 * Sections will be displayed in the order included.
	 *
	 * @var array $adminFormSections An array in the form of 'id' => 'heading'.
	 */
	private $admin_form_sections;

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
	private $admin_form_section_fieldsets;

	/**
	 * The form controls on the settings page.
	 *
	 * @var array $admin_form_controls {
	 * 		@type array $id {
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
	 * 		}
	 * }
	 */
	private $admin_form_controls = array();

	/**
	 * An array of transient names.
	 *
	 * @var array
	 */
	private $transient_names = array();

	/**
	 * The plugin instance used for setting transients.
	 *
	 * @var callable
	 */
	private $plugin;

	/**
	 * Create a new instace of WP_Typography_Setup.
	 *
	 * @param string $slug
	 * @param WP_Typography $plugin
	 */
	function __construct( $basename, WP_Typography $plugin ) {
		$this->local_plugin_path = $basename;
		$this->plugin_path       = plugin_dir_path( __DIR__ ) . basename( $this->local_plugin_path );
		$this->version			 = $plugin->get_version();
		$this->transient_names['hyphenate_languages'] = 'typo_hyphenate_languages_' . $plugin->get_version_hash();
		$this->transient_names['diacritic_languages'] = 'typo_diacritic_languages_' . $plugin->get_version_hash();
		$this->plugin = $plugin;

		// initialize admin form
		$this->admin_resource_links         = $this->initialize_resource_links();
		$this->admin_form_sections          = $this->initialize_form_sections();
		$this->admin_form_section_fieldsets = $this->initialize_fieldsets();
		$this->admin_form_controls          = $this->initialize_controls();
	}

	/**
	 * Set up the various hooks for the admin side.
	 */
	public function run() {
		// actions
		add_action( 'admin_menu', array( $this, 'add_options_page') );
		add_action( 'admin_init', array( $this, 'register_the_settings') );

		// filters
		add_filter( 'plugin_action_links_' . $this->local_plugin_path, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Return the list of form controls that double as the default settings.
	 *
	 * @return array {
	 * 		@type array $id {
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
	 * 		}
	 * }
	 */
	public function get_default_settings() {
		return $this->admin_form_controls;
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 */
	function initialize_resource_links() {
		return array(
			/*
				 'anchor text' => 'URL', 		// REQUIRED
			*/
			__( 'Plugin Home', 'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/',
			__( 'FAQs',        'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/frequently-asked-questions/',
			__( 'Changelog',   'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/changes/',
			__( 'License',     'wp-typography' ) => 'https://code.mundschenk.at/wp-typography/license/',
		);
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 */
	function initialize_form_sections() {
		// sections will be displayed in the order included
		return array(
			/*
			'id' 					=> string heading,		// REQUIRED
			*/
			'general-scope' 		=> __( 'General Scope',                     'wp-typography' ),
			'hyphenation' 			=> __( 'Hyphenation',                       'wp-typography' ),
			'character-replacement'	=> __( 'Intelligent Character Replacement', 'wp-typography' ),
			'space-control' 		=> __( 'Space Control',                     'wp-typography' ),
			'css-hooks' 			=> __( 'Add CSS Hooks',                     'wp-typography' ),
		);
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 */
	function initialize_fieldsets() {
		// fieldsets will be displayed in the order included
		return array(
			/*
			'id' => array(
			 	'heading' 	   => string Fieldset Name,     // REQUIRED
			 	'sectionID'    => string Parent Section ID, // REQUIRED
			),
			*/
			'smart-quotes'     => array(
				'heading'      => __( 'Quotemarks', 'wp-typography' ),
				'sectionID'    => 'character-replacement',
			),
			'dashes'           => array(
				'heading'      => __( 'Dashes', 'wp-typography' ),
				'sectionID'    => 'character-replacement',
			),
			'diacritics'       => array(
				'heading'      => __( 'Diacritics', 'wp-typography' ),
				'sectionID'    => 'diacritics',
			),
			'values-and-units' => array(
				'heading'      => __( 'Values &amp; Units', 'wp-typography' ),
				'sectionID'    => 'space-control',
			),
			'enable-wrapping'  => array(
				'heading'      => __( 'Enable Wrapping', 'wp-typography' ),
				'sectionID'    => 'space-control',
			),
			'widows' => array(
				'heading'      => __( 'Widows', 'wp-typography' ),
				'sectionID'    => 'space-control',
			),
		);
	}

	/**
	 * Initialize displayable strings for the plugin settings page.
	 */
	function initialize_controls() {

		return array(
			/*
			 "id" => array(
			 	'section' 		=> string Section ID, 		// REQUIRED
			 	'fieldset' 		=> string Fieldset ID,		// OPTIONAL
			 	'label'     	=> string Label Content,	// OPTIONAL
			 	'help_text' 	=> string Help Text,		// OPTIONAL
			 	'control' 		=> string Control,			// REQUIRED
			 	'input_type' 	=> string Control Type,		// OPTIONAL
			 	'option_values'	=> array(value=>text, ... )	// OPTIONAL, only for controls of type 'select'
			 	'default' 		=> string Default Value,	// REQUIRED (although it may be an empty string)
			 ),
			*/
			'typo_ignore_tags' => array(
				'section'		=> 'general-scope',
				'label' 		=> __( "Do not process the content of these <strong>HTML elements</strong>:", 'wp-typography' ),
				'help_text' 	=> __( "Separate tag names with spaces; do not include the <samp>&lt;</samp> or <samp>&gt;</samp>.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> "code head kbd object option pre samp script style textarea title var math",
			),
			'typo_ignore_classes' => array(
				'section' 		=> 'general-scope',
				'label' 		=> __( "Do not process elements of <strong>class</strong>:", 'wp-typography' ),
				'help_text' 	=> __( "Separate class names with spaces.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> "vcard noTypo",
			),
			'typo_ignore_ids' => array(
				'section' 		=> 'general-scope',
				'label' 		=> __( "Do not process elements of <strong>ID</strong>:", 'wp-typography' ),
				'help_text' 	=> __( "Separate ID names with spaces.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> "",
			),
			'typo_disable_caching' => array(
				'section' 		=> 'general-scope',
				'label' 		=> __( "%1\$s Disable caching", 'wp-typography' ),
				'help_text' 	=> __( "Prevents processed text from being cached (normally only needed for debugging purposes).", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_enable_hyphenation' => array(
				'section' 		=> 'hyphenation',
				'label' 		=> __( "%1\$s Enable hyphenation.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_hyphenate_languages' => array(
				'section'		=> 'hyphenation',
				'label' 		=> __( "Language for hyphenation rules: %1\$s", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(), // automatically detected and listed in __construct
				'default' 		=> "en-US",
			),
			'typo_hyphenate_headings' => array(
				'section' 		=> 'hyphenation',
				'label' 		=> __( "%1\$s Hyphenate headings.", 'wp-typography' ),
				'help_text' 	=> __( "Unchecking will disallow hyphenation of headings, even if allowed in the general scope.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_hyphenate_title_case' => array(
				'section' 		=> 'hyphenation',
				'label' 		=> __( "%1\$s Allow hyphenation of words that begin with a capital letter.", 'wp-typography' ),
				'help_text' 	=> __( "Uncheck to avoid hyphenation of proper nouns.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_hyphenate_caps' => array(
				'section' 		=> 'hyphenation',
				'label' 		=> __( "%1\$s Hyphenate words in ALL CAPS.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_hyphenate_min_length' => array(
				'section'		=> 'hyphenation',
				'label' 		=> __( "Do not hyphenate words with less than %1\$s letters.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10),
				'default' 		=> 5,
			),
			'typo_hyphenate_min_before' => array(
				'section'		=> 'hyphenation',
				'label' 		=> __( "Keep at least %1\$s letters before hyphenation.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(2=>2,3=>3,4=>4,5=>5),
				'default' 		=> 3,
			),
			'typo_hyphenate_min_after' => array(
				'section'		=> 'hyphenation',
				'label' 		=> __( "Keep at least %1\$s letters after hyphenation.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(2=>2,3=>3,4=>4,5=>5),
				'default' 		=> 2,
			),
			'typo_hyphenate_safari_font_workaround' => array(
				'section' 		=> 'hyphenation',
				'label' 		=> __( '%1$s Add workaround for Safari hyphenation bug', 'wp-typography' ),
				'help_text' 	=> __( 'Safari displays weird ligature-like characters with some fonts (like Open Sans) when hyhpenation is enabled. Inserts <code>-webkit-font-feature-settings: "liga", "dlig";</code> as inline CSS workaround.', 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_hyphenate_exceptions' => array(
				'section' 		=> 'hyphenation',
				'label' 		=> __( "Exception List:", 'wp-typography' ),
				'help_text' 	=> __( "Mark allowed hyphenations with \"-\"; separate words with spaces.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> "Mund-schenk",
			),
			'typo_smart_characters' => array(
				'section'		=> 'character-replacement',
				'label' 		=> __( "%1\$s Override WordPress' automatic character handling with your preferences here.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_smart_quotes' => array(
				'section'		=> 'character-replacement',
				'fieldset' 		=> 'smart-quotes',
				'label' 		=> __( "%1\$s Transform straight quotes [ <samp>'</samp> <samp>\"</samp> ] to typographically correct characters as detailed below.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),

			'typo_smart_quotes_primary' => array(
				'section'		=> 'character-replacement',
				'fieldset' 		=> 'smart-quotes',
				'label' 		=> __( "Convert <samp>\"foo\"</samp> to: %1\$s", 'wp-typography' ),
				'help_text' 	=> __( "Primary quotation style.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(
					'doubleCurled'             => "&ldquo;foo&rdquo;",
					'doubleCurledReversed'     => "&rdquo;foo&rdquo;",
					'doubleLow9'               => "&bdquo;foo&rdquo;",
					'doubleLow9Reversed'       => "&bdquo;foo&ldquo;",
					'singleCurled'             => "&lsquo;foo&rsquo;",
					'singleCurledReversed'     => "&rsquo;foo&rsquo;",
					'singleLow9'               => "&sbquo;foo&rsquo;",
					'singleLow9Reversed'       => "&sbquo;foo&lsquo;",
					'doubleGuillemetsFrench'   => "&laquo;&nbsp;foo&nbsp;&raquo;",
					'doubleGuillemets'         => "&laquo;foo&raquo;",
					'doubleGuillemetsReversed' => "&raquo;foo&laquo;",
					'singleGuillemets'         => "&lsaquo;foo&rsaquo;",
					'singleGuillemetsReversed' => "&rsaquo;foo&lsaquo;",
					'cornerBrackets'           => "&#x300c;foo&#x300d;",
					'whiteCornerBracket'       => "&#x300e;foo&#x300f;",
				),
				'default' 		=> 'doubleCurled',
			),
			'typo_smart_quotes_secondary' => array(
				'section'		=> 'character-replacement',
				'fieldset' 		=> 'smart-quotes',
				'label' 		=> __( "Convert <samp>'foo'</samp> to: %1\$s", 'wp-typography' ),
				'help_text' 	=> __( "Secondary quotation style.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(
					'doubleCurled'             => "&ldquo;foo&rdquo;",
					'doubleCurledReversed'     => "&rdquo;foo&rdquo;",
					'doubleLow9'               => "&bdquo;foo&rdquo;",
					'doubleLow9Reversed'       => "&bdquo;foo&ldquo;",
					'singleCurled'             => "&lsquo;foo&rsquo;",
					'singleCurledReversed'     => "&rsquo;foo&rsquo;",
					'singleLow9'               => "&sbquo;foo&rsquo;",
					'singleLow9Reversed'       => "&sbquo;foo&lsquo;",
					'doubleGuillemetsFrench'   => "&laquo;&nbsp;foo&nbsp;&raquo;",
					'doubleGuillemets'         => "&laquo;foo&raquo;",
					'doubleGuillemetsReversed' => "&raquo;foo&laquo;",
					'singleGuillemets'         => "&lsaquo;foo&rsaquo;",
					'singleGuillemetsReversed' => "&rsaquo;foo&lsaquo;",
					'cornerBrackets'           => "&#x300c;foo&#x300d;",
					'whiteCornerBracket'       => "&#x300e;foo&#x300f;",
				),
				'default' 		=> 'singleCurled',
			),

			'typo_smart_dashes' => array(
				'section'		=> 'character-replacement',
				'fieldset' 		=> 'dashes',
				'label' 		=> __( "%1\$s Transform minus-hyphens [ <samp>-</samp> <samp>--</samp> ] to contextually appropriate dashes, minus signs, and hyphens [ <samp>&ndash;</samp> <samp>&mdash;</samp> <samp>&#8722;</samp> <samp>&#8208;</samp> ].", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_smart_dashes_style' => array(
				'section'		=> 'character-replacement',
				'fieldset' 		=> 'dashes',
				'label' 		=> __( "Use the %1\$s style for dashes. In the US, the em dash&#8202;&mdash;&#8202with no or very little spacing&#8202;&mdash;&#8202is used for parenthetical expressions, while internationally, the en dash &ndash; with spaces &ndash; is more prevalent.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values' => array( 'traditionalUS' => __( 'Traditional US' ), 'international' => __( 'International' ) ),
				'default' 		=> 'traditionalUS',
			),

			'typo_smart_diacritics' => array(
				'section'		=> 'character-replacement',
				'fieldset' 		=> 'diacritics',
				'label' 		=> __( "%1\$s Force diacritics where appropriate.", 'wp-typography' ),
				'help_text' 	=> __( "i.e. <samp>creme brulee</samp> becomes <samp>crème brûlée</samp>", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_diacritic_languages' => array(
				'section'		=> 'character-replacement',
				'fieldset' 		=> 'diacritics',
				'label' 		=> __( "Language for diacritic replacements: %1\$s", 'wp-typography' ),
				'help_text' 	=> __( "Language definitions will purposefully <strong>not</strong> process words that have alternate meaning without diacritics like <samp>resume &amp; résumé</samp>, <samp>divorce &amp; divorcé</samp>, and <samp>expose &amp; exposé</samp>.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(), // automatically detected and listed in __construct
				'default' 		=> "en-US",
			),
			'typo_diacritic_custom_replacements' => array(
				'section' 		=> 'character-replacement',
				'fieldset' 		=> 'diacritics',
				'label' 		=> __( "Custom diacritic word replacements:", 'wp-typography' ),
				'help_text' 	=> __( "Must be formatted <samp>\"word to replace\"=>\"replacement word\",</samp>; This is case-sensitive.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> '"cooperate"=>"coöperate", "Cooperate"=>"Coöperate", "cooperation"=>"coöperation", "Cooperation"=>"Coöperation", "cooperative"=>"coöperative", "Cooperative"=>"Coöperative", "coordinate"=>"coördinate", "Coordinate"=>"Coördinate", "coordinated"=>"coördinated", "Coordinated"=>"Coördinated", "coordinating"=>"coördinating", "Coordinating"=>"Coördinating", "coordination"=>"coördination", "Coordination"=>"Coördination", "coordinator"=>"coördinator", "Coordinator"=>"Coördinator", "coordinators"=>"coördinators", "Coordinators"=>"Coördinators", "continuum"=>"continuüm", "Continuum"=>"Continuüm", "debacle"=>"débâcle", "Debacle"=>"Débâcle", "elite"=>"élite", "Elite"=>"Élite",',
			),

			'typo_smart_ellipses' => array(
				'section'		=> 'character-replacement',
				'label' 		=> __( "%1\$s Transform three periods [ <samp>...</samp> ] to  ellipses [ <samp>&hellip;</samp> ].", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_smart_marks' => array(
				'section'		=> 'character-replacement',
				'label' 		=> __( "%1\$s Transform registration marks [ <samp>(c)</samp> <samp>(r)</samp> <samp>(tm)</samp> <samp>(sm)</samp> <samp>(p)</samp> ] to  proper characters [ <samp>©</samp> <samp>®</samp> <samp>™</samp> <samp>℠</samp> <samp>℗</samp> ].", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_smart_math' => array(
				'section'		=> 'character-replacement',
				'label' 		=> __( "%1\$s Transform exponents [ <samp>3^2</samp> ] to pretty exponents [ <samp>3<sup>2</sup></samp> ] and math symbols [ <samp>(2x6)/3=4</samp> ] to correct symbols [ <samp>(2&#215;6)&#247;3=4</samp> ].", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_smart_fractions' => array(
				'section'		=> 'character-replacement',
				'label' 		=> __( "%1\$s Transform fractions [ <samp>1/2</samp> ] to  pretty fractions [ <samp><sup>1</sup>&#8260;<sub>2</sub></samp> ].<br>WARNING: If you use a font (like Lucida Grande) that does not have a fraction-slash character, this may cause a missing line between the numerator and denominator.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_smart_ordinals' => array(
				'section'		=> 'character-replacement',
				'label' 		=> __( "%1\$s Transform ordinal suffixes [ <samp>1st</samp> ] to  pretty ordinals [ <samp>1<sup>st</sup></samp> ].", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_single_character_word_spacing' => array(
				'section'		=> 'space-control',
				'label' 		=> __( "%1\$s Prevent single character words from residing at the end of a line of text (unless it is a widow).", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_dash_spacing' => array(
				'section'		=> 'space-control',
				'label' 		=> __( "%1\$s Force thin spaces between em &amp; en dashes and adjoining words.  This will display poorly in IE6 with some fonts (like Tahoma) and in rare instances in WebKit browsers (Safari and Chrome).", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_fraction_spacing' => array(
				'section'		=> 'space-control',
				'label' 		=> __( "%1\$s Keep integers with adjoining fractions.", 'wp-typography' ),
				'help_text' 	=> __( "i.e. <samp>1 1/2</samp> or <samp>1 <sup>1</sup>&#8260;<sub>2</sub></samp>", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_space_collapse' => array(
				'section'		=> 'space-control',
				'label' 		=> __( "%1\$s Collapse adjacent spacing to a single character.", 'wp-typography' ),
				'help_text' 	=> __( "Normal HTML processing collapses basic spaces.  This option will additionally collapse no-break spaces, zero-width spaces, figure spaces, etc.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_unit_spacing' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> "values-and-units",
				'label' 		=> __( "%1\$s Keep values and units together.", 'wp-typography' ),
				'help_text' 	=> __( "i.e. <samp>1 in.</samp> or <samp>10 m<sup>2</sup></samp>", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_units' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> "values-and-units",
				'label' 		=> __( "Unit names:", 'wp-typography' ),
				'help_text' 	=> __( "Separate unit names with spaces. We already look for a large list; fill in any holes here.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> "hectare fortnight",
			),
			'typo_prevent_widows' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'widows',
				'label' 		=> __( "%1\$s Prevent widows", 'wp-typography' ),
				'help_text' 	=> __( "Widows are the last word in a block of text that wraps to its own line.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_widow_min_length' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'widows',
				'label' 		=> __( "Only protect widows with %1\$s or fewer letters.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,100=>100),
				'default' 		=> 5,
			),
			'typo_widow_max_pull' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'widows',
				'label' 		=> __( "Pull at most %1\$s letters from the previous line to keep the widow company.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,100=>100),
				'default' 		=> 5,
			),
			'typo_wrap_hyphens' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'enable-wrapping',
				'label' 		=> __( "%1\$s Enable wrapping after hard hyphens.", 'wp-typography' ),
				'help_text' 	=> __( "Adds zero-width spaces after hard hyphens (like in &ldquo;zero-width&rdquo;).", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_wrap_emails' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'enable-wrapping',
				'label' 		=> __( "%1\$s Enable wrapping of long emails.", 'wp-typography' ),
				'help_text' 	=> __( "Adds zero-width spaces throughout the email.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_wrap_urls' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'enable-wrapping',
				'label' 		=> __( "%1\$s Enable wrapping of long URLs.", 'wp-typography' ),
				'help_text' 	=> __( "Adds zero-width spaces throughout the URL.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_wrap_min_after' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'enable-wrapping',
				'label' 		=> __( "Keep at least the last %1\$s characters of a URL together.", 'wp-typography' ),
				'control' 		=> 'select',
				'option_values'	=> array(3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10),
				'default' 		=> 3,
			),
			'typo_remove_ie6' => array(
				'section'		=> 'space-control',
				'fieldset' 		=> 'enable-wrapping',
				'label' 		=> __( "%1\$s Remove zero-width spaces from IE6.", 'wp-typography' ),
				'help_text' 	=> __( "IE6 displays mangles zero-width spaces with some fonts like Tahoma (uses JavaScript).", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_style_amps' => array(
				'section' 		=> 'css-hooks',
				'label' 		=> __( "%1\$s Wrap ampersands [ <samp>&amp;</samp> ] with <samp>&lt;span class=\"amp\"&gt;</samp>.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_style_caps' => array(
				'section' 		=> 'css-hooks',
				'label' 		=> __( "%1\$s Wrap acronyms (all capitals) with <samp>&lt;span class=\"caps\"&gt;</samp>.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_style_numbers' => array(
				'section' 		=> 'css-hooks',
				'label' 		=> __( "%1\$s Wrap digits [ <samp>0123456789</samp> ] with <samp>&lt;span class=\"numbers\"&gt;</samp>.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 0,
			),
			'typo_style_initial_quotes' => array(
				'section' 		=> 'css-hooks',
				'label' 		=> __( "%1\$s Wrap initial quotes", 'wp-typography' ),
				'help_text' 	=> __( "Note: matches quotemarks at the beginning of blocks of text, <strong>not</strong> all opening quotemarks. <br />Single quotes [ <samp>&lsquo;</samp> <samp>&#8218;</samp> ] are wrapped with <samp>&lt;span class=\"quo\"&gt;</samp>. <br />Double quotes [ <samp>&ldquo;</samp> <samp>&#8222;</samp> ] are wrapped with <samp>&lt;span class=\"dquo\"&gt;</samp>. <br />Guillemets [ <samp>&laquo;</samp> <samp>&raquo;</samp> ] are wrapped with <samp>&lt;span class=\"dquo\"&gt;</samp>.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_initial_quote_tags' => array(
				'section' 		=> 'css-hooks',
				'label' 		=> __( "Limit styling of initial quotes to these <strong>HTML elements</strong>:", 'wp-typography' ),
				'help_text' 	=> __( "Separate tag names with spaces; do not include the <samp>&lt;</samp> or <samp>&gt;</samp>.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> "p h1 h2 h3 h4 h5 h6 blockquote li dd dt",
			),
			'typo_style_css_include' => array(
				'section' 		=> 'css-hooks',
				'label' 		=> __( "%1\$s Include Styling for CSS Hooks", 'wp-typography' ),
				'help_text' 	=> __( "Attempts to inject the CSS specified below.  If you are familiar with CSS, it is recommended you not use this option, and maintain all styles in your main stylesheet.", 'wp-typography' ),
				'control' 		=> 'input',
				'input_type' 	=> 'checkbox',
				'default' 		=> 1,
			),
			'typo_style_css' => array(
				'section'		=> 'css-hooks',
				'label' 		=> __( "Styling for CSS Hooks:", 'wp-typography' ),
				'help_text' 	=> __( "This will only be applied if explicitly selected with the preceding option.", 'wp-typography' ),
				'control' 		=> 'textarea',
				'default' 		=> 'sup {
	vertical-align: 60%;
	font-size: 75%;
	line-height: 100%;
}
sub {
	vertical-align: -10%;
	font-size: 75%;
	line-height: 100%;
}
.amp {
	font-family: Baskerville, "Goudy Old Style", "Palatino", "Book Antiqua", "Warnock Pro", serif;
	font-weight: normal;
	font-style: italic;
	font-size: 1.1em;
	line-height: 1em;
}
.caps {
	font-size: 90%;
}
.dquo {
	margin-left:-.40em;
}
.quo {
	margin-left:-.2em;
}
/* because formatting .numbers should consider your current font settings, we will not style it here */
'
			),

		);
	}

	/**
	 * Register admin settings.
	 */
	function register_the_settings() {
		foreach ( $this->admin_form_controls as $control_id => $control ) {
			register_setting( $this->option_group, $control_id );
		}

		register_setting( $this->option_group, 'typo_restore_defaults' );
		register_setting( $this->option_group, 'typo_clear_cache' );
	}

	/**
	 * Add an options page for the plugin settings.
	 */
	function add_options_page()	{
		$page = add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', strtolower( $this->plugin_name ), array( $this, 'get_admin_page_content' ) );

		// Using registered $page handle to hook stylesheet loading
		add_action( 'admin_print_styles-' . $page, array( $this, 'print_admin_styles' ) );
	}

	/**
	 * Enqueue stylesheet for options page.
	 */
	function print_admin_styles() {
		wp_enqueue_style( 'wp-typography-settings', plugins_url( 'admin/css/settings.css', $this->local_plugin_path ), array(), $this->version, 'all' );
	}

	/**
	 * Add a 'Settings' link to the wp-Typography entry in the plugins list.
	 *
	 * @param array $links An array of links.
	 * @return array An array of links.
	 */
	function plugin_action_links( $links ) {
		$adminurl = trailingslashit( admin_url() );

		// Add link "Settings" to the plugin in /wp-admin/plugins.php
		$settings_link = '<a href="'.$adminurl.'options-general.php?page='.strtolower( $this->plugin_name ).'">' . __( 'Settings' , 'wp-typography') . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Display the plugin options page.
	 */
	function get_admin_page_content() {
		// dynamically generate the list of hyphenation language patterns
		if ( false === ( $languages = get_transient( $this->transient_names['hyphenate_languages'] ) ) ) {
			$languages = \PHP_Typography\PHP_Typography::get_hyphenation_languages();
			$this->plugin->set_transient( $this->transient_names['hyphenate_languages'], $languages, WEEK_IN_SECONDS );
		}
		$this->admin_form_controls['typo_hyphenate_languages']['option_values'] = $languages;

		if ( false === ( $languages = get_transient( $this->transient_names['diacritic_languages'] ) ) ) {
			$languages = \PHP_Typography\PHP_Typography::get_diacritic_languages();
			$this->plugin->set_transient( $this->transient_names['diacritic_languages'], $languages, WEEK_IN_SECONDS );
		}
		$this->admin_form_controls['typo_diacritic_languages']['option_values'] = $languages;

		// load the settings page HTML
		include_once dirname( __DIR__ ) . '/admin/partials/settings.php';
	}

	/**
	 * Create the markup for a plugin setting.
	 *
	 * @param string $id Required. The control/option ID.
	 * @param string $control Required. Accepts: 'input', 'select', or 'textarea'; not implemented: 'button'. Default 'input'.
	 * @param string $input_type Optional. Used when $control is set to 'input'. Accepts: 'text', 'password', 'checkbox', 'submit', 'hidden';
	 *               not implemented: 'radio', 'image', 'reset', 'button', 'file'. Default 'text'.
	 * @param string $label_before Optional. Text displayed before the control. Default empty.
	 * @param string $help_text Optional. Requires an accompanying label. Default empty.
	 * @param array  $option_values {
	 * 		Optional. Array of values and display strings in the form ($value => $display). Default empty.
	 * }
	 * @return string The markup for the control.
	 */
	function get_admin_form_control( $id, $control = 'input', $input_type = 'text', $label = null, $help_text = null, $option_values = null ) {
		$button_class  = null;
		$control_begin = '<div class="control">';
		$control_end   = '</div>';

		// translate $label & $help_text
		$label     = __( $label,     'wp-typography' );
		$help_text = __( $help_text, 'wp-typography' );

		if ( 'submit' !== $input_type ) {
			$value = get_option( $id );
		} elseif ( 'typo_restore_defaults' === $id ) {
			$value         = __( 'Restore Defaults', 'wp-typography' );
			$control_begin = $control_end = '';
			$button_class  = 'button button-secondary';
		} elseif ( 'typo_clear_cache' === $id ) {
			$value         = __( 'Clear Cache', 'wp-typography' );
			$control_begin = $control_end = '';
			$button_class  = 'button button-secondary';
		} else {
			$value         = __( 'Save Changes', 'wp-typography' );
			$control_begin = $control_end = '';
			$button_class  = 'button button-primary';
		}

		// Make sure $value is in $option_values if $option_values is set
		if ( $option_values && ! isset( $option_values[ $value ] ) ) {
			$value = null;
		}

		switch ( $control ) {
			case 'textarea':
				$control_markup = $this->get_admin_form_textarea( $id, $value, $label, $help_text );
				break;

			case 'select':
				$control_markup = $this->get_admin_form_select( $id, $value, $label, $help_text, $option_values );
				break;

			case 'input':
				$control_markup = $this->get_admin_form_input( $id, $value, $input_type, $label, $help_text, $button_class );
				break;

			default:
				error_log("Unsupported control <$control>.");
				return '';
		}

		return $control_begin . $control_markup . "$control_end\r\n";
	}

	/**
	 * Retrieve markup for <textarea>.
	 *
	 * @param string $id
	 * @param string $value
	 * @param string $label
	 * @param string $help
	 */
	private function get_admin_form_textarea( $id, $value, $label, $help ) {
		if ( ( $label || $help ) ) {
			$control_markup = "<label for='$id'>";

			if ( $label ) {
				$control_markup .= $label;
			}

			if ( $help ) {
				$control_markup .= "<span class='helptext'>$help</span>";
			}

			$control_markup .= '</label>';
		}

		return $control_markup . "<textarea id='$id' name='$id'>" . ( ! empty( $value ) ? $value : '') . "</textarea>";
	}

	/**
	 * Retrieve markup for <select>.
	 *
	 * @param string $id
	 * @param string $value
	 * @param string $label
	 * @param string $help
	 * @param array  $option_values
	 */
	private function get_admin_form_select( $id, $value, $label, $help, $option_values ) {
		$control_markup = '';

		if ( ( $label || $help ) ) {
			$control_markup .= "<label for='$id'>";

			if ( $label ) {
				$control_markup .= $label;
			} else {
				$control_markup .= '%1$s';
			}

			if ( $help ) {
				$control_markup .= " <span class='helptext'>$help</span>";
			}

			$control_markup .= '</label>';
		} else {
			$control_markup .= '%1$s';
		}

		$select_markup = "<select id='$id' name='$id' >";
		foreach ( $option_values as $option_value => $display ) {
			$select_markup .= "<option value='$option_value' " . selected( $value, $option_value, false ) . ">" . __( $display, 'wp-typography' ) . "</option>";
		}
		$select_markup .= '</select>';

		return sprintf( $control_markup, $select_markup );
	}

	/**
	 * Retrieve markup for <input>.
	 *
	 * @param string $id
	 * @param string $value
	 * @param string $input_type
	 * @param string $label
	 * @param string $help
	 * @param string $button_class
	 */
	private function get_admin_form_input( $id, $value, $input_type, $label, $help, $button_class = null ) {
		$id_and_class = "id='$id' name='$id' ";          // default ID & name, no class (except for submit buttons)
		$value_markup = $value ? "value='$value' " : ''; // default except for checkbox;

		switch( $input_type ) {
			case 'submit':
				$id_and_class = "name='$id' class='$button_class'"; // to avoid duplicate ids and some pretty stylin'
			case 'hidden':
				$control_markup = '%1$s';
				break;

			case 'checkbox':
				$value_markup = "value='1' " . checked( $value, true, false );
			default:
				if ( $label || $help ) {
					$control_markup = "<label for='$id'>";

					if ( $label ) {
						$control_markup .= $label;
					} else {
						$control_markup .= '%1$s';
					}

					if ( $help ) {
						$control_markup .= "<span class='helptext'>$help</span>";
					}

					$control_markup .= '</label>';
				} else {
					$control_markup = '%1$s';
				}
		}

		return sprintf( $control_markup, "<input type='$input_type' $id_and_class $value_markup/>" );
	}
}
