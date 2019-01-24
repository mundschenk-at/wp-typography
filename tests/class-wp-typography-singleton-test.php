<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2019 Peter Putzer.
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

use PHP_Typography\Hyphenator_Cache;

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
	protected function tearDown() {

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, 'instance', null );

		parent::tearDown();
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
	public function test_singleton() {

		// Mock WP_Typography\Data_Storage\Options instance.
		$options = m::mock( \WP_Typography\Data_Storage\Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
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
	 *
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage WP_Typography::get_instance called without prior plugin intialization.
	 */
	public function test_get_instance_failing() {
		$typo = \WP_Typography::get_instance();
		$this->assertInstanceOf( \WP_Typography::class, $typo );
	}

	/**
	 * Tests ::get_instance without a previous call to ::_get_instance (i.e. _doing_it_wrong).
	 *
	 * @covers ::set_instance
	 *
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage WP_Typography::set_instance called more than once.
	 */
	public function test_set_instance_failing() {
		$transients = m::mock( \WP_Typography\Data_Storage\Transients::class );
		$cache      = m::mock( \WP_Typography\Data_Storage\Cache::class );
		$options    = m::mock( \WP_Typography\Data_Storage\Options::class );

		$typo = m::mock( \WP_Typography::class );
		\WP_Typography::set_instance( $typo );
		\WP_Typography::set_instance( $typo );
	}
}
