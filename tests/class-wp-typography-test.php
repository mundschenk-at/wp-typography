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

use WP_Typography_Admin;

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
 * @uses ::set_transient
 */
class WP_Typography_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography
	 */
	protected $wp_typo;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine

		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		// Mock WP_Typography_Admin instance.
		$admin_mock = m::mock( \WP_Typography_Admin::class );
		$admin_mock
			->shouldReceive( 'run' )
			->shouldReceive( 'get_default_settings' )->andReturn( [] );

		// Create instance.
		$this->wp_typo = m::mock( \WP_Typography::class, [ '7.7.7', 'dummy/path', $admin_mock ] )->makePartial();

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
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography_Admin::__construct
	 */
	public function test_constructor() {
		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		$typo = new \WP_Typography( '6.6.6', 'dummy/path', m::mock( \WP_Typography_Admin::class ) );

		$this->assertInstanceOf( \WP_Typography::class, $typo );
		$this->assertAttributeInstanceOf( \WP_Typography_Admin::class, 'admin', $typo );
		$this->assertAttributeSame( '6.6.6', 'version', $typo );
	}

	/**
	 * Tests singleton methods.
	 *
	 * @covers ::get_instance
	 * @covers ::set_instance
	 *
	 * @uses ::__construct
	 * @uses ::run
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography_Admin::__construct
	 */
	public function test_singleton() {
		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		$admin = m::mock( \WP_Typography_Admin::class );
		$admin->shouldReceive( 'run' )->shouldReceive( 'get_default_settings' )->andReturn( [] );

		$typo = new \WP_Typography( '6.6.6', 'dummy/path', $admin );
		$typo->run();

		$typo2 = \WP_Typography::get_instance();
		$this->assertSame( $typo, $typo2 );

		$this->assertInstanceOf( \WP_Typography::class, $typo );
		$this->assertAttributeInstanceOf( \WP_Typography_Admin::class, 'admin', $typo );
		$this->assertAttributeSame( '6.6.6', 'version', $typo );

		// Check ::get_instance (no underscore).
		$typo3 = \WP_Typography::get_instance();
		$this->assertSame( $typo, $typo3 );
	}

	/**
	 * Tests ::get_instance without a previous call to ::_get_instance (i.e. _doing_it_wrong).
	 *
	 * @covers ::get_instance
	 *
	 * @uses ::__construct
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography_Admin::__construct
	 *
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage WP_Typography::get_instance called without prior plugin intialization.
	 */
	public function test_get_instance_failing() {
		$typo = \WP_Typography::get_instance();
		$this->assertInstanceOf( \WP_Typography::class, $typo );
	}

	/**
	 * Tests ::get_instance without a previous call to ::_get_instance (i.e. _doing_it_wrong).
	 *
	 * @covers ::set_instance
	 *
	 * @uses ::__construct
	 * @uses ::run
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography_Admin::__construct
	 *
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage WP_Typography::set_instance called more than once.
	 */
	public function test_set_instance_failing() {
		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		$admin = m::mock( \WP_Typography_Admin::class );
		$admin->shouldReceive( 'run' )->shouldReceive( 'get_default_settings' )->andReturn( [] );

		$typo = new \WP_Typography( '6.6.6', 'dummy/path', $admin );
		$typo->run();
		$typo->run();
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
	 * @uses \WP_Typography_Admin::__construct
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
	 * @covers ::get_settings
	 *
	 * @uses ::get_instance
	 * @uses ::cache_object
	 * @uses ::init_settings
	 * @uses ::maybe_fix_object
	 */
	public function test_get_user_settings() {
		$this->setStaticValue( \WP_Typography::class, '_instance', $this->wp_typo );

		Functions\expect( 'wp_json_encode' )->once()->andReturn( '{ json: "value" }' );
		Functions\expect( 'get_transient' )->once()->andReturn( false );
		Functions\expect( 'set_transient' )->once()->andReturn( true );
		Functions\expect( 'update_option' )->once()->andReturn( true );

		$s = \WP_Typography::get_user_settings();

		$this->assertInstanceOf( \PHP_Typography\Settings::class, $s );
		$this->assertAttributeNotSame( $s, 'typo_settings', $this->wp_typo );

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, '_instance', null );
	}

	/**
	 * Test get_hyphenation_languages
	 *
	 * @covers ::get_hyphenation_languages
	 *
	 * @uses \PHP_Typography\PHP_Typography::get_hyphenation_languages
	 */
	 public function test_get_hyphenation_languages() {
		$langs = \WP_Typography::get_hyphenation_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
	}

	/**
	 * Test get_diacritic_languages
	 *
	 * @covers ::get_diacritic_languages
	 *
	 * @uses \PHP_Typography\PHP_Typography::get_diacritic_languages
	 */
	 public function test_get_diacritic_languages() {
		$langs = \WP_Typography::get_diacritic_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
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
}
