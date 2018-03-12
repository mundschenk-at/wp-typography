<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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

use WP_Typography\Data_Storage\Options;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\Hyphenator_Cache;

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
 * @uses ::hash_version_string
 * @uses \WP_Typography\Components\Admin_Interface::__construct
 * @uses \WP_Typography\Components\Public_Interface::__construct
 * @uses \WP_Typography\Components\Setup::__construct
 * @uses \WP_Typography\Components\Common::__construct
 */
class WP_Typography_Implementation_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Implementation
	 */
	protected $wp_typo;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Transients
	 */
	protected $transients;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Cache
	 */
	protected $cache;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Options
	 */
	protected $options;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine

		// Mock WP_Typography\Data_Storage\Options instance.
		$this->options = m::mock( \WP_Typography\Data_Storage\Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Data_Storage\Transients instance.
		$this->transients = m::mock( \WP_Typography\Data_Storage\Transients::class )
			->shouldReceive( 'get' )->byDefault()->andReturn( false )
			->shouldReceive( 'get_large_object' )->byDefault()->andReturn( false )
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'set_large_object' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Data_Storage\Cache instance.
		$this->cache = m::mock( \WP_Typography\Data_Storage\Cache::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'invalidate' )->byDefault()
			->getMock();

		// Create instance.
		$this->wp_typo = m::mock( \WP_Typography\Implementation::class, [ '7.7.7', $this->transients, $this->cache, $this->options ] )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		parent::setUp();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
		parent::tearDown();
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses ::get_version
	 * @uses \WP_Typography\Data_Storage\Abstract_Cache::__construct
	 * @uses \WP_Typography\Data_Storage\Cache::__construct
	 * @uses \WP_Typography\Data_Storage\Cache::invalidate
	 * @uses \WP_Typography\Data_Storage\Options::__construct
	 * @uses \WP_Typography\Data_Storage\Transients::__construct
	 * @uses \WP_Typography\Data_Storage\Transients::invalidate
	 * @uses \WP_Typography\Data_Storage\Transients::get_keys_from_database
	 */
	public function test_constructor() {

		$typo = new \WP_Typography\Implementation(
			'6.6.6',
			m::mock( \WP_Typography\Data_Storage\Transients::class ),
			m::mock( \WP_Typography\Data_Storage\Cache::class ),
			m::mock( \WP_Typography\Data_Storage\Options::class )
		);

		$this->assertInstanceOf( \WP_Typography\Implementation::class, $typo );
		$this->assertAttributeSame( '6.6.6', 'version', $typo );
	}

	/**
	 * Test get_typography_instance.
	 *
	 * @covers ::get_typography_instance
	 */
	public function test_get_typography_instance() {
		$this->wp_typo->shouldReceive( 'get_config' )->once()->andReturn( [
			Config::ENABLE_HYPHENATION => true,
		] );

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );

		$this->transients->shouldReceive( 'cache_object' )->twice();

		$this->assertInstanceOf( \PHP_Typography\PHP_Typography::class, $this->invokeMethod( $this->wp_typo, 'get_typography_instance' ) );
	}

	/**
	 * Test get_config.
	 *
	 * @covers ::get_config
	 */
	public function test_get_config() {

		$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( [ 'foo' => 'bar' ] );

		$this->assertInternalType( 'array', $this->wp_typo->get_config() );
	}

	/**
	 * Test get_config with corrupted option.
	 *
	 * @covers ::get_config
	 */
	public function test_get_config_corrupted() {

		$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( 'wrong' );
		$this->wp_typo->shouldReceive( 'set_default_options' )->once()->with( true );

		// IRL set_default_options would fix the config object.
		$this->assertSame( null, $this->wp_typo->get_config() );
	}

	/**
	 * Test get_user_settings.
	 *
	 * @covers ::get_settings
	 * @covers ::init_settings_from_options
	 *
	 * @uses ::init_settings_from_options
	 */
	public function test_get_settings() {

		$this->wp_typo->shouldReceive( 'get_config' )->once()->andReturn( [
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
			Config::HYPHENATE_EXCEPTIONS           => [],
			Config::IGNORE_PARSER_ERRORS           => false,
			Config::ENABLE_MULTILINGUAL_SUPPORT    => false,
		] );
		$this->transients->shouldReceive( 'cache_object' )->once();

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );
		Filters\expectApplied( 'typo_narrow_no_break_space' )->with( false )->once();
		Filters\expectApplied( 'typo_ignore_parser_errors' )->with( false )->once();

		$s = $this->wp_typo->get_settings();

		$this->assertInstanceOf( \PHP_Typography\Settings::class, $s );
	}

	/**
	 * Test get_user_settings.
	 *
	 * @covers ::get_settings
	 * @covers ::init_settings_from_options
	 *
	 * @uses ::init_settings_from_options
	 */
	public function test_get_settings_hyphenation_off() {

		$this->wp_typo->shouldReceive( 'get_config' )->once()->andReturn( [
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
			Config::HYPHENATE_EXCEPTIONS           => [],
			Config::IGNORE_PARSER_ERRORS           => false,
		] );
		$this->transients->shouldReceive( 'cache_object' )->once();

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );
		Filters\expectApplied( 'typo_narrow_no_break_space' )->with( false )->once();
		Filters\expectApplied( 'typo_ignore_parser_errors' )->with( false )->once();

		$s = $this->wp_typo->get_settings();

		$this->assertInstanceOf( \PHP_Typography\Settings::class, $s );
	}

	/**
	 * Test get_hyphenation_languages
	 *
	 * @covers ::get_hyphenation_languages
	 * @covers ::load_languages
	 * @covers ::translate_languages
	 *
	 * @uses \PHP_Typography\PHP_Typography::get_hyphenation_languages
	 */
	public function test_get_hyphenation_languages() {
		Functions\when( '_x' )->returnArg();
		if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
			define( 'WEEK_IN_SECONDS', 999 );
		}

		$this->cache->shouldReceive( 'get' )->once()->andReturnUsing( function( $key, &$found ) {
			$found = false;
			return [];
		} )->shouldReceive( 'set' )->once();

		$langs = $this->wp_typo->get_hyphenation_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
	}

	/**
	 * Test get_hyphenation_languages
	 *
	 * @covers ::get_diacritic_languages
	 * @covers ::load_languages
	 * @covers ::translate_languages
	 *
	 * @uses \PHP_Typography\PHP_Typography::get_hyphenation_languages
	 */
	public function test_get_diacritic_languages() {
		Functions\when( '_x' )->returnArg();
		if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
			define( 'WEEK_IN_SECONDS', 999 );
		}

		$this->cache->shouldReceive( 'get' )->once()->andReturnUsing( function( $key, &$found ) {
			$found = false;
			return [];
		} )->shouldReceive( 'set' )->once();

		$langs = $this->wp_typo->get_diacritic_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
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
	 * Test process_feed_title.
	 *
	 * @covers ::process_feed_title
	 */
	public function test_process_feed_title() {
		$this->wp_typo->shouldReceive( 'process_feed' )->once()->with( 'foobar', true, null )->andReturn( 'barfoo' );

		$this->assertSame( 'barfoo', $this->wp_typo->process_feed_title( 'foobar', null ) );
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

		// Fake filter_body_classes.
		$this->setValue( $this->wp_typo, 'body_classes', [ 'foo', 'bar' ], \WP_Typography\Implementation::class );

		Filters\expectApplied( 'typo_settings' )->once()->with( m::type( \PHP_Typography\Settings::class ) )->andReturnFirstArg();
		Filters\expectApplied( 'typo_processed_text_caching_duration' )->once()->with( m::type( 'int' ) )->andReturn( 5 );

		$typo_mock = m::mock( \PHP_Typography\PHP_Typography::class );
		$this->wp_typo->shouldReceive( 'get_typography_instance' )->once()->andReturn( $typo_mock );
		if ( $is_feed || $force_feed ) {
			$typo_mock->shouldReceive( 'process_feed' )->once()->with( 'text', m::type( \PHP_Typography\Settings::class ), $is_title, [ 'foo', 'bar' ] )->andReturn( 'processed text' );
		} else {
			$typo_mock->shouldReceive( 'process' )->once()->with( 'text', m::type( \PHP_Typography\Settings::class ), $is_title, [ 'foo', 'bar' ] )->andReturn( 'processed text' );
		}

		$this->cache
			->shouldReceive( 'get' )->once()->andReturn( false )
			->shouldReceive( 'set' )->once();

		$this->assertSame( 'processed text', $this->wp_typo->process( 'text', $is_title, $force_feed, $settings ) );
	}

	/**
	 * Tests filter_body_class
	 *
	 * @covers ::filter_body_class
	 */
	public function test_filter_body_class() {
		$classes = [ 'foo', 'bar' ];

		$result = $this->wp_typo->filter_body_class( $classes );

		$this->assertSame( $classes, $result );
		$this->assertAttributeSame( $classes, 'body_classes', $this->wp_typo );
	}

	/**
	 * Test set_default_options.
	 *
	 * @covers ::set_default_options
	 *
	 * @uses ::set_instance
	 * @uses ::get_default_options
	 */
	public function test_set_default_options() {
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
	 * @uses ::set_instance
	 * @uses ::get_default_options
	 */
	public function test_set_default_options_force_defaults() {
		$this->wp_typo->shouldReceive( 'get_default_options' )->once()->andReturn( [ 'foo' => 'bar' ] );

		$this->options->shouldNotReceive( 'get' )->with( Options::RESTORE_DEFAULTS );
		$this->options->shouldReceive( 'set' )->once()->with( Options::CONFIGURATION, m::type( 'array' ) );
		$this->options->shouldReceive( 'delete' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( true );
		$this->options->shouldReceive( 'delete' )->once()->with( Options::CLEAR_CACHE )->andReturn( true );

		$this->wp_typo->set_default_options( true );
		$this->assertTrue( true );
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
	public function test_get_default_options() {
		Functions\expect( 'wp_list_pluck' )->once()->with( m::type( 'array' ), 'default' )->andReturn( [ 'bar' => 'foo' ] );
		Functions\expect( '__' )->atLeast()->once()->andReturn( 'translated_string' );

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

		$this->options->shouldReceive( 'delete' )->once()->with( 'clear_cache' )->andReturn( true );

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
	 * Test get_version.
	 *
	 * @covers ::get_version
	 */
	public function test_get_version() {
		$this->assertInternalType( 'string', $this->wp_typo->get_version() );
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

		$this->setValue( $this->wp_typo, 'hyphenator_cache', $hyphenator_cache );

		if ( $expected ) {
			$this->transients->shouldReceive( 'cache_object' )->once();
		} else {
			$this->transients->shouldReceive( 'cache_object' )->never();
		}

		$this->wp_typo->save_hyphenator_cache_on_shutdown();

		$this->assertTrue( true );
	}
}
