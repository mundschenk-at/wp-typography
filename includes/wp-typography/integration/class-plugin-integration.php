<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2023 Peter Putzer.
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

namespace WP_Typography\Integration;

/**
 * An interface for plugin integrations.
 *
 * @since  5.3.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
interface Plugin_Integration {

	/**
	 * Checks if the ACF integration should be activated.
	 *
	 * @return bool
	 */
	public function check(): bool;

	/**
	 * Activates the integration.
	 *
	 * @since 5.7.0 Parameter $plugin removed.
	 */
	public function run(): void;

	/**
	 * Retrieves the identifying tag for the frontend content filters.
	 *
	 * @return string
	 */
	public function get_filter_tag(): string;

	/**
	 * Enables frontend content filters.
	 *
	 * @param int $priority The filter priority.
	 */
	public function enable_content_filters( $priority ): void;
}
