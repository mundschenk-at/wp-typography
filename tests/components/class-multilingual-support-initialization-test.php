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

use WP_Typography\Components\Multilingual_Support;

use WP_Typography\Settings\Basic_Locale_Settings;
use WP_Typography\Settings\Locale_Settings;
use WP_Typography\Settings\Plugin_Configuration as Config;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Multilingual_Support initialization unit test.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @coversDefaultClass \WP_Typography\Components\Multilingual_Support
 * @usesDefaultClass \WP_Typography\Components\Multilingual_Support
 *
 * @uses ::run
 */
class Multilingual_Support_Initialization_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Multilingual_Support
	 */
	protected $multi;

	/**
	 * Test fixture.
	 *
	 * @var WP_Typography
	 */
	protected $plugin;

	/**
	 * Test fixture (instance mock).
	 *
	 * @var Basic_Locale_Settings
	 */
	protected $locale;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		$this->plugin = m::mock( \WP_Typography::class )
			->shouldReceive( 'get_version' )->andReturn( '6.6.6' )->byDefault()
			->getMock()->makePartial();

		$this->locale = m::mock( 'overload:' . Basic_Locale_Settings::class, Locale_Settings::class )->makePartial();

		// Mock WP_Typography\Components\Multilingual_Support instance.
		$this->multi = m::mock( Multilingual_Support::class )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tearDown() { // @codingStandardsIgnoreLine
		parent::tearDown();
	}

	/**
	 * Prepare WP_Typography options for a test.
	 *
	 * @param array $options An array of set options.
	 *
	 * @return array The options array.
	 */
	protected function prepareOptions( array $options ) {  // @codingStandardsIgnoreLine
		// Reset options.
		$this->setValue( $this->multi, 'config', $options );

		return $options;
	}

	/**
	 * Test initialize_locale_settings.
	 *
	 * @covers ::initialize_locale_settings
	 */
	public function test_initialize_locale_settings() {
		$this->locale->shouldReceive( 'priority' )->andReturn( 10, 5, 20, 15, 10, 25 );

		$this->assertContainsOnly( Locale_Settings::class, $this->invokeMethod( $this->multi, 'initialize_locale_settings' ) );
	}

}
