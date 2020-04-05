<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2020 Peter Putzer.
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
	protected function set_up() {
		parent::set_up();

		// Mock WP_Typography\Components\ACF_Integration instance.
		$this->acf_i = m::mock( ACF_Integration::class )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Provides data for testing run.
	 *
	 * @return array
	 */
	public function provide_run_data() {
		return [
			[ 4 ],
			[ 5 ],
		];
	}

	/**
	 * Test run.
	 *
	 * @covers ::run
	 *
	 * @dataProvider provide_run_data
	 *
	 * @param int $api_version Required.
	 */
	public function test_run( $api_version ) {
		$this->acf_i->shouldReceive( 'get_acf_version' )->once()->andReturn( $api_version );

		Functions\expect( 'is_admin' )->once()->andReturn( false );
		Actions\expectAdded( 'acf/init' )->never();

		$this->acf_i->run( m::mock( \WP_Typography::class ) );

		$this->assert_attribute_instance_of( \WP_Typography::class, 'plugin', $this->acf_i );
	}

	/**
	 * Test run.
	 *
	 * @covers ::run
	 *
	 * @dataProvider provide_run_data
	 *
	 * @param int $api_version Required.
	 */
	public function test_run_admin( $api_version ) {
		$this->acf_i->shouldReceive( 'get_acf_version' )->once()->andReturn( $api_version );

		Functions\expect( 'is_admin' )->once()->andReturn( true );
		if ( 5 === $api_version ) {
			Actions\expectAdded( 'acf/init' )->once();
		}

		$this->acf_i->run( m::mock( \WP_Typography::class ) );

		$this->assert_attribute_instance_of( \WP_Typography::class, 'plugin', $this->acf_i );
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
		$plugin = m::mock( \WP_Typography::class );
		$this->setValue( $this->acf_i, 'plugin', $plugin, ACF_Integration::class );
		$this->setValue( $this->acf_i, 'api_version', 4, ACF_Integration::class );

		Filters\expectAdded( 'acf/format_value_for_api/type=wysiwyg' )->once();
		Filters\expectAdded( 'acf/format_value_for_api/type=textarea' )->once();
		Filters\expectAdded( 'acf/format_value_for_api/type=text' )->once();

		$this->acf_i->enable_content_filters( 666 );

		$this->assertTrue( \has_filter( 'acf/format_value_for_api/type=wysiwyg', [ $plugin, 'process' ] ) );
		$this->assertTrue( \has_filter( 'acf/format_value_for_api/type=textarea', [ $plugin, 'process' ] ) );
		$this->assertTrue( \has_filter( 'acf/format_value_for_api/type=text', [ $plugin, 'process_title' ] ) );
	}

	/**
	 * Test enable_content_filters.
	 *
	 * @covers ::enable_content_filters
	 * @covers ::get_acf_version
	 */
	public function test_enable_content_filters_acf5() {
		$this->setValue( $this->acf_i, 'api_version', 5, ACF_Integration::class );

		Filters\expectAdded( 'acf/format_value' )->once();

		$this->acf_i->enable_content_filters( 666 );

		$this->assertTrue( \has_filter( 'acf/format_value', [ $this->acf_i, 'process_acf5' ] ) );
	}

	/**
	 * Provide data for testing process_acf5.
	 *
	 * @return array
	 */
	public function provide_process_acf5_data() {
		return [
			[ ACF_Integration::CONTENT_FILTER, 'process' ],
			[ ACF_Integration::TITLE_FILTER, 'process_title' ],
			[ ACF_Integration::FEED_CONTENT_FILTER, 'process_feed' ],
			[ ACF_Integration::FEED_TITLE_FILTER, 'process_feed_title' ],
			[ ACF_Integration::DO_NOT_FILTER, null ],
			[ '', null ],
			[ 'foo', null ],
		];
	}

	/**
	 * Test process_acf5.
	 *
	 * @covers ::process_acf5
	 *
	 * @dataProvider provide_process_acf5_data
	 *
	 * @param string      $filter_setting The field setting.
	 * @param string|null $expected       The expected method name or null.
	 */
	public function test_process_acf5( $filter_setting, $expected ) {
		$plugin = m::mock( \WP_Typography::class );
		$this->setValue( $this->acf_i, 'plugin', $plugin, ACF_Integration::class );

		if ( ! empty( $expected ) ) {
			$plugin->shouldReceive( $expected )->once()->with( 'bla' )->andReturn( 'blabla' );
			$this->assertSame( 'blabla', $this->acf_i->process_acf5( 'bla', 77, [ 'wp-typography' => $filter_setting ] ) );
		} else {
			$this->assertSame( 'bla', $this->acf_i->process_acf5( 'bla', 77, [ 'wp-typography' => $filter_setting ] ) );
		}
	}

	/**
	 * Test process_acf5 without a set filter.
	 *
	 * @covers ::process_acf5
	 */
	public function test_process_acf5_unset() {
		$plugin = m::mock( \WP_Typography::class );
		$this->setValue( $this->acf_i, 'plugin', $plugin, ACF_Integration::class );

		$this->assertSame( 'bla', $this->acf_i->process_acf5( 'bla', 77, [] ) );
	}

	/**
	 * Test get_acf_version.
	 *
	 * @covers ::get_acf_version
	 */
	public function test_get_acf_version_default() {
		$this->assertSame( 4, $this->acf_i->get_acf_version() );
	}

	/**
	 * Test get_acf_version.
	 *
	 * @covers ::get_acf_version
	 */
	public function test_get_acf_version_acf5() {
		Functions\expect( 'acf_get_setting' )->once()->with( 'version' )->andReturn( '5.5' );

		$this->assertSame( 5, $this->acf_i->get_acf_version() );
	}

	/**
	 * Provide data for testing add_field_setting.
	 *
	 * @return array
	 */
	public function provide_add_field_setting_data() {
		return [
			[ 'wysiwyg', ACF_Integration::CONTENT_FILTER ],
			[ 'textarea', ACF_Integration::CONTENT_FILTER ],
			[ 'text', ACF_Integration::TITLE_FILTER ],
			[ 'foobar', ACF_Integration::DO_NOT_FILTER ],
		];
	}

	/**
	 * Test add_field_setting.
	 *
	 * @covers ::add_field_setting
	 *
	 * @dataProvider provide_add_field_setting_data
	 *
	 * @param string $type    The field type.
	 * @param string $default The default filter setting.
	 */
	public function test_add_field_setting( $type, $default ) {
		$field = [
			'type' => $type,
		];

		Functions\when( '__' )->returnArg();
		Functions\expect( 'acf_render_field_setting' )->once()->with( $field, m::subset( [ 'default_value' => $default ] ) );

		$this->assertNull( $this->acf_i->add_field_setting( $field ) );
	}

	/**
	 * Test initialize_field_settings.
	 *
	 * @covers ::initialize_field_settings
	 */
	public function test_initialize_field_settings() {
		Functions\expect( 'acf_get_field_types' )->once()->andReturn(
			[
				'foo' => [],
				'bar' => [],
			]
		);

		Actions\expectAdded( 'acf/render_field_settings/type=foo' )->once()->with( [ $this->acf_i, 'add_field_setting' ], 1 );
		Actions\expectAdded( 'acf/render_field_settings/type=bar' )->once()->with( [ $this->acf_i, 'add_field_setting' ], 1 );

		$this->assertNull( $this->acf_i->initialize_field_settings() );
	}
}
