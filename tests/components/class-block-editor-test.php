<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2020-2024 Peter Putzer.
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

use WP_Typography\Components\Block_Editor;

use WP_Typography\Implementation;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

use org\bovigo\vfs\vfsStream;

/**
 * Block_Editor component unit test.
 *
 * @coversDefaultClass \WP_Typography\Components\Block_Editor
 * @usesDefaultClass \WP_Typography\Components\Block_Editor
 *
 * @uses ::__construct
 */
class Block_Editor_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Block_Editor&m\MockInterface
	 */
	protected $sut;

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

		// Set up virtual filesystem.
		$root = vfsStream::setup( 'root', null, [] );
		\set_include_path( 'vfs://root/' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_include_path

		$this->api = m::mock( Implementation::class );

		// Mock WP_Typography\Components\Block_Editor instance.
		$this->sut = m::mock( Block_Editor::class, [ $this->api ] )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor(): void {
		/**
		 * Block_Editor mock.
		 *
		 * @var Block_Editor&m\MockInterface
		 */
		$sut = m::mock( Block_Editor::class )->shouldAllowMockingProtectedMethods()->makePartial();

		/**
		 * Block_Editor mock.
		 *
		 * @var Implementation&m\MockInterface
		 */
		$api = m::mock( Implementation::class );

		$sut->__construct( $api );

		$this->assert_attribute_same( $api, 'api', $sut );
	}

	/**
	 * Tests run when there is no block editor.
	 *
	 * @covers ::run
	 */
	public function test_run_no_block_editor(): void {
		Actions\expectAdded( 'init' )->never();
		Actions\expectAdded( 'enqueue_block_editor_assets' )->never();

		$this->sut->run();
	}

	/**
	 * Tests run.
	 *
	 * @covers ::run
	 */
	public function test_run(): void {
		Functions\when( 'register_block_type' );

		Actions\expectAdded( 'init' )->with( [ $this->sut, 'register_sidebar_and_blocks' ] )->once();
		Actions\expectAdded( 'enqueue_block_editor_assets' )->with( [ $this->sut, 'enqueue_blocks' ] )->once();
		Actions\expectAdded( 'enqueue_block_editor_assets' )->with( [ $this->sut, 'enqueue_sidebar' ] )->once();

		$this->sut->run();
	}

	/**
	 * Tests register_sidebar_and_blocks.
	 *
	 * @covers ::register_sidebar_and_blocks
	 */
	public function test_register_sidebar_and_blocks(): void {
		$plugin_url     = 'http://my_plugin/url';
		$plugin_version = '6.6.6';
		// Simulate blocks dependencies.
		$blocks_version  = 'fake blocks version';
		$blocks_deps     = [ 'foo', 'bar' ];
		$sidebar_version = 'fake sidebar version';
		$sidebar_deps    = [ 'foo', 'bar', 'baz' ];
		$blocks_asset    = '<?php return [ "dependencies" => ' . \var_export( $blocks_deps, true ) . ', "version" => ' . \var_export( $blocks_version, true ) . ' ]; ?>'; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		$sidebar_asset   = '<?php return [ "dependencies" => ' . \var_export( $sidebar_deps, true ) . ', "version" => ' . \var_export( $sidebar_version, true ) . ' ]; ?>'; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		vfsStream::create(
			[
				'plugin' => [
					'admin' => [
						'block-editor' => [
							'js' => [
								'blocks.asset.php'  => $blocks_asset,
								'plugins.asset.php' => $sidebar_asset,
							],
						],
					],
				],
			]
		);

		$this->api->shouldReceive( 'get_version' )->once()->andReturn( $plugin_version );

		Functions\expect( 'plugins_url' )->once()->with( '', \WP_TYPOGRAPHY_PLUGIN_FILE )->andReturn( $plugin_url );
		Functions\expect( 'wp_register_script' )->once()->with( 'wp-typography-gutenberg-blocks', m::pattern( '/' . \preg_quote( $plugin_url, '/' ) . '.*\\.js$/' ), $blocks_deps, $blocks_version, false );
		Functions\expect( 'wp_register_script' )->once()->with( 'wp-typography-gutenberg-sidebar', m::pattern( '/' . \preg_quote( $plugin_url, '/' ) . '.*\\.js$/' ), $sidebar_deps, $sidebar_version, false );
		Functions\expect( 'wp_register_style' )->once()->with( 'wp-typography-gutenberg-style', m::pattern( '/' . \preg_quote( $plugin_url, '/' ) . '.*\\.css$/' ), [], $plugin_version );
		Functions\expect( 'register_block_type' )->once()->with( 'wp-typography/typography', m::type( 'array' ) );
		Functions\expect( 'wp_set_script_translations' )->once()->with( 'wp-typography-gutenberg-blocks', 'wp-typography' );
		Functions\expect( 'wp_set_script_translations' )->once()->with( 'wp-typography-gutenberg-sidebar', 'wp-typography' );

		$this->sut->register_sidebar_and_blocks();
	}

	/**
	 * Tests enqueue_sidebar.
	 *
	 * @covers ::enqueue_sidebar
	 */
	public function test_enqueue_sidebar(): void {
		Functions\expect( 'is_customize_preview' )->once()->withNoArgs()->andReturn( false );
		Functions\expect( 'wp_enqueue_script' )->once()->with( 'wp-typography-gutenberg-sidebar' );

		$this->sut->enqueue_sidebar();
	}

	/**
	 * Tests enqueue_sidebar.
	 *
	 * @covers ::enqueue_sidebar
	 */
	public function test_enqueue_sidebar_customizer(): void {
		Functions\expect( 'is_customize_preview' )->once()->withNoArgs()->andReturn( true );
		Functions\expect( 'wp_enqueue_script' )->never();

		$this->sut->enqueue_sidebar();
	}

	/**
	 * Tests enqueue_sidebar.
	 *
	 * @covers ::enqueue_sidebar
	 */
	public function test_enqueue_sidebar_widgets_editor(): void {
		global $pagenow;
		$old_pagenow = $pagenow;
		$pagenow     = 'widgets.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Unit test.

		Functions\expect( 'is_customize_preview' )->once()->withNoArgs()->andReturn( false );
		Functions\expect( 'wp_enqueue_script' )->never();

		$this->sut->enqueue_sidebar();

		$pagenow = $old_pagenow; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Unit test.
	}

	/**
	 * Tests enqueue_blocks.
	 *
	 * @covers ::enqueue_blocks
	 */
	public function test_enqueue_blocks(): void {
		Functions\expect( 'wp_enqueue_script' )->once()->with( 'wp-typography-gutenberg-blocks' );

		$this->sut->enqueue_blocks();
	}

	/**
	 * Tests render_typography_block.
	 *
	 * @covers ::render_typography_block
	 */
	public function test_render_typography_block(): void {
		$attributes = [];
		$content    = 'my content';
		$rendered   = 'my rendered block';

		$this->api->shouldReceive( 'process' )->once()->with( $content )->andReturn( $rendered );

		Filters\expectAdded( 'typo_disable_processing_for_post' )->once()->with( '__return_false', 999, 0 );
		Filters\expectRemoved( 'typo_disable_processing_for_post' )->once()->with( '__return_false', 999 );

		$this->assertSame( $rendered, $this->sut->render_typography_block( $attributes, $content ) );
	}
}
