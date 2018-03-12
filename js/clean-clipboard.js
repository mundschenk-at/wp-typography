/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2016, 2018 Peter Putzer.
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

/**
 * Clean up clipboard content on cut & copy. Removes &shy; and zero-width space.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
jQuery( function( $ ) {
	'use strict';

	if ( window.getSelection ) {

		document.addEventListener( 'copy', function() {
			var
				sel        = window.getSelection(),
				ranges     = [],
				rangeCount = sel.rangeCount,
				i, shadow;

			for ( i = 0; i < rangeCount; i++ ) {
				 ranges[i] = sel.getRangeAt( i );
			}

			// Create new div containing cleaned HTML content
			shadow = $( '<div>', {
				style: { position: 'absolute', left: '-99999px' },
				html: $( '<div></div>' ).append( sel.getRangeAt( 0 ).cloneContents() ).html().replace( /\u00AD/gi, '' ).replace( /\u200B/gi, '' )
			} );

			// Append to DOM
			$( 'body' ).append( shadow );

			// Select the children of our "clean" div
			sel.selectAllChildren( shadow[0] );

			// Clean up after copy
			window.setTimeout( function() {

				// Remove div
				shadow.remove();

				// Restore selection
				sel.removeAllRanges();
				for ( i = 0; i < rangeCount; i++ ) {
					 sel.addRange( ranges[i] );
				}
			}, 0 );
		} );
	}
} );
