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

use WP_Typography\UI\Submit_Input;
use WP_Typography\UI\Input;
use WP_Typography\Data_Storage\Options;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\UI\Submit_Input unit test.
 *
 * @coversDefaultClass \WP_Typography\UI\Submit_Input
 * @usesDefaultClass \WP_Typography\UI\Submit_Input
 *
 * @uses ::__construct
 * @uses \WP_Typography\UI\Input::__construct
 * @uses \WP_Typography\UI\Control::__construct
 */
class Submit_Input_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\UI\Submit_Input
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

		$this->input = m::mock( Submit_Input::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$args = [
			'tab_id'       => 'my_tab_id',
			'section'      => 'my_section',
			'default'      => 'my_default',
			'short'        => 'my_short',
			'label'        => 'my_label',
			'help_text'    => 'my_help_text',
			'inline_help'  => false,
			'attributes'   => [ 'foo' => 'bar' ],
			'button_class' => 'my_class',
		];

		$this->input->shouldReceive( 'prepare_args' )->once()->with( $args, [ 'tab_id', 'default', 'button_class' ] )->andReturn( $args );

		$this->invokeMethod( $this->input, '__construct', [ $this->options, 'my_id', $args ], Submit_Input::class );
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses \WP_Typography\UI\Input::__construct
	 */
	public function test_constructor() {
		$input = m::mock( Submit_Input::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$args = [
			'tab_id'       => 'my_tab_id',
			'section'      => 'my_section',
			'default'      => 'my_default',
			'short'        => 'my_short',
			'label'        => 'my_label',
			'help_text'    => 'my_help_text',
			'inline_help'  => false,
			'attributes'   => [ 'foo' => 'bar' ],
			'button_class' => 'my_class',
		];

		$input->shouldReceive( 'prepare_args' )->once()->with( $args, [ 'tab_id', 'default', 'button_class' ] )->andReturn( $args );

		$this->invokeMethod( $input, '__construct', [ $this->options, 'my_id', $args ], Submit_Input::class );

		$this->assertSame( 'submit', $this->getValue( $input, 'input_type', Input::class ) );
		$this->assertAttributeSame( 'my_class', 'button_class', $input );
	}

	/**
	 * Tests get_id_and_class_markup.
	 *
	 * @covers ::get_id_and_class_markup
	 */
	public function test_get_id_and_class_markup() {
		Functions\expect( 'esc_attr' )->once()->with( 'my_id' )->andReturn( 'my_escaped_id' );
		Functions\expect( 'esc_attr' )->once()->with( 'my_class' )->andReturn( 'my_escaped_class' );

		$this->input->shouldReceive( 'get_id' )->once()->andReturn( 'my_id' );
		$this->input->shouldReceive( 'get_html_attributes' )->once()->andReturn( 'foo="bar"' );

		$this->assertSame( 'name="my_escaped_id" class="my_escaped_class" foo="bar"', $this->invokeMethod( $this->input, 'get_id_and_class_markup' ) );
	}
}
