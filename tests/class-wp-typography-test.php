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

use WP_Typography_Admin;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

use Mockery as m;

/**
 * WP_Typography unit test.
 *
 * @coversDefaultClass \WP_Typography
 * @usesDefaultClass \WP_Typography
 */
class WP_Typography_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography
	 */
	protected $wp_typo;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine

		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		// Mock WP_Typography_Admin instance.
		$admin_mock = m::mock( \WP_Typography_Admin::class );
		$admin_mock
			->shouldReceive( 'run' )
			->shouldReceive( 'get_default_settings' )->andReturn( [] );

		// Create instance.
		$this->wp_typo = new \WP_Typography( '7.7.7', 'dummy/path', $admin_mock );

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
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography_Admin::__construct
	 */
	public function test_constructor() {
		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		$typo = new \WP_Typography( '6.6.6', 'dummy/path', m::mock( \WP_Typography_Admin::class ) );

		$this->assertInstanceOf( \WP_Typography::class, $typo );
		$this->assertAttributeInstanceOf( \WP_Typography_Admin::class, 'admin', $typo );
		$this->assertAttributeSame( '6.6.6', 'version', $typo );
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
	 * @uses \WP_Typography_Admin::__construct
	 */
	public function test_singleton() {
		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		$admin = m::mock( \WP_Typography_Admin::class );
		$admin->shouldReceive( 'run' )->shouldReceive( 'get_default_settings' )->andReturn( [] );

		$typo = new \WP_Typography( '6.6.6', 'dummy/path', $admin );
		$typo->run();

		$typo2 = \WP_Typography::get_instance();
		$this->assertSame( $typo, $typo2 );

		$this->assertInstanceOf( \WP_Typography::class, $typo );
		$this->assertAttributeInstanceOf( \WP_Typography_Admin::class, 'admin', $typo );
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
	 * @uses \WP_Typography_Admin::__construct
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
	 * @uses \WP_Typography_Admin::__construct
	 *
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage WP_Typography::set_instance called more than once.
	 */
	public function test_set_instance_failing() {
		Functions\expect( 'get_option' )
			->once()->with( 'typo_transient_keys', [] )->andReturn( [] )->andAlsoExpectIt()
			->once()->with( 'typo_cache_keys', [] )->andReturn( [] );

		$admin = m::mock( \WP_Typography_Admin::class );
		$admin->shouldReceive( 'run' )->shouldReceive( 'get_default_settings' )->andReturn( [] );

		$typo = new \WP_Typography( '6.6.6', 'dummy/path', $admin );
		$typo->run();
		$typo->run();
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::run
	 *
	 * @uses ::__construct
	 * @uses ::set_instance
	 * @uses ::get_version
	 * @uses ::get_version_hash
	 * @uses ::hash_version_string
	 * @uses \WP_Typography_Admin::__construct
	 */
	public function test_run() {
		$this->wp_typo->run();

		$this->assertTrue( has_action( 'plugins_loaded', \WP_Typography::class . '->plugins_loaded()', 10 ) );
		$this->assertTrue( has_action( 'init', \WP_Typography::class . '->init()', 10 ) );
		$this->assertAttributeInternalType( 'array', 'default_settings', $this->wp_typo );
	}
}
