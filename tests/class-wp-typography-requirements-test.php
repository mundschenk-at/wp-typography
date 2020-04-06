<?php
/**
 * This file is part of wp-Typography.
 *
 * Copyright 2020 Peter Putzer.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 * @package mundschenk-at/wp-typography/tests
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Tests;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

use Mockery as m;

/**
 * WP_Typography_Requirements unit test.
 *
 * @coversDefaultClass \WP_Typography_Requirements
 * @usesDefaultClass \WP_Typography_Requirements
 */
class WP_Typography_Requirements_Test extends Testcase {

	/**
	 * The system-under-test.
	 *
	 * @var \WP_Typography_Requirements
	 */
	private $sut;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @since 2.3.3 Renamed to `set_up`.
	 */
	protected function set_up() {
		parent::set_up();

		$this->sut = m::mock( \WP_Typography_Requirements::class )->makePartial()->shouldAllowMockingProtectedMethods();
	}

	/**
	 * Test ::__construct.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {

		Functions\expect( 'wp_parse_args' )->andReturnUsing(
			function( $args, $defaults ) {
				return \array_merge( $defaults, $args );
			}
		);
		$req = m::mock( \WP_Typography_Requirements::class )->makePartial();
		$req->__construct( 'some_file' );

		$this->assertSame( 'wp-Typography', $this->get_value( $req, 'plugin_name' ) );
		$this->assertSame( 'wp-typography', $this->get_value( $req, 'textdomain' ) );
		$this->assertSame(
			[
				'php'              => '5.6.0',
				'multibyte'        => true,
				'utf-8'            => true,
				'dom'              => true,
			],
			$this->get_value( $req, 'install_requirements' )
		);
	}

	/**
	 * Test ::get_requirements.
	 *
	 * @covers ::get_requirements
	 */
	public function test_get_requirements() {
		$req_keys = \array_column( $this->sut->get_requirements(), 'enable_key' );

		$this->assertContains( 'dom', $req_keys );
	}

	/**
	 * Test ::check_dom_support (successful).
	 *
	 * @covers ::check_dom_support
	 */
	public function test_check_dom_support() {
		// Mocking tests for PHP extensions is difficult.
		$dom = class_exists( 'DOMDocument' );

		$this->assertSame( $dom, $this->sut->check_dom_support() );
	}

	/**
	 * Test ::admin_notices_dom_disabled.
	 *
	 * @covers ::admin_notices_dom_disabled
	 */
	public function test_admin_notices_dom_disabled() {
		Functions\when( '__' )->returnArg();

		$this->sut->shouldReceive( 'display_error_notice' )->once()->with( m::type( 'string' ), m::type( 'string' ), m::type( 'string' ) );

		$this->assertNull( $this->sut->admin_notices_dom_disabled() );
	}
}
