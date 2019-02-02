<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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

namespace WP_Typography\Tests\Components;

use WP_Typography\Components\Public_Interface;
use WP_Typography\Settings\Plugin_Configuration as Config;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Public_Interface unit test for the singleton methods.
 *
 * @coversDefaultClass \WP_Typography\Components\Public_Interface
 * @usesDefaultClass \WP_Typography\Components\Public_Interface
 *
 * @uses ::__construct
 * @uses ::run
 */
class Public_Interface_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Public_Interface
	 */
	protected $public_if;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		// Mock WP_Typography\Components\Public_Interface instance.
		$this->public_if = m::mock( Public_Interface::class, [ 'plugin_basename' ] )
			->shouldAllowMockingProtectedMethods()->makePartial();

		Functions\expect( 'is_admin' )->once()->andReturn( false );

		$this->public_if->run(
			m::mock( \WP_Typography::class )->shouldReceive( 'get_version' )->andReturn( '6.6.6' )->byDefault()->getMock()
		);
	}

	/**
	 * Prepare WP_Typography options for a test.
	 *
	 * @param array $options An array of set options.
	 *
	 * @return array The options array.
	 */
	protected function prepareOptions( array $options ) {
		// Reset options.
		$this->setValue( $this->public_if, 'config', $options );

		return $options;
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$public_if = m::mock( Public_Interface::class, [ 'plugin_basename' ] );

		$this->assertAttributeSame( 'plugin_basename', 'plugin_basename', $public_if );
	}


	/**
	 * Test run.
	 *
	 * @covers ::run
	 */
	public function test_run() {
		Functions\expect( 'is_admin' )->once()->andReturn( false );
		Actions\expectAdded( 'init' )->once();

		$this->public_if->run( m::mock( \WP_Typography::class ) );

		$this->assertTrue( \has_action( 'init', [ $this->public_if, 'init' ] ) );
	}

	/**
	 * Test run on the admin side.
	 *
	 * @covers ::run
	 */
	public function test_run_admin() {
		Functions\expect( 'is_admin' )->once()->andReturn( true );
		Actions\expectAdded( 'init' )->never();

		$this->public_if->run( m::mock( \WP_Typography::class ) );

		$this->assertTrue( true );
	}

	/**
	 * Provide data for testing add_content_filters.
	 *
	 * @return array
	 */
	public function provide_init_data() {
		return [
			[ true, true, true, true ],
			[ false, false, false, false ],
			[ true, false, false, false ],
			[ false, true, false, false ],
			[ false, false, true, false ],
			[ false, false, false, true ],
		];
	}

	/**
	 * Test init
	 *
	 * @covers ::init
	 *
	 * @dataProvider provide_init_data
	 *
	 * @param bool $restore_defaults The typo_restore_defaults value.
	 * @param bool $clear_cache      The typo_clear_cache value.
	 * @param bool $smart_characters The typo_smart_characters value.
	 * @param bool $nextgen          Simulate enable NextGEN Gallery plugin.
	 */
	public function test_init( $restore_defaults, $clear_cache, $smart_characters, $nextgen ) {
		$plugin   = $this->getValue( $this->public_if, 'plugin' );
		$settings = $this->prepareOptions(
			[
				Config::SMART_CHARACTERS => $smart_characters,
			]
		);

		$plugin->shouldReceive( 'get_config' )->once()->andReturn( $settings );
		$this->public_if->shouldReceive( 'add_content_filters' )->once();

		if ( $smart_characters ) {
			Filters\expectAdded( 'run_wptexturize' );
			Functions\expect( 'wptexturize' )->once()->with( ' ', true );
		}

		if ( $nextgen ) {
			m::mock( 'C_NextGEN_Bootstrap' );
		}

		$this->public_if->init();

		self::assertTrue( has_filter( 'body_class', [ $plugin, 'filter_body_class' ] ) );

		self::assertTrue( has_action( 'wp_enqueue_scripts', [ $this->public_if, 'enqueue_styles' ] ) );
		self::assertTrue( has_action( 'wp_enqueue_scripts', [ $this->public_if, 'enqueue_scripts' ] ) );
		self::assertTrue( has_action( 'shutdown', [ $plugin, 'save_hyphenator_cache_on_shutdown' ] ) );

		if ( $nextgen ) {
			$this->assertAttributeSame( PHP_INT_MAX, 'filter_priority', $this->public_if );
		}
	}

	/**
	 * Provide data for testing add_content_filters.
	 *
	 * @return array
	 */
	public function provide_add_content_filters_data() {
		return [
			[ true,  true,  true,  '4.8' ],
			[ false, false, false, '4.8.1' ],
			[ true,  false, false, '4.6' ],
			[ false, false, false, '4.6' ],
			[ true,  false, false, '4.9.5' ],
			[ false, false, false, '4.9.5' ],
		];
	}

	/**
	 * Test add_content_filters
	 *
	 * @covers ::add_content_filters
	 * @covers ::enable_content_filters
	 * @covers ::enable_heading_filters
	 * @covers ::enable_title_filters
	 *
	 * @dataProvider provide_add_content_filters_data
	 * @runInSeparateProcess
	 *
	 * @param bool   $content     Disable content filters if true.
	 * @param bool   $heading     Disable heading filters if true.
	 * @param bool   $title       Disable title filters if true.
	 * @param string $wp_version Simulated WordPress version.
	 */
	public function test_add_content_filters( $content, $heading, $title, $wp_version ) {

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

		Filters\expectApplied( 'typo_filter_priority' )->once();
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'content' )->andReturn( $content );
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'heading' )->andReturn( $heading );
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'title' )->andReturn( $title );

		if ( ! $content ) {
			Functions\expect( 'get_bloginfo' )->once()->with( 'version' )->andReturn( $wp_version );
		}

		$this->public_if->add_content_filters();

		// Content hooks.
		$expected     = ! $content;
		$plugin_class = \get_class( $this->getValue( $this->public_if, 'plugin' ) );

		foreach ( $content_hooks as $hook ) {
			if ( 'widget_text' === $hook && \version_compare( $wp_version, '4.8', '>=' ) ) {
				$hook .= '_content';
			}

			$found = has_filter( $hook, "{$plugin_class}->process()" );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}

		// Heading hooks.
		$expected = ! $heading;
		foreach ( $heading_hooks as $hook ) {
			$found = has_filter( $hook, "{$plugin_class}->process_title()" );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}

		// Title hooks.
		$expected = ! $title;
		foreach ( $title_hooks as $hook => $method ) {
			$found = has_filter( $hook, "{$plugin_class}->{$method}()" );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}
	}

	/**
	 * Test enqueue_styles.
	 *
	 * @covers ::enqueue_styles
	 */
	public function test_enqueue_styles_css() {
		$custom_style = 'my: css;';
		$clean_style  = 'my: clean css;';
		$this->prepareOptions(
			[
				Config::STYLE_CSS_INCLUDE                => true,
				Config::STYLE_CSS                        => $custom_style,
				Config::HYPHENATE_SAFARI_FONT_WORKAROUND => false,
			]
		);

		Functions\expect( 'wp_register_style' )->once()->with( 'wp-typography-custom', false );
		Functions\expect( 'wp_enqueue_style' )->once()->with( 'wp-typography-custom' );

		$this->public_if->shouldReceive( 'clean_styles' )->once()->with( $custom_style )->andReturn( $clean_style );

		Functions\expect( 'wp_add_inline_style' )->once()->with( 'wp-typography-custom', $clean_style );

		$this->assertNull( $this->public_if->enqueue_styles() );
	}

	/**
	 * Test enqueue_styles.
	 *
	 * @covers ::enqueue_styles
	 */
	public function test_enqueue_styles_safari_workaround() {
		$this->prepareOptions(
			[
				Config::STYLE_CSS_INCLUDE                => false,
				Config::HYPHENATE_SAFARI_FONT_WORKAROUND => true,
			]
		);

		Functions\expect( 'wp_register_style' )->once()->with( 'wp-typography-safari-font-workaround', false );
		Functions\expect( 'wp_enqueue_style' )->once()->with( 'wp-typography-safari-font-workaround' );
		Functions\expect( 'wp_add_inline_style' )->once()->with( 'wp-typography-safari-font-workaround', m::type( 'string' ) );

		$this->assertNull( $this->public_if->enqueue_styles() );
	}

	/**
	 * Test enqueue_scripts.
	 *
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts() {
		$this->prepareOptions(
			[
				Config::HYPHENATE_CLEAN_CLIPBOARD => true,
			]
		);

		define( 'SCRIPT_DEBUG', false );

		Functions\expect( 'plugin_dir_url' )->andReturn( 'dummy/path' );
		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'wp-typography-cleanup-clipboard', m::type( 'string' ), m::type( 'array' ), m::type( 'string' ), true );
		$this->public_if->enqueue_scripts();

		$this->assertTrue( true );
	}

	/**
	 * Test clean_styles.
	 *
	 * @covers ::clean_styles
	 */
	public function test_clean_styles() {
		$selector1     = 'foo, bar';
		$selector2     = 'bar < foobar';
		$rules1        = 'foo-attribute: bar;bar-attribute: foo;';
		$rules2        = 'foo-attribute: x;bar-attribute: y;';
		$styles        = "{$selector1} { {$rules1} }\n{$selector2} { {$rules2} }";
		$with_comments = "/* A comment */{$styles}\n/* Another comment,\n * multiline */";
		$result        = 'clean selector#1{cleaned rules#1}clean selector#2{cleaned rules#2}';

		Functions\expect( 'wp_strip_all_tags' )->once()->with( $selector1 )->andReturn( 'clean selector#1' );
		Functions\expect( 'wp_strip_all_tags' )->once()->with( $selector2 )->andReturn( 'clean selector#2' );
		Functions\expect( 'safecss_filter_attr' )->once()->with( $rules1 )->andReturn( 'cleaned rules#1' );
		Functions\expect( 'safecss_filter_attr' )->once()->with( $rules2 )->andReturn( 'cleaned rules#2' );

		$this->assertSame( $result, $this->public_if->clean_styles( $with_comments ) );
	}
}
