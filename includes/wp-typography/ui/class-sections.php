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
 *  @package mundschenk-at/wp-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\UI;

/**
 * Settings section for wp-Typography.
 *
 * @since 5.2.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
abstract class Sections {

	// Section ID constants.
	const MATH_REPLACEMENTS = 'math-replacements';
	const LINE_WRAPPING     = 'enable-wrapping';

	/**
	 * The defaults array.
	 *
	 * @var array
	 */
	private static $sections;

	/**
	 * Retrieves the settings page sections.
	 *
	 * @return array {
	 *         @type array $id {
	 *               The form ID.
	 *
	 *               @type string $heading     Section name (translated).
	 *               @type string $description Section description (translated).
	 *               @type string $tab_id      Tab ID.
	 *         }
	 * }
	 */
	public static function get_sections() {
		if ( empty( self::$sections ) ) {
			self::$sections = [ // @codeCoverageIgnore
				self::MATH_REPLACEMENTS => [
					'heading'     => \__( 'Math & Numbers', 'wp-typography' ),
					'description' => \__( 'Not all number formattings are appropriate for all languages.', 'wp-typography' ),
					'tab_id'      => Tabs::CHARACTER_REPLACEMENT,
				],
				self::LINE_WRAPPING     => [
					'heading'     => \__( 'Enable Wrapping', 'wp-typography' ),
					'description' => \__( 'Sometimes you want to enable certain long words to wrap to a new line, while at other times you want to prevent wrapping.', 'wp-typography' ),
					'tab_id'      => Tabs::SPACE_CONTROL,
				],
			];
		}

		return self::$sections;
	}
}
