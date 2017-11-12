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

namespace WP_Typography\Tests\Components;

use WP_Typography\Components\Common;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Settings\Plugin_Configuration as Config;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Common component unit test.
 *
 * @coversDefaultClass \WP_Typography\Components\Common
 * @usesDefaultClass \WP_Typography\Components\Common
 *
 * @uses ::__construct
 * @uses ::run
 */
class Common_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Common
	 */
	protected $common;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Options
	 */
	protected $options;

	/**
	 * Test fixture.
	 *
	 * @var WP_Typography
	 */
	protected $plugin;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		// Mock WP_Typography\Data_Storage\Options instance.
		$this->options = m::mock( \WP_Typography\Data_Storage\Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Components\Common instance.
		$this->common = m::mock( Common::class, [ $this->options ] )
			->shouldAllowMockingProtectedMethods()->makePartial();

		$this->plugin = m::mock( \WP_Typography::class )->shouldReceive( 'get_version' )->andReturn( '6.6.6' )->byDefault()->getMock();

		// Finish setup.
		$this->common->run( $this->plugin );
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
		parent::tearDown();
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$common = m::mock( Common::class, [ $this->options ] );

		$this->assertAttributeSame( $this->options, 'options', $common );
	}


	/**
	 * Test run.
	 *
	 * @covers ::run
	 */
	public function test_run() {
		Actions\expectAdded( 'init' )->with( [ $this->common, 'init' ] )->once();

		$this->common->run( $this->plugin );

		$this->assertAttributeSame( $this->plugin, 'plugin', $this->common );
	}

	/**
	 * Provide data for testing add_content_filters.
	 *
	 * @return array
	 */
	public function provide_init_data() {
		return [
			[ true, true ],
			[ false, false ],
			[ true, false ],
			[ false, true ],
		];
	}

	/**
	 * Test init
	 *
	 * @covers ::init
	 *
	 * @dataProvider provide_init_data
	 *
	 * @param bool $restore_defaults The typo_restore_defaults value.
	 * @param bool $clear_cache      The typo_clear_cache value.
	 */
	public function test_init( $restore_defaults, $clear_cache ) {
		$this->options->shouldReceive( 'get' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( $restore_defaults );
		$this->options->shouldReceive( 'get' )->once()->with( Options::CLEAR_CACHE )->andReturn( $clear_cache );

		if ( $restore_defaults ) {
			$this->plugin->shouldReceive( 'set_default_options' )->once()->with( true );
		}

		if ( $clear_cache ) {
			$this->plugin->shouldReceive( 'clear_cache' )->once();
		}

		$this->assertNull( $this->common->init() );
	}
}
