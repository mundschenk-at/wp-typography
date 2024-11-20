<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2024 Peter Putzer.
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

namespace WP_Typography\Typography;

use PHP_Typography\Settings;
use PHP_Typography\Fixes\Node_Fixes\Abstract_Node_Fix;

/**
 * A node fix with WordPress hooks.
 *
 * @since  5.4.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Custom_Node_Fix extends Abstract_Node_Fix {

	/**
	 * The group part of the filter hook.
	 *
	 * @var string
	 */
	private string $group;

	/**
	 * Creates a new fix instance.
	 *
	 * @param string $group The fix group used in the hook.
	 */
	public function __construct( $group ) {
		parent::__construct( true );

		$this->group = $group;
	}


	/**
	 * Apply the fix to a given textnode.
	 *
	 * @param \DOMText $textnode Required.
	 * @param Settings $settings Required.
	 * @param bool     $is_title Optional. Default false.
	 *
	 * @return void
	 */
	public function apply( \DOMText $textnode, Settings $settings, $is_title = false ): void {
		/**
		 * Filters the text node content for the given group.
		 *
		 * @param string   $content  The textnode data.
		 * @param \DOMText $textnode The processed node.
		 * @param Settings $settings The typography settings to apply.
		 * @param bool     $is_title Whether the content occurs in a title/heading context.
		 */
		$textnode->data = \apply_filters( "typo_custom_{$this->group}_node_fix", $textnode->data, $textnode, $settings, $is_title );
	}
}
