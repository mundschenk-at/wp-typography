<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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

namespace WP_Typography\Integration;

/**
 * Admin and frontend integrations for Advanced Custom Fields (https://www.advancedcustomfields.com).
 *
 * @since      5.3.0
 * @author     Peter Putzer <github@mundschenk.at>
 */
class ACF_Integration implements Plugin_Integration {

	/**
	 * The plugin API instance.
	 *
	 * @var \WP_Typography
	 */
	private $plugin;

	/**
	 * Check if the ACF integration should be activated.
	 *
	 * @return bool
	 */
	public function check() {
		return \class_exists( 'acf' );
	}

	/**
	 * Activate the integration.
	 *
	 * @param \WP_Typography $plugin The plugin object.
	 */
	public function run( \WP_Typography $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Retrieves the identifying tag for the frontend content filters.
	 *
	 * @return string
	 */
	public function get_filter_tag() {
		return 'acf';
	}

	/**
	 * Enables frontend content filters.
	 *
	 * @param int $priority The filter priority.
	 */
	public function enable_content_filters( $priority ) {
		// Adjust hook prefix.
		if ( 5 === $this->get_acf_version() ) {
			// Advanced Custom Fields Pro (version 5).
			$acf_prefix = 'acf/format_value';
		} else {
			// Advanced Custom Fields (version 4).
			$acf_prefix = 'acf/format_value_for_api';
		}

		// Other ACF versions (i.e. < 4) are not supported.
		if ( ! empty( $acf_prefix ) ) {
			\add_filter( "{$acf_prefix}/type=wysiwyg",  [ $this, 'acf_process' ],       $priority, 3 );
			\add_filter( "{$acf_prefix}/type=textarea", [ $this, 'acf_process' ],       $priority, 3 );
			\add_filter( "{$acf_prefix}/type=text",     [ $this, 'acf_process_title' ], $priority, 3 );
		}
	}

	/**
	 * Retrieves the ACF API version.
	 *
	 * @return int
	 */
	private function get_acf_version() {
		// We assume version 4 by default.
		$acf_version = 4;

		// Check for newer versions.
		if ( \function_exists( 'acf_get_setting' ) ) {
			$acf_version = \intval( \acf_get_setting( 'version' ) );
		}

		return $acf_version;
	}

	/**
	 * Custom filter for ACF to allow fine-grained control over individual fields.
	 *
	 * @param  string $content The field content.
	 * @param  int    $post_id The post ID.
	 * @param  array  $field   An array containing all the field settings for the field.
	 *
	 * @return string
	 */
	public function acf_process( $content, $post_id, $field ) {
		return $this->filter_acf_field( $content, ! empty( $field['name'] ) ? $field['name'] : '', 'process' );
	}

	/**
	 * Custom filter for ACF to allow fine-grained control over individual fields.
	 *
	 * @param  string $content The field content.
	 * @param  int    $post_id The post ID.
	 * @param  array  $field   An array containing all the field settings for the field.
	 *
	 * @return string
	 */
	public function acf_process_title( $content, $post_id, $field ) {
		return $this->filter_acf_field( $content, ! empty( $field['name'] ) ? $field['name'] : '', 'process_title' );
	}

	/**
	 * Custom filter implementation for ACF fields.
	 *
	 * @param  string $content         The field content.
	 * @param  string $field_name      The field slug.
	 * @param  string $filter_function The name of the filter method.
	 *
	 * @return string
	 */
	private function filter_acf_field( $content, $field_name, $filter_function ) {
		/**
		 * Allows automatic filtering for the ACF field {$field_name}.
		 *
		 * @since 5.3.0
		 *
		 * @param bool $allow Whether to enable or disable filters for the field. Default true.
		 */
		if ( \apply_filters( "typo_filter_acf_field_{$field_name}", true ) ) {
			return $this->plugin->$filter_function( $content );
		} else {
			return $content;
		}
	}
}
