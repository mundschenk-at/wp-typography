<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2023 Peter Putzer.
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

use WP_Typography\Settings\Basic_Locale_Settings;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\Settings\Dash_Style;
use PHP_Typography\Settings\Quote_Style;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography\Settings\Basic_Locale_Settings unit test.
 *
 * @coversDefaultClass \WP_Typography\Settings\Basic_Locale_Settings
 * @usesDefaultClass \WP_Typography\Settings\Basic_Locale_Settings
 */
class Basic_Locale_Settings_Test extends \WP_Typography\Tests\TestCase {

	/**
	 * Provides data for testing constructors and matching behavior.
	 *
	 * @return mixed[]
	 */
	public function provide_match_data(): array {
		return [
			[
				[ 'de' ],
				[ 'DE' ],
				[ '' ],
				'de',
				'DE',
				'',
				true,
			],
			[
				[ 'de' ],
				[],
				[],
				'de',
				'DE',
				'foo',
				true,
			],
			[
				[ 'de' ],
				[ 'AT' ],
				[],
				'de',
				'DE',
				'foo',
				false,
			],
			[
				[ 'de' ],
				[ 'DE', 'AT' ],
				[ '', '-1901' ],
				'de',
				'DE',
				'',
				true,
			],
		];
	}

	/**
	 * Test __construct & match.
	 *
	 * @dataProvider provide_match_data
	 *
	 * @covers ::__construct
	 * @covers ::match
	 *
	 * @uses WP_Typography\Settings\Abstract_Locale_Settings::__construct
	 *
	 * @param  string[] $languages Allowed language codes.
	 * @param  string[] $countries Allowed country codes.
	 * @param  string[] $modifiers Allowed modifiers.
	 * @param  string   $language  Actual language code.
	 * @param  string   $country   Actual country code.
	 * @param  string   $modifier  Actual modifier.
	 * @param  bool     $expected  Expected result.
	 */
	public function test_constructor_and_match( $languages, $countries, $modifiers, $language, $country, $modifier, $expected ): void {
		/**
		 * Locale mock.
		 *
		 * @var Basic_Locale_Settings&m\MockInterface
		 */
		$locale = m::mock( Basic_Locale_Settings::class, [ $languages, $countries, $modifiers, 'dash', 'primary', 'secondary', false ] )->makePartial();

		$this->assertSame( $expected, $locale->match( $language, $country, $modifier ) );
	}
}
