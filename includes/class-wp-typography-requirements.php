<?php
/**
 * This file is part of wp-Typography.
 *
 * Copyright 2020 Peter Putzer.
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
 *  ***
 *
 * @package mundschenk-at/wp-typography
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 */

// We can't rely on autoloading for the requirements check.
require_once dirname( dirname( __FILE__ ) ) . '/vendor/mundschenk-at/check-wp-requirements/class-mundschenk-wp-requirements.php'; // @codeCoverageIgnore

/**
 * A custom requirements class to check for additional PHP packages and other
 * prerequisites.
 *
 * @since 5.7.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class WP_Typography_Requirements extends Mundschenk_WP_Requirements {

	/**
	 * Creates a new requirements instance.
	 */
	public function __construct() {
		$requirements = array(
			'php'              => '5.6.0',
			'multibyte'        => true,
			'utf-8'            => true,
			'dom'              => true,
		);

		parent::__construct( 'wp-Typography', WP_TYPOGRAPHY_PLUGIN_FILE, 'wp-typography', $requirements );
	}

	/**
	 * Retrieves an array of requirement specifications.
	 *
	 * @return array {
	 *         An array of requirements checks.
	 *
	 *   @type string   $enable_key An index in the $install_requirements array to switch the check on and off.
	 *   @type callable $check      A function returning true if the check was successful, false otherwise.
	 *   @type callable $notice     A function displaying an appropriate error notice.
	 * }
	 */
	protected function get_requirements() {
		$requirements   = parent::get_requirements();
		$requirements[] = array(
			'enable_key' => 'dom',
			'check'      => array( $this, 'check_dom_support' ),
			'notice'     => array( $this, 'admin_notices_dom_disabled' ),
		);

		return $requirements;
	}

	/**
	 * Checks for availability of the DOM extension.
	 *
	 * @return bool
	 */
	protected function check_dom_support() {
		return class_exists( 'DOMDocument' );
	}

	/**
	 * Prints 'DOM extension missing' admin notice
	 */
	public function admin_notices_dom_disabled() {
		$this->display_error_notice(
			/* translators: 1: plugin name 2: GD documentation URL */
			__( 'The activated plugin %1$s requires the DOM PHP extension to be enabled on your server. Please deactivate this plugin, or <a href="%2$s">enable the extension</a>.', 'wp-typography' ),
			'<strong>wp-Typography</strong>',
			/* translators: URL with GD PHP extension installation instructions */
			__( 'https://www.php.net/manual/en/dom.setup.php', 'wp-typography' )
		);
	}
}
