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
use WP_Typography\Options;
use WP_Typography\Settings\Plugin_Configuration as Config;

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
 * @uses \WP_Typography\Admin::__construct
 * @uses \WP_Typography\Setup::__construct
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
	 * @var \WP_Typography\Setup
	 */
	protected $setup;

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
	 * Test fixture.
	 *
	 * @var \WP_Typography\Options
	 */
	protected $options;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine

		// Mock WP_Typography\Settings\Multlingual instance.
		$this->multi = m::mock( \WP_Typography\Settings\Multilingual::class )
			->shouldReceive( 'run' )->byDefault()
			->shouldReceive( 'filter_defaults' )->andReturnUsing( function( array $defaults ) {
				return $defaults;
			} )->byDefault()
			->getMock();

		// Mock WP_Typography\Setup instance.
		$this->setup = m::mock( \WP_Typography\Setup::class, [ '/some/path' ] )
			->shouldReceive( 'run' )->byDefault()
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

		// Mock WP_Typography\Options instance.
		$this->options = m::mock( \WP_Typography\Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Admin instance.
		$this->wp_typo_admin = m::mock( \WP_Typography\Admin::class, [ 'plugin_basename', $this->options ] )
			->shouldReceive( 'run' )->byDefault()
			->shouldReceive( 'get_default_settings' )->andReturn( [ 'dummy_settings' => 'bar' ] )->byDefault()
			->getMock();

		// Create instance.
		Functions\expect( 'plugin_basename' )->once()->with( m::type( 'string' ) )->andReturn( 'base/name' );
		$this->wp_typo = m::mock( \WP_Typography::class, [ '7.7.7', 'dummy/path', $this->setup, $this->wp_typo_admin, $this->multi, $this->transients, $this->cache, $this->options ] )
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
	 *
	 * @return array The options array.
	 */
	protected function prepareOptions( array $options ) {  // @codingStandardsIgnoreLine
		// Reset options.
		$this->setValue( $this->wp_typo, 'config', $options );

		return $options;
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
	 * @uses \WP_Typography\Options::__construct
	 * @uses \WP_Typography\Setup::__construct
	 * @uses \WP_Typography\Transients::__construct
	 * @uses \WP_Typography\Transients::invalidate
	 * @uses \WP_Typography\Transients::get_keys_from_database
	 * @uses \WP_Typography\Settings\Multilingual::__construct
	 */
	public function test_constructor() {
		global $wpdb;

		if ( ! defined( 'ARRAY_A' ) ) {
			define( 'ARRAY_A', 'array' );
		}

		$wpdb          = m::mock( 'wpdb' ); // WPCS: override ok.
		$wpdb->options = 'wp_options';
		$wpdb->shouldReceive( 'prepare' )->withAnyArgs()->andReturn( 'fake SQL string' )->byDefault();
		$wpdb->shouldReceive( 'get_results' )->withAnyArgs()->andReturn( [] )->byDefault();

		Functions\expect( 'get_transient' )->once()->with( 'typo_transients_incrementor' )->andReturn( false );
		Functions\expect( 'set_transient' )->once()->andReturn( 0 );
		Functions\expect( 'wp_cache_get' )->once()->with( 'typo_cache_incrementor', 'wp-typography' )->andReturn( 0 );
		Functions\expect( 'wp_cache_set' )->once()->with( 'typo_cache_incrementor', m::type( 'int' ), 'wp-typography', 0 )->andReturn( true );
		Functions\expect( 'wp_using_ext_object_cache' )->once()->andReturn( false );
		Functions\expect( 'wp_list_pluck' )->once()->andReturn( [] );
		Functions\expect( 'plugin_basename' )->once()->with( m::type( 'string' ) )->andReturn( 'base/name' );

		$typo = new \WP_Typography( '6.6.6', '/dummy/path', m::mock( \WP_Typography\Setup::class ), m::mock( \WP_Typography\Admin::class ), m::mock( \WP_Typography\Settings\Multilingual::class ) );

		$this->assertInstanceOf( \WP_Typography::class, $typo );
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
	 * @uses \WP_Typography\Setup::__construct
	 */
	public function test_run() {
		$this->wp_typo->run();

		$this->assertTrue( has_action( 'plugins_loaded', get_class( $this->wp_typo ) . '->plugins_loaded()', 10 ) );
		$this->assertTrue( has_action( 'init',           get_class( $this->wp_typo ) . '->init()',           10 ) );
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
			Config::IGNORE_TAGS                    => [ 'script' ],
			Config::IGNORE_CLASSES                 => [ 'noTypo' ],
			Config::IGNORE_IDS                     => [],
			Config::SMART_CHARACTERS               => true,
			Config::SMART_DASHES                   => false,
			Config::SMART_DASHES_STYLE             => 'international',
			Config::SMART_ELLIPSES                 => false,
			Config::SMART_MATH                     => false,
			Config::SMART_FRACTIONS                => false,
			Config::SMART_ORDINALS                 => false,
			Config::SMART_MARKS                    => false,
			Config::SMART_QUOTES                   => false,
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
			Config::HYPHENATION_EXCEPTIONS         => [],
			Config::IGNORE_PARSER_ERRORS           => false,
			Config::ENABLE_MULTILINGUAL_SUPPORT    => false,
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
			Config::ENABLE_HYPHENATION => true,
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
			Config::IGNORE_TAGS                    => [ 'script' ],
			Config::IGNORE_CLASSES                 => [ 'noTypo' ],
			Config::IGNORE_IDS                     => [],
			Config::SMART_CHARACTERS               => true,
			Config::SMART_DASHES                   => false,
			Config::SMART_DASHES_STYLE             => 'international',
			Config::SMART_ELLIPSES                 => false,
			Config::SMART_MATH                     => false,
			Config::SMART_FRACTIONS                => false,
			Config::SMART_ORDINALS                 => false,
			Config::SMART_MARKS                    => false,
			Config::SMART_QUOTES                   => false,
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
			Config::WRAP_URLS                      => false,
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
			Config::HYPHENATION_EXCEPTIONS         => [],
			Config::IGNORE_PARSER_ERRORS           => false,
			Config::ENABLE_MULTILINGUAL_SUPPORT    => false,
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
			Config::IGNORE_TAGS                    => [ 'script' ],
			Config::IGNORE_CLASSES                 => [ 'noTypo' ],
			Config::IGNORE_IDS                     => [],
			Config::SMART_CHARACTERS               => false,
			Config::SMART_DASHES                   => false,
			Config::SMART_DASHES_STYLE             => 'international',
			Config::SMART_ELLIPSES                 => false,
			Config::SMART_MATH                     => false,
			Config::SMART_FRACTIONS                => false,
			Config::SMART_ORDINALS                 => false,
			Config::SMART_MARKS                    => false,
			Config::SMART_QUOTES                   => false,
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
			Config::WRAP_URLS                      => false,
			Config::STYLE_HANGING_PUNCTUATION      => false,
			Config::STYLE_INITIAL_QUOTES           => false,
			Config::INITIAL_QUOTE_TAGS             => [],
			Config::ENABLE_HYPHENATION             => false,
			Config::HYPHENATE_HEADINGS             => false,
			Config::HYPHENATE_CAPS                 => false,
			Config::HYPHENATE_TITLE_CASE           => false,
			Config::HYPHENATE_COMPOUNDS            => false,
			Config::HYPHENATE_LANGUAGES            => 'en-US',
			Config::HYPHENATE_MIN_LENGTH           => 2,
			Config::HYPHENATE_MIN_BEFORE           => 2,
			Config::HYPHENATE_MIN_AFTER            => 2,
			Config::HYPHENATION_EXCEPTIONS         => [],
			Config::IGNORE_PARSER_ERRORS           => false,
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
			[ true, true, true, true, true, true ],
			[ false, false, false, false, false, true ],
			[ true, false, false, false, false, false ],
			[ false, true, false, false, false, false ],
			[ false, false, true, false, false, true ],
			[ false, false, false, true, false, false ],
			[ false, false, false, false, true, false ],
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
	 * @param bool $get_config       Whether the plugin configuration can be retrieved successfully.
	 */
	public function test_init( $restore_defaults, $clear_cache, $smart_characters, $admin, $multilingual, $get_config ) {
		$settings = $this->prepareOptions( [
			Config::SMART_CHARACTERS            => $smart_characters,
			Config::ENABLE_MULTILINGUAL_SUPPORT => $multilingual,
		] );
		$this->wp_typo->run();

		Functions\expect( 'is_admin' )->atLeast()->once()->andReturn( $admin );

		$this->options->shouldReceive( 'get' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( $restore_defaults );
		$this->options->shouldReceive( 'get' )->once()->with( Options::CLEAR_CACHE )->andReturn( $clear_cache );

		if ( $get_config ) {
			$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( $settings );
		} else {
			$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( '' );
			$this->wp_typo->shouldReceive( 'set_default_options' )->once()->with( true );
		}

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
		$title_hooks   = [
			'wp_title'             => 'process_feed',
			'document_title_parts' => 'process_title_parts',
			'wp_title_parts'       => 'process_title_parts',
		];
		$acf_hooks     = [
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
			'my_transient'       => true,
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
			'my_cache_key'       => true,
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
		$key    = 'my_transient_key';
		$object = new \stdClass();
		$handle = 'my_handle';

		$this->transients->shouldReceive( 'set_large_object' )->once()->with( $key, $object, m::type( 'int' ) );

		Filters\expectApplied( 'typo_php_typography_caching_enabled' )
			->once()
			->with( true, $handle );
		Filters\expectApplied( 'typo_php_typography_caching_duration' )
			->once()
			->with( 0, $handle );

		$this->invokeMethod( $this->wp_typo, 'cache_object', [ $key, $object, $handle ] );

		$this->assertTrue( 1 === Filters\applied( 'typo_php_typography_caching_enabled' ) );
		$this->assertTrue( 1 === Filters\applied( 'typo_php_typography_caching_duration' ) );
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

		$this->wp_typo->shouldReceive( 'get_default_options' )->once()->andReturn( [ 'foo' => 'bar' ] );

		$this->options->shouldReceive( 'set' )->once()->with( Options::CONFIGURATION, m::type( 'array' ) );

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

		$this->wp_typo->shouldReceive( 'get_default_options' )->once()->andReturn( [ 'foo' => 'bar' ] );

		$this->options->shouldNotReceive( 'get' )->with( Options::RESTORE_DEFAULTS );
		$this->options->shouldReceive( 'set' )->once()->with( Options::CONFIGURATION, m::type( 'array' ) );
		$this->options->shouldReceive( 'set' )->once()->with( Options::RESTORE_DEFAULTS, false )->andReturn( true );
		$this->options->shouldReceive( 'set' )->once()->with( Options::CLEAR_CACHE, false )->andReturn( true );

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
	 *
	 * @runInSeparateProcess
	 */
	public function test_get_default_options() {
		$this->wp_typo->run();

		Functions\expect( 'wp_list_pluck' )->once()->with( m::type( 'array' ), 'default' )->andReturn( [ 'bar' => 'foo' ] );
		Functions\expect( '__' )->atLeast()->once()->andReturn( 'translated_string' );

		$this->multi->shouldReceive( 'filter_defaults' )->with( m::type( 'array' ) )->andReturn( [ 'foo' => 'bar' ] );

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

		$this->options->shouldReceive( 'set' )->once()->with( 'clear_cache', false )->andReturn( true );

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
			Config::STYLE_CSS_INCLUDE                => true,
			Config::STYLE_CSS                        => 'my: css;',
			Config::HYPHENATE_SAFARI_FONT_WORKAROUND => false,
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
			Config::STYLE_CSS_INCLUDE                => false,
			Config::HYPHENATE_SAFARI_FONT_WORKAROUND => true,
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
			Config::HYPHENATE_CLEAN_CLIPBOARD => true,
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
		$fake_object        = unserialize( $fake_object_string ); // @codingStandardsIgnoreLine

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
			Config::ENABLE_HYPHENATION => $enable_hyphenation,
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
