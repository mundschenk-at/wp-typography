<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2020 Peter Putzer.
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
 */
class Block_Editor_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Block_Editor
	 */
	protected $sut;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		// Set up virtual filesystem.
		$root = vfsStream::setup( 'root', null, [] );
		\set_include_path( 'vfs://root/' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_include_path

		// Mock WP_Typography\Components\Block_Editor instance.
		$this->sut = m::mock( Block_Editor::class )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Tests run when there is no block editor.
	 *
	 * @covers ::run
	 */
	public function test_run_no_block_editor() {
		Actions\expectAdded( 'init' )->never();
		Actions\expectAdded( 'enqueue_block_editor_assets' )->never();

		$this->assertNull( $this->sut->run() );
	}

	/**
	 * Tests run.
	 *
	 * @covers ::run
	 */
	public function test_run() {
		Functions\when( 'register_block_type' );

		Actions\expectAdded( 'init' )->with( [ $this->sut, 'register_sidebar' ] )->once();
		Actions\expectAdded( 'enqueue_block_editor_assets' )->with( [ $this->sut, 'enqueue_sidebar' ] )->once();

		$this->assertNull( $this->sut->run() );
	}

	/**
	 * Tests register_sidebar.
	 *
	 * @covers ::register_sidebar
	 */
	public function test_register_sidebar() {
		$plugin_url = 'http://my_plugin/url';
		// Simulate blocks dependencies.
		$blocks_version = 'fake blocks version';
		$blocks_deps    = [ 'foo', 'bar' ];
		$asset          = '<?php return [ "dependencies" => ' . \var_export( $blocks_deps, true ) . ', "version" => ' . \var_export( $blocks_version, true ) . ' ]; ?>'; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		vfsStream::create(
			[
				'plugin' => [
					'admin' => [
						'blocks' => [
							'js' => [
								'index.asset.php' => $asset,
							],
						],
					],
				],
			]
		);

		Functions\expect( 'plugins_url' )->once()->with( '', \WP_TYPOGRAPHY_PLUGIN_FILE )->andReturn( $plugin_url );
		Functions\expect( 'wp_register_script' )->once()->with( 'wp-typography-gutenberg', m::pattern( '/' . \preg_quote( $plugin_url, '/' ) . '.*\\.js$/' ), $blocks_deps, $blocks_version, false );
		Functions\expect( 'wp_set_script_translations' )->once()->with( 'wp-typography-gutenberg', 'wp-typography' );

		$this->assertNull( $this->sut->register_sidebar() );
	}

	/**
	 * Tests enqueue_sidebar.
	 *
	 * @covers ::enqueue_sidebar
	 */
	public function test_enqueue_sidebar() {
		Functions\expect( 'wp_enqueue_script' )->once()->with( 'wp-typography-gutenberg' );

		$this->assertNull( $this->sut->enqueue_sidebar() );
	}
}
