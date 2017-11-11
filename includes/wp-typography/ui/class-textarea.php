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

use \WP_Typography\Data_Storage\Options;

/**
 * HTML <textarea> element.
 */
class Textarea extends Control {

	/**
	 * Create a new textarea control object.
	 *
	 * @param Options $options      Options API handler.
	 * @param string  $id           Control ID (equivalent to option name). Required.
	 * @param array   $args {
	 *    Optional and required arguments.
	 *
	 *    @type string      $tab_id       Tab ID. Required.
	 *    @type string      $section      Section ID. Required.
	 *    @type string|int  $default      The default value. Required, but may be an empty string.
	 *    @type string      $short        Optional. Short label.
	 *    @type string|null $label        Optional. Label content with the position of the control marked as %1$s. Default null.
	 *    @type string|null $help_text    Optional. Help text. Default null.
	 *    @type array       $attributes   Optional. Default [],
	 * }
	 *
	 * @throws \InvalidArgumentException Missing argument.
	 */
	public function __construct( Options $options, $id, array $args ) {
		$args = $this->prepare_args( $args, [ 'tab_id', 'default' ] );

		parent::__construct( $options, $id, $args['tab_id'], $args['section'], $args['default'], $args['short'], $args['label'], $args['help_text'], false, $args['attributes'] );
	}

	/**
	 * Retrieves the control-specific HTML markup.
	 *
	 * @var string
	 */
	protected function get_element_markup() {
		$value = \esc_textarea( $this->get_value() );

		return "<textarea class=\"large-text\" {$this->get_id_and_class_markup()}>{$value}</textarea>";
	}
}
