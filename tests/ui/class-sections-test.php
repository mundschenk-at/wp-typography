<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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

use WP_Typography\UI\Sections;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\UI\Sections unit test.
 *
 * @coversDefaultClass \WP_Typography\UI\Sections
 * @usesDefaultClass \WP_Typography\UI\Sections
 */
class Sections_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test get_sections static method.
	 *
	 * @covers ::get_sections
	 */
	public function test_get_sections() : void {
		Functions\expect( '__' )->atLeast()->once()->with( m::type( 'string' ), 'wp-typography' )->andReturn( 'dummy text' );

		$sections = Sections::get_sections();

		$this->assert_is_array( $sections, 'Sections::get_sections should return an array.' );
		$this->assertGreaterThan( 1, count( $sections ), 'The sections array should contain more than one element.' );
		$this->assertContainsOnly( 'array', $sections );
	}

	/**
	 * Test get_sections static method called twice.
	 *
	 * @covers ::get_sections
	 */
	public function test_get_tabs_again() : void {
		Functions\expect( '__' )->never();

		$sections       = Sections::get_sections();
		$sections_again = Sections::get_sections();

		$this->assertSame( $sections, $sections_again );
	}
}
