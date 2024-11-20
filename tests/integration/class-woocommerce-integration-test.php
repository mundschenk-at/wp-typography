<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2024 Peter Putzer.
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

use WP_Typography\Integration\WooCommerce_Integration;
use WP_Typography\Implementation;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WooCommerce_Integration unit test.
 *
 * @coversDefaultClass \WP_Typography\Integration\WooCommerce_Integration
 * @usesDefaultClass \WP_Typography\Integration\WooCommerce_Integration
 *
 * @uses ::__construct
 */
class WooCommerce_Integration_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var WooCommerce_Integration&m\MockInterface
	 */
	protected $woo_i;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Mock WP_Typography\Components\WooCommerce_Integration instance.
		$this->woo_i = m::mock( WooCommerce_Integration::class )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Test __construct.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor(): void {
		/**
		 * WooCommerce_Integration mock.
		 *
		 * @var WooCommerce_Integration&m\MockInterface
		 */
		$sut = m::mock( WooCommerce_Integration::class )->shouldAllowMockingProtectedMethods()->makePartial();

		/**
		 * Implementation mock.
		 *
		 * @var Implementation&m\MockInterface
		 */
		$api = m::mock( Implementation::class );

		$sut->__construct( $api );

		$this->assert_attribute_same( $api, 'api', $sut );
	}


	/**
	 * Test run.
	 *
	 * @covers ::run
	 */
	public function test_run(): void {
		$this->assertNull( $this->invokeMethod( $this->woo_i, 'run' ) );
	}

	/**
	 * Test check.
	 *
	 * @covers ::check
	 */
	public function test_check_failing(): void {
		$this->assertFalse( $this->woo_i->check() );
	}

	/**
	 * Test check.
	 *
	 * @covers ::check
	 *
	 * @runInSeperateProcess
	 */
	public function test_check_success(): void {
		m::mock( 'WooCommerce' );
		$this->assertTrue( $this->woo_i->check() );
	}

	/**
	 * Test get_filter_tag.
	 *
	 * @covers ::get_filter_tag
	 */
	public function test_get_filter_tag(): void {
		$this->assertSame( 'woocommerce', $this->woo_i->get_filter_tag() );
	}

	/**
	 * Test enable_content_filters.
	 *
	 * @covers ::enable_content_filters
	 */
	public function test_enable_content_filters(): void {
		$api = m::mock( \WP_Typography::class );
		$this->setValue( $this->woo_i, 'api', $api, WooCommerce_Integration::class );

		Filters\expectAdded( 'woocommerce_format_content' )->once();
		Filters\expectAdded( 'woocommerce_add_error' )->once();
		Filters\expectAdded( 'woocommerce_add_success' )->once();
		Filters\expectAdded( 'woocommerce_add_notice' )->once();

		$this->woo_i->enable_content_filters( 666 );

		$this->assertTrue( (bool) \has_filter( 'woocommerce_format_content', [ $api, 'process' ] ) );
		$this->assertTrue( (bool) \has_filter( 'woocommerce_add_error', [ $api, 'process' ] ) );
		$this->assertTrue( (bool) \has_filter( 'woocommerce_add_success', [ $api, 'process' ] ) );
		$this->assertTrue( (bool) \has_filter( 'woocommerce_add_notice', [ $api, 'process' ] ) );
	}
}
