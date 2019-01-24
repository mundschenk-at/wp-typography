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

namespace WP_Typography\Tests\UI;

use WP_Typography\UI\Tabs;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\UI\Tabs unit test.
 *
 * @coversDefaultClass \WP_Typography\UI\Tabs
 * @usesDefaultClass \WP_Typography\UI\Tabs
 */
class Tabs_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test get_tabs static method called twice.
	 *
	 * @covers ::get_tabs
	 */
	public function test_get_tabs() {
		Functions\expect( '__' )->atLeast()->once()->with( m::type( 'string' ), 'wp-typography' )->andReturn( 'dummy text' );

		$tabs = Tabs::get_tabs();

		$this->assertInternalType( 'array', $tabs, 'Plugin_Configuration::get_defaults should return an array.' );
		$this->assertGreaterThan( 1, count( $tabs ), 'The defaults array should contain more than one element.' );
		$this->assertContainsOnly( 'array', $tabs );
	}

	/**
	 * Test get_tabs static method called twice.
	 *
	 * @covers ::get_tabs
	 */
	public function test_get_tabs_again() {
		Functions\expect( '__' )->never();

		$tabs       = Tabs::get_tabs();
		$tabs_again = Tabs::get_tabs();

		$this->assertSame( $tabs, $tabs_again );
	}
}
