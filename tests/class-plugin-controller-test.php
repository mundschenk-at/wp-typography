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

namespace WP_Typography\Tests;

use WP_Typography\Plugin_Controller;
use WP_Typography\Implementation;
use WP_Typography\Components\Plugin_Component;

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
	 * The system-under-test.
	 *
	 * @var Plugin_Controller&m\MockInterface
	 */
	protected $sut;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() : void {
		parent::set_up();

		$this->sut = m::mock( Plugin_Controller::class )->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tear_down() : void {
		// Reset singleton.
		$this->set_static_value( \WP_Typography::class, 'instance', null );

		parent::tear_down();
	}

	/**
	 * Tests constructor methods.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() : void {

		/**
		 * Implementation mock.
		 *
		 * @var Implementation&m\MockInterface
		 */
		$api = m::mock( Implementation::class );

		/**
		 * Components mock.
		 *
		 * @var array<Plugin_Component&m\MockInterface>
		 */
		$components = [
			m::mock( Plugin_Component::class ),
			m::mock( Plugin_Component::class ),
			m::mock( Plugin_Component::class ),
			m::mock( Plugin_Component::class ),
		];

		$this->sut->__construct( $api, $components );

		$this->assert_attribute_same( $api, 'api', $this->sut );
		$this->assert_attribute_same( $components, 'plugin_components', $this->sut );
	}

	/**
	 * Tests run.
	 *
	 * @covers ::run
	 * @uses \WP_Typography::set_instance
	 */
	public function test_run() : void {
		/**
		 * Implementation mock.
		 *
		 * @var Implementation&m\MockInterface
		 */
		$api = m::mock( \WP_Typography\Implementation::class );

		/**
		 * Components mock.
		 *
		 * @var array<Plugin_Component&m\MockInterface>
		 */
		$components = [
			m::mock( \WP_Typography\Components\Plugin_Component::class ),
			m::mock( \WP_Typography\Components\Plugin_Component::class ),
			m::mock( \WP_Typography\Components\Plugin_Component::class ),
			m::mock( \WP_Typography\Components\Plugin_Component::class ),
		];
		$this->set_value( $this->sut, 'api', $api );
		$this->set_value( $this->sut, 'plugin_components', $components );

		foreach ( $components as $component ) {
			$component->shouldReceive( 'run' )->once();
		}

		$this->assertNull( $this->sut->run() );
		$this->assertSame( $this->get_static_value( \WP_Typography::class, 'instance' ), $api );
	}
}
