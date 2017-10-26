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
	 * @param string  $option_group Application-specific prefix.
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
	 *    @type bool        $inline_help  Optional. Display help inline. Default false.
	 *    @type array       $attributes   Optional. Default [],
	 * }
	 *
	 * @throws \InvalidArgumentException Missing argument.
	 */
	public function __construct( Options $options, $option_group, $id, array $args ) {
		$args = $this->prepare_args( $args, [ 'tab_id', 'default' ] );

		parent::__construct( $options, $option_group, $id, $args['tab_id'], $args['section'], $args['default'], $args['short'], null, $args['help_text'], false, $args['attributes'] );
	}

	/**
	 * Render the value markup for this input.
	 *
	 * @param  mixed $value The input value.
	 * @return string
	 */
	protected function value_markup( $value ) {
		return ! empty( $value ) ? \esc_textarea( $value ) : '';
	}

	/**
	 * Render control-specific HTML.
	 *
	 * @param string|null $label           Translated label (or null).
	 * @param string|null $help_text       Translated help text (or null).
	 * @param string      $html_attributes An HTML attribute string (may be empty).
	 */
	protected function internal_render( $label, $help_text, $html_attributes ) {
		$control_markup = '';
		$id             = \esc_attr( $this->get_id() );
		$help_text      = \wp_kses( $help_text, [
			'code' => [],
		] );

		// Generate markup for label.
		if ( ! empty( $label ) ) {
			$control_markup = "<label for=\"{$id}\">" . \esc_html( $label ) . '</label>';
		}

		// Add the <textarea> itself.
		$control_markup .= "<textarea class=\"large-text\" id=\"{$id}\" name=\"$id\" {$html_attributes}>{$this->value_markup( $this->get_value() )}</textarea>";

		if ( ! empty( $help_text ) ) {
			$control_markup .= '<p class="description">' . $help_text . '</p>';
		}

		echo $control_markup; // WPCS: XSS ok.
	}
}
