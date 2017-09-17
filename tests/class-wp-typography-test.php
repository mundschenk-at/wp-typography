<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

use WP_Typography\Admin;

use PHP_Typography\Hyphenator_Cache;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography unit test.
 *
 * @coversDefaultClass \WP_Typography
 * @usesDefaultClass \WP_Typography
 *
 * @uses ::__construct
 * @uses ::hash_version_string
 */
class WP_Typography_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography
	 */
	protected $wp_typo;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Admin
	 */
	protected $wp_typo_admin;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Settings\Multilingual
	 */
	protected $multi;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Transients
	 */
	protected $transients;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Cache
	 */
	protected $cache;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine

		// Mock WP_Typography\Admin instance.
		$this->wp_typo_admin = m::mock( \WP_Typography\Admin::class )
			->shouldReceive( 'run' )->byDefault()
			->shouldReceive( 'get_default_settings' )->andReturn( [ 'dummy_settings' => 'bar' ] )->byDefault()
			->getMock();

		// Mock WP_Typography\Settings\Multlingual instance.
		$this->multi = m::mock( \WP_Typography\Settings\Multilingual::class )
			->shouldReceive( 'run' )->byDefault()
			->shouldReceive( 'filter_defaults' )->andReturnUsing( function( array $defaults ) {
				return $defaults;
			} )->byDefault()
			->getMock();

		// Mock WP_Typography\Transients instance.
		$this->transients = m::mock( \WP_Typography\Transients::class )
			->shouldReceive( 'get' )->byDefault()->andReturn( false )
			->shouldReceive( 'get_large_object' )->byDefault()->andReturn( false )
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'set_large_object' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Cache instance.
		$this->cache = m::mock( \WP_Typography\Cache::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'invalidate' )->byDefault()
			->getMock();

		// Create instance.
		$this->wp_typo = m::mock( \WP_Typography::class, [ '7.7.7', 'dummy/path', $this->wp_typo_admin, $this->multi, $this->transients, $this->cache ] )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		parent::setUp();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, '_instance', null );

		parent::tearDown();
	}

	/**
	 * Prepare WP_Typography options for a test.
	 *
	 * @param array $options An array of set options.
	 */
	protected function prepareOptions( array $options ) {  // @codingStandardsIgnoreLine
		// Reset options.
		$this->setValue( $this->wp_typo, 'options', $options );
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses \WP_Typography\Admin::__construct
	 * @uses \WP_Typography\Abstract_Cache::__construct
	 * @uses \WP_Typography\Cache::__construct
	 * @uses \WP_Typography\Cache::invalidate
	 * @uses \WP_Typography\Transients::__construct
	 * @uses \WP_Typography\Transients::invalidate
	 * @uses \WP_Typography\Settings\Multilingual::__construct
	 */
	public function test_constructor() {
		Functions\expect( 'get_option' )->once()->with( 'typo_transients_keys', [] )->andReturn( [] );
		Functions\expect( 'update_option' )->once()->with( 'typo_transients_keys', [] )->andReturn( [] );
		Functions\expect( 'get_transient' )->once()->with( 'typo_transients_incrementor' )->andReturn( false );
		Functions\expect( 'set_transient' )->once()->andReturn( 0 );
		Functions\expect( 'wp_cache_get' )->once()->with( 'typo_cache_incrementor', 'wp-typography' )->andReturn( 0 );
		Functions\expect( 'wp_cache_set' )->once()->with( 'typo_cache_incrementor', m::type( 'int' ), 'wp-typography', 0 )->andReturn( true );

		$typo = new \WP_Typography( '6.6.6', 'dummy/path', m::mock( \WP_Typography\Admin::class ), m::mock( \WP_Typography\Settings\Multilingual::class ) );

		$this->assertInstanceOf( \WP_Typography::class, $typo );
		$this->assertAttributeInstanceOf( \WP_Typography\Admin::class, 'admin', $typo );
		$this->assertAttributeSame( '6.6.6', 'version', $typo );
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::run
	 *
	 * @uses ::__construct
	 * @uses ::set_instance
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography\Admin::__construct
	 */
	public function test_run() {
		$this->wp_typo->run();

		$this->assertTrue( has_action( 'plugins_loaded', get_class( $this->wp_typo ) . '->plugins_loaded()', 10 ) );
		$this->assertTrue( has_action( 'init',           get_class( $this->wp_typo ) . '->init()',           10 ) );
		$this->assertAttributeInternalType( 'array', 'default_settings', $this->wp_typo );
	}

	/**
	 * Test get_user_settings.
	 *
	 * @covers ::get_user_settings
	 *
	 * @uses ::get_settings
	 * @uses ::init_settings_from_options
	 * @uses ::get_instance
	 * @uses ::cache_object
	 * @uses ::init_settings_from_options
	 * @uses ::maybe_fix_object
	 */
	public function test_get_user_settings() {
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );

		$this->prepareOptions( [
			'typo_ignore_tags'                    => [ 'script' ],
			'typo_ignore_classes'                 => [ 'noTypo' ],
			'typo_ignore_ids'                     => [],
			'typo_smart_characters'               => true,
			'typo_smart_dashes'                   => false,
			'typo_smart_dashes_style'             => 'international',
			'typo_smart_ellipses'                 => false,
			'typo_smart_math'                     => false,
			'typo_smart_fractions'                => false,
			'typo_smart_ordinals'                 => false,
			'typo_smart_marks'                    => false,
			'typo_smart_quotes'                   => false,
			'typo_smart_diacritics'               => false,
			'typo_diacritic_languages'            => 'en-US',
			'typo_diacritic_custom_replacements'  => [],
			'typo_smart_quotes_primary'           => 'doubleCurled',
			'typo_smart_quotes_secondary'         => 'singleCurled',
			'typo_single_character_word_spacing'  => false,
			'typo_dash_spacing'                   => false,
			'typo_fraction_spacing'               => false,
			'typo_unit_spacing'                   => false,
			'typo_numbered_abbreviations_spacing' => false,
			'typo_french_punctuation_spacing'     => false,
			'typo_units'                          => [],
			'typo_space_collapse'                 => false,
			'typo_prevent_widows'                 => false,
			'typo_widow_min_length'               => 2,
			'typo_widow_max_pull'                 => 2,
			'typo_wrap_hyphens'                   => false,
			'typo_wrap_emails'                    => false,
			'typo_wrap_urls'                      => false,
			'typo_wrap_min_after'                 => 2,
			'typo_style_amps'                     => false,
			'typo_style_caps'                     => false,
			'typo_style_numbers'                  => false,
			'typo_wrap_urls'                      => false,
			'typo_style_hanging_punctuation'      => false,
			'typo_style_initial_quotes'           => false,
			'typo_initial_quote_tags'             => [],
			'typo_enable_hyphenation'             => true,
			'typo_hyphenate_headings'             => false,
			'typo_hyphenate_caps'                 => false,
			'typo_hyphenate_title_case'           => false,
			'typo_hyphenate_compounds'            => false,
			'typo_hyphenate_languages'            => 'en-US',
			'typo_hyphenate_min_length'           => 2,
			'typo_hyphenate_min_before'           => 2,
			'typo_hyphenate_min_after'            => 2,
			'typo_hyphenate_exceptions'           => [],
			'typo_ignore_parser_errors'           => false,
			'typo_enable_multilingual_support'    => false,
		] );

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );

		$s = \WP_Typography::get_user_settings();

		$this->assertInstanceOf( \PHP_Typography\Settings::class, $s );
		$this->assertAttributeNotSame( $s, 'typo_settings', $this->wp_typo );

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, '_instance', null );
	}

	/**
	 * Test get_typography_instance.
	 *
	 * @covers ::get_typography_instance
	 *
	 * @uses ::maybe_fix_object
	 */
	public function test_get_typography_instance() {
		$this->prepareOptions( [
			'typo_enable_hyphenation' => true,
		] );

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );

		$this->wp_typo->shouldReceive( 'cache_object' )->twice();

		$this->assertInstanceOf( \PHP_Typography\PHP_Typography::class, $this->invokeMethod( $this->wp_typo, 'get_typography_instance' ) );
	}

	/**
	 * Test get_user_settings.
	 *
	 * @covers ::get_settings
	 * @covers ::init_settings_from_options
	 *
	 * @uses ::cache_object
	 * @uses ::init_settings_from_options
	 * @uses ::maybe_fix_object
	 */
	public function test_get_settings() {

		$this->prepareOptions( [
			'typo_ignore_tags'                    => [ 'script' ],
			'typo_ignore_classes'                 => [ 'noTypo' ],
			'typo_ignore_ids'                     => [],
			'typo_smart_characters'               => true,
			'typo_smart_dashes'                   => false,
			'typo_smart_dashes_style'             => 'international',
			'typo_smart_ellipses'                 => false,
			'typo_smart_math'                     => false,
			'typo_smart_fractions'                => false,
			'typo_smart_ordinals'                 => false,
			'typo_smart_marks'                    => false,
			'typo_smart_quotes'                   => false,
			'typo_smart_diacritics'               => false,
			'typo_diacritic_languages'            => 'en-US',
			'typo_diacritic_custom_replacements'  => [],
			'typo_smart_quotes_primary'           => 'doubleCurled',
			'typo_smart_quotes_secondary'         => 'singleCurled',
			'typo_single_character_word_spacing'  => false,
			'typo_dash_spacing'                   => false,
			'typo_fraction_spacing'               => false,
			'typo_unit_spacing'                   => false,
			'typo_numbered_abbreviations_spacing' => false,
			'typo_french_punctuation_spacing'     => false,
			'typo_units'                          => [],
			'typo_space_collapse'                 => false,
			'typo_prevent_widows'                 => false,
			'typo_widow_min_length'               => 2,
			'typo_widow_max_pull'                 => 2,
			'typo_wrap_hyphens'                   => false,
			'typo_wrap_emails'                    => false,
			'typo_wrap_urls'                      => false,
			'typo_wrap_min_after'                 => 2,
			'typo_style_amps'                     => false,
			'typo_style_caps'                     => false,
			'typo_style_numbers'                  => false,
			'typo_wrap_urls'                      => false,
			'typo_style_hanging_punctuation'      => false,
			'typo_style_initial_quotes'           => false,
			'typo_initial_quote_tags'             => [],
			'typo_enable_hyphenation'             => true,
			'typo_hyphenate_headings'             => false,
			'typo_hyphenate_caps'                 => false,
			'typo_hyphenate_title_case'           => false,
			'typo_hyphenate_compounds'            => false,
			'typo_hyphenate_languages'            => 'en-US',
			'typo_hyphenate_min_length'           => 2,
			'typo_hyphenate_min_before'           => 2,
			'typo_hyphenate_min_after'            => 2,
			'typo_hyphenate_exceptions'           => [],
			'typo_ignore_parser_errors'           => false,
			'typo_enable_multilingual_support'    => false,
		] );

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );

		$s = $this->wp_typo->get_settings();

		$this->assertInstanceOf( \PHP_Typography\Settings::class, $s );
	}

	/**
	 * Test get_user_settings.
	 *
	 * @covers ::get_settings
	 * @covers ::init_settings_from_options
	 *
	 * @uses ::cache_object
	 * @uses ::init_settings_from_options
	 * @uses ::maybe_fix_object
	 */
	public function test_get_settings_off() {

		$this->prepareOptions( [
			'typo_ignore_tags'                    => [ 'script' ],
			'typo_ignore_classes'                 => [ 'noTypo' ],
			'typo_ignore_ids'                     => [],
			'typo_smart_characters'               => false,
			'typo_smart_dashes'                   => false,
			'typo_smart_dashes_style'             => 'international',
			'typo_smart_ellipses'                 => false,
			'typo_smart_math'                     => false,
			'typo_smart_fractions'                => false,
			'typo_smart_ordinals'                 => false,
			'typo_smart_marks'                    => false,
			'typo_smart_quotes'                   => false,
			'typo_smart_diacritics'               => false,
			'typo_diacritic_languages'            => 'en-US',
			'typo_diacritic_custom_replacements'  => [],
			'typo_smart_quotes_primary'           => 'doubleCurled',
			'typo_smart_quotes_secondary'         => 'singleCurled',
			'typo_single_character_word_spacing'  => false,
			'typo_dash_spacing'                   => false,
			'typo_fraction_spacing'               => false,
			'typo_unit_spacing'                   => false,
			'typo_numbered_abbreviations_spacing' => false,
			'typo_french_punctuation_spacing'     => false,
			'typo_units'                          => [],
			'typo_space_collapse'                 => false,
			'typo_prevent_widows'                 => false,
			'typo_widow_min_length'               => 2,
			'typo_widow_max_pull'                 => 2,
			'typo_wrap_hyphens'                   => false,
			'typo_wrap_emails'                    => false,
			'typo_wrap_urls'                      => false,
			'typo_wrap_min_after'                 => 2,
			'typo_style_amps'                     => false,
			'typo_style_caps'                     => false,
			'typo_style_numbers'                  => false,
			'typo_wrap_urls'                      => false,
			'typo_style_hanging_punctuation'      => false,
			'typo_style_initial_quotes'           => false,
			'typo_initial_quote_tags'             => [],
			'typo_enable_hyphenation'             => false,
			'typo_hyphenate_headings'             => false,
			'typo_hyphenate_caps'                 => false,
			'typo_hyphenate_title_case'           => false,
			'typo_hyphenate_compounds'            => false,
			'typo_hyphenate_languages'            => 'en-US',
			'typo_hyphenate_min_length'           => 2,
			'typo_hyphenate_min_before'           => 2,
			'typo_hyphenate_min_after'            => 2,
			'typo_hyphenate_exceptions'           => [],
			'typo_ignore_parser_errors'           => false,
		] );

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );

		$s = $this->wp_typo->get_settings();

		$this->assertInstanceOf( \PHP_Typography\Settings::class, $s );
	}

	/**
	 * Test get_hyphenation_languages
	 *
	 * @covers ::get_hyphenation_languages
	 *
	 * @uses ::get_instance
	 */
	public function test_get_hyphenation_languages() {
		// Set up singleton.
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );

		$this->wp_typo->shouldReceive( 'load_hyphenation_languages' )->once()->andReturn( [ 'de' => 'German' ] );

		$langs = \WP_Typography::get_hyphenation_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
	}

	/**
	 * Test get_diacritic_languages
	 *
	 * @covers ::get_diacritic_languages
	 *
	 * @uses ::get_instance
	 */
	public function test_get_diacritic_languages() {
		// Set up singleton.
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );

		$this->wp_typo->shouldReceive( 'load_diacritic_languages' )->once()->andReturn( [ 'de' => 'German' ] );

		$langs = \WP_Typography::get_diacritic_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
	}

	/**
	 * Test get_hyphenation_languages
	 *
	 * @covers ::load_hyphenation_languages
	 * @covers ::load_languages
	 * @covers ::translate_languages
	 *
	 * @uses \PHP_Typography\PHP_Typography::get_hyphenation_languages
	 */
	public function test_load_hyphenation_languages() {
		Functions\when( '_x' )->returnArg();
		if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
			define( 'WEEK_IN_SECONDS', 999 );
		}

		$this->cache->shouldReceive( 'get' )->once()->andReturnUsing( function( $key, &$found ) {
			$found = false;
			return [];
		} )->shouldReceive( 'set' )->once();

		$langs = $this->wp_typo->load_hyphenation_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
	}

	/**
	 * Test get_hyphenation_languages
	 *
	 * @covers ::load_diacritic_languages
	 * @covers ::load_languages
	 * @covers ::translate_languages
	 *
	 * @uses \PHP_Typography\PHP_Typography::get_hyphenation_languages
	 */
	public function test_load_diacritic_languages() {
		Functions\when( '_x' )->returnArg();
		if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
			define( 'WEEK_IN_SECONDS', 999 );
		}

		$this->cache->shouldReceive( 'get' )->once()->andReturnUsing( function( $key, &$found ) {
			$found = false;
			return [];
		} )->shouldReceive( 'set' )->once();

		$langs = $this->wp_typo->load_diacritic_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
	}

	/**
	 * Test filter.
	 *
	 * @covers ::filter
	 *
	 * @uses ::get_instance
	 */
	public function test_filter() {
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process' )->once()->with( 'foobar', false, false, null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter( 'foobar', null ) );
	}

	/**
	 * Test filter_title.
	 *
	 * @covers ::filter_title
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_title() {
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_title' )->once()->with( 'foobar', null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_title( 'foobar', null ) );
	}

	/**
	 * Test filter_title_parts.
	 *
	 * @covers ::filter_title_parts
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_title_parts() {
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_title_parts' )->once()->with( 'foobar', null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_title_parts( 'foobar', null ) );
	}

	/**
	 * Test filter_feed.
	 *
	 * @covers ::filter_feed
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_feed() {
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_feed' )->once()->with( 'foobar', false, null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_feed( 'foobar', null ) );
	}

	/**
	 * Test filter_feed_title.
	 *
	 * @covers ::filter_feed_title
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_feed_title() {
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_feed' )->once()->with( 'foobar', true, null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_feed_title( 'foobar', null ) );
	}

	/**
	 * Provide data for testing add_content_filters.
	 *
	 * @return array
	 */
	public function provide_init_data() {
		return [
			[ true, true, true, true, true ],
			[ false, false, false, false, false ],
			[ true, false, false, false, false ],
			[ false, true, false, false, false ],
			[ false, false, true, false, false ],
			[ false, false, false, true, false ],
			[ false, false, false, false, true ],
		];
	}

	/**
	 * Test init
	 *
	 * @covers ::init
	 *
	 * @uses ::run
	 * @uses ::set_instance
	 *
	 * @dataProvider provide_init_data
	 *
	 * @param bool $restore_defaults The typo_restore_defaults value.
	 * @param bool $clear_cache      The typo_clear_cache value.
	 * @param bool $smart_characters The typo_smart_characters value.
	 * @param bool $admin            Whether is_admin() should return true.
	 * @param bool $multilingual     The typo_enable_multilingual_support value.
	 */
	public function test_init( $restore_defaults, $clear_cache, $smart_characters, $admin, $multilingual ) {
		$this->prepareOptions( [
			'typo_smart_characters'            => $smart_characters,
			'typo_enable_multilingual_support' => $multilingual,
		] );
		$this->wp_typo->run();

		Functions\expect( 'get_option' )
			->once()->with( 'typo_restore_defaults' )->andReturn( $restore_defaults )
			->andAlsoExpectIt()->once()->with( 'typo_clear_cache' )->andReturn( $clear_cache )
			->andAlsoExpectIt()->zeroOrMoreTimes();

		Functions\expect( 'is_admin' )->atLeast()->once()->andReturn( $admin );

		if ( $restore_defaults ) {
			$this->wp_typo->shouldReceive( 'set_default_options' )->once()->with( true );
		}

		if ( $clear_cache ) {
			$this->wp_typo->shouldReceive( 'clear_cache' )->once();
		}

		if ( ! $admin ) {
			$this->wp_typo->shouldReceive( 'add_content_filters' )->once();

			if ( $smart_characters ) {
				Filters\expectAdded( 'run_wptexturize' );
				Functions\expect( 'wptexturize' )->once()->with( ' ', true );
			}
		}

		$this->wp_typo->init();

		self::assertTrue( has_action( 'wp_head', [ $this->wp_typo, 'add_wp_head' ] ) );
		self::assertTrue( has_action( 'wp_enqueue_scripts', [ $this->wp_typo, 'enqueue_scripts' ] ) );
		self::assertSame( ! $admin, has_action( 'shutdown', [ $this->wp_typo, 'save_hyphenator_cache_on_shutdown' ] ) );
		self::assertSame( $multilingual, has_filter( 'typo_settings', [ $this->multi, 'automatic_language_settings' ] ) );
	}

	/**
	 * Provide data for testing add_content_filters.
	 *
	 * @return array
	 */
	public function provide_add_content_filters_data() {
		return [
			[ true, true, true, 0, false ],
			[ false, false, false, 0, false ],
			[ true, false, false, 5, false ],
			[ true, false, false, 4, true ],
			[ false, false, false, 4, false ],
		];
	}

	/**
	 * Test add_content_filters
	 *
	 * @covers ::add_content_filters
	 * @covers ::enable_content_filters
	 * @covers ::enable_heading_filters
	 * @covers ::enable_title_filters
	 * @covers ::enable_acf_filters
	 *
	 * @dataProvider provide_add_content_filters_data
	 *
	 * @param bool $content     Disable content filters if true.
	 * @param bool $heading     Disable heading filters if true.
	 * @param bool $title       Disable title filters if true.
	 * @param int  $acf_version Simulated ACF version.
	 * @param bool $acf         Disable ACF filters if true.
	 */
	public function test_add_content_filters( $content, $heading, $title, $acf_version, $acf ) {

		$content_hooks = [
			'comment_author',
			'comment_text',
			'the_content',
			'term_name',
			'term_description',
			'link_name',
			'the_excerpt',
			'the_excerpt_embed',
			'widget_text',
		];
		$heading_hooks = [
			'the_title',
			'single_post_title',
			'single_cat_title',
			'single_tag_title',
			'single_month_title',
			'nav_menu_attr_title',
			'nav_menu_description',
			'widget_title',
			'list_cats',
		];
		$title_hooks = [
			'wp_title'             => 'process_feed',
			'document_title_parts' => 'process_title_parts',
			'wp_title_parts'       => 'process_title_parts',
		];
		$acf_hooks = [
			4 => [
				'acf/format_value_for_api/type=wysiwyg'  => 'process',
				'acf/format_value_for_api/type=textarea' => 'process',
				'acf/format_value_for_api/type=text'     => 'process_title',
			],
			5 => [
				'acf/format_value/type=wysiwyg'  => 'process',
				'acf/format_value/type=textarea' => 'process',
				'acf/format_value/type=text'     => 'process_title',
			],
		];

		Filters\expectApplied( 'typo_filter_priority' )->once();
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'content' )->andReturn( $content );
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'heading' )->andReturn( $heading );
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'title' )->andReturn( $title );

		if ( $acf_version > 0 ) {
			m::mock( 'acf' );

			Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'acf' )->andReturn( $acf );

			if ( ! $acf ) {
				Functions\expect( 'acf_get_setting' )->once()->with( 'version' )->andReturn( $acf_version );
			}
		}

		$this->wp_typo->add_content_filters();

		$expected = ! $content;
		foreach ( $content_hooks as $hook ) {
			$found = has_filter( $hook, get_class( $this->wp_typo ) . '->process()' );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}

		$expected = ! $heading;
		foreach ( $heading_hooks as $hook ) {
			$found = has_filter( $hook, get_class( $this->wp_typo ) . '->process_title()' );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}

		$expected = ! $title;
		foreach ( $title_hooks as $hook => $method ) {
			$found = has_filter( $hook, get_class( $this->wp_typo ) . "->$method()" );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}

		foreach ( array_keys( $acf_hooks ) as $version ) {
			$expected = $acf_version === $version && ! $acf;
			foreach ( $acf_hooks[ $version ] as $hook => $method ) {
				$found = has_filter( $hook, get_class( $this->wp_typo ) . "->$method()" );
				$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
			}
		}
	}

	/**
	 * Test process_title.
	 *
	 * @covers ::process_title
	 */
	public function test_process_title() {
		$this->wp_typo->shouldReceive( 'process' )->once()->with( 'foobar', true, false, null )->andReturn( 'barfoo' );

		$this->assertSame( 'barfoo', $this->wp_typo->process_title( 'foobar', null ) );
	}

	/**
	 * Test process_feed.
	 *
	 * @covers ::process_feed
	 */
	public function test_process_feed() {
		$this->wp_typo->shouldReceive( 'process' )->once()->with( 'foobar', true, true, null )->andReturn( 'barfoo' );

		$this->assertSame( 'barfoo', $this->wp_typo->process_feed( 'foobar', true, null ) );
	}

	/**
	 * Test process_title_parts.
	 *
	 * @covers ::process_title_parts
	 */
	public function test_process_title_parts() {
		$title_parts = [
			'fo' . \PHP_Typography\U::SOFT_HYPHEN . 'o',
			'bar',
			'baz',
		];

		foreach ( $title_parts as $part ) {
			$this->wp_typo->shouldReceive( 'process' )->once()->with( $part, true, true, null )->andReturn( $part . $part );
		}

		$this->assertSame( [ 'foofoo', 'barbar', 'bazbaz' ], $this->wp_typo->process_title_parts( $title_parts, null ) );
	}

	/**
	 * Provide data for testing process.
	 *
	 * @return array
	 */
	public function provide_process_data() {
		return [
			[ true, true, true, null ],
			[ false, false, false, null ],
			[ false, true, false, null ],
			[ false, false, true, null ],
			[ true, false, true, null ],
			[ false, false, false, m::mock( \PHP_Typography\Settings::class )->shouldReceive( 'get_hash' )->andReturn( 'another_fake_hash' )->getMock() ],
		];
	}

	/**
	 * Test process
	 *
	 * @covers ::process
	 *
	 * @uses ::run
	 * @uses ::set_instance
	 *
	 * @dataProvider provide_process_data
	 *
	 * @param  bool     $is_title   Fragment is a title.
	 * @param  bool     $force_feed Enforce feed processing.
	 * @param  bool     $is_feed    Value for is_feed().
	 * @param  Settings $settings   May be null.
	 */
	public function test_process( $is_title, $force_feed, $is_feed, $settings = null ) {
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 999 );
		}

		Functions\expect( 'is_feed' )->andReturn( $is_feed );

		if ( null === $settings ) {
			$settings_mock = m::mock( \PHP_Typography\Settings::class )->shouldReceive( 'get_hash' )->andReturn( 'another_fake_hash' )->getMock();

			$this->wp_typo->shouldReceive( 'get_settings' )->once()->andReturn( $settings_mock );
		}

		Filters\expectApplied( 'typo_settings' )->once()->with( m::type( \PHP_Typography\Settings::class ) )->andReturnFirstArg();
		Filters\expectApplied( 'typo_processed_text_caching_duration' )->once()->with( m::type( 'int' ) )->andReturn( 5 );

		$typo_mock = m::mock( \PHP_Typography\PHP_Typography::class );
		if ( $is_feed || $force_feed ) {
			$typo_mock->shouldReceive( 'process_feed' )->once()->with( 'text', m::type( \PHP_Typography\Settings::class ), $is_title )->andReturn( 'processed text' );
		} else {
			$typo_mock->shouldReceive( 'process' )->once()->with( 'text', m::type( \PHP_Typography\Settings::class ), $is_title )->andReturn( 'processed text' );
		}

		$this->wp_typo->shouldReceive( 'get_typography_instance' )->once()->andReturn( $typo_mock );

		$this->cache
			->shouldReceive( 'get' )->once()->andReturn( false )
			->shouldReceive( 'set' )->once();

		$this->assertSame( 'processed text', $this->wp_typo->process( 'text', $is_title, $force_feed, $settings ) );
	}

	/**
	 * Test set_transient.
	 *
	 * @covers ::set_transient
	 */
	public function foo_test_set_transient() {
		Functions\expect( 'set_transient' )->once()->with( 'my_transient', 'my_value', 666 )->andReturn( true );
		Functions\expect( 'update_option' )->once()->with( 'typo_transient_keys', [
			'my_transient' => true,
		] )->andReturn( true );
		$this->assertTrue( $this->wp_typo->set_transient( 'my_transient', 'my_value', 666 ) );

		Functions\expect( 'set_transient' )->once()->with( 'my_other_transient', 'my_value', 666 )->andReturn( true );
		Functions\expect( 'update_option' )->once()->with( 'typo_transient_keys', [
			'my_transient' => true,
			'my_other_transient' => true,
		] )->andReturn( true );
		$this->assertTrue( $this->wp_typo->set_transient( 'my_other_transient', 'my_value', 666 ) );

		Functions\expect( 'set_transient' )->once()->with( 'my_third_transient', 'my_value', 666 )->andReturn( false );
		$this->assertFalse( $this->wp_typo->set_transient( 'my_third_transient', 'my_value', 666 ) );
	}

	/**
	 * Test set_cache.
	 *
	 * @covers ::set_cache
	 */
	public function foo_test_set_cache() {
		Functions\expect( 'wp_cache_set' )->once()->with( 'my_cache_key', 'my_value', 'wp-typography', 666 )->andReturn( true );
		Functions\expect( 'update_option' )->once()->with( 'typo_cache_keys', [
			'my_cache_key' => true,
		] )->andReturn( true );
		$this->assertTrue( $this->wp_typo->set_cache( 'my_cache_key', 'my_value', 666 ) );

		Functions\expect( 'wp_cache_set' )->once()->with( 'my_other_cache_key', 'my_value', 'wp-typography', 666 )->andReturn( true );
		Functions\expect( 'update_option' )->once()->with( 'typo_cache_keys', [
			'my_cache_key' => true,
			'my_other_cache_key' => true,
		] )->andReturn( true );
		$this->assertTrue( $this->wp_typo->set_cache( 'my_other_cache_key', 'my_value', 666 ) );

		Functions\expect( 'wp_cache_set' )->once()->with( 'my_third_cache_key', 'my_value', 'wp-typography', 666 )->andReturn( false );
		$this->assertFalse( $this->wp_typo->set_cache( 'my_third_cache_key', 'my_value', 666 ) );
	}

	/**
	 * Test get_cache.
	 *
	 * @covers ::get_cache
	 */
	public function foo_test_get_cache() {
		Functions\expect( 'wp_cache_get' )->once()->with( 'my_cache_key', 'wp-typography', false, false )->andReturn( 'foo' );
		$this->assertSame( 'foo', $this->wp_typo->get_cache( 'my_cache_key', $found ) );
	}

	/**
	 * Test cache_object.
	 *
	 * @covers ::cache_object
	 */
	public function test_cache_object() {
		$key = 'my_transient_key';
		$object = new \stdClass();

		$this->transients->shouldReceive( 'set_large_object' )->once()->with( $key, $object, m::type( 'int' ) );

		$this->invokeMethod( $this->wp_typo, 'cache_object', [ $key, $object ] );

		$this->assertTrue( 1 === Filters\applied( 'typo_php_typography_caching_duration' ) );
		$this->assertTrue( 1 === Filters\applied( 'typo_php_typography_caching_enabled' ) );
	}

	/**
	 * Test set_default_options.
	 *
	 * @covers ::set_default_options
	 *
	 * @uses ::run
	 * @uses ::set_instance
	 * @uses ::get_default_options
	 */
	public function test_set_default_options() {
		$this->wp_typo->run();

		Functions\expect( 'get_option' )->atLeast()->once()->andReturn( false );
		Functions\expect( 'update_option' )->atLeast()->once();

		$this->wp_typo->set_default_options();
		$this->assertTrue( true );
	}

	/**
	 * Test set_default_options.
	 *
	 * @covers ::set_default_options
	 *
	 * @uses ::run
	 * @uses ::set_instance
	 * @uses ::get_default_options
	 */
	public function test_set_default_options_force_defaults() {
		$this->wp_typo->run();

		Functions\expect( 'get_option' )->never();
		Functions\expect( 'update_option' )->once()
			->andAlsoExpectIt()->once()->with( 'typo_restore_defaults', false )
			->andAlsoExpectIt()->once()->with( 'typo_clear_cache', false );

		$this->wp_typo->set_default_options( true );
		$this->assertTrue( true );
	}


	/**
	 * Test get_default_options.
	 *
	 * @covers ::get_default_options
	 *
	 * @uses ::run
	 * @uses ::set_instance
	 */
	public function test_get_default_options() {
		$this->wp_typo->run();
		$this->assertInternalType( 'array', $this->wp_typo->get_default_options() );
	}

	/**
	 * Test clear_cache.
	 *
	 * @covers ::clear_cache
	 */
	public function test_clear_cache() {
		$this->transients->shouldReceive( 'invalidate' );
		$this->cache->shouldReceive( 'invalidate' );

		Functions\expect( 'update_option' )->once()->with( 'typo_clear_cache', false );

		$this->wp_typo->clear_cache();
		$this->assertTrue( true );
	}


	/**
	 * Test parser_errors_handler.
	 *
	 * @covers ::parser_errors_handler
	 */
	public function test_parser_errors_handler() {
		$this->wp_typo->parser_errors_handler( [] );
		$this->assertTrue( 1 === Filters\applied( 'typo_handle_parser_errors' ) );
	}


	/**
	 * Test add_wp_head.
	 *
	 * @covers ::add_wp_head
	 */
	public function test_add_wp_head_css() {
		$this->prepareOptions( [
			'typo_style_css_include'                => true,
			'typo_style_css'                        => 'my: css;',
			'typo_hyphenate_safari_font_workaround' => false,
		] );
		Functions\expect( 'esc_html' )->once()->andReturnFirstArg();
		$this->expectOutputString( "<style type=\"text/css\">\r\nmy: css;\r\n</style>\r\n" );
		$this->wp_typo->add_wp_head();
	}

	/**
	 * Test add_wp_head.
	 *
	 * @covers ::add_wp_head
	 */
	public function test_add_wp_head_safari_workaround() {
		$this->prepareOptions( [
			'typo_style_css_include'                => false,
			'typo_hyphenate_safari_font_workaround' => true,
		] );
		$this->expectOutputString( "<style type=\"text/css\">body {-webkit-font-feature-settings: \"liga\";font-feature-settings: \"liga\";-ms-font-feature-settings: normal;}</style>\r\n" );
		$this->wp_typo->add_wp_head();

	}

	/**
	 * Test plugins_loaded.
	 *
	 * @covers ::plugins_loaded
	 */
	public function test_plugins_loaded() {
		Functions\expect( 'load_plugin_textdomain' )->once();

		$this->wp_typo->plugins_loaded();
		$this->assertTrue( true );
	}

	/**
	 * Test plugins_loaded.
	 *
	 * @covers ::plugins_loaded
	 */
	public function test_plugins_loaded_with_nextgen() {
		Functions\expect( 'load_plugin_textdomain' )->once();
		m::mock( 'C_NextGEN_Bootstrap' );

		$this->wp_typo->plugins_loaded();
		$this->assertAttributeSame( PHP_INT_MAX, 'filter_priority', $this->wp_typo );

	}

	/**
	 * Test get_version.
	 *
	 * @covers ::get_version
	 */
	public function test_get_version() {
		$this->assertInternalType( 'string', $this->wp_typo->get_version() );
	}

	/**
	 * Test get_version_hash.
	 *
	 * @covers ::get_version_hash
	 * @covers ::hash_version_string
	 */
	public function test_get_version_hash() {
		$this->assertInternalType( 'string', $this->wp_typo->get_version_hash() );
	}

	/**
	 * Test enqueue_scripts.
	 *
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts() {
		$this->prepareOptions( [
			'typo_hyphenate_clean_clipboard' => true,
		] );

		define( 'SCRIPT_DEBUG', false );

		Functions\expect( 'plugin_dir_url' )->andReturn( 'dummy/path' );
		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'jquery-selection', m::type( 'string' ), m::type( 'array' ), m::type( 'string' ), true )
			->andAlsoExpectIt()->once()
			->with( 'wp-typography-cleanup-clipboard', m::type( 'string' ), m::type( 'array' ), m::type( 'string' ), true );
		$this->wp_typo->enqueue_scripts();

		$this->assertTrue( true );
	}

	/**
	 * Test maybe_fix_object.
	 *
	 * @covers ::maybe_fix_object
	 */
	public function test_maybe_fix_object() {
		$fake_object_string = 'O:16:"SomeMissingClass":1:{s:1:"a";s:1:"b";}';
		$fake_object = unserialize( $fake_object_string );

		// Unfortunately, serialize and  unserialize cannot be mocked.
		$object = $this->invokeMethod( $this->wp_typo, 'maybe_fix_object', [ $fake_object ] );

		$this->assertTrue( $object !== $fake_object );
	}

	/**
	 * Provide data for testing save_hyphenator_cache_on_shutdown.
	 *
	 * @return array
	 */
	public function provide_save_hyphenator_cache_on_shutdown_data() {
		return [
			[ true,  m::mock( Hyphenator_Cache::class ), true ],
			[ false, m::mock( Hyphenator_Cache::class ), false ],
			[ true,  null,                               false ],
			[ false, null,                               false ],
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
	 */
	public function test_save_hyphenator_cache_on_shutdown( $enable_hyphenation, $hyphenator_cache, $expected ) {

		if ( null !== $hyphenator_cache ) {
			$hyphenator_cache->shouldReceive( 'has_changed' )->andReturn( $expected );
		}

		$this->prepareOptions( [
			'typo_enable_hyphenation' => $enable_hyphenation,
		] );

		$this->setValue( $this->wp_typo, 'hyphenator_cache', $hyphenator_cache );

		if ( $expected ) {
			$this->wp_typo->shouldReceive( 'cache_object' )->once();
		} else {
			$this->wp_typo->shouldReceive( 'cache_object' )->never();
		}

		$this->wp_typo->save_hyphenator_cache_on_shutdown();

		$this->assertTrue( true );
	}
}
