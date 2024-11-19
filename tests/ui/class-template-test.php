<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2023-2024 Peter Putzer.
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

namespace WP_Typography\Tests\UI;

use WP_Typography\UI\Template;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

use org\bovigo\vfs\vfsStream;

/**
 * WP_Typography\UI\Template unit test.
 *
 * @coversDefaultClass \WP_Typography\UI\Template
 * @usesDefaultClass \WP_Typography\UI\Template
 *
 * @uses ::__construct
 */
class Template_Test extends \WP_Typography\Tests\TestCase {

	const BASEDIR = 'root/fake/basedir';

	/**
	 * Test fixture.
	 *
	 * @var Template&m\MockInterface
	 */
	protected $sut;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Set up virtual filesystem.
		vfsStream::setup(
			'root',
			null,
			[
				'fake' => [
					'basedir' => [
						'partial_foo.php' => 'FOO',
						'partial_bar.php' => 'BAR',
					],
				],
			]
		);
		set_include_path( 'vfs://root/' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_include_path

		$this->sut = m::mock( Template::class, [ vfsStream::url( self::BASEDIR ) ] );
		$this->sut
			->shouldAllowMockingProtectedMethods()
			->makePartial();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 *
	 * @return void
	 */
	public function test_construct() {
		$basedir         = '/foo/bar';
		$slashed_basedir = '/foo/bar/';

		Functions\expect( 'untrailingslashit' )->once()->with( $slashed_basedir )->andReturn( $basedir );

		$template = m::mock( Template::class, [ $slashed_basedir ] );

		$this->assert_attribute_same( $basedir, 'base_dir', $template );
	}

	/**
	 * Tests print_partial.
	 *
	 * @covers ::print_partial
	 *
	 * @return void
	 */
	public function test_print_partial() {
		$args = [
			'foo' => 'bar',
			'baz' => [
				'bar' => 1,
				'rab' => 0,
			],
		];

		Functions\expect( '_doing_it_wrong' )->never();

		$this->expectOutputString( 'FOO' );

		$this->sut->print_partial( 'partial_foo.php', $args );
	}

	/**
	 * Tests print_partial with invalid arguments.
	 *
	 * @covers ::print_partial
	 *
	 * @return void
	 */
	public function test_print_partial_invalid_args() {
		$args = [
			'1, 2, 3' => 'bar',
			'baz'     => [
				'bar' => 1,
				'rab' => 0,
			],
		];

		Functions\expect( 'esc_html' )->once()->with( m::type( 'string' ) )->andReturnFirstArg();
		Functions\expect( '_doing_it_wrong' )->once()->with( Template::class . '::print_partial', m::type( 'string' ), 'wp-Typography 5.9.2' )->andReturnUsing(
			function () {
				throw new \InvalidArgumentException( '_doing_it_wrong' );
			}
		);

		$this->expectExceptionMessage( '_doing_it_wrong' );
		$this->expectOutputString( '' );

		$this->sut->print_partial( 'partial_foo.php', $args );
	}

	/**
	 * Tests get_partial.
	 *
	 * @covers ::get_partial
	 * @uses ::print_partial
	 *
	 * @return void
	 */
	public function test_get_partial() {
		$args = [
			'foo' => 'bar',
			'baz' => [
				'bar' => 1,
				'rab' => 0,
			],
		];

		Functions\expect( '_doing_it_wrong' )->never();

		$this->assertSame( 'FOO', $this->sut->get_partial( 'partial_foo.php', $args ) );
	}

	/**
	 * Tests get_partial with invalid arguments.
	 *
	 * @covers ::get_partial
	 * @uses ::print_partial
	 *
	 * @return void
	 */
	public function test_get_partial_invalid_args() {
		$args = [
			'1, 2, 3' => 'bar',
			'baz'     => [
				'bar' => 1,
				'rab' => 0,
			],
		];

		Functions\expect( 'esc_html' )->once()->with( m::type( 'string' ) )->andReturnFirstArg();
		Functions\expect( '_doing_it_wrong' )->once()->with( Template::class . '::print_partial', m::type( 'string' ), 'wp-Typography 5.9.2' )->andReturnUsing(
			function () {
				throw new \InvalidArgumentException( '_doing_it_wrong' );
			}
		);

		$this->expectExceptionMessage( '_doing_it_wrong' );

		$this->assertSame( '', $this->sut->get_partial( 'partial_foo.php', $args ) );
	}
}
