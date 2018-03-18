<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017 Peter Putzer.
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

namespace WP_Typography\Data_Storage;

/**
 * Implements an interface to the Options API for wp-Typography.
 *
 * @since 5.1.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Options extends \Mundschenk\Data_Storage\Options {

	const PREFIX = 'typo_';

	const RESTORE_DEFAULTS  = 'restore_defaults';
	const CLEAR_CACHE       = 'clear_cache';
	const CONFIGURATION     = 'configuration';
	const INSTALLED_VERSION = 'installed_version';

	/**
	 * Create new Options instance.
	 */
	public function __construct() {
		parent::__construct( self::PREFIX );
	}
}
