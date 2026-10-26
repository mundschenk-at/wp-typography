<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2019-2026 Peter Putzer.
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

namespace WP_Typography\Tests\Settings;

use WP_Typography\Settings\Tools;

use PHP_Typography\U;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\Settings\Tools unit test.
 *
 * @coversDefaultClass \WP_Typography\Settings\Tools
 * @usesDefaultClass \WP_Typography\Settings\Tools
 */
class Tools_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test parse_smart_quote_exceptions_string.
	 *
	 * @covers ::parse_smart_quote_exceptions_string
	 *
	 * @uses ::array_map_assoc
	 */
	public function test_parse_smart_quote_exceptions_string(): void {
		$input  = "'tain't,'twere,'twas,'tis,   ,'twill,'til,'bout,'nuff,'round,'cause,'em, ,";
		$result = [
			"'tain't" => U::APOSTROPHE . 'tain' . U::APOSTROPHE . 't',
			"'twere"  => U::APOSTROPHE . 'twere',
			"'twas"   => U::APOSTROPHE . 'twas',
			"'tis"    => U::APOSTROPHE . 'tis',
			"'twill"  => U::APOSTROPHE . 'twill',
			"'til"    => U::APOSTROPHE . 'til',
			"'bout"   => U::APOSTROPHE . 'bout',
			"'nuff"   => U::APOSTROPHE . 'nuff',
			"'round"  => U::APOSTROPHE . 'round',
			"'cause"  => U::APOSTROPHE . 'cause',
			"'em"     => U::APOSTROPHE . 'em',
		];

		$this->assertSame( $result, Tools::parse_smart_quote_exceptions_string( $input ) );
	}

	/**
	 * Test array_map_assoc.
	 *
	 * @covers ::array_map_assoc
	 */
	public function test_array_map_assoc(): void {
		$callback = function ( $key, $value ) {
			return [ "cb_$key" => "cb_$value" ];
		};
		$array    = [
			1       => 'eins',
			'a key' => 'zwei',
			'3'     => 'drei',
		];
		$expected = [
			'cb_1'     => 'cb_eins',
			'cb_a key' => 'cb_zwei',
			'cb_3'     => 'cb_drei',
		];

		Functions\expect( '_deprecated_function' )->once()->with( m::type( 'string' ), '5.8.0' );

		$this->assertSame( $expected, Tools::array_map_assoc( $callback, $array ) );
	}

	/**
	 * Provides data for testing ensure_array.
	 *
	 * @return mixed[]
	 */
	public function provide_ensure_array_data(): array {
		return [
			'empty array'      => [ [], [], ' ' ],
			'non-empty array'  => [ [ 'foo' ], [ 'foo' ], ' ' ],
			'empty string'     => [ [], '', ' ' ],
			'non-empty string' => [ [ 'foo', 'bar' ], 'foo bar', ' ' ],
			'single string'    => [ [ 'foo' ], 'foo', ' ' ],
			'custom separator' => [ [ 'foo', 'bar', 'baz' ], 'foo,bar,baz', ',' ],
		];
	}

	/**
	 * Tests ensure_array.
	 *
	 * @covers ::ensure_array
	 *
	 * @dataProvider provide_ensure_array_data
	 *
	 * @param  string[]         $expected     The expected result.
	 * @param  string[]|string  $config_value The input value.
	 * @param  non-empty-string $separator    The separator.
	 *
	 * @return void
	 */
	public function test_ensure_array( array $expected, $config_value, string $separator ): void {
		$this->assertSame( $expected, Tools::ensure_array( $config_value, $separator ) );
	}
}
