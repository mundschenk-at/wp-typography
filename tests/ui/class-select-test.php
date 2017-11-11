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

namespace WP_Typography\Tests\UI;

use WP_Typography\UI\Select;
use WP_Typography\Data_Storage\Options;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\UI\Select unit test.
 *
 * @coversDefaultClass \WP_Typography\UI\Select
 * @usesDefaultClass \WP_Typography\UI\Select
 *
 * @uses ::__construct
 * @uses \WP_Typography\UI\Control::__construct
 */
class Select_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\UI\Select
	 */
	protected $select;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		// Mock WP_Typography\Data_Storage\Options instance.
		$this->options = m::mock( Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		$this->select = m::mock( Select::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$args = [
			'tab_id'        => 'my_tab_id',
			'section'       => 'my_section',
			'default'       => 'my_default',
			'short'         => 'my_short',
			'label'         => 'my_label',
			'help_text'     => 'my_help_text',
			'inline_help'   => false,
			'attributes'    => [ 'foo' => 'bar' ],
			'option_values' => [ 'option', 'values', 'three' ],
		];

		$this->select->shouldReceive( 'prepare_args' )->once()->with( $args, [ 'tab_id', 'default', 'option_values' ] )->andReturn( $args );

		$this->invokeMethod( $this->select, '__construct', [ $this->options, 'my_id', $args ], Select::class );
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses \WP_Typography\UI\Select::__construct
	 */
	public function test_constructor() {
		$select = m::mock( Select::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$args = [
			'tab_id'        => 'my_tab_id',
			'section'       => 'my_section',
			'default'       => 'my_default',
			'short'         => 'my_short',
			'label'         => 'my_label',
			'help_text'     => 'my_help_text',
			'inline_help'   => false,
			'attributes'    => [ 'foo' => 'bar' ],
			'option_values' => [ 'option', 'values' ],
		];

		$select->shouldReceive( 'prepare_args' )->once()->with( $args, [ 'tab_id', 'default', 'option_values' ] )->andReturn( $args );

		$this->invokeMethod( $select, '__construct', [ $this->options, 'my_id', $args ], Select::class );

		$this->assertSame( [ 'option', 'values' ], $this->getValue( $select, 'option_values', Select::class ) );
	}

	/**
	 * Tests set_option_values.
	 *
	 * @covers ::set_option_values
	 */
	public function test_set_option_values() {
		$option_values = [
			'my',
			'option',
			'values',
		];

		$this->select->set_option_values( $option_values );

		$this->assertSame( $option_values, $this->getValue( $this->select, 'option_values', Select::class ) );
	}

	/**
	 * Tests get_value.
	 *
	 * @covers ::get_value
	 */
	public function test_get_value() {
		$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( [
			'foo'   => 'bar',
			'my_id' => 2,
		] );

		$this->assertSame( 2, $this->invokeMethod( $this->select, 'get_value' ) );
	}

	/**
	 * Tests get_value when value is not in options.
	 *
	 * @covers ::get_value
	 */
	public function test_get_value_unsuccessful() {
		$this->options->shouldReceive( 'get' )->once()->with( Options::CONFIGURATION )->andReturn( [
			'foo'   => 'bar',
			'my_id' => 'foobar',
		] );

		$this->assertNull( $this->invokeMethod( $this->select, 'get_value' ) );
	}

	/**
	 * Tests get_element_markup.
	 *
	 * @covers ::get_element_markup
	 */
	public function test_get_element_markup() {
		$option_count = count( $this->getValue( $this->select, 'option_values', Select::class ) );

		Functions\expect( 'esc_html__' )->times( $option_count )->with( m::type( 'string' ), 'wp-typography' )->andReturn( 'DISPLAY' );
		Functions\expect( 'selected' )->times( $option_count )->with( 'value', m::type( 'int' ), false )->andReturn( 'SELECTED' );
		Functions\expect( 'esc_attr' )->times( $option_count )->with( m::type( 'int' ) )->andReturn( 'VALUE' );

		$this->select->shouldReceive( 'get_value' )->once()->andReturn( 'value' );
		$this->select->shouldReceive( 'get_id_and_class_markup' )->once()->andReturn( 'ID_AND_CLASS' );

		$this->assertRegExp( "#<select ID_AND_CLASS>(<option value=\"VALUE\" SELECTED>DISPLAY</option>){{$option_count}}</select>#", $this->invokeMethod( $this->select, 'get_element_markup' ) );
	}
}
