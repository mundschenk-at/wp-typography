<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

namespace WP_Typography\Settings;

use \WP_Typography;
use \WP_Typography\UI;

use \PHP_Typography\Settings\Dash_Style;
use \PHP_Typography\Settings\Quote_Style;

/**
 * Default configuration for wp-Typography.
 *
 * @since 5.2.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
abstract class Plugin_Configuration {

	// Plugin configuration indexes.
	const IGNORE_TAGS                      = 'ignore_tags';
	const IGNORE_CLASSES                   = 'ignore_classes';
	const IGNORE_IDS                       = 'ignore_ids';
	const ENABLE_MULTILINGUAL_SUPPORT      = 'enable_multilingual_support';
	const SMART_CHARACTERS                 = 'smart_characters';
	const SMART_DASHES                     = 'smart_dashes';
	const SMART_DASHES_STYLE               = 'smart_dashes_style';
	const SMART_ELLIPSES                   = 'smart_ellipses';
	const SMART_MATH                       = 'smart_math';
	const SMART_FRACTIONS                  = 'smart_fractions';
	const SMART_ORDINALS                   = 'smart_ordinals';
	const SMART_MARKS                      = 'smart_marks';
	const SMART_QUOTES                     = 'smart_quotes';
	const SMART_DIACRITICS                 = 'smart_diacritics';
	const DIACRITIC_LANGUAGES              = 'diacritic_languages';
	const DIACRITIC_CUSTOM_REPLACEMENTS    = 'diacritic_custom_replacements';
	const SMART_QUOTES_PRIMARY             = 'smart_quotes_primary';
	const SMART_QUOTES_SECONDARY           = 'smart_quotes_secondary';
	const SINGLE_CHARACTER_WORD_SPACING    = 'single_character_word_spacing';
	const DASH_SPACING                     = 'dash_spacing';
	const FRACTION_SPACING                 = 'fraction_spacing';
	const UNIT_SPACING                     = 'unit_spacing';
	const NUMBERED_ABBREVIATIONS_SPACING   = 'numbered_abbreviations_spacing';
	const FRENCH_PUNCTUATION_SPACING       = 'french_punctuation_spacing';
	const UNITS                            = 'units';
	const SPACE_COLLAPSE                   = 'space_collapse';
	const PREVENT_WIDOWS                   = 'prevent_widows';
	const WIDOW_MIN_LENGTH                 = 'widow_min_length';
	const WIDOW_MAX_PULL                   = 'widow_max_pull';
	const WRAP_HYPHENS                     = 'wrap_hyphens';
	const WRAP_EMAILS                      = 'wrap_emails';
	const WRAP_URLS                        = 'wrap_urls';
	const WRAP_MIN_AFTER                   = 'wrap_min_after';
	const STYLE_CSS                        = 'style_css';
	const STYLE_CSS_INCLUDE                = 'style_css_include';
	const STYLE_AMPS                       = 'style_amps';
	const STYLE_CAPS                       = 'style_caps';
	const STYLE_NUMBERS                    = 'style_numbers';
	const STYLE_HANGING_PUNCTUATION        = 'style_hanging_punctuation';
	const STYLE_INITIAL_QUOTES             = 'style_initial_quotes';
	const INITIAL_QUOTE_TAGS               = 'initial_quote_tags';
	const ENABLE_HYPHENATION               = 'enable_hyphenation';
	const HYPHENATE_HEADINGS               = 'hyphenate_headings';
	const HYPHENATE_CAPS                   = 'hyphenate_caps';
	const HYPHENATE_TITLE_CASE             = 'hyphenate_title_case';
	const HYPHENATE_COMPOUNDS              = 'hyphenate_compounds';
	const HYPHENATE_LANGUAGES              = 'hyphenate_languages';
	const HYPHENATE_MIN_LENGTH             = 'hyphenate_min_length';
	const HYPHENATE_MIN_BEFORE             = 'hyphenate_min_before';
	const HYPHENATE_MIN_AFTER              = 'hyphenate_min_after';
	const HYPHENATE_EXCEPTIONS             = 'hyphenate_exceptions';
	const HYPHENATE_CLEAN_CLIPBOARD        = 'hyphenate_clean_clipboard';
	const HYPHENATE_SAFARI_FONT_WORKAROUND = 'hyphenate_safari_font_workaround';
	const HYPHENATION_EXCEPTIONS           = 'hyphenation_exceptions';
	const IGNORE_PARSER_ERRORS             = 'ignore_parser_errors';

	/**
	 * The defaults array.
	 *
	 * @var array
	 */
	private static $defaults;

	/**
	 * Retrieves the default settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		if ( empty( self::$defaults ) ) {
			self::$defaults = [ // @codeCoverageIgnore
				self::IGNORE_TAGS                      => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'general-scope',
					'short'         => \__( 'Ignore HTML elements', 'wp-typography' ),
					'help_text'     => \__( 'Separate tag names with spaces; do not include the <code>&lt;</code> or <code>&gt;</code>. The content of these HTML elements will not be processed.', 'wp-typography' ),
					'default'       => 'code head kbd object option pre samp script style textarea title var math',
				],
				self::IGNORE_CLASSES                   => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'general-scope',
					'short'         => \__( 'Ignore CSS classes', 'wp-typography' ),
					'help_text'     => \__( 'Separate class names with spaces. Elements with these classes will not be processed.', 'wp-typography' ),
					'default'       => 'vcard noTypo',
				],
				self::IGNORE_IDS                       => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'general-scope',
					'short'         => \__( 'Ignore IDs', 'wp-typography' ),
					'help_text'     => \__( 'Separate ID names with spaces. Elements with these IDs will not be processed.', 'wp-typography' ),
					'default'       => '',
				],
				self::IGNORE_PARSER_ERRORS             => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'general-scope',
					'short'         => \__( 'Parser errors', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Ignore errors in parsed HTML.', 'wp-typography' ),
					'help_text'     => \__( 'Unchecking will prevent processing completely if the HTML parser produces any errors for a given content part. You should only need to do this in case your site layout changes with wp-Typography enabled.', 'wp-typography' ),
					'default'       => 1,
				],
				self::ENABLE_MULTILINGUAL_SUPPORT      => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'general-scope',
					'short'         => \__( 'Multilingual support', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Enable support for using multiple languages on the same site.', 'wp-typography' ),
					'help_text'     => \__( 'Enable if you are using multilingual plugin like WPML or Polylang and want automatic hyphenation language, dash and quote style adjustments.', 'wp-typography' ),
					'default'       => 0,
				],
				self::ENABLE_HYPHENATION               => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'hyphenation',
					'short'         => \__( 'Hyphenation', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Enable hyphenation.', 'wp-typography' ),
					'default'       => 1,
				],
				self::HYPHENATE_LANGUAGES              => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'hyphenation',
					/* translators: 1: language dropdown */
					'label'         => \__( 'Language for hyphenation rules: %1$s', 'wp-typography' ),
					'option_values' => [], // Automatically detected and listed in __construct.
					'default'       => 'en-US',
				],
				self::HYPHENATE_HEADINGS               => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'hyphenation',
					'short'         => \__( 'Special cases', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Hyphenate headings.', 'wp-typography' ),
					'help_text'     => \__( 'Unchecking will disallow hyphenation of headings, even if allowed in the general scope.', 'wp-typography' ),
					'default'       => 0,
				],
				self::HYPHENATE_TITLE_CASE             => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'hyphenation',
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Allow hyphenation of words that begin with a capital letter.', 'wp-typography' ),
					'help_text'     => \__( 'Uncheck to avoid hyphenation of proper nouns.', 'wp-typography' ),
					'default'       => 1,
				],
				self::HYPHENATE_COMPOUNDS              => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'hyphenation',
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Allow hyphenation of the components of hyphenated compound words.', 'wp-typography' ),
					'help_text'     => \__( 'Uncheck to disallow the hyphenation of the words making up a hyphenated compound (e.g. <code>editor-in-chief</code>).', 'wp-typography' ),
					'default'       => 1,
				],
				self::HYPHENATE_CAPS                   => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'hyphenation',
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Hyphenate words in ALL CAPS.', 'wp-typography' ),
					'default'       => 0,
				],
				self::HYPHENATE_MIN_LENGTH             => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'hyphenation',
					'short'         => \__( 'Character limits', 'wp-typography' ),
					/* translators: 1: number dropdown */
					'label'         => \__( 'Do not hyphenate words with less than %1$s letters.', 'wp-typography' ),
					'option_values' => self::get_numeric_option_values( [ 4, 5, 6, 7, 8, 9, 10 ] ),
					'default'       => 5,
				],
				self::HYPHENATE_MIN_BEFORE             => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'hyphenation',
					/* translators: 1: number dropdown */
					'label'         => \__( 'Keep at least %1$s letters before hyphenation.', 'wp-typography' ),
					'option_values' => self::get_numeric_option_values( [ 2, 3, 4, 5 ] ),
					'default'       => 3,
				],
				self::HYPHENATE_MIN_AFTER              => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'hyphenation',
					/* translators: 1: number dropdown */
					'label'         => \__( 'Keep at least %1$s letters after hyphenation.', 'wp-typography' ),
					'option_values' => self::get_numeric_option_values( [ 2, 3, 4, 5 ] ),
					'default'       => 2,
				],
				self::HYPHENATE_EXCEPTIONS             => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'hyphenation',
					'short'         => \__( 'Exception list', 'wp-typography' ),
					'help_text'     => \__( 'Mark allowed hyphenations with "-"; separate words with spaces.', 'wp-typography' ),
					'attributes'    => [
						'rows' => '8',
					],
					'default'       => 'Mund-schenk',
				],
				self::HYPHENATE_CLEAN_CLIPBOARD        => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'hyphenation',
					'short'         => \__( 'Browser support', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Remove hyphenation when copying to clipboard', 'wp-typography' ),
					'help_text'     => \__( 'To prevent legacy applications from displaying inappropriate hyphens, all soft hyphens and zero-width spaces are removed from the clipboard selection. Requires JavaScript.', 'wp-typography' ),
					'default'       => 1,
				],
				self::HYPHENATE_SAFARI_FONT_WORKAROUND => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'hyphenation',
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Add workaround for Safari hyphenation bug', 'wp-typography' ),
					'help_text'     => \__( 'Safari displays weird ligature-like characters with some fonts (like Open Sans) when hyhpenation is enabled. Inserts <code>-webkit-font-feature-settings: "liga", "dlig";</code> as inline CSS workaround.', 'wp-typography' ),
					'default'       => 1,
				],
				self::SMART_CHARACTERS                 => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'short'         => \__( 'Intelligent character replacement', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Override WordPress\' automatic character handling with your preferences here.', 'wp-typography' ),
					'default'       => 1,
				],
				self::SMART_QUOTES                     => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'short'         => \__( 'Smart quotes', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Transform straight quotes [ <code>\'</code> <code>"</code> ] to typographically correct characters as detailed below.', 'wp-typography' ),
					'default'       => 1,
				],
				self::SMART_QUOTES_PRIMARY             => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'character-replacement',
					/* translators: 1: style dropdown */
					'label'         => \__( 'Primary quotation style: Convert <code>"foo"</code> to %1$s.', 'wp-typography' ),
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
				],
				self::SMART_QUOTES_SECONDARY           => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'character-replacement',
					/* translators: 1: style dropdown */
					'label'         => \__( "Secondary quotation style: Convert <code>'foo'</code> to %1\$s.", 'wp-typography' ),
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
				],
				self::SMART_DASHES                     => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'short'         => \__( 'Smart dashes', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Transform minus-hyphens [ <code>-</code> <code>--</code> ] to contextually appropriate dashes, minus signs, and hyphens [ <code>&ndash;</code> <code>&mdash;</code> <code>&#8722;</code> <code>&#8208;</code> ].', 'wp-typography' ),
					'default'       => 1,
				],
				self::SMART_DASHES_STYLE               => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'character-replacement',
					/* translators: 1: style dropdown */
					'label'         => \__( 'Use the %1$s style for dashes.', 'wp-typography' ),
					'help_text'     => \__( 'In the US, the em dash&#8202;&mdash;&#8202;with no or very little spacing&#8202;&mdash;&#8202;is used for parenthetical expressions, while internationally, the en dash &ndash; with spaces &ndash; is more prevalent.', 'wp-typography' ),
					'option_values' => [
						Dash_Style::TRADITIONAL_US => \__( 'Traditional US', 'wp-typography' ),
						Dash_Style::INTERNATIONAL  => \__( 'International', 'wp-typography' ),
					],
					'default'       => Dash_Style::TRADITIONAL_US,
				],
				self::SMART_DIACRITICS                 => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'short'         => \__( 'Smart diacritics', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Force diacritics where appropriate.', 'wp-typography' ),
					'help_text'     => \__( 'For example, <code>creme brulee</code> becomes <code>crème brûlée</code>.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::DIACRITIC_LANGUAGES              => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'character-replacement',
					/* translators: 1: language dropdown */
					'label'         => \__( 'Language for diacritic replacements: %1$s', 'wp-typography' ),
					'help_text'     => \__( 'Language definitions will purposefully not process words that have alternate meaning without diacritics like <code>resume</code>/<code>résumé</code>, <code>divorce</code>/<code>divorcé</code>, and <code>expose</code>/<code>exposé</code>.', 'wp-typography' ),
					'option_values' => [], // Automatically detected and listed in __construct.
					'default'       => 'en-US',
				],
				self::DIACRITIC_CUSTOM_REPLACEMENTS    => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'character-replacement',
					'label'         => \__( 'Custom diacritic word replacements:', 'wp-typography' ),
					'help_text'     => \__( 'Must be formatted <code>"word to replace"=>"replacement word",</code>. The entries are case-sensitive.', 'wp-typography' ),
					'attributes'    => [
						'rows' => '8',
					],
					'default'       => '"cooperate"=>"coöperate", "Cooperate"=>"Coöperate", "cooperation"=>"coöperation", "Cooperation"=>"Coöperation", "cooperative"=>"coöperative", "Cooperative"=>"Coöperative", "coordinate"=>"coördinate", "Coordinate"=>"Coördinate", "coordinated"=>"coördinated", "Coordinated"=>"Coördinated", "coordinating"=>"coördinating", "Coordinating"=>"Coördinating", "coordination"=>"coördination", "Coordination"=>"Coördination", "coordinator"=>"coördinator", "Coordinator"=>"Coördinator", "coordinators"=>"coördinators", "Coordinators"=>"Coördinators", "continuum"=>"continuüm", "Continuum"=>"Continuüm", "debacle"=>"débâcle", "Debacle"=>"Débâcle", "elite"=>"élite", "Elite"=>"Élite",',
				],
				self::SMART_ELLIPSES                   => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'short'         => \__( 'Ellipses', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Transform three periods [ <code>...</code> ] to  ellipses [ <code>&hellip;</code> ].', 'wp-typography' ),
					'default'       => 1,
				],
				self::SMART_MARKS                      => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'short'         => \__( 'Registration marks', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Transform registration marks [ <code>(c)</code> <code>(r)</code> <code>(tm)</code> <code>(sm)</code> <code>(p)</code> ] to  proper characters [ <code>©</code> <code>®</code> <code>™</code> <code>℠</code> <code>℗</code> ].', 'wp-typography' ),
					'default'       => 1,
				],
				self::SMART_MATH                       => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'section'       => 'math-replacements',
					'short'         => \__( 'Math symbols', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Transform exponents [ <code>3^2</code> ] to pretty exponents [ <code>3<sup>2</sup></code> ] and math symbols [ <code>(2x6)/3=4</code> ] to correct symbols [ <code>(2&#215;6)&#247;3=4</code> ].', 'wp-typography' ),
					'default'       => 0,
				],
				self::SMART_FRACTIONS                  => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'section'       => 'math-replacements',
					'short'         => \__( 'Fractions', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Transform fractions [ <code>1/2</code> ] to  pretty fractions [ <code><sup>1</sup>&#8260;<sub>2</sub></code> ].', 'wp-typography' ),
					'help_text'     => \__( 'Warning: If you use a font (like Lucida Grande) that does not have a fraction-slash character, this may cause a missing line between the numerator and denominator.', 'wp-typography' ),
					'default'       => 0,
				],
				self::SMART_ORDINALS                   => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'character-replacement',
					'section'       => 'math-replacements',
					'short'         => \__( 'Ordinal numbers', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Transform ordinal suffixes [ <code>1st</code> ] to  pretty ordinals [ <code>1<sup>st</sup></code> ].', 'wp-typography' ),
					'default'       => 0,
				],
				self::SINGLE_CHARACTER_WORD_SPACING    => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'short'         => \__( 'Single character words', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Prevent single character words from residing at the end of a line of text (unless it is a widow).', 'wp-typography' ),
					'default'       => 0,
				],
				self::DASH_SPACING                     => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'short'         => \__( 'Dashes', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Force thin spaces between em &amp; en dashes and adjoining words.', 'wp-typography' ),
					'default'       => 0,
				],
				self::FRACTION_SPACING                 => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'short'         => \__( 'Fractions', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Keep integers with adjoining fractions.', 'wp-typography' ),
					'help_text'     => \__( 'Examples: <code>1 1/2</code> or <code>1 <sup>1</sup>&#8260;<sub>2</sub></code>.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::SPACE_COLLAPSE                   => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'short'         => \__( 'Adjacent spacing', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Collapse adjacent spacing to a single character.', 'wp-typography' ),
					'help_text'     => \__( 'Normal HTML processing collapses basic spaces. This option will additionally collapse no-break spaces, zero-width spaces, figure spaces, etc.', 'wp-typography' ),
					'default'       => 0,
				],
				self::FRENCH_PUNCTUATION_SPACING       => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'short'         => \__( 'French punctuation', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Apply French punctuation rules.', 'wp-typography' ),
					'help_text'     => \__( 'This option adds a thin non-breakable space before <code>?!:;</code>.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::NUMBERED_ABBREVIATIONS_SPACING   => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'short'         => \__( 'Numbered abbreviations', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Keep abbreviations containing numbers together.', 'wp-typography' ),
					'help_text'     => \__( 'Examples: <code>ISO 9001</code> or <code>E 100</code>.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::UNIT_SPACING                     => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'short'         => \__( 'Values &amp; Units', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Keep values and units together.', 'wp-typography' ),
					'help_text'     => \__( 'Examples: <code>1 in.</code> or <code>10 m<sup>2</sup></code>.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::UNITS                            => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'space-control',
					'label'         => \__( 'Additional unit names:', 'wp-typography' ),
					'help_text'     => \__( 'Separate unit names with spaces. We already look for a large list; fill in any holes here.', 'wp-typography' ),
					'default'       => 'hectare fortnight',
				],
				self::WRAP_HYPHENS                     => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'section'       => 'enable-wrapping',
					'short'         => \__( 'Hyphens', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Enable wrapping after hard hyphens.', 'wp-typography' ),
					'help_text'     => \__( 'Adds zero-width spaces after hard hyphens (like in &ldquo;zero-width&rdquo;).', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::WRAP_EMAILS                      => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'section'       => 'enable-wrapping',
					'short'         => \__( 'Email addresses', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Enable wrapping of long email addresses.', 'wp-typography' ),
					'help_text'     => \__( 'Adds zero-width spaces throughout the email address.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::WRAP_URLS                        => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'section'       => 'enable-wrapping',
					'short'         => \__( 'URLs', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Enable wrapping of long URLs.', 'wp-typography' ),
					'help_text'     => \__( 'Adds zero-width spaces throughout the URL.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 0,
				],
				self::WRAP_MIN_AFTER                   => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'space-control',
					'section'       => 'enable-wrapping',
					/* translators: 1: number dropdown */
					'label'         => \__( 'Keep at least the last %1$s characters of a URL together.', 'wp-typography' ),
					'option_values' => self::get_numeric_option_values( [ 3, 4, 5, 6, 7, 8, 9, 10 ] ),
					'default'       => 3,
				],
				self::PREVENT_WIDOWS                   => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'space-control',
					'section'       => 'enable-wrapping',
					'short'         => \__( 'Widows', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Prevent widows.', 'wp-typography' ),
					'help_text'     => \__( 'Widows are the last word in a block of text that wraps to its own line.', 'wp-typography' ),
					'help_inline'   => true,
					'default'       => 1,
				],
				self::WIDOW_MIN_LENGTH                 => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'space-control',
					'section'       => 'enable-wrapping',
					/* translators: 1: number dropdown */
					'label'         => \__( 'Only protect widows with %1$s or fewer letters.', 'wp-typography' ),
					'option_values' => self::get_numeric_option_values( [ 4, 5, 6, 7, 8, 9, 10, 100 ] ),
					'default'       => 5,
				],
				self::WIDOW_MAX_PULL                   => [
					'ui'            => UI\Select::class,
					'tab_id'        => 'space-control',
					'section'       => 'enable-wrapping',
					/* translators: 1: number dropdown */
					'label'         => \__( 'Pull at most %1$s letters from the previous line to keep the widow company.', 'wp-typography' ),
					'option_values' => self::get_numeric_option_values( [ 4, 5, 6, 7, 8, 9, 10, 100 ] ),
					'default'       => 5,
				],
				self::STYLE_AMPS                       => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'css-hooks',
					'short'         => \__( 'Ampersands', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Wrap ampersands [ <code>&amp;</code> ] with <code>&lt;span class="amp"&gt;</code>.', 'wp-typography' ),
					'default'       => 1,
				],
				self::STYLE_CAPS                       => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'css-hooks',
					'short'         => \__( 'Caps', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Wrap acronyms (all capitals) with <code>&lt;span class="caps"&gt;</code>.', 'wp-typography' ),
					'default'       => 1,
				],
				self::STYLE_NUMBERS                    => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'css-hooks',
					'short'         => \__( 'Numbers', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Wrap digits [ <code>0123456789</code> ] with <code>&lt;span class="numbers"&gt;</code>.', 'wp-typography' ),
					'default'       => 0,
				],
				self::STYLE_HANGING_PUNCTUATION        => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'css-hooks',
					'short'         => \__( 'Hanging punctuation', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Wrap small punctuation marks.', 'wp-typography' ),
					'help_text'     => \__( "The amount of push/pull should be adjusted for your selected font in the stylesheet. <br />Single quote-like marks [ <code>&#8218;&lsquo;&apos;&prime;'</code> ] are wrapped with <code>&lt;span class=\"pull-single\"&gt;</code>. <br />Double quote-like marks [ <code>&#8222;&ldquo;&Prime;\"</code> ] are wrapped with <code>&lt;span class=\"pull-double\"&gt;</code>. <br/>For punctuation marks that do not begin a block of text, a corresponding empty <code>&lt;span class=\"push-&hellip;\"&gt;</code> ensures proper alignment within the line.", 'wp-typography' ),
					'default'       => 0,
				],
				self::STYLE_INITIAL_QUOTES             => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'css-hooks',
					'short'         => \__( 'Initial quotes', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Wrap initial quotes.', 'wp-typography' ),
					'help_text'     => \__( 'Matches quotemarks at the beginning of blocks of text, not all opening quotemarks. <br />Single quotes [ <code>&lsquo;</code> <code>&#8218;</code> ] are wrapped with <code>&lt;span class="quo"&gt;</code>. <br />Double quotes [ <code>&ldquo;</code> <code>&#8222;</code> ] are wrapped with <code>&lt;span class="dquo"&gt;</code>. <br />Guillemets [ <code>&laquo;</code> <code>&raquo;</code> ] are wrapped with <code>&lt;span class="dquo"&gt;</code>.', 'wp-typography' ),
					'default'       => 1,
				],
				self::INITIAL_QUOTE_TAGS               => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'css-hooks',
					'label'         => \__( 'Limit styling of initial quotes to these <strong>HTML elements</strong>:', 'wp-typography' ),
					'help_text'     => \__( 'Separate tag names with spaces; do not include the <code>&lt;</code> or <code>&gt;</code>.', 'wp-typography' ),
					'default'       => 'p h1 h2 h3 h4 h5 h6 blockquote li dd dt',
				],
				self::STYLE_CSS_INCLUDE                => [
					'ui'            => UI\Checkbox_Input::class,
					'tab_id'        => 'css-hooks',
					'short'         => \__( 'Styles', 'wp-typography' ),
					/* translators: 1: checkbox HTML */
					'label'         => \__( '%1$s Include styling for CSS hooks.', 'wp-typography' ),
					'help_text'     => \__( 'Attempts to inject the CSS specified below.  If you are familiar with CSS, it is recommended you not use this option, and maintain all styles in your main stylesheet.', 'wp-typography' ),
					'default'       => 1,
				],
				self::STYLE_CSS                        => [
					'ui'            => UI\Textarea::class,
					'tab_id'        => 'css-hooks',
					'label'         => \__( 'Styling for CSS hooks:', 'wp-typography' ),
					'help_text'     => \__( 'This will only be applied if explicitly selected with the preceding option.', 'wp-typography' ),
					'attributes'    => [
						'rows' => '10',
					],
					'default'       => file_get_contents( dirname( dirname( dirname( __DIR__ ) ) ) . '/admin/css/default-styles.css' ),
				],
			];
		}

		return self::$defaults;
	}

	/**
	 * Return numeric values as in associative form $value => $value.
	 *
	 * @param array $values Option values.
	 *
	 * @return array
	 */
	private static function get_numeric_option_values( array $values ) {
		return \array_combine( $values, $values );
	}
}
