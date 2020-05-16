'use strict';

/**
 * wp-Typography
 *
 * A toggle switch for disabling wp-Typography for specific posts.
 *
 * @requires Gutenberg 4.3
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
		help={ __( 'No help is coming for you.', 'wp-typography' ) }
		checked={ props.typographyEnabled }
		onChange={ ( checked ) => {
			props.setTypographyEnabled( checked );
		} }
	/>
);

export default compose( [
	withSelect( ( select ) => {
		return {
			typographyEnabled: ! select( 'core/editor' ).getEditedPostAttribute(
				'meta'
			).wp_typography_post_enhancements_disabled,
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
