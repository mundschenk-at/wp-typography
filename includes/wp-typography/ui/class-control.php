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

use WP_Typography\Data_Storage\Options;

/**
 * Abstract base class for HTML controls.
 */
abstract class Control {

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
	 * An abstraction of the WordPress Options API.
	 *
	 * @since 5.1.0
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * The root path of the plugin.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	protected $plugin_path;

	const ALLOWED_INPUT_ATTRIBUTES = [
		'id'      => [],
		'name'    => [],
		'value'   => [],
		'checked' => [],
		'type'    => [],
	];

	const ALLOWED_HTML = [
		'span'   => [ 'class' => [] ],
		'input'  => self::ALLOWED_INPUT_ATTRIBUTES,
		'select' => self::ALLOWED_INPUT_ATTRIBUTES,
		'option' => [
			'value'    => [],
			'selected' => [],
		],
		'code'   => [],
		'strong' => [],
		'em'     => [],
	];

	/**
	 * Create a new UI control object.
	 *
	 * @param Options     $options      Options API handler.
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
	protected function __construct( Options $options, $id, $tab_id, $section, $default, $short = null, $label = null, $help_text = null, $inline_help = false, $attributes = [] ) {
		$this->options     = $options;
		$this->id          = $id;
		$this->tab_id      = $tab_id;
		$this->section     = $section;
		$this->short       = $short ?: '';
		$this->label       = $label;
		$this->help_text   = $help_text;
		$this->inline_help = $inline_help;
		$this->default     = $default;
		$this->attributes  = $attributes;
		$this->plugin_path = dirname( dirname( dirname( __DIR__ ) ) );
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
		$options = $this->options->get( Options::CONFIGURATION );

		return $options[ $this->id ];
	}

	/**
	 * Renders control-specific HTML.
	 *
	 * @since 5.1.0
	 *
	 * @return void
	 */
	protected function render_element() {
		echo $this->get_element_markup(); // WPCS: XSS ok.
	}

	/**
	 * Retrieves the control-specific HTML markup.
	 *
	 * @return string
	 */
	abstract protected function get_element_markup();

	/**
	 * Render the HTML representation of the control.
	 */
	public function render() {
		require $this->plugin_path . '/admin/partials/ui/control.php';
	}

	/**
	 * Retrieves additional HTML attributes as a string ready for inclusion in markup.
	 *
	 * @return string
	 */
	protected function get_html_attributes() {
		$html_attributes = '';
		if ( ! empty( $this->attributes ) ) {
			foreach ( $this->attributes as $attr => $val ) {
				$html_attributes .= \esc_attr( $attr ) . '="' . \esc_attr( $val ) . '" ';
			}
		}

		return $html_attributes;
	}

	/**
	 * Retrieve default value.
	 *
	 * @return string|int
	 */
	public function get_default() {
		return $this->default;
	}

	/**
	 * Retrieve control ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return "{$this->options->get_name( Options::CONFIGURATION )}[{$this->id}]";
	}


	/**
	 * Retrieves the markup for ID, name and class(es).
	 * Also adds additional attributes if they are set.
	 *
	 * @since 5.1.0
	 *
	 * @return string
	 */
	protected function get_id_and_class_markup() {
		$id = \esc_attr( $this->get_id() );

		// Set default ID & name, no class (except for submit buttons).
		return "id=\"{$id}\" name=\"{$id}\" {$this->get_html_attributes()}";
	}

	/**
	 * Determines if the label contains a placeholder for the actual control element(s).
	 *
	 * @since 5.1.0
	 *
	 * @return bool
	 */
	protected function label_has_placeholder() {
		return false !== strpos( $this->label, '%1$s' );
	}

	/**
	 * Determines if this control has an inline help text to display.
	 *
	 * @since 5.1.0
	 *
	 * @return bool
	 */
	protected function has_inline_help() {
		return $this->inline_help && ! empty( $this->help_text );
	}

	/**
	 * Retrieves the label. If the label text contains a string placeholder, it
	 * is replaced by the control element markup.
	 *
	 * @since 5.1.0
	 *
	 * @var string
	 */
	public function get_label() {
		if ( $this->label_has_placeholder() ) {
			return sprintf( $this->label, $this->get_element_markup() );
		} else {
			return $this->label;
		}
	}

	/**
	 * Register the control with the settings API.
	 *
	 * @param string $option_group Application-specific prefix.
	 */
	public function register( $option_group ) {

		// Register rendering callbacks only for non-grouped controls.
		if ( empty( $this->grouped_with ) ) {
			\add_settings_field( $this->get_id(), $this->short, [ $this, 'render' ], $option_group . $this->tab_id, $this->section );
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
			$control->grouped_with    = $this;
		}
	}
}
