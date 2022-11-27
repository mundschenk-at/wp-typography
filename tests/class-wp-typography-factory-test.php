<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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

use Dice\Dice;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use org\bovigo\vfs\vfsStream;

use Mockery as m;

use WP_Typography\Factory;

/**
 * Factory unit test.
 *
 * @coversDefaultClass \WP_Typography\Factory
 * @usesDefaultClass \WP_Typography\Factory
 */
class WP_Typography_Factory_Test extends TestCase {

	/**
	 * The system-under-test.
	 *
	 * @var Factory
	 */
	private $sut;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() : void {
		parent::set_up();

		// Set up virtual filesystem.
		vfsStream::setup(
			'root',
			null,
			[
				'wordpress' => [
					'path' => [
						'wp-admin' => [
							'includes' => [
								'plugin.php' => "<?php Brain\\Monkey\\Functions\\expect( 'get_plugin_data' )->once()->andReturn( [ 'Version' => '6.6.6' ] ); ?>",
							],
						],
					],
				],
			]
		);
		set_include_path( 'vfs://root/' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_include_path

		// Set up the mock.
		$this->sut = m::mock( Factory::class )->makePartial()->shouldAllowMockingProtectedMethods();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() : void {
		$this->sut->shouldReceive( 'get_rules' )->never();

		// Manually call constructor.
		$this->sut->__construct();

		$resulting_rules = $this->get_value( $this->sut, 'rules' );
		$this->assert_is_array( $resulting_rules );
		$this->assertCount( 0, $resulting_rules );
	}

	/**
	 * Test ::get_plugin_version.
	 *
	 * @covers ::get_plugin_version
	 */
	public function test_get_plugin_version() : void {
		$version     = '6.6.6';
		$plugin_file = '/the/main/plugin/file.php';

		$this->assertSame( $version, $this->sut->get_plugin_version( $plugin_file ) );
	}

	/**
	 * Test ::get. Should be run after test_get_plugin_version.
	 *
	 * @covers ::get
	 *
	 * @covers ::__construct
	 * @covers ::get_components
	 * @covers ::get_plugin_integrations
	 * @covers ::get_plugin_version
	 * @covers ::get_rules
	 * @covers ::get_supported_locales
	 */
	public function test_get() : void {
		Functions\expect( 'get_plugin_data' )->once()->with( m::type( 'string' ), false, false )->andReturn( [ 'Version' => '42' ] );

		$result1 = Factory::get();

		$this->assertInstanceOf( Factory::class, $result1 );

		$result2 = Factory::get();

		$this->assertSame( $result1, $result2 );
	}
}
