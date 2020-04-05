<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2020 Peter Putzer.
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

use WP_Typography\Typography\Custom_Node_Fix;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Custom_Node_Fix unit test.
 *
 * @coversDefaultClass \WP_Typography\Typography\Custom_Node_Fix
 * @usesDefaultClass \WP_Typography\Typography\Custom_Node_Fix
 */
class Custom_Node_Fix_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test the constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix::__construct
	 */
	public function test_constructor() {
		$fix = new Custom_Node_Fix( 'foobar' );

		$this->assert_attribute_same( 'foobar', 'group', $fix );
	}

	/**
	 * Test apply function.
	 *
	 * @covers ::apply
	 *
	 * @uses ::__construct
	 */
	public function test_apply() {
		$group   = 'foobar';
		$fix     = new Custom_Node_Fix( $group );
		$s       = m::mock( \PHP_Typography\Settings::class );
		$element = new \DOMElement( 'foo', 'content' );
		$node    = $element->firstChild;

		Filters\expectApplied( "typo_custom_{$group}_node_fix" )
			->once()
			->with( 'content', $node, $s, false )
			->andReturn( 'filtered_content' );

		$fix->apply( $node, $s, false );

		$this->assertSame( 'filtered_content', $node->data );
	}
}
