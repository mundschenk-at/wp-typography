<?php
/**
 *  This file is part of wp-Typography.
 *
 *	Copyright 2015 Peter Putzer.
 *
 *	This program is free software; you can redistribute it and/or
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
 *  @package wpTypography
 *  @author Peter Putzer <github@mundschenk.at>
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * An autoloader implementation for the WP_Typography classes.
 *
 * @param string $class_name Required.
 */
function wp_typography_autoloader( $class_name ) {
	if ( false === strpos( $class_name, 'WP_Typography' ) ) {
		return; // abort early.
	}

	static $classes_dir;
	if ( empty( $classes_dir ) ) {
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
	}

	$class_file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';
	if ( is_file( $class_file_path = $classes_dir . $class_file ) ) {
		require_once( $class_file_path );
	}
}
spl_autoload_register( 'wp_typography_autoloader' );
