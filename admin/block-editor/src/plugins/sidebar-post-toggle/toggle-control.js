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
 * @file    A toggle switch for disabling wp-Typography for specific posts.
 * @author  Peter Putzer <github@mundschenk.at>
 * @since   5.7.0
 */

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { ToggleControl } from '@wordpress/components';

export const TypographyToggleControl = ( props ) => (
	<ToggleControl
		label={ __( 'Enable enhancements.', 'wp-typography' ) }
		help={ __(
			'wp-Typography is enabled for all posts unless you disable processing via this switch.',
			'wp-typography'
		) }
		checked={ props.typographyEnabled }
		onChange={ ( checked ) => {
			props.setTypographyEnabled( checked );
		} }
	/>
);

export default compose( [
	withSelect( ( select ) => {
		return {
			typographyEnabled:
				! select( 'core/editor' ).getEditedPostAttribute( 'meta' )
					.wp_typography_post_enhancements_disabled,
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setTypographyEnabled: ( value ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { wp_typography_post_enhancements_disabled: ! value },
				} );
			},
		};
	} ),
] )( TypographyToggleControl );
