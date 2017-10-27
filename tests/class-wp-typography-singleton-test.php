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

namespace WP_Typography\Tests;

use WP_Typography\Components\Admin_Interface;
use WP_Typography\Components\Public_Interface;

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
 * @uses ::__construct
 * @uses ::hash_version_string
 */
class WP_Typography_Singleton_Test extends TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, '_instance', null );

		parent::tearDown();
	}

	/**
	 * Tests singleton methods.
	 *
	 * @covers ::get_instance
	 * @covers ::set_instance
	 *
	 * @uses ::__construct
	 * @uses ::run
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography\Components\Admin_Interface::__construct
	 * @uses \WP_Typography\Data_Storage\Abstract_Cache::__construct
	 * @uses \WP_Typography\Data_Storage\Cache::__construct
	 * @uses \WP_Typography\Components\Public_Interface::__construct
	 * @uses \WP_Typography\Data_Storage\Options::__construct
	 * @uses \WP_Typography\Components\Setup::__construct
	 * @uses \WP_Typography\Data_Storage\Transients::__construct
	 * @uses \WP_Typography\Components\Multilingual::__construct
	 */
	public function test_singleton() {

		$multi = m::mock( \WP_Typography\Components\Multilingual::class );
		$multi->shouldReceive( 'run' );

		// Mock WP_Typography\Data_Storage\Options instance.
		$options = m::mock( \WP_Typography\Data_Storage\Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Components\Setup instance.
		$setup = m::mock( \WP_Typography\Components\Setup::class, [ '/some/path', $options ] )
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
		$admin = m::mock( Admin_Interface::class, [ 'plugin_basename', '/plugin/path', $options ] );
		$admin->shouldReceive( 'run' )->shouldReceive( 'get_default_settings' )->andReturn( [] );

		// Mock Public_Interface instance.
		$public_if = m::mock( Public_Interface::class, [ 'plugin_basename' ] );
		$public_if->shouldReceive( 'run' )->byDefault();

		$typo = new \WP_Typography( '6.6.6', $setup, $admin, $public_if, $multi, $transients, $cache, $options );
		$typo->run();

		$typo2 = \WP_Typography::get_instance();
		$this->assertSame( $typo, $typo2 );

		$this->assertInstanceOf( \WP_Typography::class, $typo );
		$this->assertAttributeSame( '6.6.6', 'version', $typo );

		// Check ::get_instance (no underscore).
		$typo3 = \WP_Typography::get_instance();
		$this->assertSame( $typo, $typo3 );
	}

	/**
	 * Tests ::get_instance without a previous call to ::_get_instance (i.e. _doing_it_wrong).
	 *
	 * @covers ::get_instance
	 *
	 * @uses ::__construct
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography\Components\Admin_Interface::__construct
	 * @uses \WP_Typography\Components\Multilingual::__construct
	 * @uses \WP_Typography\Components\Multilingual::initialize_locale_settings
	 * @uses \WP_Typography\Components\Multilingual::run
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
	 * @uses ::__construct
	 * @uses ::run
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography\Components\Admin_Interface::__construct
	 *
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage WP_Typography::set_instance called more than once.
	 */
	public function test_set_instance_failing() {
		$setup = m::mock( \WP_Typography\Components\Setup::class )
			->shouldReceive( 'run' )->byDefault()
			->getMock();

		$admin = m::mock( Admin_Interface::class );
		$admin->shouldReceive( 'run' )->shouldReceive( 'get_default_settings' )->andReturn( [] );

		$public_if = m::mock( Public_Interface::class );
		$public_if->shouldReceive( 'run' );

		$multi = m::mock( \WP_Typography\Components\Multilingual::class );
		$multi->shouldReceive( 'run' );

		$transients = m::mock( \WP_Typography\Data_Storage\Transients::class );
		$cache      = m::mock( \WP_Typography\Data_Storage\Cache::class );
		$options    = m::mock( \WP_Typography\Data_Storage\Options::class );

		$typo = new \WP_Typography( '6.6.6', $setup, $admin, $public_if, $multi, $transients, $cache, $options );
		$typo->run();
		$typo->run();
	}
}
