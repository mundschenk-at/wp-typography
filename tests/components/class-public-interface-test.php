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

namespace WP_Typography\Tests\Components;

use WP_Typography\Components\Public_Interface;
use WP_Typography\Settings\Plugin_Configuration as Config;
use WP_Typography\Implementation;

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
 */
class Public_Interface_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Public_Interface&m\MockInterface
	 */
	protected $public_if;

	/**
	 * Test fixture.
	 *
	 * @var Implementation&m\MockInterface
	 */
	protected $api;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->api = m::mock( Implementation::class );

		// Mock WP_Typography\Components\Public_Interface instance.
		$this->public_if = m::mock( Public_Interface::class, [ $this->api ] )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Prepare WP_Typography options for a test.
	 *
	 * @param mixed[] $options An array of set options.
	 *
	 * @return mixed[]         The options array.
	 */
	protected function prepareOptions( array $options ): array {
		// Reset options.
		$this->setValue( $this->public_if, 'config', $options );

		return $options;
	}

	/**
	 * Test __construct.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor(): void {
		/**
		 * Public_Intefface mock.
		 *
		 * @var Public_Interface&m\MockInterface
		 */
		$sut = m::mock( Public_Interface::class )->shouldAllowMockingProtectedMethods()->makePartial();

		/**
		 * Implementation mock.
		 *
		 * @var Implementation&m\MockInterface
		 */
		$api = m::mock( Implementation::class );

		$sut->__construct( $api );

		$this->assert_attribute_same( $api, 'api', $sut );
	}

	/**
	 * Test run.
	 *
	 * @covers ::run
	 */
	public function test_run(): void {
		Functions\expect( 'is_admin' )->once()->andReturn( false );
		Actions\expectAdded( 'init' )->once();

		$this->public_if->run();

		$this->assertTrue( (bool) \has_action( 'init', [ $this->public_if, 'init' ] ) );
	}

	/**
	 * Test run on the admin side.
	 *
	 * @covers ::run
	 */
	public function test_run_admin(): void {
		Functions\expect( 'is_admin' )->once()->andReturn( true );
		Actions\expectAdded( 'init' )->never();

		$this->public_if->run();
	}

	/**
	 * Provide data for testing add_content_filters.
	 *
	 * @return mixed[]
	 */
	public function provide_init_data(): array {
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
	public function test_init( $restore_defaults, $clear_cache, $smart_characters, $nextgen ): void {
		$settings = $this->prepareOptions(
			[
				Config::SMART_CHARACTERS => $smart_characters,
			]
		);

		$this->api->shouldReceive( 'get_config' )->once()->andReturn( $settings );
		$this->public_if->shouldReceive( 'add_content_filters' )->once();

		if ( $smart_characters ) {
			Filters\expectAdded( 'run_wptexturize' );
			Functions\expect( 'wptexturize' )->once()->with( ' ', true );
		}

		if ( $nextgen ) {
			m::mock( 'C_NextGEN_Bootstrap' );
		}

		$this->public_if->init();

		self::assertTrue( (bool) \has_filter( 'body_class', [ $this->api, 'filter_body_class' ] ) );

		self::assertTrue( (bool) \has_action( 'wp_enqueue_scripts', [ $this->public_if, 'enqueue_styles' ] ) );
		self::assertTrue( (bool) \has_action( 'wp_enqueue_scripts', [ $this->public_if, 'enqueue_scripts' ] ) );
		self::assertTrue( (bool) \has_action( 'shutdown', [ $this->api, 'save_hyphenator_cache_on_shutdown' ] ) );

		if ( $nextgen ) {
			$this->assert_attribute_same( PHP_INT_MAX, 'filter_priority', $this->public_if );
		}
	}

	/**
	 * Provide data for testing add_content_filters.
	 *
	 * @return mixed[]
	 */
	public function provide_add_content_filters_data(): array {
		return [
			[ true, true, true, '4.8' ],
			[ false, false, false, '4.8.1' ],
			[ true, false, false, '4.6' ],
			[ false, false, false, '4.6' ],
			[ true, false, false, '4.9.5' ],
			[ false, false, false, '4.9.5' ],
		];
	}

	/**
	 * Test add_content_filters
	 *
	 * @covers ::add_content_filters
	 * @covers ::enable_content_filters
	 * @covers ::enable_heading_filters
	 *
	 * @dataProvider provide_add_content_filters_data
	 *
	 * @param bool   $content     Disable content filters if true.
	 * @param bool   $heading     Disable heading filters if true.
	 * @param bool   $title       Disable title filters if true.
	 * @param string $wp_version Simulated WordPress version.
	 */
	public function test_add_content_filters( $content, $heading, $title, $wp_version ): void {

		$content_hooks      = [
			'comment_author',
			'comment_text',
			'the_content',
			'term_description',
			'the_excerpt',
			'the_excerpt_embed',
			'widget_text',
			'wp_dropdown_cats',
		];
		$content_feed_hooks = [
			'term_name',
			'link_name',
		];
		$heading_hooks      = [
			'the_title',
			'widget_title',
		];

		Filters\expectApplied( 'typo_filter_priority' )->once();
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'content' )->andReturn( $content );
		Filters\expectApplied( 'typo_disable_filtering' )->once()->with( false, 'heading' )->andReturn( $heading );

		if ( ! $content ) {
			Functions\expect( 'get_bloginfo' )->once()->with( 'version' )->andReturn( $wp_version );
		}

		$this->public_if->add_content_filters();

		// Content hooks.
		$expected  = ! $content;
		$api_class = \get_class( $this->api );

		foreach ( $content_hooks as $hook ) {
			if ( 'widget_text' === $hook && \version_compare( $wp_version, '4.8', '>=' ) ) {
				$hook .= '_content';
			}

			$found = (bool) \has_filter( $hook, "{$api_class}->process()" );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}

		foreach ( $content_feed_hooks as $hook ) {
			$found = (bool) \has_filter( $hook, "{$api_class}->process_feed()" );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}

		// Heading hooks.
		$expected = ! $heading;
		foreach ( $heading_hooks as $hook ) {
			$found = (bool) \has_filter( $hook, "{$api_class}->process_title()" );
			$this->assertEquals( $expected, $found, "Hook $hook" . ( $expected ? '' : ' not' ) . ' expected, but' . ( $found ? '' : ' not' ) . ' found.' );
		}
	}

	/**
	 * Test enqueue_styles.
	 *
	 * @covers ::enqueue_styles
	 */
	public function test_enqueue_styles_css(): void {
		$custom_style = 'my: css;';
		$clean_style  = 'my: clean css;';
		$this->prepareOptions(
			[
				Config::STYLE_CSS_INCLUDE                => true,
				Config::STYLE_CSS                        => $custom_style,
				Config::HYPHENATE_SAFARI_FONT_WORKAROUND => false,
			]
		);

		Functions\expect( 'wp_register_style' )->once()->with( 'wp-typography-custom', '' );
		Functions\expect( 'wp_enqueue_style' )->once()->with( 'wp-typography-custom' );

		$this->public_if->shouldReceive( 'clean_styles' )->once()->with( $custom_style )->andReturn( $clean_style );

		Functions\expect( 'wp_add_inline_style' )->once()->with( 'wp-typography-custom', $clean_style );

		$this->public_if->enqueue_styles();
	}

	/**
	 * Test enqueue_styles.
	 *
	 * @covers ::enqueue_styles
	 */
	public function test_enqueue_styles_css_disabled(): void {
		$custom_style = 'my: css;';
		$this->prepareOptions(
			[
				Config::STYLE_CSS_INCLUDE                => false,
				Config::STYLE_CSS                        => $custom_style,
				Config::HYPHENATE_SAFARI_FONT_WORKAROUND => false,
			]
		);

		Functions\expect( 'wp_register_style' )->never();
		Functions\expect( 'wp_enqueue_style' )->never();

		$this->public_if->shouldReceive( 'clean_styles' )->never();

		Functions\expect( 'wp_add_inline_style' )->never();

		$this->public_if->enqueue_styles();
	}


	/**
	 * Test enqueue_styles.
	 *
	 * @covers ::enqueue_styles
	 */
	public function test_enqueue_styles_safari_workaround(): void {
		$this->prepareOptions(
			[
				Config::STYLE_CSS_INCLUDE                => false,
				Config::STYLE_CSS                        => ' ',
				Config::HYPHENATE_SAFARI_FONT_WORKAROUND => true,
			]
		);

		Functions\expect( 'wp_register_style' )->once()->with( 'wp-typography-safari-font-workaround', false );
		Functions\expect( 'wp_enqueue_style' )->once()->with( 'wp-typography-safari-font-workaround' );
		Functions\expect( 'wp_add_inline_style' )->once()->with( 'wp-typography-safari-font-workaround', m::type( 'string' ) );

		$this->public_if->enqueue_styles();
	}

	/**
	 * Test enqueue_scripts.
	 *
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts(): void {
		$version = '6.6.6';
		$this->prepareOptions(
			[
				Config::HYPHENATE_CLEAN_CLIPBOARD => true,
			]
		);

		$this->api->shouldReceive( 'get_version' )->once()->andReturn( $version );

		Functions\expect( 'plugin_dir_url' )->once()->with( \WP_TYPOGRAPHY_PLUGIN_FILE )->andReturn( 'dummy/path' );
		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'wp-typography-cleanup-clipboard', m::type( 'string' ), m::type( 'array' ), $version, true );

		$this->public_if->enqueue_scripts();
	}

	/**
	 * Test enqueue_scripts.
	 *
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts_no_clipboard(): void {
		$this->prepareOptions(
			[
				Config::HYPHENATE_CLEAN_CLIPBOARD => false,
			]
		);

		$this->api->shouldReceive( 'get_version' )->never();
		Functions\expect( 'plugin_dir_url' )->never();
		Functions\expect( 'wp_enqueue_script' )->never();

		$this->public_if->enqueue_scripts();
	}

	/**
	 * Test clean_styles.
	 *
	 * @covers ::clean_styles
	 */
	public function test_clean_styles(): void {
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

		$this->assertSame( $result, $this->invokeMethod( $this->public_if, 'clean_styles', [ $with_comments ] ) );
	}

	/**
	 * Test clean_styles.
	 *
	 * @covers ::clean_styles
	 */
	public function test_clean_styles_realworld(): void {
		$input = <<<EOT
		/*
		 * Styled sub- and superscripts.
		 */
		sub, sup {
		  font-size: 75%;
		  line-height: 100%;
		}

		sup {
		  vertical-align: 60%;
		}

		sub {
		  vertical-align: -10%;
		}

		/*
		 * Styled ampersands.
		 */
		.amp {
		  font-family: Baskerville, "Goudy Old Style", Palatino, "Book Antiqua", "Warnock Pro", serif;
		  font-size: 1.1em;
		  font-style: italic;
		  font-weight: normal;
		  line-height: 1em;
		}

		/*
		 * Words in caps.
		 */
		.caps {
		  font-size: 90%;
		}

		/*
		 * Styled initial quotes.
		 */
		.dquo {
		  margin-left: -0.4em;
		}

		.quo {
		  margin-left: -0.2em;
		}

		/*
		 * Hanging punctuation.
		 */
		.pull-single {
		  margin-left: -0.15em;
		}

		.push-single {
		  margin-right: 0.15em;
		}

		.pull-double {
		  margin-left: -0.38em;
		}

		.push-double {
		  margin-right: 0.38em;
		}

		/*
		 * Because formatting .numbers should consider your current font settings, we will not do it here.
		 */
EOT;

		$result = 'sub, sup{font-size: 75%;line-height: 100%;}sup{vertical-align: 60%;}sub{vertical-align: -10%;}.amp{font-family: Baskerville, "Goudy Old Style", Palatino, "Book Antiqua", "Warnock Pro", serif;font-size: 1.1em;font-style: italic;font-weight: normal;line-height: 1em;}.caps{font-size: 90%;}.dquo{margin-left: -0.4em;}.quo{margin-left: -0.2em;}.pull-single{margin-left: -0.15em;}.push-single{margin-right: 0.15em;}.pull-double{margin-left: -0.38em;}.push-double{margin-right: 0.38em;}';

		Functions\expect( 'wp_strip_all_tags' )->atLeast()->once()->with( m::type( 'string' ) )->andReturnUsing(
			function ( $in ) {
				return $in;
			}
		);
		Functions\expect( 'safecss_filter_attr' )->atLeast()->once()->with( m::type( 'string' ) )->andReturnUsing(
			function ( $in ) {
				return $in;
			}
		);

		$this->assertSame( $result, $this->invokeMethod( $this->public_if, 'clean_styles', [ $input ] ) );
	}
}
