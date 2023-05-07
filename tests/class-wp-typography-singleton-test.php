<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2023 Peter Putzer.
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

use \WP_Typography\Data_Storage\Options;
use \WP_Typography\Data_Storage\Transients;
use \WP_Typography\Data_Storage\Cache;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography unit test for the singleton methods.
 *
 * @coversDefaultClass \WP_Typography
 * @usesDefaultClass \WP_Typography
 *
 * @uses ::hash_version_string
 */
class WP_Typography_Singleton_Test extends TestCase {

	/**
	 * Necesssary clean-up work.
	 */
	protected function tear_down(): void {

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, 'instance', null );

		parent::tear_down();
	}

	/**
	 * Tests singleton methods.
	 *
	 * @covers ::get_instance
	 * @covers ::set_instance
	 *
	 * @uses \WP_Typography\Data_Storage\Cache::__construct
	 * @uses \WP_Typography\Data_Storage\Options::__construct
	 * @uses \WP_Typography\Data_Storage\Transients::__construct
	 */
	public function test_singleton(): void {

		/**
		 * Options mock.
		 *
		 * @var Options&m\MockInterface
		 */
		$options = m::mock( Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		/**
		 * Transients mock.
		 *
		 * @var Transients&m\MockInterface
		 */
		$transients = m::mock( Transients::class )
			->shouldReceive( 'get' )->byDefault()->andReturn( false )
			->shouldReceive( 'get_large_object' )->byDefault()->andReturn( false )
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'set_large_object' )->andReturn( false )->byDefault()
			->getMock();

		/**
		 * Cache mock.
		 *
		 * @var Cache&m\MockInterface
		 */
		$cache = m::mock( Cache::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'invalidate' )->byDefault()
			->getMock();

		/**
		 * Singleton instance.
		 *
		 * @var \WP_Typography&m\MockInterface
		 */
		$typo = m::mock( \WP_Typography::class );
		\WP_Typography::set_instance( $typo );

		$typo2 = \WP_Typography::get_instance();
		$this->assertSame( $typo, $typo2 );

		// Check ::get_instance (no underscore).
		$typo3 = \WP_Typography::get_instance();
		$this->assertSame( $typo, $typo3 );
	}

	/**
	 * Tests ::get_instance without a previous call to ::_get_instance (i.e. _doing_it_wrong).
	 *
	 * @covers ::get_instance
	 */
	public function test_get_instance_failing(): void {
		$this->expect_exception( \BadMethodCallException::class );
		$this->expect_exception_message_matches( '/WP_Typography::get_instance called without prior plugin intialization/' );

		$typo = \WP_Typography::get_instance();
		$this->assertInstanceOf( \WP_Typography::class, $typo );
	}

	/**
	 * Tests ::get_instance without a previous call to ::_get_instance (i.e. _doing_it_wrong).
	 *
	 * @covers ::set_instance
	 */
	public function test_set_instance_failing(): void {
		$transients = m::mock( \WP_Typography\Data_Storage\Transients::class );
		$cache      = m::mock( \WP_Typography\Data_Storage\Cache::class );
		$options    = m::mock( \WP_Typography\Data_Storage\Options::class );

		/**
		 * Second "singleton" instance.
		 *
		 * @var \WP_Typography&m\MockInterface
		 */
		$typo = m::mock( \WP_Typography::class );
		\WP_Typography::set_instance( $typo );

		$this->expect_exception( \BadMethodCallException::class );
		$this->expect_exception_message_matches( '/WP_Typography::set_instance called more than once/' );

		\WP_Typography::set_instance( $typo );
	}
}
