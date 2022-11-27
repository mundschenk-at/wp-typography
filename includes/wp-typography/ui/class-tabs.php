<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2022 Peter Putzer.
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
 * Settings tabs for wp-Typography.
 *
 * @since  5.1.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
abstract class Tabs {

	// Tab ID constants.
	const GENERAL_SCOPE         = 'general-scope';
	const HYPHENATION           = 'hyphenation';
	const CHARACTER_REPLACEMENT = 'character-replacement';
	const SPACE_CONTROL         = 'space-control';
	const CSS_HOOKS             = 'css-hooks';

	/**
	 * The defaults array.
	 *
	 * @var array
	 */
	private static $tabs;

	/**
	 * Retrieves the settings page tabs.
	 *
	 * @return array {
	 *      @type array $id {
	 *            The tab ID.
	 *
	 *            @type string $heading     Tab heading (translated).
	 *            @type string $description Tab description (translated).
	 *      }
	 * }
	 */
	public static function get_tabs() : array {
		if ( empty( self::$tabs ) ) {
			self::$tabs = [ // @codeCoverageIgnore
				self::GENERAL_SCOPE         => [
					'heading'     => \__( 'General Scope', 'wp-typography' ),
					'description' => \__( 'By default, wp-Typography processes all post content and titles (but not the whole page). Certain HTML elements within your content can be exempted to prevent conflicts with your theme or other plugins.', 'wp-typography' ),
				],
				self::HYPHENATION           => [
					'heading'     => \__( 'Hyphenation', 'wp-typography' ),
					'description' => \__( 'Hyphenation rules are based on pre-computed dictionaries, but can be fine tuned. Custom hyphenations always override the patterns from the dictionary.', 'wp-typography' ),
				],
				self::CHARACTER_REPLACEMENT => [
					'heading'     => \__( 'Intelligent Character Replacement', 'wp-typography' ),
					'description' => \__( 'Modern keyboards are still based on the limited character range of typewriters. This section allows you to selectively replace typewriter characters with better alternatives.', 'wp-typography' ),
				],
				self::SPACE_CONTROL         => [
					'heading'     => \__( 'Space Control', 'wp-typography' ),
					'description' => \__( 'Take control of space. At least in your WordPress posts.', 'wp-typography' ),
				],
				self::CSS_HOOKS             => [
					'heading'     => \__( 'CSS Hooks', 'wp-typography' ),
					'description' => \__( 'To help with styling your posts, some additional CSS classes can be added automatically.', 'wp-typography' ),
				],
			];
		}

		return self::$tabs;
	}
}
