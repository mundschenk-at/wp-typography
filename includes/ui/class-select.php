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
 * HTML <select> element.
 */
class Select extends Control {
	/**
	 * The selectable values.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Create a new select control object.
	 *
	 * @param string $option_group Application-specific prefix.
	 * @param string $id           Control ID (equivalent to option name). Required.
	 * @param array  $args {
	 *    Optional and required arguments.
	 *
	 *    @type string      $tab_id        Tab ID. Required.
	 *    @type string      $section       Section ID. Required.
	 *    @type string|int  $default       The default value. Required, but may be an empty string.
	 *    @type array       $option_values The allowed values. Required.
	 *    @type string|null $short         Optional. Short label. Default null.
	 *    @type string|null $label         Optional. Label content with the position of the control marked as %1$s. Default null.
	 *    @type string|null $help_text     Optional. Help text. Default null.
	 *    @type bool        $inline_help   Optional. Display help inline. Default false.
	 *    @type array       $attributes    Optional. Default [],
	 * }
	 *
	 * @throws \InvalidArgumentException Missing argument.
	 */
	public function __construct( $option_group, $id, array $args ) {
		$args = $this->prepare_args( $args, [ 'tab_id', 'default', 'option_values' ] );

		parent::__construct( $option_group, $id, $args['tab_id'], $args['section'], $args['default'], $args['short'], $args['label'], $args['help_text'], $args['inline_help'], $args['attributes'] );

		$this->options = $args['option_values'];
	}

	/**
	 * Set selectable options.
	 *
	 * @param array $options An array of VALUE => DISPLAY.
	 */
	public function set_options( array $options ) {
		$this->options = $options;
	}

	/**
	 * Retrieve the current value for the control.
	 * May be overridden by subclasses.
	 *
	 * @return mixed
	 */
	protected function get_value() {
		$value = get_option( $this->id );

		// Make sure $value is in $option_values if $option_values is set.
		if ( isset( $this->options ) && ! isset( $this->options[ $value ] ) ) {
			$value = null;
		}

		return $value;
	}

	/**
	 * Render control-specific HTML.
	 *
	 * @param string|null $label           Translated label (or null).
	 * @param string|null $help_text       Translated help text (or null).
	 * @param string      $html_attributes An HTML attribute string (may be empty).
	 */
	protected function internal_render( $label, $help_text, $html_attributes ) {
		$control_markup = $this->control_markup( $label, $help_text );

		$select_markup = '<select id="' . esc_attr( $this->id ) . '" name="' . esc_attr( $this->id ) . '" ' . $html_attributes . '>';
		foreach ( $this->options as $option_value => $display ) {
			$translated_display = esc_html__( $display, 'wp-typography' ); // @codingStandardsIgnoreLine.
			$select_markup .= '<option value="' . esc_attr( $option_value ) . '" ' . selected( $this->get_value(), $option_value, false ) . '>' . $translated_display . '</option>';
		}
		$select_markup .= '</select>';

		printf( $control_markup, $select_markup ); // WPCS: XSS ok.
	}
}
