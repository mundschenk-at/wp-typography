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

use WP_Typography\Transients;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\Transients unit test for the singleton methods.
 *
 * @coversDefaultClass \WP_Typography\Transients
 * @usesDefaultClass \WP_Typography\Transients
 *
 * @uses ::__construct
 * @uses \WP_Typography\Abstract_Cache::__construct
 */
class Transients_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Transients
	 */
	protected $transients;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		Functions\expect( 'get_transient' )->once()->with( Transients::INCREMENTOR_KEY )->andReturn( 999 );
		Functions\expect( 'get_option' )->once()->with( Transients::BACKLOG_KEY, [] )->andReturn( [
			'bar' => true,
			'baz' => true,
		] );

		$this->transients = m::mock( Transients::class, [] )->shouldAllowMockingProtectedMethods()->makePartial();

		parent::setUp();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
		parent::tearDown();
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 *
	 * @uses \WP_Typography\Abstract_Cache::__construct
	 */
	public function test___construct() {
		Functions\expect( 'get_transient' )->once()->with( Transients::INCREMENTOR_KEY )->andReturn( 0 );
		Functions\expect( 'get_option' )->once()->with( Transients::BACKLOG_KEY, [] )->andReturn( [] );

		$transients = m::mock( Transients::class )->shouldAllowMockingProtectedMethods()->makePartial()
			->shouldReceive( 'invalidate' )->once()
			->getMock();
		$transients->__construct();

		$this->assertInstanceOf( Transients::class, $transients );
	}

	/**
	 * Tests invalidate.
	 *
	 * @covers ::invalidate
	 *
	 * @uses ::get_key
	 */
	public function test_invalidate() {
		Functions\expect( 'delete_transient' )->once()->with( $this->invokeMethod( $this->transients, 'get_key', [ 'bar' ] ) );
		Functions\expect( 'delete_transient' )->once()->with( $this->invokeMethod( $this->transients, 'get_key', [ 'baz' ] ) );
		Functions\expect( 'update_option' )->once()->with( Transients::BACKLOG_KEY, [] );
		Functions\expect( 'set_transient' )->once()->with( Transients::INCREMENTOR_KEY, m::type( 'int' ) );

		$this->transients->invalidate();

		$this->assertTrue( true );
	}

	/**
	 * Tests get.
	 *
	 * @covers ::get
	 */
	public function test_get() {
		$raw_key = 'foo';
		$key     = 'bar_foo';

		$this->transients->shouldReceive( 'get_key' )->once()->with( $raw_key )->andReturn( $key );

		Functions\expect( 'get_transient' )->once()->with( $key )->andReturn( 'bar' );

		$this->assertSame( 'bar', $this->transients->get( $raw_key ) );
	}

	/**
	 * Tests set.
	 *
	 * @covers ::set
	 */
	public function test_set() {
		$value    = 'bar';
		$raw_key  = 'foo';
		$key      = 'bar_foo';
		$duration = 99;

		$this->transients->shouldReceive( 'get_key' )->once()->with( $raw_key )->andReturn( $key );
		$this->transients->shouldReceive( 'store_transient_key' )->once()->with( $raw_key );

		Functions\expect( 'set_transient' )->once()->with( $key, $value, $duration )->andReturn( true );

		$this->assertTrue( $this->transients->set( $raw_key, $value, $duration ) );
	}

	/**
	 * Tests set_large_object. Can't test the failing branch.
	 *
	 * @covers ::set_large_object
	 */
	public function test_set_large_object() {
		$value    = new \stdClass();
		$raw_key  = 'foo';
		$duration = 99;

		$this->transients->shouldReceive( 'set' )->once()->with( $raw_key, m::type( 'string' ), $duration )->andReturn( true );

		$this->assertTrue( $this->transients->set_large_object( $raw_key, $value, $duration ) );
	}

	/**
	 * Tests get_large_object.
	 *
	 * @covers ::get_large_object
	 */
	public function test_get_large_object() {
		$raw_key  = 'foo';

		$this->transients->shouldReceive( 'get' )->once()->with( $raw_key )->andReturn( \base64_encode( \gzencode( \serialize( new \stdClass() ) ) ) ); // @codingStandardsIgnoreLine

		$this->assertInstanceOf( \stdClass::class, $this->transients->get_large_object( $raw_key ) );
	}

	/**
	 * Tests get_large_object (but not found).
	 *
	 * @covers ::get_large_object
	 */
	public function test_get_large_object_not_found() {
		$raw_key  = 'foo';

		$this->transients->shouldReceive( 'get' )->once()->with( $raw_key )->andReturn( false );

		$this->assertFalse( $this->transients->get_large_object( $raw_key ) );
	}

	/**
	 * Tests get_large_object with failing uncompress.
	 *
	 * @covers ::get_large_object
	 */
	public function test_get_large_object_uncompression_failing() {
		$raw_key  = 'foo';

		$this->transients->shouldReceive( 'get' )->once()->with( $raw_key )->andReturn( \base64_encode( \serialize( new \stdClass() ) ) ); // @codingStandardsIgnoreLine

		$this->assertFalse( $this->transients->get_large_object( $raw_key ) );
	}

	/**
	 * Tests store_transient_key.
	 *
	 * @covers ::store_transient_key
	 */
	public function test_store_transient_key() {
		$key  = 'foo';

		Functions\expect( 'update_option' )->once()->with( Transients::BACKLOG_KEY, m::type( 'array' ) )->andReturn( true );

		$this->invokeMethod( $this->transients, 'store_transient_key', [ $key ] );

		$transient_keys = $this->getValue( $this->transients, 'transient_keys' );

		$this->assertNotEmpty( $transient_keys[ $key ] );
	}
}
