<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2024 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
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
 *  @package mundschenk-at/wp-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Tests;

use WP_Typography\Implementation;

use WP_Typography\Components\REST_API;
use WP_Typography\Data_Storage\Cache;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Data_Storage\Transients;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\PHP_Typography;
use PHP_Typography\Hyphenator\Cache as Hyphenator_Cache;
use PHP_Typography\Settings;
use PHP_Typography\U;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\Implementation unit test.
 *
 * @coversDefaultClass \WP_Typography\Implementation
 * @usesDefaultClass \WP_Typography\Implementation
 *
 * @uses ::__construct
 * @uses \WP_Typography\Components\Admin_Interface::__construct
 * @uses \WP_Typography\Components\Setup::__construct
 * @uses \WP_Typography\Components\Common::__construct
 */
class WP_Typography_Implementation_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Implementation&m\MockInterface
	 */
	protected $wp_typo;

	/**
	 * Test fixture.
	 *
	 * @var Transients&m\MockInterface
	 */
	protected $transients;

	/**
	 * Test fixture.
	 *
	 * @var Cache&m\MockInterface
	 */
	protected $cache;

	/**
	 * Test fixture.
	 *
	 * @var Options&m\MockInterface
	 */
	protected $options;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Mock WP_Typography\Data_Storage\Options instance.
		$this->options = m::mock( Options::class );
		$this->options->shouldReceive( 'get' )->andReturn( false )->byDefault();
		$this->options->shouldReceive( 'set' )->andReturn( false )->byDefault();

		// Mock WP_Typography\Data_Storage\Transients instance.
		$this->transients = m::mock( Transients::class );
		$this->transients // @phpstan-ignore method.notFound
			->shouldReceive( 'get' )->byDefault()->andReturn( false )
			->shouldReceive( 'get_large_object' )->byDefault()->andReturn( false )
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'set_large_object' )->andReturn( false )->byDefault();

		// Mock WP_Typography\Data_Storage\Cache instance.
		$this->cache = m::mock( Cache::class );
		$this->cache  // @phpstan-ignore method.notFound
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'invalidate' )->byDefault();

		// Create instance.
		$this->wp_typo = m::mock( Implementation::class, [ '7.7.7', $this->transients, $this->cache, $this->options ] )
			->shouldAllowMockingProtectedMethods()
			->makePartial();
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses ::get_version
	 * @uses \WP_Typography\Data_Storage\Cache::__construct
	 * @uses \WP_Typography\Data_Storage\Cache::invalidate
	 * @uses \WP_Typography\Data_Storage\Options::__construct
	 * @uses \WP_Typography\Data_Storage\Transients::__construct
	 * @uses \WP_Typography\Data_Storage\Transients::invalidate
	 * @uses \WP_Typography\Data_Storage\Transients::get_keys_from_database
	 */
	public function test_constructor(): void {
		/**
		 * Mock.
		 *
		 * @var Transients&m\MockInterface
		 */
		$transients = m::mock( Transients::class );
		/**
		 * Mock.
		 *
		 * @var Cache&m\MockInterface
		 */
		$cache = m::mock( Cache::class );
		/**
		 * Mock.
		 *
		 * @var Options&m\MockInterface
		 */
		$options = m::mock( Options::class );

		$typo = new Implementation( '6.6.6', $transients, $cache, $options );

		$this->assert_attribute_same( '6.6.6', 'version', $typo );
	}

	/**
	 * Test get_typography_instance.
	 *
	 * @covers ::get_typography_instance
	 *
	 * @uses \WP_Typography\Typography\Custom_Node_Fix::__construct
	 * @uses \WP_Typography\Typography\Custom_Registry::__construct
	 * @uses \WP_Typography\Typography\Custom_Token_Fix::__construct
	 */
	public function test_get_typography_instance(): void {
		$this->wp_typo->shouldReceive( 'get_config' )->once()->andReturn(
			[
				Config::ENABLE_HYPHENATION => true,
			]
		);

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );

		$this->transients->shouldReceive( 'cache_object' )->twice();

		$this->assertInstanceOf( \PHP_Typography\PHP_Typography::class, $this->invokeMethod( $this->wp_typo, 'get_typography_instance' ) );
	}

	/**
	 * Test get_config.
	 *
	 * @covers ::get_config
	 */
	public function test_get_config(): void {

		$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( [ 'foo' => 'bar' ] );
		$this->wp_typo->shouldReceive( 'get_default_options' )->once()->andReturn( [ 'newbar' => 'foobar' ] );

		// Check result.
		$result = $this->wp_typo->get_config();
		$this->assert_is_array( $result );
		$this->assertArrayHasKey( 'foo', $result );
		$this->assertArrayHasKey( 'newbar', $result );
	}

	/**
	 * Test get_config with corrupted option.
	 *
	 * @covers ::get_config
	 */
	public function test_get_config_corrupted(): void {

		$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( 'wrong' );
		$this->wp_typo->shouldReceive( 'set_default_options' )->once()->with( true );

		// IRL set_default_options would fix the config object.
		$this->assertSame( [], $this->wp_typo->get_config() );
	}

	/**
	 * Test get_settings.
	 *
	 * @covers ::get_settings
	 */
	public function test_get_settings(): void {
		$remap_narrow_no_break_space = false;
		$config                      = [
			Config::IGNORE_TAGS                    => [ 'script' ],
			Config::IGNORE_CLASSES                 => [ 'noTypo' ],
			Config::IGNORE_IDS                     => [],
			Config::REMAP_NARROW_NO_BREAK_SPACE    => $remap_narrow_no_break_space,
			Config::REMAP_HYPHEN                   => false,
			Config::SMART_CHARACTERS               => true,
			Config::SMART_DASHES                   => false,
			Config::SMART_DASHES_STYLE             => 'international',
			Config::SMART_ELLIPSES                 => false,
			Config::SMART_MATH                     => false,
			Config::SMART_FRACTIONS                => false,
			Config::SMART_ORDINALS                 => false,
			Config::SMART_ORDINALS_ROMAN_NUMBERS   => false,
			Config::SMART_MARKS                    => false,
			Config::SMART_AREA_UNITS               => false,
			Config::SMART_QUOTES                   => false,
			Config::SMART_QUOTES_EXCEPTIONS        => [],
			Config::SMART_DIACRITICS               => false,
			Config::DIACRITIC_LANGUAGES            => 'en-US',
			Config::DIACRITIC_CUSTOM_REPLACEMENTS  => [],
			Config::SMART_QUOTES_PRIMARY           => 'doubleCurled',
			Config::SMART_QUOTES_SECONDARY         => 'singleCurled',
			Config::SINGLE_CHARACTER_WORD_SPACING  => false,
			Config::DASH_SPACING                   => false,
			Config::FRACTION_SPACING               => false,
			Config::UNIT_SPACING                   => false,
			Config::NUMBERED_ABBREVIATIONS_SPACING => false,
			Config::FRENCH_PUNCTUATION_SPACING     => false,
			Config::UNITS                          => [],
			Config::SPACE_COLLAPSE                 => false,
			Config::PREVENT_WIDOWS                 => false,
			Config::WIDOW_MIN_LENGTH               => 2,
			Config::WIDOW_MAX_PULL                 => 2,
			Config::WRAP_HYPHENS                   => false,
			Config::WRAP_EMAILS                    => false,
			Config::WRAP_URLS                      => false,
			Config::WRAP_MIN_AFTER                 => 2,
			Config::STYLE_AMPS                     => false,
			Config::STYLE_CAPS                     => false,
			Config::STYLE_NUMBERS                  => false,
			Config::STYLE_HANGING_PUNCTUATION      => false,
			Config::STYLE_INITIAL_QUOTES           => false,
			Config::INITIAL_QUOTE_TAGS             => [],
			Config::ENABLE_HYPHENATION             => true,
			Config::HYPHENATE_HEADINGS             => false,
			Config::HYPHENATE_CAPS                 => false,
			Config::HYPHENATE_TITLE_CASE           => false,
			Config::HYPHENATE_COMPOUNDS            => false,
			Config::HYPHENATE_LANGUAGES            => 'en-US',
			Config::HYPHENATE_MIN_LENGTH           => 2,
			Config::HYPHENATE_MIN_BEFORE           => 2,
			Config::HYPHENATE_MIN_AFTER            => 2,
			Config::HYPHENATE_EXCEPTIONS           => [],
			Config::IGNORE_PARSER_ERRORS           => false,
			Config::ENABLE_MULTILINGUAL_SUPPORT    => false,
		];

		$this->wp_typo->shouldReceive( 'get_config' )->once()->andReturn( $config );
		$this->transients->shouldReceive( 'get_large_object' )->once()->with( m::type( 'string' ), [ Settings::class ] )->andReturn( false );
		$this->wp_typo->shouldReceive( 'init_settings_from_options' )->once()->with( m::type( Settings::class ), $config );
		$this->transients->shouldReceive( 'cache_object' )->once()->with( m::type( 'string' ), m::type( Settings::class ), 'settings' );

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );

		$this->assertInstanceOf( Settings::class, $this->wp_typo->get_settings() );
	}

	/**
	 * Provides data for testing init_settings_from_options.
	 *
	 * @return mixed[]
	 */
	public function provide_init_settings_from_options_data(): array {
		return [
			'everything enabled'         => [ true, true, true, true ],
			'everything but smart chars' => [ false, true, true, true ],
			'everything but hyphenation' => [ true, false, true, true ],
			'no remapping'               => [ true, true, false, false ],
		];
	}

	/**
	 * Test init_settings_from_options.
	 *
	 * @covers ::init_settings_from_options
	 *
	 * @dataProvider provide_init_settings_from_options_data
	 *
	 * @param  bool $smart_chars                 Whether SMART_CHARACTERS is enabled.
	 * @param  bool $hyphenation                 Whether ENABLE_HYPHENATION is enabled.
	 * @param  bool $remap_hyphen                Whether REMAP_HYPHEN is enabled.
	 * @param  bool $remap_narrow_no_break_space Whether REMAP_NARROW_NO_BREAK_SPACE is enabled.
	 */
	public function test_init_settings_from_options( $smart_chars, $hyphenation, $remap_hyphen, $remap_narrow_no_break_space ): void {
		/**
		 * Settings mock.
		 *
		 * @var Settings&m\MockInterface
		 */
		$s      = m::mock( Settings::class );
		$config = [
			Config::IGNORE_TAGS                    => [ 'script' ],
			Config::IGNORE_CLASSES                 => [ 'noTypo' ],
			Config::IGNORE_IDS                     => [],
			Config::REMAP_NARROW_NO_BREAK_SPACE    => $remap_narrow_no_break_space,
			Config::REMAP_HYPHEN                   => $remap_hyphen,
			Config::SMART_CHARACTERS               => $smart_chars,
			Config::SMART_DASHES                   => false,
			Config::SMART_DASHES_STYLE             => 'international',
			Config::SMART_ELLIPSES                 => false,
			Config::SMART_MATH                     => false,
			Config::SMART_FRACTIONS                => false,
			Config::SMART_ORDINALS                 => false,
			Config::SMART_ORDINALS_ROMAN_NUMBERS   => false,
			Config::SMART_MARKS                    => false,
			Config::SMART_AREA_UNITS               => false,
			Config::SMART_QUOTES                   => false,
			Config::SMART_QUOTES_EXCEPTIONS        => [],
			Config::SMART_DIACRITICS               => false,
			Config::DIACRITIC_LANGUAGES            => 'en-US',
			Config::DIACRITIC_CUSTOM_REPLACEMENTS  => [],
			Config::SMART_QUOTES_PRIMARY           => 'doubleCurled',
			Config::SMART_QUOTES_SECONDARY         => 'singleCurled',
			Config::SINGLE_CHARACTER_WORD_SPACING  => false,
			Config::DASH_SPACING                   => false,
			Config::FRACTION_SPACING               => false,
			Config::UNIT_SPACING                   => false,
			Config::NUMBERED_ABBREVIATIONS_SPACING => false,
			Config::FRENCH_PUNCTUATION_SPACING     => false,
			Config::UNITS                          => [],
			Config::SPACE_COLLAPSE                 => false,
			Config::PREVENT_WIDOWS                 => false,
			Config::WIDOW_MIN_LENGTH               => 2,
			Config::WIDOW_MAX_PULL                 => 2,
			Config::WRAP_HYPHENS                   => false,
			Config::WRAP_EMAILS                    => false,
			Config::WRAP_URLS                      => false,
			Config::WRAP_MIN_AFTER                 => 2,
			Config::STYLE_AMPS                     => false,
			Config::STYLE_CAPS                     => false,
			Config::STYLE_NUMBERS                  => false,
			Config::STYLE_HANGING_PUNCTUATION      => false,
			Config::STYLE_INITIAL_QUOTES           => false,
			Config::INITIAL_QUOTE_TAGS             => [],
			Config::ENABLE_HYPHENATION             => $hyphenation,
			Config::HYPHENATE_HEADINGS             => false,
			Config::HYPHENATE_CAPS                 => false,
			Config::HYPHENATE_TITLE_CASE           => false,
			Config::HYPHENATE_COMPOUNDS            => false,
			Config::HYPHENATE_LANGUAGES            => 'en-US',
			Config::HYPHENATE_MIN_LENGTH           => 2,
			Config::HYPHENATE_MIN_BEFORE           => 2,
			Config::HYPHENATE_MIN_AFTER            => 2,
			Config::HYPHENATE_EXCEPTIONS           => [],
			Config::IGNORE_PARSER_ERRORS           => false,
			Config::ENABLE_MULTILINGUAL_SUPPORT    => false,
		];

		$s->shouldReceive( 'set_smart_dashes_style' )->once()->with( $config[ Config::SMART_DASHES_STYLE ] );
		$s->shouldReceive( 'set_smart_quotes_primary' )->once()->with( $config[ Config::SMART_QUOTES_PRIMARY ] );
		$s->shouldReceive( 'set_smart_quotes_secondary' )->once()->with( $config[ Config::SMART_QUOTES_SECONDARY ] );

		$s->shouldReceive( 'set_tags_to_ignore' )->once()->with( $config[ Config::IGNORE_TAGS ] );
		$s->shouldReceive( 'set_classes_to_ignore' )->once()->with( $config[ Config::IGNORE_CLASSES ] );
		$s->shouldReceive( 'set_ids_to_ignore' )->once()->with( $config[ Config::IGNORE_IDS ] );

		$s->shouldReceive( 'remap_character' )->once()->with( U::HYPHEN, m::type( 'string' ) );
		Filters\expectApplied( 'typo_narrow_no_break_space' )->once()->andReturn( $remap_narrow_no_break_space );
		$s->shouldReceive( 'remap_character' )->once()->with( U::NO_BREAK_NARROW_SPACE, m::type( 'string' ) );

		if ( $smart_chars ) {
			$s->shouldReceive( 'set_smart_dashes' )->once()->with( $config[ Config::SMART_DASHES ] );
			$s->shouldReceive( 'set_smart_ellipses' )->once()->with( $config[ Config::SMART_ELLIPSES ] );
			$s->shouldReceive( 'set_smart_math' )->once()->with( $config[ Config::SMART_MATH ] );
			$s->shouldReceive( 'set_smart_exponents' )->once()->with( $config[ Config::SMART_MATH ] );
			$s->shouldReceive( 'set_smart_fractions' )->once()->with( $config[ Config::SMART_FRACTIONS ] );
			$s->shouldReceive( 'set_smart_ordinal_suffix' )->once()->with( $config[ Config::SMART_ORDINALS ] );
			$s->shouldReceive( 'set_smart_ordinal_suffix_match_roman_numerals' )->once()->with( $config[ Config::SMART_ORDINALS_ROMAN_NUMBERS ] );
			$s->shouldReceive( 'set_smart_marks' )->once()->with( $config[ Config::SMART_MARKS ] );
			$s->shouldReceive( 'set_smart_area_units' )->once()->with( $config[ Config::SMART_AREA_UNITS ] );
			$s->shouldReceive( 'set_smart_quotes' )->once()->with( $config[ Config::SMART_QUOTES ] );
			$this->wp_typo->shouldReceive( 'prepare_smart_quotes_exceptions' )->once()->with( $config[ Config::SMART_QUOTES_EXCEPTIONS ] )->andReturn( [ 'prepared' => 'exceptions' ] );
			$s->shouldReceive( 'set_smart_quotes_exceptions' )->once()->with( m::type( 'array' ) );
			$s->shouldReceive( 'set_smart_diacritics' )->once()->with( $config[ Config::SMART_DIACRITICS ] );
			$s->shouldReceive( 'set_diacritic_language' )->once()->with( $config[ Config::DIACRITIC_LANGUAGES ] );
			$s->shouldReceive( 'set_diacritic_custom_replacements' )->once()->with( $config[ Config::DIACRITIC_CUSTOM_REPLACEMENTS ] );
		} else {
			$s->shouldReceive( 'set_smart_dashes' )->once()->with( false );
			$s->shouldReceive( 'set_smart_ellipses' )->once()->with( false );
			$s->shouldReceive( 'set_smart_math' )->once()->with( false );
			$s->shouldReceive( 'set_smart_exponents' )->once()->with( false );
			$s->shouldReceive( 'set_smart_fractions' )->once()->with( false );
			$s->shouldReceive( 'set_smart_ordinal_suffix' )->once()->with( false );
			$s->shouldReceive( 'set_smart_marks' )->once()->with( false );
			$s->shouldReceive( 'set_smart_area_units' )->once()->with( false );
			$s->shouldReceive( 'set_smart_quotes' )->once()->with( false );
			$s->shouldReceive( 'set_smart_diacritics' )->once()->with( false );

			$s->shouldReceive( 'set_smart_ordinal_suffix_match_roman_numerals' )->never();
			$this->wp_typo->shouldReceive( 'prepare_smart_quotes_exceptions' )->never();
			$s->shouldReceive( 'set_smart_quotes_exceptions' )->never();
			$s->shouldReceive( 'set_diacritic_language' )->never();
			$s->shouldReceive( 'set_diacritic_custom_replacements' )->never();
		}

		$s->shouldReceive( 'set_single_character_word_spacing' )->once()->with( $config[ Config::SINGLE_CHARACTER_WORD_SPACING ] );
		$s->shouldReceive( 'set_dash_spacing' )->once()->with( $config[ Config::DASH_SPACING ] );
		$s->shouldReceive( 'set_fraction_spacing' )->once()->with( $config[ Config::FRACTION_SPACING ] );
		$s->shouldReceive( 'set_unit_spacing' )->once()->with( $config[ Config::UNIT_SPACING ] );
		$s->shouldReceive( 'set_numbered_abbreviation_spacing' )->once()->with( $config[ Config::NUMBERED_ABBREVIATIONS_SPACING ] );
		$s->shouldReceive( 'set_french_punctuation_spacing' )->once()->with( $config[ Config::FRENCH_PUNCTUATION_SPACING ] );
		$s->shouldReceive( 'set_units' )->once()->with( $config[ Config::UNITS ] );
		$s->shouldReceive( 'set_space_collapse' )->once()->with( $config[ Config::SPACE_COLLAPSE ] );
		$s->shouldReceive( 'set_dewidow' )->once()->with( $config[ Config::PREVENT_WIDOWS ] );
		$s->shouldReceive( 'set_max_dewidow_length' )->once()->with( $config[ Config::WIDOW_MIN_LENGTH ] );
		$s->shouldReceive( 'set_max_dewidow_pull' )->once()->with( $config[ Config::WIDOW_MAX_PULL ] );
		$s->shouldReceive( 'set_dewidow_word_number' )->once()->with( 1 );

		$s->shouldReceive( 'set_wrap_hard_hyphens' )->once()->with( $config[ Config::WRAP_HYPHENS ] );
		$s->shouldReceive( 'set_email_wrap' )->once()->with( $config[ Config::WRAP_EMAILS ] );
		$s->shouldReceive( 'set_url_wrap' )->once()->with( $config[ Config::WRAP_URLS ] );
		$s->shouldReceive( 'set_min_after_url_wrap' )->once()->with( $config[ Config::WRAP_MIN_AFTER ] );

		$s->shouldReceive( 'set_style_ampersands' )->once()->with( $config[ Config::STYLE_AMPS ] );
		$s->shouldReceive( 'set_style_caps' )->once()->with( $config[ Config::STYLE_CAPS ] );
		$s->shouldReceive( 'set_style_numbers' )->once()->with( $config[ Config::STYLE_NUMBERS ] );
		$s->shouldReceive( 'set_style_hanging_punctuation' )->once()->with( $config[ Config::STYLE_HANGING_PUNCTUATION ] );
		$s->shouldReceive( 'set_style_initial_quotes' )->once()->with( $config[ Config::STYLE_INITIAL_QUOTES ] );
		$s->shouldReceive( 'set_initial_quote_tags' )->once()->with( $config[ Config::INITIAL_QUOTE_TAGS ] );

		if ( $hyphenation ) {
			$s->shouldReceive( 'set_hyphenation' )->once()->with( true );
			$s->shouldReceive( 'set_hyphenate_headings' )->once()->with( $config[ Config::HYPHENATE_HEADINGS ] );
			$s->shouldReceive( 'set_hyphenate_all_caps' )->once()->with( $config[ Config::HYPHENATE_TITLE_CASE ] );
			$s->shouldReceive( 'set_hyphenate_title_case' )->once()->with( $config[ Config::HYPHENATE_CAPS ] );
			$s->shouldReceive( 'set_hyphenate_compounds' )->once()->with( $config[ Config::HYPHENATE_COMPOUNDS ] );
			$s->shouldReceive( 'set_hyphenation_language' )->once()->with( $config[ Config::HYPHENATE_LANGUAGES ] );
			$s->shouldReceive( 'set_min_length_hyphenation' )->once()->with( $config[ Config::HYPHENATE_MIN_LENGTH ] );
			$s->shouldReceive( 'set_min_before_hyphenation' )->once()->with( $config[ Config::HYPHENATE_MIN_BEFORE ] );
			$s->shouldReceive( 'set_min_after_hyphenation' )->once()->with( $config[ Config::HYPHENATE_MIN_AFTER ] );
			$s->shouldReceive( 'set_hyphenation_exceptions' )->once()->with( $config[ Config::HYPHENATE_EXCEPTIONS ] );
		} else {
			$s->shouldReceive( 'set_hyphenation' )->once()->with( false );
		}

		Filters\expectApplied( 'typo_ignore_parser_errors' )->with( false )->once();
		$s->shouldReceive( 'set_ignore_parser_errors' )->once()->with( $config[ Config::IGNORE_PARSER_ERRORS ] );

		$this->invokeMethod( $this->wp_typo, 'init_settings_from_options', [ $s, $config ] );
	}

	/**
	 * Tests prepare_smart_quotes_exceptions.
	 *
	 * @covers ::prepare_smart_quotes_exceptions
	 *
	 * @uses WP_Typography\Settings\Tools::parse_smart_quote_exceptions_string
	 */
	public function test_prepare_smart_quotes_exceptions(): void {
		$custom_string = "rock 'n' roll";
		$expected_keys = [
			$custom_string,
			"'tain't",
			"'cause",
			"'round",
			"'twere",
			"'twill",
			"'bout",
			"'nuff",
			"'twas",
			"'til",
			"'tis",
			"'em",
		];

		Functions\when( '_x' )->returnArg();

		$result = $this->invokeMethod( $this->wp_typo, 'prepare_smart_quotes_exceptions', [ $custom_string ] );

		$this->assertSame( $expected_keys, \array_keys( $result ) );
	}

	/**
	 * Tests prepare_smart_quotes_exceptions.
	 *
	 * @covers ::prepare_smart_quotes_exceptions
	 *
	 * @uses WP_Typography\Settings\Tools::parse_smart_quote_exceptions_string
	 */
	public function test_prepare_smart_quotes_exceptions_wp_cockney_replace(): void {
		global $wp_cockneyreplace;

		$custom_string     = "rock 'n' roll";
		$wp_cockneyreplace = [ // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			"'foo"    => '’foo',
			"'foobar" => '’foobar',
		];
		$expected_keys     = [
			$custom_string,
			"'foobar",
			"'foo",
		];

		Functions\when( '_x' )->returnArg();

		$result = $this->invokeMethod( $this->wp_typo, 'prepare_smart_quotes_exceptions', [ $custom_string ] );

		$this->assertSame( $expected_keys, \array_keys( $result ) );
	}

	/**
	 * Test get_hyphenation_languages
	 *
	 * @covers ::get_hyphenation_languages
	 */
	public function test_get_hyphenation_languages(): void {
		$translate = true;
		$expected  = [
			'af' => 'Afghanisch',
			'de' => 'Deutsch',
			'en' => 'Englisch',
			'fu' => 'Foobar',
		];

		$this->wp_typo->shouldReceive( 'load_languages' )->once()->with( 'hyphenate_languages', [ PHP_Typography::class, 'get_hyphenation_languages' ], 'hyphenate', $translate )->andReturn( $expected );

		$this->assertSame( $expected, $this->wp_typo->get_hyphenation_languages( $translate ) );
	}

	/**
	 * Test get_diacritic_languages
	 *
	 * @covers ::get_diacritic_languages
	 */
	public function test_get_diacritic_languages(): void {
		$translate = true;
		$expected  = [
			'af' => 'Afghanisch',
			'de' => 'Deutsch',
			'en' => 'Englisch',
			'fu' => 'Foobar',
		];

		$this->wp_typo->shouldReceive( 'load_languages' )->once()->with( 'diacritic_languages', [ PHP_Typography::class, 'get_diacritic_languages' ], 'diacritic', $translate )->andReturn( $expected );

		$this->assertSame( $expected, $this->wp_typo->get_diacritic_languages( $translate ) );
	}

	/**
	 * Provides data for testing load_languages.
	 *
	 * @return mixed[]
	 */
	public function provide_load_languages_data(): array {
		return [
			'translated'   => [ 'some_cache_key', 'some_type', true ],
			'untranslated' => [ 'some_other_cache_key', 'some_other_type', false ],
		];
	}

	/**
	 * Test load_languages
	 *
	 * @dataProvider provide_load_languages_data
	 *
	 * @covers ::load_languages
	 *
	 * @param  string $cache_key The cache key.
	 * @param  string $type      The language list type (directory).
	 * @param  bool   $translate If the language names should be translated.
	 */
	public function test_load_languages( string $cache_key, string $type, bool $translate ): void {
		// Additional input data.
		$get_language_list = function () {}; // Noop, only used as parameter for maybe_load_untranslated_languages_from_disk.

		// Intermediate data.
		$langs_raw  = [
			'af' => 'Afghan',
			'de' => 'German',
			'en' => 'English',
			'fu' => 'Foobar',
		];
		$langs_i18n = [
			'af' => 'Afghanisch',
			'de' => 'Deutsch',
			'en' => 'Englisch',
			'fu' => 'Foobar',
		];
		$result     = $translate ? $langs_i18n : $langs_raw;

		if ( $translate ) {
			// Convoluted syntax necessary because of argument-by-reference.
			$this->cache->shouldReceive( 'get' )->once()->withArgs(
				function ( $key ) use ( $cache_key ) {
					return $key === $cache_key;
				}
			)->andReturnUsing(
				function ( $key, &$found ) {
					$found = false;
					return [];
				}
			);

			$this->cache->shouldReceive( 'set' )->once()->with( $cache_key, $result, m::type( 'int' ) );
			$this->wp_typo->shouldReceive( 'translate_languages' )->once()->with( $langs_raw )->andReturn( $langs_i18n );
		} else {
			$this->cache->shouldReceive( 'get' )->never();
			$this->wp_typo->shouldReceive( 'translate_languages' )->never();
			$this->cache->shouldReceive( 'set' )->never();
		}
		$this->wp_typo->shouldReceive( 'maybe_load_untranslated_languages_from_disk' )->once()->with( $cache_key, $get_language_list, $type )->andReturn( $langs_raw );

		$this->assertSame( $result, $this->wp_typo->load_languages( $cache_key, $get_language_list, $type, $translate ) ); // @phpstan-ignore method.protected
	}

	/**
	 * Test load_languages
	 *
	 * @dataProvider provide_load_languages_data
	 *
	 * @covers ::load_languages
	 *
	 * @param string $cache_key The cache key.
	 * @param string $type      The language list type (directory).
	 */
	public function test_load_translated_languages_cached( string $cache_key, string $type ): void {
		// Additional input data.
		$get_language_list = function () {}; // Noop, only used as parameter for maybe_load_untranslated_languages_from_disk.

		// Intermediate data.
		$langs_i18n = [
			'af' => 'Afghanisch',
			'de' => 'Deutsch',
			'en' => 'Englisch',
			'fu' => 'Foobar',
		];

		// Convoluted syntax necessary because of argument-by-reference.
		$this->cache->shouldReceive( 'get' )->once()->withArgs(
			function ( $key ) use ( $cache_key ) {
				return $key === $cache_key;
			}
		)->andReturnUsing(
			function ( $key, &$found ) use ( $langs_i18n ) {
				$found = true;
				return $langs_i18n;
			}
		);

		$this->wp_typo->shouldReceive( 'maybe_load_untranslated_languages_from_disk' )->never();
		$this->wp_typo->shouldReceive( 'translate_languages' )->never();
		$this->cache->shouldReceive( 'set' )->never();

		$this->assertSame( $langs_i18n, $this->wp_typo->load_languages( $cache_key, $get_language_list, $type, true ) ); // @phpstan-ignore method.protected
	}

	/**
	 * Test maybe_load_untranslated_languages_from_disk
	 *
	 * @dataProvider provide_load_languages_data
	 *
	 * @covers ::maybe_load_untranslated_languages_from_disk
	 *
	 * @param string $cache_key The cache key.
	 * @param string $type      The language list type (directory).
	 */
	public function test_maybe_load_untranslated_languages_from_disk( string $cache_key, string $type ): void {
		/**
		 * Fake callable.
		 *
		 * @var callable $get_language_list
		 */
		$get_language_list = [ $this->wp_typo, 'get_language_list_mock' ];

		// Intermediate data.
		$cache_key_raw = "{$cache_key}_raw";
		$langs_raw     = [
			'af' => 'Afghan',
			'de' => 'German',
			'en' => 'English',
			'fu' => 'Foobar',
		];

		// Convoluted syntax necessary because of argument-by-reference.
		$this->cache->shouldReceive( 'get' )->once()->withArgs(
			function ( $key ) use ( $cache_key_raw ) {
				return $key === $cache_key_raw;
			}
		)->andReturnUsing(
			function ( $key, &$found ) {
				$found = false;
				return [];
			}
		);
		$this->wp_typo->shouldReceive( 'get_language_list_mock' )->once()->andReturn( $langs_raw );
		$this->cache->shouldReceive( 'set' )->once()->with( $cache_key_raw, $langs_raw, m::type( 'int' ) );

		$this->assertSame( $langs_raw, $this->wp_typo->maybe_load_untranslated_languages_from_disk( $cache_key, $get_language_list, $type ) ); // @phpstan-ignore method.protected
	}

	/**
	 * Test maybe_load_untranslated_languages_from_disk
	 *
	 * @dataProvider provide_load_languages_data
	 *
	 * @covers ::maybe_load_untranslated_languages_from_disk
	 *
	 * @param string $cache_key The cache key.
	 * @param string $type      The language list type (directory).
	 */
	public function test_maybe_load_untranslated_languages_from_disk_cached( string $cache_key, string $type ): void {
		/**
		 * Fake callable.
		 *
		 * @var callable $get_language_list
		 */
		$get_language_list = [ $this->wp_typo, 'get_language_list_mock' ];

		// Intermediate data.
		$cache_key_raw = "{$cache_key}_raw";
		$langs_raw     = [
			'af' => 'Afghan',
			'de' => 'German',
			'en' => 'English',
			'fu' => 'Foobar',
		];

		// Convoluted syntax necessary because of argument-by-reference.
		$this->cache->shouldReceive( 'get' )->once()->withArgs(
			function ( $key ) use ( $cache_key_raw ) {
				return $key === $cache_key_raw;
			}
		)->andReturnUsing(
			function ( $key, &$found ) use ( $langs_raw ) {
				$found = true;
				return $langs_raw;
			}
		);
		$this->wp_typo->shouldReceive( 'get_language_list_mock' )->never();
		$this->cache->shouldReceive( 'set' )->never();

		$this->assertSame( $langs_raw, $this->wp_typo->maybe_load_untranslated_languages_from_disk( $cache_key, $get_language_list, $type ) ); // @phpstan-ignore method.protected
	}

	/**
	 * Test translate_languages.
	 *
	 * @covers ::translate_languages
	 */
	public function test_translate_languages(): void {
		// Input data.
		$input = [
			'af' => 'Afghan',
			'de' => 'German',
			'en' => 'English',
			'fu' => 'Foobar',
		];

		// Expected output.
		$result = [
			'af' => 'Afghanisch',
			'fu' => 'Barfoo', // Re-sorted!
			'de' => 'Deutsch',
			'en' => 'Englisch',
		];

		Functions\expect( '_x' )->once()->with( 'Afghan', 'language name', 'wp-typography' )->andReturn( 'Afghanisch' );
		Functions\expect( '_x' )->once()->with( 'German', 'language name', 'wp-typography' )->andReturn( 'Deutsch' );
		Functions\expect( '_x' )->once()->with( 'English', 'language name', 'wp-typography' )->andReturn( 'Englisch' );
		Functions\expect( '_x' )->once()->with( 'Foobar', 'language name', 'wp-typography' )->andReturn( 'Barfoo' ); // To force re-sort.

		$this->assertSame( $result, $this->wp_typo->translate_languages( $input ) ); // @phpstan-ignore method.protected
	}

	/**
	 * Test process_title.
	 *
	 * @covers ::process_title
	 */
	public function test_process_title(): void {
		$this->wp_typo->shouldReceive( 'process' )->once()->with( 'foobar', true, false, null )->andReturn( 'barfoo' );

		$this->assertSame( 'barfoo', $this->wp_typo->process_title( 'foobar', null ) );
	}

	/**
	 * Test process_feed.
	 *
	 * @covers ::process_feed
	 */
	public function test_process_feed(): void {
		$this->wp_typo->shouldReceive( 'process' )->once()->with( 'foobar', true, true, null )->andReturn( 'barfoo' );

		$this->assertSame( 'barfoo', $this->wp_typo->process_feed( 'foobar', true, null ) );
	}

	/**
	 * Test process_feed_title.
	 *
	 * @covers ::process_feed_title
	 */
	public function test_process_feed_title(): void {
		$this->wp_typo->shouldReceive( 'process_feed' )->once()->with( 'foobar', true, null )->andReturn( 'barfoo' );

		$this->assertSame( 'barfoo', $this->wp_typo->process_feed_title( 'foobar', null ) );
	}


	/**
	 * Test process_title_parts.
	 *
	 * @covers ::process_title_parts
	 */
	public function test_process_title_parts(): void {
		$title_parts = [
			'fo' . U::SOFT_HYPHEN . 'o',
			'bar',
			'baz',
		];

		Functions\expect( 'wp_strip_all_tags' )->times( \count( $title_parts ) )->andReturnUsing(
			function ( $arg ) {
				return \strip_tags( $arg ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
			}
		);
		foreach ( $title_parts as $part ) {
			$this->wp_typo->shouldReceive( 'process' )->once()->with( $part, true, true, null )->andReturn( $part . $part );
		}

		$this->assertSame( [ 'foofoo', 'barbar', 'bazbaz' ], $this->wp_typo->process_title_parts( $title_parts, null ) );
	}

	/**
	 * Provide data for testing process.
	 *
	 * @return mixed[]
	 */
	public function provide_process_data(): array {
		return [
			[ true, true, true, null ],
			[ false, false, false, null ],
			[ false, true, false, null ],
			[ false, false, true, null ],
			[ true, false, true, null ],
			[ false, false, false, m::mock( Settings::class ) ],
		];
	}

	/**
	 * Test process
	 *
	 * @covers ::process
	 *
	 * @uses ::set_instance
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param  bool     $is_title   Fragment is a title.
	 * @param  bool     $force_feed Enforce feed processing.
	 * @param  bool     $is_feed    Value for is_feed().
	 * @param  Settings $settings   May be null.
	 */
	public function test_process( $is_title, $force_feed, $is_feed, $settings = null ): void {
		$post_id = false; // Not in the loop.

		Functions\expect( 'is_feed' )->andReturn( $is_feed );
		Functions\expect( 'get_the_ID' )->once()->andReturn( $post_id );
		Functions\expect( 'get_post_meta' )->never();

		Filters\expectApplied( 'typo_disable_processing_for_post' )->once()->with( m::type( 'bool' ), $post_id )->andReturnFirstArg();

		if ( null === $settings ) {
			$settings_mock = m::mock( Settings::class );

			$this->wp_typo->shouldReceive( 'get_settings' )->once()->andReturn( $settings_mock );
		} else {
			$settings_mock = $settings;
		}

		Filters\expectApplied( 'typo_settings' )->once()->with( m::type( Settings::class ) )->andReturnFirstArg();

		$this->wp_typo->shouldReceive( 'maybe_process_fragment' )
			->once()
			->with( 'text', $is_title, $force_feed || $is_feed, $settings_mock )
			->andReturn( 'processed text' );

		$this->assertSame( 'processed text', $this->wp_typo->process( 'text', $is_title, $force_feed, $settings ) );
	}

	/**
	 * Test process
	 *
	 * @covers ::process
	 *
	 * @uses ::set_instance
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param  bool     $is_title   Fragment is a title.
	 * @param  bool     $force_feed Enforce feed processing.
	 * @param  bool     $is_feed    Value for is_feed().
	 * @param  Settings $settings   May be null.
	 */
	public function test_process_disabled( $is_title, $force_feed, $is_feed, $settings = null ): void {
		$text    = 'my text';
		$post_id = 67;

		Functions\expect( 'is_feed' )->andReturn( $is_feed );
		Functions\expect( 'get_the_ID' )->once()->andReturn( $post_id );
		Functions\expect( 'get_post_meta' )->once()->with( $post_id, REST_API::WP_TYPOGRAPHY_DISABLED_META_KEY, true )->andReturn( true );

		Filters\expectApplied( 'typo_disable_processing_for_post' )->once()->with( true, $post_id )->andReturnFirstArg();

		$this->wp_typo->shouldReceive( 'get_settings' )->never();

		Filters\expectApplied( 'typo_settings' )->never();

		$this->wp_typo->shouldReceive( 'maybe_process_fragment' )->never();

		$this->assertSame( $text, $this->wp_typo->process( $text, $is_title, $force_feed, $settings ) );
	}

	/**
	 * Test process
	 *
	 * @covers ::process
	 *
	 * @uses ::set_instance
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param  bool     $is_title   Fragment is a title.
	 * @param  bool     $force_feed Enforce feed processing.
	 * @param  bool     $is_feed    Value for is_feed().
	 * @param  Settings $settings   May be null.
	 */
	public function test_process_disabled_null( $is_title, $force_feed, $is_feed, $settings = null ): void {
		$post_id = 67;

		Functions\expect( 'is_feed' )->andReturn( $is_feed );
		Functions\expect( 'get_the_ID' )->once()->andReturn( $post_id );
		Functions\expect( 'get_post_meta' )->once()->with( $post_id, REST_API::WP_TYPOGRAPHY_DISABLED_META_KEY, true )->andReturn( true );

		Filters\expectApplied( 'typo_disable_processing_for_post' )->once()->with( true, $post_id )->andReturnFirstArg();

		$this->wp_typo->shouldReceive( 'get_settings' )->never();

		Filters\expectApplied( 'typo_settings' )->never();

		$this->wp_typo->shouldReceive( 'maybe_process_fragment' )->never();

		$this->assertSame( '', $this->wp_typo->process( null, $is_title, $force_feed, $settings ) ); // @phpstan-ignore argument.type
	}

	/**
	 * Provide data for testing process.
	 *
	 * @return mixed[]
	 */
	public function provide_maybe_process_fragment_data(): array {
		return [
			[ false, false ],
			[ false, true ],
			[ true, false ],
			[ true, true ],
		];
	}

	/**
	 * Test maybe_process_fragment
	 *
	 * @covers ::maybe_process_fragment
	 *
	 * @dataProvider provide_maybe_process_fragment_data
	 *
	 * @param  bool $is_title Fragment is a title.
	 * @param  bool $is_feed  Value for is_feed().
	 */
	public function test_maybe_process_fragment( $is_title, $is_feed ): void {
		/**
		 * Settings mock.
		 *
		 * @var Settings&m\MockInterface
		 */
		$settings = m::mock( Settings::class );
		$text     = 'some text or other';

		// Fake filter_body_classes.
		$this->set_value( $this->wp_typo, 'body_classes', [ 'foo', 'bar' ] );

		$settings->shouldReceive( 'get_hash' )->once()->with( m::type( 'int' ), false )->andReturn( '3858f62230ac3c915f300c664312c63f' );

		Filters\expectApplied( 'typo_processed_text_caching_duration' )->once()->with( m::type( 'int' ) )->andReturn( 5 );

		$process_method = $is_feed ? 'process_feed' : 'process';
		$typo_mock      = m::mock( \PHP_Typography\PHP_Typography::class );
		$this->wp_typo->shouldReceive( 'get_typography_instance' )->once()->andReturn( $typo_mock );

		$typo_mock->shouldReceive( $process_method )->once()->with( $text, m::type( Settings::class ), $is_title, [ 'foo', 'bar' ] )->andReturn( 'processed text' );

		$this->cache // @phpstan-ignore method.notFound
			->shouldReceive( 'get' )->once()->andReturn( false )
			->shouldReceive( 'set' )->once();

		$this->assertSame( 'processed text', $this->invokeMethod( $this->wp_typo, 'maybe_process_fragment', [ $text, $is_title, $is_feed, $settings ] ) );
	}

	/**
	 * Tests filter_body_class
	 *
	 * @covers ::filter_body_class
	 */
	public function test_filter_body_class(): void {
		$classes = [ 'foo', 'bar' ];

		$result = $this->wp_typo->filter_body_class( $classes );

		$this->assertSame( $classes, $result );
		$this->assert_attribute_same( $classes, 'body_classes', $this->wp_typo );
	}

	/**
	 * Test set_default_options.
	 *
	 * @covers ::set_default_options
	 *
	 * @uses ::set_instance
	 * @uses ::get_default_options
	 */
	public function test_set_default_options(): void {
		$this->wp_typo->shouldReceive( 'get_default_options' )->once()->andReturn( [ 'foo' => 'bar' ] );

		$this->options->shouldReceive( 'set' )->once()->with( Options::CONFIGURATION, m::type( 'array' ) );

		$this->wp_typo->set_default_options();
	}

	/**
	 * Test set_default_options.
	 *
	 * @covers ::set_default_options
	 *
	 * @uses ::set_instance
	 * @uses ::get_default_options
	 */
	public function test_set_default_options_force_defaults(): void {
		$this->wp_typo->shouldReceive( 'get_default_options' )->once()->andReturn( [ 'foo' => 'bar' ] );

		$this->options->shouldNotReceive( 'get' )->with( Options::RESTORE_DEFAULTS );
		$this->options->shouldReceive( 'set' )->once()->with( Options::CONFIGURATION, m::type( 'array' ) );
		$this->options->shouldReceive( 'delete' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( true );
		$this->options->shouldReceive( 'delete' )->once()->with( Options::CLEAR_CACHE )->andReturn( true );

		$this->wp_typo->set_default_options( true );
	}


	/**
	 * Test get_default_options.
	 *
	 * @covers ::get_default_options
	 *
	 * @uses ::set_instance
	 *
	 * @runInSeparateProcess
	 */
	public function test_get_default_options(): void {
		Functions\expect( 'wp_list_pluck' )->once()->with( m::type( 'array' ), 'default' )->andReturn( [ 'bar' => 'foo' ] );
		Functions\expect( '__' )->atLeast()->once()->andReturn( 'translated_string' );

		$this->assert_is_array( $this->wp_typo->get_default_options() );
	}

	/**
	 * Test clear_cache.
	 *
	 * @covers ::clear_cache
	 */
	public function test_clear_cache(): void {
		$this->transients->shouldReceive( 'invalidate' );
		$this->cache->shouldReceive( 'invalidate' );

		$this->options->shouldReceive( 'delete' )->once()->with( 'clear_cache' )->andReturn( true );

		$this->wp_typo->clear_cache();
	}


	/**
	 * Test parser_errors_handler.
	 *
	 * @covers ::parser_errors_handler
	 */
	public function test_parser_errors_handler(): void {
		$this->wp_typo->parser_errors_handler( [] );
		$this->assertTrue( 1 === Filters\applied( 'typo_handle_parser_errors' ) );
	}

	/**
	 * Test get_version.
	 *
	 * @covers ::get_version
	 */
	public function test_get_version(): void {
		$this->assert_is_string( $this->wp_typo->get_version() );
	}

	/**
	 * Provide data for testing save_hyphenator_cache_on_shutdown.
	 *
	 * @return mixed[]
	 */
	public function provide_save_hyphenator_cache_on_shutdown_data(): array {
		return [
			[ true, m::mock( Hyphenator_Cache::class ), true ],
			[ false, m::mock( Hyphenator_Cache::class ), false ],
			[ true, null, false ],
			[ false, null, false ],
		];
	}

	/**
	 * Test save_hyphenator_cache_on_shutdown.
	 *
	 * @covers ::save_hyphenator_cache_on_shutdown
	 *
	 * @dataProvider provide_save_hyphenator_cache_on_shutdown_data
	 *
	 * @param bool                  $enable_hyphenation The typo_enable_hyphenation value.
	 * @param Hyphenator_Cache|null $hyphenator_cache   The hyphenator cache instance.
	 * @param bool                  $expected           If the hyphenator cache should be saved.
	 *
	 * @phpstan-param (Hyphenator_Cache&m\MockInterface)|null $hyphenator_cache
	 */
	public function test_save_hyphenator_cache_on_shutdown( $enable_hyphenation, $hyphenator_cache, $expected ): void {

		if ( null !== $hyphenator_cache ) {
			$hyphenator_cache->shouldReceive( 'has_changed' )->andReturn( $expected );
			$this->setValue( $this->wp_typo, 'hyphenator_cache', $hyphenator_cache );
		}

		if ( $expected ) {
			$this->transients->shouldReceive( 'cache_object' )->once();
		} else {
			$this->transients->shouldReceive( 'cache_object' )->never();
		}

		$this->wp_typo->save_hyphenator_cache_on_shutdown();
	}
}
