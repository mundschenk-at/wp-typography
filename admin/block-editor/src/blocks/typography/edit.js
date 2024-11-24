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
 * @file     This file handles the edit function for the Typography block.
 * @author   Peter Putzer <github@mundschenk.at>
 * @since    5.7.0
 * @requires Gutenberg 4.3
 */

/**
 * WordPress dependencies
 */
import { InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Edits the block attributes.
 *
 * Makes the markup for the editor interface.
 *
 * @param {Object} props {
 *                       attributes    - The block attributes.
 *                       setAttributes - The attribute setter function.
 *                       }
 *
 * @return {Object} ECMAScript JSX Markup for the editor
 */
export default ( props ) => {
	const { className } = props;

	return (
		<div className={ className }>
			<span className="wp-typography-block-help">
				{ __(
					'Any blocks added as children will have wp-Typography fixes applied.',
					'wp-typography'
				) }
			</span>
			<InnerBlocks />
		</div>
	);
};
