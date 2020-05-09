<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2020 Peter Putzer.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or ( at your option ) any later version.
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
 *  @package mundschenk-at/wp-typography/tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Tests\Components;

use WP_Typography\Components\Setup;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Settings\Plugin_Configuration as Config;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Setup component unit test.
 *
 * @coversDefaultClass \WP_Typography\Components\Setup
 * @usesDefaultClass \WP_Typography\Components\Setup
 *
 * @uses ::__construct
 */
class Setup_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Setup
	 */
	protected $setup;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Options
	 */
	protected $options;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Implementation
	 */
	protected $api;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->api     = m::mock( \WP_Typography\Implementation::class );
		$this->options = m::mock( \WP_Typography\Data_Storage\Options::class );

		// Mock WP_Typography\Components\Setup instance.
		$this->setup = m::mock( Setup::class, [ $this->api, $this->options ] )
			->shouldAllowMockingProtectedMethods()->makePartial();
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$setup = m::mock( Setup::class, [ $this->api, $this->options ] );

		$this->assert_attribute_same( $this->api, 'api', $setup );
		$this->assert_attribute_same( $this->options, 'options', $setup );
	}


	/**
	 * Test run.
	 *
	 * @covers ::run
	 */
	public function test_run() {
		Functions\expect( 'register_activation_hook' )->once();
		Functions\expect( 'register_deactivation_hook' )->once();
		Functions\expect( 'register_uninstall_hook' )->once();

		Actions\expectAdded( 'plugins_loaded' )->with( [ $this->setup, 'plugin_update_check' ] )->once();

		$this->assertNull( $this->setup->run() );
	}

	/**
	 * Test activate.
	 *
	 * @covers ::activate
	 */
	public function test_activate() {
		$this->api->shouldReceive( 'get_config' )->once();
		$this->api->shouldReceive( 'set_default_options' )->once();

		$this->assertNull( $this->setup->activate() );
	}

	/**
	 * Test deactive.
	 *
	 * @covers ::deactivate
	 */
	public function test_deactivate() {
		// Currently, this hook does nothing.
		$this->assertNull( $this->setup->deactivate() );
	}

	/**
	 * Test uninstall hook.
	 *
	 * @covers ::uninstall
	 *
	 * @uses WP_Typography\Data_Storage\Transients::__construct
	 * @uses WP_Typography\Data_Storage\Transients::invalidate
	 */
	public function test_uninstall() {
		Functions\expect( 'get_transient' )->atLeast()->once();
		Functions\expect( 'set_transient' )->atLeast()->once();

		// Fake object cache used to shortcircuit Transients::invalidate.
		Functions\expect( 'wp_using_ext_object_cache' )->atLeast()->once()->andReturn( true );

		// Since this is a static method, we can't inject our test fixtures.
		$this->assertNull( Setup::uninstall() );
	}

	/**
	 * Provided data for testing get_old_option_name.
	 *
	 * @return array
	 */
	public function provide_get_old_option_name_data() {
		return [
			[ 'typo_disable_caching',    'typoDisableCaching' ],
			[ 'typo_remove_ie6',         'typoRemoveIE6' ],
			[ 'typo_smart_characters',   'typoSmartCharacters' ],
			[ 'typo_wrap_urls',          'typoWrapURLs' ],
			[ 'typo_ignore_ids',         'typoIgnoreIDs' ],
			[ 'typo_style_css',          'typoStyleCSS' ],
			[ 'typo_hyphenate_headings', 'typoHyphenateHeadings' ],
		];
	}

	/**
	 * Test get_old_option_name.
	 *
	 * @dataProvider provide_get_old_option_name_data
	 *
	 * @covers ::get_old_option_name
	 *
	 * @param string $input    New option name (e.g. 'typo_foo_bar').
	 * @param string $expected Old option name (e.g. 'typoFooBar').
	 */
	public function test_get_old_option_name( $input, $expected ) {
		$this->assertSame( $expected, $this->invokeMethod( $this->setup, 'get_old_option_name', [ $input ] ) );
	}

	/**
	 * Test set_installed_version.
	 *
	 * @covers ::set_installed_version
	 */
	public function test_set_installed_version() {
		$this->api->shouldReceive( 'get_version' )->once()->andReturn( '9.9.9' );
		$this->options->shouldReceive( 'set' )->with( Options::INSTALLED_VERSION, '9.9.9' )->once();

		$this->assertNull( $this->invokeMethod( $this->setup, 'set_installed_version' ) );
	}

	/**
	 * Test plugin_update_check.
	 *
	 * @covers ::plugin_update_check
	 */
	public function test_plugin_update_check() {

		$this->options->shouldReceive( 'get' )->with( Options::INSTALLED_VERSION, '' )->once()->andReturn( '7.7.7' );
		$this->api->shouldReceive( 'get_version' )->once()->andReturn( '6.6.6' );
		$this->setup->shouldReceive( 'plugin_updated' )->with( '7.7.7' )->once();

		$this->assertNull( $this->setup->plugin_update_check() );
	}

	/**
	 * Provided data for testing get_old_option_name.
	 *
	 * @return array
	 */
	public function provide_plugin_updated_data() {
		return [
			[ '',              [ 'upgrade_options_3_1', 'upgrade_options_3_2', 'upgrade_options_3_3', 'upgrade_options_3_5', 'upgrade_options_5_1' ] ],
			[ '3.0.0',         [ 'upgrade_options_3_1', 'upgrade_options_3_2', 'upgrade_options_3_3', 'upgrade_options_3_5', 'upgrade_options_5_1' ] ],
			[ '3.1.0',         [ 'upgrade_options_3_2', 'upgrade_options_3_3', 'upgrade_options_3_5', 'upgrade_options_5_1' ] ],
			[ '3.2.0-beta.2',  [ 'upgrade_options_3_3', 'upgrade_options_3_5', 'upgrade_options_5_1' ] ],
			[ '3.3.0-alpha.1', [ 'upgrade_options_3_3', 'upgrade_options_3_5', 'upgrade_options_5_1' ] ],
			[ '3.3.0-alpha.2', [ 'upgrade_options_3_5', 'upgrade_options_5_1' ] ],
			[ '5.0.0',         [ 'upgrade_options_5_1' ] ],
			[ '5.2.0',         [] ],
		];
	}

	/**
	 * Test plugin_updated.
	 *
	 * @covers ::plugin_updated
	 *
	 * @dataProvider provide_plugin_updated_data
	 *
	 * @param  string   $installed_version The previously installed version.
	 * @param  string[] $expected_handlers An array of callbacks.
	 */
	public function test_plugin_updated( $installed_version, array $expected_handlers ) {
		foreach ( $expected_handlers as $handler ) {
			$this->setup->shouldReceive( $handler )->once();
		}

		$this->setup->shouldReceive( 'set_installed_version' )->once();
		$this->api->shouldReceive( 'clear_cache' )->once();

		$this->assertNull( $this->invokeMethod( $this->setup, 'plugin_updated', [ $installed_version ] ) );
	}

	/**
	 * Test upgrade_options_3_1.
	 *
	 * @covers ::upgrade_options_3_1
	 */
	public function test_upgrade_options_3_1() {
		$keys            = [
			'1option' => 'one_option',
			'2option' => 'another_option',
			'3option' => 'a_third_option',
			'4option' => 'a_fourth_option',
		];
		$default_options = [
			$keys['1option'] => 'with a default',
			$keys['2option'] => 5,
			$keys['3option'] => [ 'with', 'an', 'array' ],
			$keys['4option'] => 'will be unknown',
		];

		$this->api->shouldReceive( 'get_default_options' )->once()->andReturn( $default_options );

		foreach ( $keys as $old_option_name => $new_option_name ) {
			$value = 'value_' . $old_option_name;

			$this->setup->shouldReceive( 'get_old_option_name' )->with( Setup::LEGACY_OPTIONS_PREFIX . "_$new_option_name" )->once()->andReturn( $old_option_name );

			$this->options->shouldReceive( 'get' )->with( $old_option_name, Setup::UPGRADING, true )->once()->andReturn( $value );
			$this->options->shouldReceive( 'set' )->with( $new_option_name, $value )->once()->andReturn( true );
			$this->options->shouldReceive( 'delete' )->with( $old_option_name, true )->once()->andReturn( true );
		}

		$this->assertNull( $this->invokeMethod( $this->setup, 'upgrade_options_3_1' ) );
	}

	/**
	 * Test upgrade_options_3_2.
	 *
	 * @covers ::upgrade_options_3_2
	 */
	public function test_upgrade_options_3_2() {
		$this->options->shouldReceive( 'delete' )->with( 'typo_disable_caching', true )->once();

		$this->assertNull( $this->invokeMethod( $this->setup, 'upgrade_options_3_2' ) );
	}

	/**
	 * Test upgrade_options_3_3.
	 *
	 * @covers ::upgrade_options_3_3
	 */
	public function test_upgrade_options_3_3() {
		$this->options->shouldReceive( 'delete' )->with( 'typo_remove_ie6', true )->once();

		$this->assertNull( $this->invokeMethod( $this->setup, 'upgrade_options_3_3' ) );
	}

	/**
	 * Test upgrade_options_3_5.
	 *
	 * @covers ::upgrade_options_3_5
	 */
	public function test_upgrade_options_3_5() {
		$this->options->shouldReceive( 'delete' )->with( 'typo_enable_caching', true )->once();
		$this->options->shouldReceive( 'delete' )->with( 'typo_caching_limit', true )->once();

		$this->assertNull( $this->invokeMethod( $this->setup, 'upgrade_options_3_5' ) );
	}

	/**
	 * Test upgrade_options_5_1.
	 *
	 * @covers ::upgrade_options_5_1
	 */
	public function test_upgrade_options_5_1() {
		$this->options->shouldReceive( 'delete' )->with( 'typo_transient_keys', true )->once();
		$this->options->shouldReceive( 'delete' )->with( 'typo_cache_keys', true )->once();
		$this->setup->shouldReceive( 'upgrade_options_to_array' )->once();

		$this->assertNull( $this->invokeMethod( $this->setup, 'upgrade_options_5_1' ) );
	}

	/**
	 * Test upgrade_options_to_array.
	 *
	 * @covers ::upgrade_options_to_array
	 */
	public function test_upgrade_options_to_array() {
		$default_options = [
			'one_option'      => 'with a default',
			'another_option'  => 5,
			'a_third_option'  => [ 'with', 'an', 'array' ],
			'a_fourth_option' => 'will be unknown',
		];

		$this->api->shouldReceive( 'get_default_options' )->once()->andReturn( $default_options );

		$this->options->shouldReceive( 'get' )->with( 'one_option', Setup::UPGRADING )->once()->andReturn( 'foo1' );
		$this->options->shouldReceive( 'get' )->with( 'another_option', Setup::UPGRADING )->once()->andReturn( 'foo2' );
		$this->options->shouldReceive( 'get' )->with( 'a_third_option', Setup::UPGRADING )->once()->andReturn( 'foo3' );
		$this->options->shouldReceive( 'delete' )->with( 'one_option' )->once();
		$this->options->shouldReceive( 'delete' )->with( 'another_option' )->once();
		$this->options->shouldReceive( 'delete' )->with( 'a_third_option' )->once();

		$this->options->shouldReceive( 'get' )->with( 'a_fourth_option', Setup::UPGRADING )->once()->andReturn( Setup::UPGRADING );
		$this->options->shouldNotReceive( 'delete' )->with( 'a_fourth_option' );

		$this->options->shouldReceive( 'set' )->with(
			Options::CONFIGURATION,
			[
				'one_option'      => 'foo1',
				'another_option'  => 'foo2',
				'a_third_option'  => 'foo3',
				'a_fourth_option' => 'will be unknown',
			]
		)->once();

		$this->assertNull( $this->invokeMethod( $this->setup, 'upgrade_options_to_array' ) );
	}
}
