<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2022 Peter Putzer.
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

use WP_Typography\Typography\Custom_Token_Fix;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Custom_Token_Fix unit test.
 *
 * @coversDefaultClass \WP_Typography\Typography\Custom_Token_Fix
 * @usesDefaultClass \WP_Typography\Typography\Custom_Token_Fix
 */
class Custom_Token_Fix_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test get_defaults static method called twice.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Token_fixes\Abstract_Token_Fix::__construct
	 */
	public function test_constructor() : void {
		$fix = new Custom_Token_Fix( 'words' );

		$this->assert_attribute_same( 'words', 'type', $fix );
	}

	/**
	 * Test get_defaults static method called twice.
	 *
	 * @covers ::__construct
	 *
	 * @uses PHP_Typography\Fixes\Token_fixes\Abstract_Token_Fix::__construct
	 */
	public function test_constructor_invalid() : void {
		$this->expectException( \InvalidArgumentException::class );

		$fix = new Custom_Token_Fix( 'foobar' );
	}

	/**
	 * Test apply function.
	 *
	 * @covers ::apply
	 *
	 * @uses ::__construct
	 */
	public function test_apply() : void {
		$type    = 'compound_words';
		$fix     = new Custom_Token_Fix( $type );
		$s       = m::mock( \PHP_Typography\Settings::class );
		$element = new \DOMElement( 'foo', 'content' );
		$node    = $element->firstChild;

		Filters\expectApplied( "typo_custom_{$type}_token_fix" )
			->once()
			->with( [], $node, $s, false )
			->andReturn( [] );

		$this->assertSame( [], $fix->apply( [], $s, false, $node ) );
	}
}
