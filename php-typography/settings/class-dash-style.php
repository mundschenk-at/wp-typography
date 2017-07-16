<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
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
 *  ***
 *
 *  @package wpTypography/PHPTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Settings;

use \PHP_Typography\Settings;
use \PHP_Typography\U;

/**
 * An interface encapsulating different quote styles.
 *
 * @author Peter Putzer <github@mundschenk.at>
 *
 * @since 5.0.0
 */
abstract class Dash_Style {

	// Valid dash styles.
	const TRADITIONAL_US = 'traditionalUS';
	const INTERNATIONAL  = 'international';

	/**
	 * Available dash styles.
	 *
	 * @var array
	 */
	private static $styles = [
		self::TRADITIONAL_US => [
			self::_PARENTHETICAL       => U::EM_DASH,
			self::_PARENTHETICAL_SPACE => U::THIN_SPACE,
			self::_INTERVAL            => U::EN_DASH,
			self::_INTERVAL_SPACE      => U::THIN_SPACE,
		],
		self::INTERNATIONAL  => [
			self::_PARENTHETICAL       => U::EN_DASH,
			self::_PARENTHETICAL_SPACE => ' ',
			self::_INTERVAL            => U::EN_DASH,
			self::_INTERVAL_SPACE      => U::HAIR_SPACE,
		],
	];

	/**
	 * Interval dash.
	 *
	 * @internal
	 *
	 * @var int
	 */
	const _INTERVAL = 0;

	/**
	 * Interval dash space.
	 *
	 * @internal
	 *
	 * @var int
	 */
	const _INTERVAL_SPACE = 1;

	/**
	 * Parenthetical dash.
	 *
	 * @internal
	 *
	 * @var int
	 */
	const _PARENTHETICAL = 2;

	/**
	 * Parenthetical dash space.
	 *
	 * @internal
	 *
	 * @var int
	 */
	const _PARENTHETICAL_SPACE = 3;

	/**
	 * Creates a new Dashes object in the given style.
	 *
	 * @param string   $style    The dash style.
	 * @param Settings $settings The current settings.
	 *
	 * @return Dashes|null Returns null in case of an invalid $style parameter.
	 */
	public static function get_styled_dashes( $style, Settings $settings ) {
		if ( isset( self::$styles[ $style ] ) ) {
			return new Simple_Dashes(
				self::$styles[ $style ][ self::_PARENTHETICAL ],
				self::$styles[ $style ][ self::_PARENTHETICAL_SPACE ],
				self::$styles[ $style ][ self::_INTERVAL ],
				self::$styles[ $style ][ self::_INTERVAL_SPACE ]
			);
		}

		return null;
	}
}