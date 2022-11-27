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

namespace WP_Typography\Tests\Settings;

use WP_Typography\Settings\Abstract_Locale_Settings;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\Settings\Quote_Style;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\Settings\Abstract_Locale_Settings unit test.
 *
 * @coversDefaultClass \WP_Typography\Settings\Abstract_Locale_Settings
 * @usesDefaultClass \WP_Typography\Settings\Abstract_Locale_Settings
 *
 * @uses ::__construct
 */
class Abstract_Locale_Settings_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Abstract_Locale_Settings&m\MockInterface
	 */
	protected $locale;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() : void {
		parent::set_up();

		$this->locale = m::mock( Abstract_Locale_Settings::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();
	}

	/**
	 * Test __construct.
	 *
	 * @covers ::__construct
	 * @covers ::priority
	 * @covers ::primary_quote_style
	 * @covers ::secondary_quote_style
	 * @covers ::dash_style
	 * @covers ::use_french_punctuation_spacing
	 */
	public function test_constructor_and_accessors() : void {

		$priority  = 5;
		$dash      = Dash_Style::INTERNATIONAL;
		$primary   = Quote_Style::DOUBLE_CURLED;
		$secondary = Quote_Style::SINGLE_CURLED;
		$french    = true;

		$this->invokeMethod( $this->locale, '__construct', [ $priority, $dash, $primary, $secondary, $french ] );

		$this->assertSame( $priority, $this->locale->priority() );
		$this->assertSame( $dash, $this->locale->dash_style() );
		$this->assertSame( $primary, $this->locale->primary_quote_style() );
		$this->assertSame( $secondary, $this->locale->secondary_quote_style() );
		$this->assertSame( $french, $this->locale->use_french_punctuation_spacing() );
	}

	/**
	 * Test adjust_defaults.
	 *
	 * @covers ::adjust_defaults
	 */
	public function test_adjust_defaults() : void {
		$this->invokeMethod( $this->locale, '__construct', [ 5, 'dash', 'primary', 'secondary', false ] );

		$defaults = [
			'foo'                              => 'bar',
			Config::SMART_DASHES_STYLE         => 'xxx',
			Config::SMART_QUOTES_PRIMARY       => 'xxx',
			Config::SMART_QUOTES_SECONDARY     => 'xxx',
			Config::FRENCH_PUNCTUATION_SPACING => 'xxx',
		];

		$expected = [
			'foo'                              => 'bar',
			Config::SMART_DASHES_STYLE         => 'dash',
			Config::SMART_QUOTES_PRIMARY       => 'primary',
			Config::SMART_QUOTES_SECONDARY     => 'secondary',
			Config::FRENCH_PUNCTUATION_SPACING => false,
		];

		$this->assertSame( $expected, $this->locale->adjust_defaults( $defaults ) );
	}
}
