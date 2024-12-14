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
 * @file    This file handles the Gutenberg sidebar provided by the wp-Typography plugin.
 * @author  Peter Putzer <github@mundschenk.at>
 * @since   5.7.0
 */

/**
 * WordPress dependencies
 */
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { select } from '@wordpress/data';

/**
 * Type checking dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import TypographyToggleControl from './toggle-control';

/**
 * Plugin metadata.
 */
export const name = 'wp-typography-sidebar';
const icon = 'format-quote';

/**
 * Type checking.
 */
const propTypes = {
	className: PropTypes.string.isRequired,
	typographyEnabled: PropTypes.bool.isRequired,
	setTypographyEnabled: PropTypes.func.isRequired,
};

/**
 * Renders the plugin.
 *
 * @return {PluginDocumentSettingPanel} The sidebar component.
 */
const render = () => {
	const isGBPost =
		select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || false;

	if ( ! isGBPost ) {
		// No custom-fields support for this custom post type.
		// Instead of the panel, we just return an empty fragment.
		return <></>;
	}

	return (
		<PluginDocumentSettingPanel
			name="wp-typography-settings-panel"
			title="wp-Typography"
		>
			<TypographyToggleControl />
		</PluginDocumentSettingPanel>
	);
};

// Export settings.
export const settings = { icon, render, propTypes };
