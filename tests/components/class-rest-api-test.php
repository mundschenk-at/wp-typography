<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2020-2022 Peter Putzer.
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

namespace WP_Typography\Tests\Components;

use WP_Typography\Components\REST_API;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * REST_API component unit test.
 *
 * @coversDefaultClass \WP_Typography\Components\REST_API
 * @usesDefaultClass \WP_Typography\Components\REST_API
 */
class REST_API_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var REST_API&m\MockInterface
	 */
	protected $sut;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() : void {
		parent::set_up();

		// Mock WP_Typography\Components\REST_API instance.
		$this->sut = m::mock( REST_API::class )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Tests run.
	 *
	 * @covers ::run
	 */
	public function test_run() : void {
		Actions\expectAdded( 'init' )->with( [ $this->sut, 'register_meta_fields' ] )->once();

		$this->sut->run();
	}

	/**
	 * Tests register_meta_fields.
	 *
	 * @covers ::register_meta_fields
	 */
	public function test_register_meta_fields() : void {
		Functions\expect( 'register_post_meta' )->once()->with( '', REST_API::WP_TYPOGRAPHY_DISABLED_META_KEY, m::type( 'array' ) );

		$this->sut->register_meta_fields();
	}
}
