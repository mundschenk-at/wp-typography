<?php
/**
 * This file is part of wp-Typography.
 *
 * Copyright 2020-2023 Peter Putzer.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * ***
 *
 * @package mundschenk-at/wp-typography
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Components;

use WP_Typography\Implementation;

/**
 * The component providing our Gutenberg blocks.
 *
 * @since  5.7.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Block_Editor implements Plugin_Component {

	/**
	 * The plugin API.
	 *
	 * @var Implementation
	 */
	private Implementation $api;

	/**
	 * Create a new instace.
	 *
	 * @param Implementation $api The core API.
	 */
	public function __construct( Implementation $api ) {
		$this->api = $api;
	}

	/**
	 * Set up the various hooks for the block editor.
	 */
	public function run(): void {
		if ( ! \function_exists( 'register_block_type' ) ) {
			// Block editor not installed.
			return;
		}

		// Register and enqueue sidebar.
		\add_action( 'init', [ $this, 'register_sidebar_and_blocks' ] );
		\add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_sidebar' ] );
	}

	/**
	 * Registers the Gutenberg sidebar.
	 */
	public function register_sidebar_and_blocks(): void {
		$suffix     = ( \defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ) ? '' : '.min';
		$plugin_url = \plugins_url( '', \WP_TYPOGRAPHY_PLUGIN_FILE );

		// Register the script containing all our block types (and the sidebar plugin).
		$blocks = 'admin/block-editor/js/index';
		$asset  = include \WP_TYPOGRAPHY_PLUGIN_PATH . "/{$blocks}.asset.php";
		\wp_register_script( 'wp-typography-gutenberg', "{$plugin_url}/{$blocks}.js", $asset['dependencies'], $asset['version'], false );
		\wp_register_style( 'wp-typography-gutenberg-style', "{$plugin_url}/admin/css/blocks{$suffix}.css", [], $this->api->get_version() );

		// Register each individual block type:
		// The frontend form block.
		\register_block_type(
			'wp-typography/typography',
			[
				'editor_script'   => 'wp-typography-gutenberg',
				'editor_style'    => 'wp-typography-gutenberg-style',
				'render_callback' => [ $this, 'render_typography_block' ],
				'attributes'      => [],
			]
		);

		// Enable i18n.
		\wp_set_script_translations( 'wp-typography-gutenberg', 'wp-typography' );
	}

	/**
	 * Enqueues the block editor sidebar script.
	 */
	public function enqueue_sidebar(): void {
		\wp_enqueue_script( 'wp-typography-gutenberg' );
	}

	/**
	 * Renders the frontend form.
	 *
	 * @param  mixed[] $attributes The `wp-typography/typography` block attributes.
	 * @param  string  $content    The content of the inner blocks.
	 *
	 * @return string
	 */
	public function render_typography_block( array $attributes, $content ): string {
		// Ensure that the inner blocks are processed.
		\add_filter( 'typo_disable_processing_for_post', '__return_false', 999, 0 );
		$markup = $this->api->process( $content );
		\remove_filter( 'typo_disable_processing_for_post', '__return_false', 999 );

		return $markup;
	}
}
