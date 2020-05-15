'use strict';

/**
 * wp-Typography
 *
 * The sidebar provided by the wp-Typography plugin.
 *
 * @requires Gutenberg 4.3
 */

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { ToggleControl } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const TypographyToggleControl = ( props ) => (
	<ToggleControl
		label={ __( 'Enable enhancements.', 'wp-typography' ) }
		help={ __( 'No help is coming for you.', 'wp-typography' ) }
		checked={ props.typographyEnabled }
		onChange={ ( checked ) => {
			props.setTypographyEnabled( checked );
		} }
	/>
);

const TypographyToggleControlWithData = withSelect( ( select ) => {
	return {
		typographyEnabled: ! select( 'core/editor' ).getEditedPostAttribute(
			'meta'
		).wp_typography_post_enhancements_disabled,
	};
} )( TypographyToggleControl );

const TypographyToggleControlWithDataAndActions = withDispatch(
	( dispatch ) => {
		return {
			setTypographyEnabled: ( value ) => {
				dispatch( 'core/editor' ).editPost( {
					meta: { wp_typography_post_enhancements_disabled: ! value },
				} );
			},
		};
	}
)( TypographyToggleControlWithData );

const Component = () => (
	<PluginDocumentSettingPanel
		name="wp-typography-settings-panel"
		title="wp-Typography"
	>
		<TypographyToggleControlWithDataAndActions />
	</PluginDocumentSettingPanel>
);

registerPlugin( 'wp-typography', {
	icon: 'format-quote',
	render: Component,
} );
