<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2020 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
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
 *  @package mundschenk-at/wp-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Tests;

// Autoload everything using Composer.
require_once \dirname( __DIR__ ) . '/vendor/autoload.php';

/**
 * Autoload everything using Composer.
 */
require_once \dirname( __DIR__ ) . '/vendor/autoload.php';

// wp-Typography constants.
if ( ! \defined( 'WP_TYPOGRAPHY_PLUGIN_FILE' ) ) {
	\define( 'WP_TYPOGRAPHY_PLUGIN_FILE', 'plugin/file' );
}
if ( ! \defined( 'WP_TYPOGRAPHY_PLUGIN_PATH' ) ) {
	\define( 'WP_TYPOGRAPHY_PLUGIN_PATH', 'plugin' );
}
