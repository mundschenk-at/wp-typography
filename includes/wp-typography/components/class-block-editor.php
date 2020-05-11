<?php
/**
 * This file is part of wp-Typography.
 *
 * Copyright 2020 Peter Putzer.
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

/**
 * The component providing our Gutenberg blocks.
 *
 * @since 5.7.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Block_Editor implements Plugin_Component {

	/**
	 * Set up the various hooks for the block editor.
	 */
	public function run() {
		if ( ! \function_exists( 'register_block_type' ) ) {
			// Block editor not installed.
			return;
		}

		// Register and enqueue sidebar.
		\add_action( 'init', [ $this, 'register_sidebar' ] );
		\add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_sidebar' ] );
	}

	/**
	 * Registers the Gutenberg sidebar.
	 */
	public function register_sidebar() {
		$suffix     = ( \defined( 'SCRIPT_DEBUG' ) && \SCRIPT_DEBUG ) ? '' : '.min';
		$plugin_url = \plugins_url( '', \WP_TYPOGRAPHY_PLUGIN_FILE );

		// Register the script containing all our block types.
		$blocks = 'admin/blocks/js/index';
		$asset  = include \WP_TYPOGRAPHY_PLUGIN_PATH . "/{$blocks}.asset.php";
		\wp_register_script( 'wp-typography-gutenberg', "{$plugin_url}/{$blocks}.js", $asset['dependencies'], $asset['version'], false );

		// Enable i18n.
		\wp_set_script_translations( 'wp-typography-gutenberg', 'wp-typography' );
	}

	/**
	 * Enqueues the block editor sidebar script.
	 */
	public function enqueue_sidebar() {
		\wp_enqueue_script( 'wp-typography-gutenberg' );
	}
}
