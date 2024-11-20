<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2024 Peter Putzer.
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

namespace WP_Typography\Tests\Typography;

use WP_Typography\Typography\Custom_Registry;
use WP_Typography\Typography\Custom_Node_Fix;
use WP_Typography\Typography\Custom_Token_Fix;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Custom_Registry unit test.
 *
 * @coversDefaultClass \WP_Typography\Typography\Custom_Registry
 * @usesDefaultClass \WP_Typography\Typography\Custom_Registry
 */
class Custom_Registry_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses WP_Typography\Typography\Custom_Node_Fix::__construct
	 * @uses WP_Typography\Typography\Custom_Token_Fix::__construct
	 * @uses PHP_Typography\Fixes\Token_fixes\Abstract_Token_Fix::__construct
	 */
	public function test_constructor(): void {
		/**
		 * Test fixture.
		 *
		 * @var Custom_Registry&m\MockInterface $registry
		 */
		$registry = m::mock( Custom_Registry::class ) // @phpstan-ignore method.notFound
			->shouldAllowMockingProtectedMethods()
			->makePartial()
			->shouldReceive( 'register_node_fix' )->times( 4 )->with( m::type( Custom_Node_Fix::class ), m::type( 'int' ) )
			->shouldReceive( 'register_token_fix' )->times( 4 )->with( m::type( Custom_Token_Fix::class ) )
			->getMock();

		$registry->__construct();

		$this->assertInstanceOf( Custom_Registry::class, $registry );
	}
}
