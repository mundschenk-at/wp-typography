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

use Dice\Dice;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography_Factory unit test.
 *
 * @coversDefaultClass \WP_Typography_Factory
 * @usesDefaultClass \WP_Typography_Factory
 */
class WP_Typography_Factory_Test extends TestCase {

	/**
	 * Test ::get.
	 *
	 * @covers ::get
	 */
	public function test_get() {
		Functions\expect( 'plugin_basename' )->once()->andReturn( 'path' );

		$this->assertInstanceOf( Dice::class, \WP_Typography_Factory::get( '/dummy/path' ) );
	}
}