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

use WP_Typography\UI\Input;
use WP_Typography\Data_Storage\Options;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\UI\Input unit test.
 *
 * @coversDefaultClass \WP_Typography\UI\Input
 * @usesDefaultClass \WP_Typography\UI\Input
 *
 * @uses ::__construct
 * @uses \WP_Typography\UI\Control::__construct
 */
class Input_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\UI\Input
	 */
	protected $input;

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

		$this->input = m::mock( Input::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$this->invokeMethod( $this->input, '__construct', [ $this->options, 'input_type', 'id', 'tab_id', 'section', 'default', 'short', 'label', 'help_text', true, [] ] );
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses \WP_Typography\UI\Input::__construct
	 */
	public function test_constructor() {
		$input = m::mock( Input::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$this->invokeMethod( $input, '__construct', [ $this->options, 'input_type', 'id', 'tab_id', 'section', 'default', 'short', 'label', 'help_text', true, [] ] );

		$this->assertSame( 'input_type', $this->getValue( $input, 'input_type' ) );
	}

	/**
	 * Tests get_value_markup.
	 *
	 * @covers ::get_value_markup
	 */
	public function test_get_value_markup() {
		Functions\expect( 'esc_attr' )->once()->with( 'my_value' )->andReturn( 'my_escaped_value' );

		$this->assertSame( 'value="my_escaped_value" ', $this->invokeMethod( $this->input, 'get_value_markup', [ 'my_value' ] ) );
		$this->assertSame( '', $this->invokeMethod( $this->input, 'get_value_markup', [ false ] ) );
	}

	/**
	 * Tests get_element_markup.
	 *
	 * @covers ::get_element_markup
	 */
	public function test_get_element_markup() {
		Functions\expect( 'esc_attr' )->once()->with( 'input_type' )->andReturn( 'escaped_input_type' );

		$this->input->shouldReceive( 'get_value' )->once()->andReturn( 'value' );
		$this->input->shouldReceive( 'get_value_markup' )->once()->with( 'value' )->andReturn( 'VALUE' );
		$this->input->shouldReceive( 'get_id_and_class_markup' )->once()->andReturn( 'ID_AND_CLASS' );

		$this->assertSame( '<input type="escaped_input_type" ID_AND_CLASS VALUE/>', $this->invokeMethod( $this->input, 'get_element_markup' ) );
	}
}
