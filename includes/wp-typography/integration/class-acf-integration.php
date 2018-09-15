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

	const DO_NOT_FILTER       = 'none';
	const CONTENT_FILTER      = 'content';
	const TITLE_FILTER        = 'title';
	const FEED_CONTENT_FILTER = 'feed_content';
	const FEED_TITLE_FILTER   = 'feed_title';

	const FILTER_SETTING = 'wp-typography';

	/**
	 * The plugin API instance.
	 *
	 * @var \WP_Typography
	 */
	private $plugin;

	/**
	 * The ACF API version.
	 *
	 * @var int
	 */
	private $api_version;

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
		$this->plugin      = $plugin;
		$this->api_version = $this->get_acf_version();

		if ( \is_admin() && 5 === $this->api_version ) {
			\add_action( 'acf/render_field_settings', [ $this, 'add_field_setting' ] );
		}
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
		if ( 5 === $this->api_version ) {
			// Advanced Custom Fields Pro (version 5).
			\add_filter( 'acf/format_value', [ $this, 'process_acf5' ], $priority, 3 );
		} else {
			// Advanced Custom Fields (version 4).
			\add_filter( 'acf/format_value_for_api/type=wysiwyg',  [ $this->plugin, 'process' ],       $priority );
			\add_filter( 'acf/format_value_for_api/type=textarea', [ $this->plugin, 'process' ],       $priority );
			\add_filter( 'acf/format_value_for_api/type=text',     [ $this->plugin, 'process_title' ], $priority );
		}
	}

	/**
	 * Adds a custom setting for the wp-Typography filters to the ACF field settings.
	 *
	 * @param array $field The field settings.
	 */
	public function add_field_setting( array $field ) {
		$default = self::DO_NOT_FILTER;

		// Enable filters by default for some field types.
		switch ( isset( $field['type'] ) ? $field['type'] : '' ) {
			case 'wysiwyg':
			case 'textarea':
				$default = self::CONTENT_FILTER;
				break;

			case 'text':
				$default = self::TITLE_FILTER;
				break;
		}

		// Define field properties.
		$props = [
			'label'        => __( 'Typography', 'wp-typography' ),
			'instructions' => __( 'Select the wp-Typography filter to apply', 'wp-typography' ),
			'name'         => self::FILTER_SETTING,
			'type'         => 'select',
			'choices'      => [
				self::DO_NOT_FILTER                     => __( 'Do not filter', 'wp-typography' ),
				__( 'Standard Posts', 'wp-typography' ) => [
					self::CONTENT_FILTER => __( 'Treat as body text', 'wp-typography' ),
					self::TITLE_FILTER   => __( 'Treat as title', 'wp-typography' ),
				],
				__( 'RSS Feeds', 'wp-Typography' )      => [
					self::FEED_CONTENT_FILTER => __( 'Treat as feed body text', 'wp-typography' ),
					self::FEED_TITLE_FILTER   => __( 'Treat as feed title', 'wp-typography' ),
				],
			],
			'default'      => $default,
		];

		// Render the new field setting.
		/* @scrutinizer ignore-call */ \acf_render_field_setting( $field, $props, true );
	}

	/**
	 * Retrieves the ACF API version.
	 *
	 * @return int
	 */
	protected function get_acf_version() {
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
	public function process_acf5( $content, $post_id, $field ) {
		switch ( isset( $field[ self::FILTER_SETTING ] ) ? $field[ self::FILTER_SETTING ] : '' ) {
			case self::CONTENT_FILTER:
				$content = $this->plugin->process( $content );
				break;
			case self::TITLE_FILTER:
				$content = $this->plugin->process_title( $content );
				break;
			case self::FEED_CONTENT_FILTER:
				$content = $this->plugin->process_feed( $content );
				break;
			case self::FEED_TITLE_FILTER:
				$content = $this->plugin->process_feed_title( $content );
				break;
		}

		return $content;
	}
}
