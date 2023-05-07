<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2023 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
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

use WP_Typography\Data_Storage\Cache;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Data_Storage\Transients;

use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\PHP_Typography;
use PHP_Typography\Settings;
use PHP_Typography\Hyphenator\Cache as Hyphenator_Cache;

/**
 * Main wp-Typography plugin class. All WordPress specific code goes here.
 *
 * @since  5.9.0 Return type declarations added.
 *
 * @api
 *
 * @method string get_version() Retrieves the plugin version.
 *
 * @method Settings get_settings() Retrieves the internal Settings object for the preferences set via the plugin options screen.
 *
 * @method void clear_cache() Retrieves the plugin's default option values.
 *
 * @method array get_config() Retrieves the plugin configuration.
 * @method array get_default_options() Retrieves the plugin's default option values.
 * @method void set_default_options($force_defaults = false) Initializes the options with default values.
 *
 * @method string process(string $text, bool $is_title = false, bool $force_feed = false, Settings $settings = null) Processes a text fragment.
 * @method string process_title($text, Settings $settings = null) Processes a heading text fragment.
 * @method string process_feed_title($text, Settings $settings = null) Processes a heading text fragment as part of an RSS feed.
 * @method string process_feed($text, $is_title = false, Settings $settings = null) Processes a content text fragment as part of an RSS feed.
 * @method array process_title_parts($title_parts, Settings $settings = null) Processes title parts and strips &shy; and zero-width space.
 *
 * @method array get_hyphenation_languages() Retrieves and caches the list of valid hyphenation languages.
 * @method array get_diacritic_languages() Retrieves and caches the list of valid diacritic replacement languages.
 */
abstract class WP_Typography {

	/**
	 * The singleton instance.
	 *
	 * @var WP_Typography
	 */
	private static $instance;

	/**
	 * Retrieves (and if necessary creates) the WP_Typography instance. Should not be called outside of plugin set-up.
	 *
	 * @internal
	 *
	 * @since 5.0.0
	 *
	 * @param WP_Typography $instance Only used for plugin initialization. Don't ever pass a value in user code.
	 *
	 * @throws BadMethodCallException Thrown when WP_Typography::set_instance after plugin initialization.
	 */
	public static function set_instance( WP_Typography $instance ): void {
		if ( null === self::$instance ) {
			self::$instance = $instance;
		} else {
			throw new BadMethodCallException( 'WP_Typography::set_instance called more than once.' );
		}
	}

	/**
	 * Retrieves the plugin instance.
	 *
	 * @since 3.2.0
	 * @since 5.0.0 Errors handled ia exceptions.
	 *
	 * @throws BadMethodCallException Thrown when WP_Typography::get_instance is called before plugin initialization.
	 *
	 * @return WP_Typography
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			throw new BadMethodCallException( 'WP_Typography::get_instance called without prior plugin intialization.' );
		}

		return self::$instance;
	}

	/**
	 * Allows API methods to be called statically, using the instance returned by
	 * `WP_Typography::get_instance()`.
	 *
	 * @param  string  $name      The method name.
	 * @param  mixed[] $arguments The method arguments.
	 *
	 * @throws BadMethodCallException Thrown when a non-existent method is called statically.
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, array $arguments ) {
		if ( \method_exists( self::$instance, $name ) ) {
			return self::$instance->$name( ...$arguments );
		} else {
			throw new BadMethodCallException( "Static method WP_Typography::$name does not exist." );
		}
	}

	/**
	 * Retrieves a copy of the preferences set by the user via the plugin settings screen.
	 *
	 * @since 4.0.0
	 *
	 * @return Settings
	 */
	public static function get_user_settings() {
		return clone self::get_instance()->get_settings();
	}

	/**
	 * Processes a content text fragment.
	 *
	 * @since 4.0.0
	 *
	 * @deprecated 5.3.0 Use `process` instead.
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter( $text, Settings $settings = null ) {
		return self::get_instance()->process( $text, false, false, $settings );
	}

	/**
	 * Processes a title text fragment.
	 *
	 * @since 4.0.0
	 *
	 * @deprecated 5.3.0 Use `process_title` instead.
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter_title( $text, Settings $settings = null ) {
		return self::get_instance()->process_title( $text, $settings );
	}

	/**
	 * Processes the title parts and strips &shy; and zero-width space.
	 *
	 * @since 4.0.0
	 *
	 * @deprecated 5.3.0 Use `process_title_parts` instead.
	 *
	 * @param string[]      $title_parts An array of strings.
	 * @param Settings|null $settings    Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string[]
	 */
	public static function filter_title_parts( array $title_parts, Settings $settings = null ): array {
		return self::get_instance()->process_title_parts( $title_parts, $settings );
	}

	/**
	 * Processes a content text fragment as part of an RSS feed (limiting HTML features to most widely compatible ones).
	 *
	 * @since 4.0.0
	 *
	 * @deprecated 5.3.0 Use `process_feed` instead.
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter_feed( $text, Settings $settings = null ) {
		return self::get_instance()->process_feed( $text, false, $settings );
	}

	/**
	 * Processes a title text fragment as part of an RSS feed (limiting HTML features to most widely compatible ones).
	 *
	 * @since 4.0.0
	 *
	 * @deprecated 5.3.0 Use `process_feed_title` instead.
	 *
	 * @param string        $text     Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public static function filter_feed_title( $text, Settings $settings = null ) {
		return self::get_instance()->process_feed_title( $text, $settings );
	}

	/**
	 * Encodes the given version string (in the form "3.0.0-beta.1") to a representation suitable for hashing.
	 *
	 * The current implementation works as follows:
	 * 1. The version is broken into tokens at each ".".
	 * 2. Each token is stripped of all characters except numbers.
	 * 3. Each number is added to decimal 64 to arrive at an ASCII code.
	 * 4. The character representation of that ASCII code is added to the result.
	 *
	 * This means that textual qualifiers like "alpha" and "beta" are ignored, so "3.0.0-alpha.1" and
	 * "3.0.0-beta.1" result in the same hash. Since those are not regular release names, this is deemed
	 * acceptable to make the algorithm simpler.
	 *
	 * @param string $version A version string.
	 *
	 * @return string The hashed version (containing as few bytes as possible);
	 */
	private static function hash_version_string( $version ) {
		$hash = '';

		foreach ( \explode( '.', $version ) as $part ) {
			$hash .= \chr( 64 + (int) \preg_replace( '/[^0-9]/', '', $part ) );
		}

		return $hash;
	}

	/**
	 * Retrieves the plugin version hash.
	 *
	 * @deprecated 5.1.0
	 *
	 * @return string
	 */
	public function get_version_hash() {
		return self::hash_version_string( self::get_instance()->get_version() );
	}
}
