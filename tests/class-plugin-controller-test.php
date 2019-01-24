<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2019 Peter Putzer.
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

use WP_Typography\Plugin_Controller;
use WP_Typography\Components\Admin_Interface;
use WP_Typography\Components\Public_Interface;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Unit tests for plugin controller.
 *
 * @coversDefaultClass \WP_Typography\Plugin_Controller
 * @usesDefaultClass \WP_Typography\Plugin_Controller
 */
class Plugin_Controller_Test extends TestCase {

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() {
		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, 'instance', null );

		parent::tearDown();
	}

	/**
	 * Tests singleton methods.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {

		$multi = m::mock( \WP_Typography\Components\Multilingual_Support::class )
			->shouldReceive( 'run' )->byDefault()
			->getMock();

		// Mock WP_Typography\Data_Storage\Options instance.
		$options = m::mock( \WP_Typography\Data_Storage\Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Components\Setup instance.
		$setup = m::mock( \WP_Typography\Components\Setup::class )
			->shouldReceive( 'run' )->byDefault()
			->getMock();

		// Mock WP_Typography\Components\Common instance.
		$common = m::mock( \WP_Typography\Components\Common::class )
			->shouldReceive( 'run' )->byDefault()
			->getMock();

		// Mock WP_Typography\Data_Storage\Transients instance.
		$transients = m::mock( \WP_Typography\Data_Storage\Transients::class )
			->shouldReceive( 'get' )->byDefault()->andReturn( false )
			->shouldReceive( 'get_large_object' )->byDefault()->andReturn( false )
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'set_large_object' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Data_Storage\Cache instance.
		$cache = m::mock( \WP_Typography\Data_Storage\Cache::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'invalidate' )->byDefault()
			->getMock();

		// Mock WP_Typography\Components\Admin_Interface instance.
		$admin = m::mock( Admin_Interface::class )
			->shouldReceive( 'run' )->shouldReceive( 'get_default_settings' )->andReturn( [] )->byDefault()
			->getMock();

		// Mock Public_Interface instance.
		$public_if = m::mock( Public_Interface::class )
			->shouldReceive( 'run' )->byDefault()
			->getMock();

		$typo = m::mock( \WP_Typography\Implementation::class );

		$controller = new \WP_Typography\Plugin_Controller( $typo, $setup, $common, $admin, $public_if, $multi, $transients, $cache, $options );

		$this->assertInstanceOf( \WP_Typography\Plugin_Controller::class, $controller );

		return $controller;
	}

	/**
	 * Tests constructor.
	 *
	 * @depends test_constructor
	 *
	 * @covers ::run
	 *
	 * @uses \WP_Typography::set_instance
	 *
	 * @param Plugin_Controller $controller Required.
	 */
	public function test_run( $controller ) {
		foreach ( $this->getValue( $controller, 'plugin_components', Plugin_Controller::class ) as $component ) {
			$component->shouldReceive( 'run' )->once()->with( m::type( \WP_Typography::class ) );
		}

		$this->assertNull( $controller->run() );
	}
}
