'use strict';

/**
 * This file is part of wp-Typography.
 *
 * Copyright 2020-2024 Peter Putzer.
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
 * @file     This file handles the Gutenberg sidebar integration for the wp-Typography plugin.
 * @author   Peter Putzer <github@mundschenk.at>
 * @since    5.10.0
 */

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import * as sidebarPlugin from './sidebar-post-toggle';

// Register the plugin(s).
[ sidebarPlugin ].forEach( ( plugin ) => {
	if ( ! plugin ) {
		return;
	}

	const { name, settings } = plugin;
	registerPlugin( name, settings );
} );
