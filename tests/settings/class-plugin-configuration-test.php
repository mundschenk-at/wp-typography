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

namespace WP_Typography\Tests\Settings;

use WP_Typography\Settings\Plugin_Configuration as Config;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\Settings\Plugin_Configuration unit test.
 *
 * @coversDefaultClass \WP_Typography\Settings\Plugin_Configuration
 * @usesDefaultClass \WP_Typography\Settings\Plugin_Configuration
 */
class Plugin_Configuration_Test extends \WP_Typography\Tests\TestCase {


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
		parent::tearDown();
	}

	/**
	 * Test get_defaults static method called twice.
	 *
	 * @covers ::get_defaults
	 * @covers ::get_numeric_option_values
	 * @covers ::get_quote_style_option_values
	 */
	public function test_get_defaults() {
		Functions\expect( '__' )->atLeast()->once()->with( m::type( 'string' ), 'wp-typography' )->andReturn( 'dummy text' );

		$defaults = Config::get_defaults();

		$this->assertInternalType( 'array', $defaults, 'Plugin_Configuration::get_defaults should return an array.' );
		$this->assertGreaterThan( 1, count( $defaults ), 'The defaults array should contain more than one element.' );
		$this->assertContainsOnly( 'array', $defaults );
	}

	/**
	 * Test get_defaults static method called twice.
	 *
	 * @covers ::get_defaults
	 */
	public function test_get_defaults_again() {
		Functions\expect( '__' )->never();

		$defaults       = Config::get_defaults();
		$defaults_again = Config::get_defaults();

		$this->assertSame( $defaults, $defaults_again );
	}
}
