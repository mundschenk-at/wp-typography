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

use WP_Typography\UI\Hidden_Input;
use WP_Typography\UI\Input;
use WP_Typography\Data_Storage\Options;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\UI\Hidden_Input unit test.
 *
 * @coversDefaultClass \WP_Typography\UI\Hidden_Input
 * @usesDefaultClass \WP_Typography\UI\Hidden_Input
 */
class Hidden_Input_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\UI\Hidden_Input
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
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses \WP_Typography\UI\Input::__construct
	 * @uses \WP_Typography\UI\Control::__construct
	 */
	public function test_constructor() {
		$input = m::mock( Hidden_Input::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$args = [
			'tab_id'      => 'my_tab_id',
			'section'     => 'my_section',
			'default'     => 'my_default',
			'short'       => 'my_short',
			'label'       => 'my_label',
			'help_text'   => 'my_help_text',
			'inline_help' => false,
			'attributes'  => [ 'foo' => 'bar' ],
		];

		$input->shouldReceive( 'prepare_args' )->once()->with( $args, [ 'tab_id', 'default' ] )->andReturn( $args );

		$this->invokeMethod( $input, '__construct', [ $this->options, 'my_id', $args ], Hidden_Input::class );

		$this->assertSame( 'hidden', $this->getValue( $input, 'input_type', Input::class ) );
	}
}
