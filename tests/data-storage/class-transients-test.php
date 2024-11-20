<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2024 Peter Putzer.
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

namespace WP_Typography\Tests\Data_Storage;

use WP_Typography\Data_Storage\Transients;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\Data_Storage\Transients unit test for the singleton methods.
 *
 * @coversDefaultClass \WP_Typography\Data_Storage\Transients
 * @usesDefaultClass \WP_Typography\Data_Storage\Transients
 *
 * @uses ::__construct
 */
class Transients_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Transients&m\MockInterface
	 */
	protected $transients;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Mock object without calling the constructor.
		$this->transients = m::mock( Transients::class ) // @phpstan-ignore method.notFound
			->shouldAllowMockingProtectedMethods()
			->makePartial()
			->shouldReceive( 'get' )->once()->with( m::pattern( '/incrementor/' ), true )->andReturn( 0 )
			->shouldReceive( 'invalidate' )->once()
			->getMock();
		$this->transients->__construct( [] );
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 */
	public function test___construct(): void {
		/**
		 * Test fixture.
		 *
		 * @var Transients&m\MockInterface $transients
		 */
		$transients = m::mock( Transients::class )->shouldAllowMockingProtectedMethods()->makePartial() // @phpstan-ignore method.notFound
			->shouldReceive( 'get' )->once()->with( m::pattern( '/incrementor/' ), true )->andReturn( 0 )
			->shouldReceive( 'invalidate' )->once()
			->getMock();
		$transients->__construct();

		$this->assertInstanceOf( Transients::class, $transients );
	}

	/**
	 * Test cache_object.
	 *
	 * @covers ::cache_object
	 */
	public function test_cache_object(): void {
		$key    = 'my_transient_key';
		$object = new \stdClass();
		$handle = 'my_handle';

		$this->transients->shouldReceive( 'set_large_object' )->once()->with( $key, $object, m::type( 'int' ) );

		Filters\expectApplied( 'typo_php_typography_caching_enabled' )
			->once()
			->with( true, $handle );
		Filters\expectApplied( 'typo_php_typography_caching_duration' )
			->once()
			->with( 0, $handle );

		$this->transients->cache_object( $key, $object, $handle );

		$this->assertTrue( 1 === Filters\applied( 'typo_php_typography_caching_enabled' ) );
		$this->assertTrue( 1 === Filters\applied( 'typo_php_typography_caching_duration' ) );
	}
}
