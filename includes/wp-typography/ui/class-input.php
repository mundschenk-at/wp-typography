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

use WP_Typography\Data_Storage\Options;

/**
 * HTML <input> element.
 */
abstract class Input extends Control {

	/**
	 * The input type ('checkbox', ...).
	 *
	 * @var string
	 */
	protected $input_type;

	/**
	 * Create a new input control object.
	 *
	 * @param Options     $options      Options API handler.
	 * @param string      $input_type   HTML input type ('checkbox' etc.). Required.
	 * @param string      $id           Control ID (equivalent to option name). Required.
	 * @param string      $tab_id       Tab ID. Required.
	 * @param string      $section      Section ID. Required.
	 * @param string|int  $default      The default value. Required, but may be an empty string.
	 * @param string|null $short        Optional. Short label. Default null.
	 * @param string|null $label        Optional. Label content with the position of the control marked as %1$s. Default null.
	 * @param string|null $help_text    Optional. Help text. Default null.
	 * @param bool        $inline_help  Optional. Display help inline. Default false.
	 * @param array       $attributes   Optional. Default [].
	 */
	protected function __construct( Options $options, $input_type, $id, $tab_id, $section, $default, $short, $label = null, $help_text = null, $inline_help = false, $attributes = [] ) {
		parent::__construct( $options, $id, $tab_id, $section, $default, $short, $label, $help_text, $inline_help, $attributes );

		$this->input_type = $input_type;
	}

	/**
	 * Retrieves the value markup for this input.
	 *
	 * @param mixed $value The input value.
	 *
	 * @return string
	 */
	protected function get_value_markup( $value ) {
		return $value ? 'value="' . \esc_attr( $value ) . '" ' : '';
	}

	/**
	 * Retrieves the control-specific HTML markup.
	 *
	 * @var string
	 */
	protected function get_element_markup() {
		return '<input type="' . \esc_attr( $this->input_type ) . '" ' . "{$this->get_id_and_class_markup()} {$this->get_value_markup( $this->get_value() )}/>";
	}
}
