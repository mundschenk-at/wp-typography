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

namespace WP_Typography\Tests\Integration;

use WP_Typography\Integration\Container;
use WP_Typography\Integration\Plugin_Integration;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Integration\Container unit test.
 *
 * @coversDefaultClass \WP_Typography\Integration\Container
 * @usesDefaultClass \WP_Typography\Integration\Container
 *
 * @uses ::__construct
 */
class Container_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Container
	 */
	protected $integrations;

	/**
	 * Test fixture.
	 *
	 * @var Plugin_Integration
	 */
	protected $int1;

	/**
	 * Test fixture.
	 *
	 * @var Plugin_Integration
	 */
	protected $int2;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->int1 = m::mock( Plugin_Integration::class );
		$this->int2 = m::mock( Plugin_Integration::class );

		// Mock WP_Typography\Components\Container instance.
		$this->integrations = m::mock( Container::class, [ [ $this->int1, $this->int2 ] ] )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$integrations = m::mock( Container::class, [ [ $this->int1, $this->int2 ] ] );

		$this->assert_attribute_count( 2, 'integrations', $integrations );
	}

	/**
	 * Test activate.
	 *
	 * @covers ::activate
	 */
	public function test_activate() {
		$this->int1->shouldReceive( 'check' )->once()->andReturn( true );
		$this->int2->shouldReceive( 'check' )->once()->andReturn( false );

		$this->int1->shouldReceive( 'run' )->once();
		$this->int2->shouldNotReceive( 'run' );

		Filters\expectAdded( 'typo_content_filters' )->once();

		$this->integrations->activate();

		$this->assertTrue( \has_filter( 'typo_content_filters', [ $this->integrations, 'get_content_filters' ] ) );
	}

	/**
	 * Test get_content_filters.
	 *
	 * @covers ::get_content_filters
	 */
	public function test_get_content_filters() {
		// Simulate a previous call to "activate".
		$this->setValue( $this->integrations, 'active_integrations', [ $this->int2 ], Container::class );

		$this->int1->shouldNotReceive( 'get_filter_tag' );
		$this->int2->shouldReceive( 'get_filter_tag' )->once()->andReturn( 'fizban' );

		$result = $this->integrations->get_content_filters( [ 'foo' => 'bar' ] );

		$this->assertArrayHasKey( 'foo', $result );
		$this->assertArrayHasKey( 'fizban', $result );
	}
}
