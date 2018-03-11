<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018 Peter Putzer.
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

use WP_Typography\Integration\ACF_Integration;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * ACF_Integration unit test.
 *
 * @coversDefaultClass \WP_Typography\Integration\ACF_Integration
 * @usesDefaultClass \WP_Typography\Integration\ACF_Integration
 */
class ACF_Integration_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var ACF_Integration
	 */
	protected $acf_i;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		// Mock WP_Typography\Components\ACF_Integration instance.
		$this->acf_i = m::mock( ACF_Integration::class )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
		parent::tearDown();
	}


	/**
	 * Test run.
	 *
	 * @covers ::run
	 */
	public function test_run() {
		$this->acf_i->run( m::mock( \WP_Typography::class ) );

		$this->assertAttributeInstanceOf( \WP_Typography::class, 'plugin', $this->acf_i );
	}

	/**
	 * Test check.
	 *
	 * @covers ::check
	 *
	 * @runInSeperateProcess
	 */
	public function test_check_failing() {
		$this->assertFalse( $this->acf_i->check() );
	}

	/**
	 * Test check.
	 *
	 * @covers ::check
	 *
	 * @runInSeperateProcess
	 */
	public function test_check_success() {
		m::mock( 'acf' );
		$this->assertTrue( $this->acf_i->check() );
	}

	/**
	 * Test get_filter_tag.
	 *
	 * @covers ::get_filter_tag
	 */
	public function test_get_filter_tag() {
		$this->assertSame( 'acf', $this->acf_i->get_filter_tag() );
	}

	/**
	 * Test enable_content_filters.
	 *
	 * @covers ::enable_content_filters
	 * @covers ::get_acf_version
	 */
	public function test_enable_content_filters_acf4() {
		Filters\expectAdded( 'acf/format_value_for_api/type=wysiwyg' )->once();
		Filters\expectAdded( 'acf/format_value_for_api/type=textarea' )->once();
		Filters\expectAdded( 'acf/format_value_for_api/type=text' )->once();

		$this->acf_i->enable_content_filters( 666 );

		$this->assertTrue( \has_filter( 'acf/format_value_for_api/type=wysiwyg', [ $this->acf_i, 'acf_process' ] ) );
		$this->assertTrue( \has_filter( 'acf/format_value_for_api/type=textarea', [ $this->acf_i, 'acf_process' ] ) );
		$this->assertTrue( \has_filter( 'acf/format_value_for_api/type=text', [ $this->acf_i, 'acf_process_title' ] ) );
	}

	/**
	 * Test enable_content_filters.
	 *
	 * @covers ::enable_content_filters
	 * @covers ::get_acf_version
	 */
	public function test_enable_content_filters_acf5() {
		Filters\expectAdded( 'acf/format_value/type=wysiwyg' )->once();
		Filters\expectAdded( 'acf/format_value/type=textarea' )->once();
		Filters\expectAdded( 'acf/format_value/type=text' )->once();

		Functions\expect( 'acf_get_setting' )->once()->with( 'version' )->andReturn( '5.5' );

		$this->acf_i->enable_content_filters( 666 );

		$this->assertTrue( \has_filter( 'acf/format_value/type=wysiwyg', [ $this->acf_i, 'acf_process' ] ) );
		$this->assertTrue( \has_filter( 'acf/format_value/type=textarea', [ $this->acf_i, 'acf_process' ] ) );
		$this->assertTrue( \has_filter( 'acf/format_value/type=text', [ $this->acf_i, 'acf_process_title' ] ) );
	}

	/**
	 * Test acf_process.
	 *
	 * @covers ::acf_process
	 * @covers ::filter_acf_field
	 */
	public function test_acf_process() {
		$plugin = m::mock( \WP_Typography::class );
		$this->setValue( $this->acf_i, 'plugin', $plugin, ACF_Integration::class );

		Filters\expectApplied( 'typo_filter_acf_field_bar' )->once()->with( true )->andReturn( true );
		$plugin->shouldReceive( 'process' )->once()->with( 'bla' )->andReturn( 'blabla' );
		$this->assertSame( 'blabla', $this->acf_i->acf_process( 'bla', 77, [ 'name' => 'bar' ] ) );

		Filters\expectApplied( 'typo_filter_acf_field_foo' )->once()->with( true )->andReturn( false );
		$plugin->shouldNotReceive( 'process' );
		$this->assertSame( 'bla', $this->acf_i->acf_process( 'bla', 77, [ 'name' => 'foo' ] ) );
	}

	/**
	 * Test acf_process_title.
	 *
	 * @covers ::acf_process_title
	 * @covers ::filter_acf_field
	 */
	public function test_acf_process_title() {
		$plugin = m::mock( \WP_Typography::class );
		$this->setValue( $this->acf_i, 'plugin', $plugin, ACF_Integration::class );

		Filters\expectApplied( 'typo_filter_acf_field_bar' )->once()->with( true )->andReturn( true );
		$plugin->shouldReceive( 'process_title' )->once()->with( 'bla' )->andReturn( 'blabla' );
		$this->assertSame( 'blabla', $this->acf_i->acf_process_title( 'bla', 77, [ 'name' => 'bar' ] ) );

		Filters\expectApplied( 'typo_filter_acf_field_foo' )->once()->with( true )->andReturn( false );
		$plugin->shouldNotReceive( 'process_title' );
		$this->assertSame( 'bla', $this->acf_i->acf_process_title( 'bla', 77, [ 'name' => 'foo' ] ) );
	}
}
