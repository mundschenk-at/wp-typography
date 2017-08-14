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
 * HTML <input> element.
 */
class Number_Input extends Input {

	/**
	 * Create a new input control object.
	 *
	 * @param string $option_group Application-specific prefix.
	 * @param string $id           Control ID (equivalent to option name). Required.
	 * @param array  $args {
	 *    Optional and required arguments.
	 *
	 *    @type string      $tab_id       Tab ID. Required.
	 *    @type string      $section      Section ID. Required.
	 *    @type string|int  $default      The default value. Required, but may be an empty string.
	 *    @type string|null $short        Optional. Short label. Default null.
	 *    @type string|null $label        Optional. Label content with the position of the control marked as %1$s. Default null.
	 *    @type string|null $help_text    Optional. Help text. Default null.
	 *    @type bool        $inline_help  Optional. Display help inline. Default false.
	 *    @type array       $attributes   Optional. Default [],
	 * }
	 *
	 * @throws \InvalidArgumentException Missing argument.
	 */
	public function __construct( $option_group, $id, array $args ) {
		$args = $this->prepare_args( $args, [ 'tab_id', 'default' ] );

		parent::__construct( 'number', $option_group, $id, $args['tab_id'], $args['section'], $args['default'], $args['short'], $args['label'], $args['help_text'], $args['inline_help'], $args['attributes'] );
	}

	/**
	 * Render the value markup for this input.
	 *
	 * @param mixed $value The input value.
	 *
	 * @return string
	 */
	protected function value_markup( $value ) {
		return 'value="' . esc_attr( $value ) . '" ';
	}
}
