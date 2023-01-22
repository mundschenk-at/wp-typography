<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2023 Peter Putzer.
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
use WP_Typography\Implementation;

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
 *
 * @uses ::__construct
 */
class ACF_Integration_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var ACF_Integration&m\MockInterface
	 */
	protected $acf_i;

	/**
	 * Test fixture.
	 *
	 * @var Implementation&m\MockInterface
	 */
	protected $api;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() : void {
		parent::set_up();

		$this->api = m::mock( Implementation::class );

		// Mock WP_Typography\Components\ACF_Integration instance.
		$this->acf_i = m::mock( ACF_Integration::class, [ $this->api ] )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Test __construct.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() : void {
		/**
		 * ACF_Integration mock.
		 *
		 * @var ACF_Integration&m\MockInterface
		 */
		$sut = m::mock( ACF_Integration::class )->shouldAllowMockingProtectedMethods()->makePartial();

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
	 * Provides data for testing run.
	 *
	 * @return mixed[]
	 */
	public function provide_run_data() : array {
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
	public function test_run( $api_version ) : void {
		$this->acf_i->shouldReceive( 'get_acf_version' )->once()->andReturn( $api_version );

		Functions\expect( 'is_admin' )->once()->andReturn( false );
		Actions\expectAdded( 'acf/init' )->never();

		$this->acf_i->run();
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
	public function test_run_admin( $api_version ) : void {
		$this->acf_i->shouldReceive( 'get_acf_version' )->once()->andReturn( $api_version );

		Functions\expect( 'is_admin' )->once()->andReturn( true );
		if ( 5 === $api_version ) {
			Actions\expectAdded( 'acf/init' )->once();
		}

		$this->acf_i->run();
	}


	/**
	 * Test check.
	 *
	 * @covers ::check
	 */
	public function test_check_failing() : void {
		$this->assertFalse( $this->acf_i->check() );
	}

	/**
	 * Test check.
	 *
	 * @covers ::check
	 *
	 * @runInSeperateProcess
	 */
	public function test_check_success() : void {
		m::mock( 'acf' );
		$this->assertTrue( $this->acf_i->check() );
	}

	/**
	 * Test get_filter_tag.
	 *
	 * @covers ::get_filter_tag
	 */
	public function test_get_filter_tag() : void {
		$this->assertSame( 'acf', $this->acf_i->get_filter_tag() );
	}

	/**
	 * Test enable_content_filters.
	 *
	 * @covers ::enable_content_filters
	 * @covers ::get_acf_version
	 */
	public function test_enable_content_filters_acf4() : void {
		$this->setValue( $this->acf_i, 'api_version', 4, ACF_Integration::class );

		Filters\expectAdded( 'acf/format_value_for_api/type=wysiwyg' )->once();
		Filters\expectAdded( 'acf/format_value_for_api/type=textarea' )->once();
		Filters\expectAdded( 'acf/format_value_for_api/type=text' )->once();

		$this->acf_i->enable_content_filters( 666 );

		$this->assertTrue( (bool) \has_filter( 'acf/format_value_for_api/type=wysiwyg', [ $this->api, 'process' ] ) );
		$this->assertTrue( (bool) \has_filter( 'acf/format_value_for_api/type=textarea', [ $this->api, 'process' ] ) );
		$this->assertTrue( (bool) \has_filter( 'acf/format_value_for_api/type=text', [ $this->api, 'process_title' ] ) );
	}

	/**
	 * Test enable_content_filters.
	 *
	 * @covers ::enable_content_filters
	 * @covers ::get_acf_version
	 */
	public function test_enable_content_filters_acf5() : void {
		$this->setValue( $this->acf_i, 'api_version', 5, ACF_Integration::class );

		Filters\expectAdded( 'acf/format_value' )->once();

		$this->acf_i->enable_content_filters( 666 );

		$this->assertTrue( (bool) \has_filter( 'acf/format_value', [ $this->acf_i, 'process_acf5' ] ) );
	}

	/**
	 * Provide data for testing process_acf5.
	 *
	 * @return mixed[]
	 */
	public function provide_process_acf5_data() : array {
		return [
			[
				'some text',
				[
					'type'          => 'text',
					'wp-typography' => 'some-filter',
				],
				'some text_processed',
			],
			[
				666, // not a string.
				[
					'type'          => 'range',
					'wp-typography' => 'some-filter',
				],
				666, // not processed.
			],
			[
				[ 'some text' ],
				[
					'type'          => 'text',
					'wp-typography' => 'some-filter',
				],
				[ 'some text' ],
			],
			[
				[
					'checkbox 1',
					'checkbox 2',
					'checkbox 3',
				],
				[
					'type'          => 'checkbox',
					'wp-typography' => 'some-filter',
				],
				[
					'checkbox 1_processed',
					'checkbox 2_processed',
					'checkbox 3_processed',
				],
			],
			[
				[
					'https://example.org/url1',
					'https://example.org/url2',
					'https://example.org/url3',
				],
				[
					'type'          => 'page_link',
					'wp-typography' => 'some-filter',
				],
				[
					'https://example.org/url1_processed',
					'https://example.org/url2_processed',
					'https://example.org/url3_processed',
				],
			],
			[
				[
					'Some value',
					'Some other value',
				],
				[
					'type'          => 'select',
					'wp-typography' => 'some-filter',
				],
				[
					'Some value_processed',
					'Some other value_processed',
				],
			],
			[
				[
					'foo'     => 'bar',
					'title'   => 'File title',
					'caption' => 'File caption',
				],
				[
					'type'          => 'file',
					'wp-typography' => 'some-filter',
				],
				[
					'foo'     => 'bar',
					'title'   => 'File title_processed',
					'caption' => 'File caption_processed',
				],
			],
			[
				[
					'url'   => 'https://example.org/some/image',
					'title' => 'Image title',
				],
				[
					'type'          => 'image',
					'wp-typography' => 'some-filter',
				],
				[
					'url'   => 'https://example.org/some/image',
					'title' => 'Image title_processed',
				],
			],
			[
				[
					'url'    => 'https://example.org/some/image',
					'title'  => 'Link title',
					'target' => 'ref',
				],
				[
					'type'          => 'link',
					'wp-typography' => 'some-filter',
				],
				[
					'url'    => 'https://example.org/some/image',
					'title'  => 'Link title_processed',
					'target' => 'ref',
				],
			],
		];
	}

	/**
	 * Test process_acf5.
	 *
	 * @covers ::process_acf5
	 *
	 * @dataProvider provide_process_acf5_data
	 *
	 * @param string|string[] $content  The field content.
	 * @param mixed[]         $field    The field settings.
	 * @param string|string[] $expected The expected method name or null.
	 */
	public function test_process_acf5( $content, $field, $expected ) : void {
		$post_id = 77;

		$this->acf_i->shouldReceive( 'process_acf_content' )->zeroOrMoreTimes()->with( m::type( 'string' ), $field )->andReturnUsing(
			function( $content ) {
				return "{$content}_processed";
			}
		);

		$this->assertSame( $expected, $this->acf_i->process_acf5( $content, $post_id, $field ) );
	}

	/**
	 * Provide data for testing process_acf_content.
	 *
	 * @return mixed[]
	 */
	public function provide_process_acf_content_data() : array {
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
	 * Test process_acf_content.
	 *
	 * @covers ::process_acf_content
	 *
	 * @dataProvider provide_process_acf_content_data
	 *
	 * @param string      $filter_setting The field setting.
	 * @param string|null $expected       The expected method name or null.
	 */
	public function test_process_acf_content( $filter_setting, $expected ) : void {
		if ( ! empty( $expected ) ) {
			$this->api->shouldReceive( $expected )->once()->with( 'bla' )->andReturn( 'blabla' );
			$this->assertSame( 'blabla', $this->invokeMethod( $this->acf_i, 'process_acf_content', [ 'bla', [ 'wp-typography' => $filter_setting ] ] ) );
		} else {
			$this->assertSame( 'bla', $this->invokeMethod( $this->acf_i, 'process_acf_content', [ 'bla', [ 'wp-typography' => $filter_setting ] ] ) );
		}
	}

	/**
	 * Test process_acf_content without a set filter.
	 *
	 * @covers ::process_acf_content
	 */
	public function test_process_acf_content_filter_unset() : void {
		$this->assertSame( 'bla', $this->invokeMethod( $this->acf_i, 'process_acf_content', [ 'bla', [] ] ) );
	}

	/**
	 * Test get_acf_version.
	 *
	 * @covers ::get_acf_version
	 */
	public function test_get_acf_version_default() : void {
		$this->assertSame( 4, $this->acf_i->get_acf_version() ); // @phpstan-ignore-line - testing protected method.
	}

	/**
	 * Test get_acf_version.
	 *
	 * @covers ::get_acf_version
	 */
	public function test_get_acf_version_acf5() : void {
		Functions\expect( 'acf_get_setting' )->once()->with( 'version' )->andReturn( '5.5' );

		$this->assertSame( 5, $this->acf_i->get_acf_version() ); // @phpstan-ignore-line - testing protected method.
	}

	/**
	 * Provide data for testing add_field_setting.
	 *
	 * @return mixed[]
	 */
	public function provide_add_field_setting_data() : array {
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
	public function test_add_field_setting( $type, $default ) : void {
		$field = [
			'type' => $type,
		];

		Functions\when( '__' )->returnArg();
		Functions\expect( 'acf_render_field_setting' )->once()->with( $field, m::subset( [ 'default_value' => $default ] ) );

		$this->acf_i->add_field_setting( $field );
	}

	/**
	 * Test initialize_field_settings.
	 *
	 * @covers ::initialize_field_settings
	 */
	public function test_initialize_field_settings() : void {
		Functions\expect( 'acf_get_field_types' )->once()->andReturn(
			[
				'foo' => [],
				'bar' => [],
			]
		);

		Actions\expectAdded( 'acf/render_field_settings/type=foo' )->once()->with( [ $this->acf_i, 'add_field_setting' ], 1 );
		Actions\expectAdded( 'acf/render_field_settings/type=bar' )->once()->with( [ $this->acf_i, 'add_field_setting' ], 1 );

		$this->acf_i->initialize_field_settings();
	}
}
