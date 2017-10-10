<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
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
 * Abstract base class for HTML controls.
 */
abstract class Control {

	/**
	 * Option group.
	 *
	 * @var string
	 */
	protected $option_group;

	/**
	 * Control ID (= option name).
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Tab ID.
	 *
	 * @var string
	 */
	protected $tab_id;

	/**
	 * Section ID.
	 *
	 * @var string
	 */
	protected $section;

	/**
	 * Short label. Optional.
	 *
	 * @var string|null
	 */
	protected $short;

	/**
	 * Label content with the position of the control marked as %1$s. Optional.
	 *
	 * @var string|null
	 */
	protected $label;

	/**
	 * Help text. Optional.
	 *
	 * @var string|null
	 */
	protected $help_text;

	/**
	 * Whether the help text should be displayed inline.
	 *
	 * @var bool
	 */
	protected $inline_help;

	/**
	 * The default value. Required, but may be an empty string.
	 *
	 * @var string|int
	 */
	protected $default;

	/**
	 * Additional HTML attributes to add.
	 *
	 * @var array {
	 *      Attribute/value pairs.
	 *
	 *      string $attr Attribute value.
	 * }
	 */
	protected $attributes;

	/**
	 * Grouped controls.
	 *
	 * @var array {
	 *      An array of Controls.
	 *
	 *      Control $control Grouped control.
	 * }
	 */
	protected $grouped_controls = [];

	/**
	 * The Control this one is grouped with.
	 *
	 * @var Control|null
	 */
	protected $grouped_with = null;

	/**
	 * Create a new UI control object.
	 *
	 * @param string      $option_group Application-specific prefix.
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
	protected function __construct( $option_group, $id, $tab_id, $section, $default, $short = null, $label = null, $help_text = null, $inline_help = false, $attributes = [] ) {
		$this->option_group = $option_group;
		$this->id           = $id;
		$this->tab_id       = $tab_id;
		$this->section      = $section;
		$this->short        = $short;
		$this->label        = $label;
		$this->help_text    = $help_text;
		$this->inline_help  = $inline_help;
		$this->default      = $default;
		$this->attributes   = $attributes;
	}

	/**
	 * Prepares keyowrd arguments passed via an array for usage.
	 *
	 * @param array $args     Arguments.
	 * @param array $required Required argument names. 'tab_id' is always required.
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException Thrown when a required argument is missing.
	 */
	protected function prepare_args( array $args, array $required ) {

		// Check for required arguments.
		$required = \wp_parse_args( $required, [ 'tab_id' ] );

		foreach ( $required as $property ) {
			if ( ! isset( $args[ $property ] ) ) {
				throw new \InvalidArgumentException( "Missing argument '$property'." );
			}
		}

		// Add default arguments.
		$args = \wp_parse_args( $args, [
			'section'     => $args['tab_id'],
			'short'       => null,
			'label'       => null,
			'help_text'   => null,
			'inline_help' => false,
			'attributes'  => [],
		] );

		return $args;
	}

	/**
	 * Retrieve the current value for the control.
	 * May be overridden by subclasses.
	 *
	 * @return mixed
	 */
	protected function get_value() {
		return \get_option( $this->id );
	}

	/**
	 * Render control-specific HTML.
	 *
	 * @param string|null $label           Translated label (or null).
	 * @param string|null $help_text       Translated help text (or null).
	 * @param string      $html_attributes An HTML attribute string (may be empty).
	 *
	 * @return void
	 */
	abstract protected function internal_render( $label, $help_text, $html_attributes );

	/**
	 * Render the HTML representation of the control.
	 */
	public function render() {
		// Translate label & help_text.
		$label     = isset( $this->label ) ? \__( $this->label, 'wp-typography' ) : null; // @codingStandardsIgnoreLine.
		$help_text = isset( $this->help_text ) ? \__( $this->help_text, 'wp-typography' ) : null; // @codingStandardsIgnoreLine.

		if ( ! empty( $this->grouped_controls ) ) {
			echo '<fieldset><legend class="screen-reader-text">' . \esc_html( $this->short ) . '</legend>';
		}

		// Flatten attributes to string.
		$html_attributes = '';
		if ( ! empty( $this->attributes ) ) {
			foreach ( $this->attributes as $attr => $val ) {
				$html_attributes .= \esc_attr( $attr ) . '="' . \esc_attr( $val ) . '"';
			}
		}

		// Render control-specific HTML code.
		$this->internal_render( $label, $help_text, $html_attributes );

		// Some additional controls to group with this one.
		if ( ! empty( $this->grouped_controls ) ) {
			foreach ( $this->grouped_controls as $control ) {
				echo '<br />';
				$control->render();
			}

			echo '</fieldset>';
		}
	}

	/**
	 * Retrieve default value.
	 *
	 * @var string|int
	 */
	public function get_default() {
		return $this->default;
	}

	/**
	 * Retrieve control ID.
	 *
	 * @var string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Markup the control itself.
	 *
	 * @param string|null $label     Translated label (or null).
	 * @param string|null $help_text Translated help text (or null).
	 *
	 * @return string
	 */
	protected function control_markup( $label, $help_text ) {
		$markup = '%1$s';
		$help_text = \wp_kses( $help_text, [
			'code' => [],
		] );

		if ( ( ! empty( $label ) || ! empty( $this->inline_help ) ) ) {
			$markup = '<label for="' . \esc_attr( $this->id ) . '">';

			if ( ! empty( $label ) ) {
				$markup .= $label;
			} else {
				$markup .= '%1$s';
			}

			if ( ! empty( $this->inline_help ) && ! empty( $help_text ) ) {
				$markup .= ' <span class="description">' . $help_text . '</span>';
			}

			$markup .= '</label>';
		}

		if ( empty( $this->inline_help ) && ! empty( $help_text ) ) {
			$markup .= '<p class="description">' . $help_text . '</p>';
		}

		return $markup;
	}

	/**
	 * Register the control with the settings API.
	 */
	public function register() {
		// Register setting.
		register_setting( $this->option_group . $this->tab_id, $this->id );

		// Add settings fields.
		if ( empty( $this->grouped_with ) ) {
			$short   = isset( $this->short ) ? $this->short : '';
			add_settings_field( $this->id, $short, [ $this, 'render' ], $this->option_group . $this->tab_id, $this->section );
		}
	}

	/**
	 * Group another control with this one.
	 *
	 * @param Control $control Any control.
	 */
	public function add_grouped_control( Control $control ) {
		// Prevent self-references.
		if ( $this !== $control ) {
			$this->grouped_controls[] = $control;
			$control->grouped_with     = $this;
		}
	}
}
