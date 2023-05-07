<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2023 Peter Putzer.
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

use WP_Typography\Components\Admin_Interface;
use WP_Typography\Data_Storage\Options;
use WP_Typography\Implementation;
use WP_Typography\Settings\Plugin_Configuration as Config;

use Mundschenk\UI;

use WP_Typography\Tests\TestCase;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use org\bovigo\vfs\vfsStream;

use Mockery as m;

/**
 * Admin_Interface component unit test.
 *
 * @coversDefaultClass \WP_Typography\Components\Admin_Interface
 * @usesDefaultClass \WP_Typography\Components\Admin_Interface
 *
 * This should be replaced by runClassInSeperateProcess (which currently is broken):
 * @runTestsInSeparateProcesses
 *
 * @uses ::__construct
 * @uses ::run
 * @uses ::initialize_resource_links
 * @uses ::initialize_help_pages
 * @uses \WP_Typography\Settings\Plugin_Configuration::get_defaults
 * @uses \WP_Typography\UI\Tabs::get_tabs
 * @uses \WP_Typography\UI\Sections::get_sections
 */
class Admin_Interface_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Admin_Interface&m\MockInterface
	 */
	protected $admin;

	/**
	 * Test fixture.
	 *
	 * @var array<string,UI\Control&m\MockInterface>
	 */
	protected $admin_form_controls;

	/**
	 * Test fixture.
	 *
	 * @var Options&m\MockInterface
	 */
	protected $options;

	/**
	 * Test fixture.
	 *
	 * @var Implementation&m\MockInterface
	 */
	protected $api;

	/**
	 * Test fixture.
	 *
	 * @var UI\Control_Factory&m\MockInterface
	 */
	protected $control_factory;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Set up virtual filesystem.
		vfsStream::setup(
			'root',
			null,
			[
				'plugin' => [
					'admin' => [
						'partials' => [
							'settings' => [
								'settings-page.php' => 'SETTINGS_PHP',
								'section.php'       => 'SECTION',
							],
						],
					],
				],
			]
		);
		set_include_path( 'vfs://root/' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_include_path

		// Mock WP_Typography\Data_Storage\Options instance.
		$this->options = m::mock( Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		$this->api = m::mock( Implementation::class )->shouldReceive( 'get_version' )->andReturn( '6.6.6' )->byDefault()->getMock();

		// Mock WP_Typography\Components\Admin_Interface instance.
		$this->admin = m::mock( Admin_Interface::class, [ $this->api, $this->options ] )
			->shouldAllowMockingProtectedMethods()->makePartial();

		/**
		 * UI control mock.
		 *
		 * @var UI\Control&m\MockInterface
		 */
		$control1 = m::mock( UI\Controls\Select::class );

		/**
		 * UI control mock.
		 *
		 * @var UI\Control&m\MockInterface
		 */
		$control2 = m::mock( UI\Controls\Select::class );

		$this->admin_form_controls = [
			Config::HYPHENATE_LANGUAGES => $control1,
			Config::DIACRITIC_LANGUAGES => $control2,
		];

		$this->control_factory = m::mock( 'alias:' . UI\Control_Factory::class )
			->shouldReceive( 'initialize' )->with( m::type( 'array' ), m::type( Options::class ), m::type( 'string' ) )->andReturn( $this->admin_form_controls )->byDefault()
			->getMock();

		// Finish Admin_Interface.
		Functions\expect( '__' )->atLeast()->once()->with( m::type( 'string' ), 'wp-typography' )->andReturnUsing(
			function( $str, $domain ) {
				return $str;
			}
		);
		Functions\expect( 'is_admin' )->once()->andReturn( true );
		Functions\expect( 'plugin_basename' )->once()->with( \WP_TYPOGRAPHY_PLUGIN_FILE )->andReturn( 'plugin/basename' );
		$this->admin->run();  // @phpstan-ignore-line - We need to run this method to properly set up the fixture.
	}

	/**
	 * Test constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor(): void {
		$admin = m::mock( Admin_Interface::class, [ $this->api, $this->options ] );

		$this->assert_attribute_same( $this->api, 'api', $admin );
		$this->assert_attribute_same( $this->options, 'options', $admin );
	}


	/**
	 * Test run.
	 *
	 * @covers ::run
	 * @covers ::initialize_resource_links
	 * @covers ::initialize_help_pages
	 *
	 * @uses \WP_Typography\Settings\Plugin_Configuration::get_defaults
	 * @uses \WP_Typography\UI\Tabs::get_tabs
	 * @uses \WP_Typography\UI\Sections::get_sections
	 */
	public function test_run(): void {
		Functions\expect( 'is_admin' )->once()->andReturn( true );
		Functions\expect( 'plugin_basename' )->once()->with( \WP_TYPOGRAPHY_PLUGIN_FILE )->andReturn( 'plugin/basename' );

		Actions\expectAdded( 'admin_menu' )->with( [ $this->admin, 'add_options_page' ] )->once();
		Actions\expectAdded( 'admin_init' )->with( [ $this->admin, 'register_the_settings' ] )->once();
		Actions\expectAdded( 'admin_init' )->with( [ $this->admin, 'maybe_add_privacy_notice_content' ] )->once();
		Filters\expectAdded( 'plugin_action_links_plugin/basename' )->with( [ $this->admin, 'plugin_action_links' ] )->once();

		$this->control_factory->shouldReceive( 'initialize' )->with( m::type( 'array' ), m::type( Options::class ), m::type( 'string' ) )->andReturn( [] );

		$this->admin->run();

		$this->assert_attribute_same( $this->api, 'api', $this->admin );
	}

	/**
	 * Test get_admin_page_content.
	 *
	 * @covers ::get_admin_page_content
	 * @covers ::load_language_options
	 */
	public function test_get_admin_page_content(): void {
		// Set up expectations.
		$this->admin_form_controls[ Config::HYPHENATE_LANGUAGES ]->shouldReceive( 'set_option_values' )->once()->with( m::type( 'array' ) );
		$this->admin_form_controls[ Config::DIACRITIC_LANGUAGES ]->shouldReceive( 'set_option_values' )->once()->with( m::type( 'array' ) );

		$this->api->shouldReceive( 'get_hyphenation_languages' )->andReturn( [ 'CODE' => 'Language' ] );
		$this->api->shouldReceive( 'get_diacritic_languages' )->andReturn( [ 'CODE' => 'Language' ] );

		$this->expectOutputString( 'SETTINGS_PHP' );

		// Do it.
		$this->admin->get_admin_page_content();
	}

	/**
	 * Test print_admin_styles.
	 *
	 * @covers ::print_admin_styles
	 */
	public function test_print_admin_styles(): void {
		// Set up expectations.
		Functions\expect( 'plugins_url' )->once()->with( 'admin/css/settings.css', m::type( 'string' ) )->andReturn( 'some/path/settings.css' );
		Functions\expect( 'wp_enqueue_style' )->once()->with( 'wp-typography-settings', 'some/path/settings.css', m::type( 'array' ), m::type( 'string' ), 'all' );

		// Do it.
		$this->admin->print_admin_styles();
	}

	/**
	 * Test plugin_action_links.
	 *
	 * @covers ::plugin_action_links
	 */
	public function test_plugin_action_links(): void {
		// Test data.
		$links = [
			'foo',
			'bar',
		];

		// Set up expectations.
		Functions\expect( 'admin_url' )->once()->with()->andReturn( 'adminurl' );

		// Do it.
		$new_links = $this->admin->plugin_action_links( $links );

		$this->assertCount( count( $links ) + 1, $new_links );
		$this->assertContainsOnly( 'string', $new_links );
	}

	/**
	 * Test register_the_settings.
	 *
	 * @covers ::register_the_settings
	 */
	public function test_register_the_settings(): void {
		// Set up expectations.
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::CONFIGURATION )->andReturn( 'typo_configuration' );
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( 'typo_restore_defaults' );
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::CLEAR_CACHE )->andReturn( 'typo_clear_cache' );

		$tab_count = count( $this->getValue( $this->admin, 'admin_form_tabs', Admin_Interface::class ) );

		Functions\expect( 'register_setting' )->times( $tab_count )->with( m::type( 'string' ), 'typo_configuration', [ $this->admin, 'sanitize_settings' ] );
		Functions\expect( 'register_setting' )->times( $tab_count )->with( m::type( 'string' ), 'typo_restore_defaults', [ $this->admin, 'sanitize_restore_defaults' ] );
		Functions\expect( 'register_setting' )->times( $tab_count )->with( m::type( 'string' ), 'typo_clear_cache', [ $this->admin, 'sanitize_clear_cache' ] );

		Filters\expectAdded( 'pre_update_option_typo_configuration' )->with( [ $this->admin, 'filter_update_option' ], 10, 2 );

		foreach ( $this->admin_form_controls as $control ) {
			$control->shouldReceive( 'register' )->once();
		}

		// Do it.
		$this->admin->register_the_settings();
	}

	/**
	 * Test filter_update_option.
	 *
	 * @covers ::filter_update_option
	 */
	public function test_filter_update_option(): void {
		// Test data.
		$old_value = [
			'foo' => 'bar',
			'bar' => 'foo',
		];
		$value     = [
			'foo' => 'baz',
		];

		$combined = [
			'foo' => 'baz',
			'bar' => 'foo',
		];

		// Set up expectations.
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( 'typo_restore_defaults' );
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::CLEAR_CACHE )->andReturn( 'typo_clear_cache' );

		Functions\expect( 'wp_parse_args' )->once()->with( $value, $old_value )->andReturn( $combined );

		// Do it.
		$this->assertSame( $combined, $this->admin->filter_update_option( $value, $old_value ) );
	}

	/**
	 * Test filter_update_option with $_POST['typo_clear_cache'].
	 *
	 * @covers ::filter_update_option
	 */
	public function test_filter_update_option_clear_cache(): void {
		// Test data.
		$old_value = [
			'foo' => 'bar',
			'bar' => 'foo',
		];
		$value     = [
			'foo' => 'baz',
		];

		$combined = [
			'foo' => 'baz',
			'bar' => 'foo',
		];

		// Set up $_POST.
		$_POST['typo_clear_cache'] = true;

		// Set up expectations.
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( 'typo_restore_defaults' );
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::CLEAR_CACHE )->andReturn( 'typo_clear_cache' );

		Functions\expect( 'wp_parse_args' )->once()->with( $value, $old_value )->andReturn( $combined );

		// Do it.
		$this->assertSame( $old_value, $this->admin->filter_update_option( $value, $old_value ) );
	}

	/**
	 * Test filter_update_option with $_POST['typo_restore_defaults'].
	 *
	 * @covers ::filter_update_option
	 */
	public function test_filter_update_option_restore_defaults(): void {
		// Test data.
		$old_value = [
			'foo' => 'bar',
			'bar' => 'foo',
		];
		$value     = [
			'foo' => 'baz',
		];

		$combined = [
			'foo' => 'baz',
			'bar' => 'foo',
		];

		// Set up $_POST.
		$_POST['typo_restore_defaults'] = true;

		// Set up expectations.
		$this->options->shouldReceive( 'get_name' )->once()->with( Options::RESTORE_DEFAULTS )->andReturn( 'typo_restore_defaults' );

		Functions\expect( 'wp_parse_args' )->once()->with( $value, $old_value )->andReturn( $combined );

		// Do it.
		$this->assertSame( $old_value, $this->admin->filter_update_option( $value, $old_value ) );
	}

	/**
	 * Test sanitize_restore_defaults.
	 *
	 * @covers ::sanitize_restore_defaults
	 */
	public function test_sanitize_restore_defaults(): void {
		// Set up data.
		$input = 'foo';

		// Set up expectations.
		$this->admin->shouldReceive( 'trigger_admin_notice' )->once()->with( Options::RESTORE_DEFAULTS, 'defaults-restored', m::type( 'string' ), 'updated', $input )->andReturn( $input );

		// Do it.
		$this->assertTrue( $this->admin->sanitize_restore_defaults( $input ) );
	}

	/**
	 * Test sanitize_clear_cache.
	 *
	 * @covers ::sanitize_clear_cache
	 */
	public function test_sanitize_clear_cache(): void {
		// Set up data.
		$input = 'foo';

		// Set up expectations.
		$this->admin->shouldReceive( 'trigger_admin_notice' )->once()->with( Options::CLEAR_CACHE, 'cache-cleared', m::type( 'string' ), 'notice-info', $input )->andReturn( $input );

		// Do it.
		$this->assertTrue( $this->admin->sanitize_clear_cache( $input ) );
	}

	/**
	 * Test trigger_admin_notice.
	 *
	 * @covers ::trigger_admin_notice
	 */
	public function test_trigger_admin_notice(): void {
		// Set up data.
		$input = 'foo';

		// Set up $_POST.
		$_POST['typo_setting'] = true;

		// Set up expectations.
		$this->options->shouldReceive( 'get_name' )->once()->with( 'setting' )->andReturn( 'typo_setting' );
		$this->admin->shouldReceive( 'get_active_settings_tab' )->once()->andReturn( 'my-tab' );
		Functions\expect( 'add_settings_error' )->once()->with( Admin_Interface::OPTION_GROUP . 'my-tab', 'some-id', 'An important message.', 'notice-info' );

		// Do it.
		$this->assertSame( (bool) $input, $this->admin->trigger_admin_notice( 'setting', 'some-id', 'An important message.', 'notice-info', $input ) ); // @phpstan-ignore-line - testing protected method.
	}

	/**
	 * Provide data for testing sanitize_settings.
	 *
	 * @return mixed[]
	 */
	public function provide_sanitize_settings_data(): array {
		return [
			[
				[ 'foo' => 'bar' ],
				[
					'foo'    => 'bar',
					'check1' => false,
				],
				'my-tab',
			],
			[
				[ 'foo' => 'bar' ],
				[
					'foo'    => 'bar',
					'check2' => false,
				],
				'other-tab',
			],
			[
				[
					'foo'    => 'xxx',
					'check1' => true,
				],
				[
					'foo'    => 'xxx',
					'check1' => true,
				],
				'my-tab',
			],
		];
	}

	/**
	 * Test sanitize_settings.
	 *
	 * @dataProvider provide_sanitize_settings_data
	 *
	 * @covers ::sanitize_settings
	 *
	 * @param  mixed[] $input    Input array.
	 * @param  mixed[] $expected Result array.
	 * @param  string  $tab      Selected tab.
	 */
	public function test_sanitize_settings( $input, $expected, $tab ): void {
		// Set up data.
		$defaults = [
			'foo'    => [
				'tab_id' => 'my-tab',
				'ui'     => UI\Controls\Number_Input::class,
			],
			'check1' => [
				'tab_id' => 'my-tab',
				'ui'     => UI\Controls\Checkbox_Input::class,
			],
			'check2' => [
				'tab_id' => 'other-tab',
				'ui'     => UI\Controls\Checkbox_Input::class,
			],
		];

		$this->setValue( $this->admin, 'defaults', $defaults, Admin_Interface::class );

		// Set up expectations.
		$this->admin->shouldReceive( 'get_active_settings_tab' )->once()->andReturn( $tab );

		// Do it.
		$this->assertSame( $expected, $this->admin->sanitize_settings( $input ) );
	}

	/**
	 * Test get_active_settings_tab.
	 *
	 * @covers ::get_active_settings_tab
	 */
	public function test_get_active_settings_tab(): void {
		// Set up data.
		$_REQUEST['tab'] = 'my-tab';

		// Set up expectations.
		Functions\expect( 'sanitize_key' )->once()->with( 'my-tab' )->andReturn( 'my-tab' );

		// Do it.
		$this->assertSame( 'my-tab', $this->invokeMethod( $this->admin, 'get_active_settings_tab' ) );
	}

	/**
	 * Test get_active_settings_tab.
	 *
	 * @covers ::get_active_settings_tab
	 */
	public function test_get_active_settings_tab_options_page(): void {
		// Set up data.
		$page                    = Admin_Interface::OPTION_GROUP . 'my-tab';
		$_REQUEST['option_page'] = $page;

		unset( $_REQUEST['tab'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Set up expectations.
		Functions\expect( 'sanitize_key' )->twice()->with( $page )->andReturn( $page );

		// Do it.
		$this->assertSame( 'my-tab', $this->invokeMethod( $this->admin, 'get_active_settings_tab' ) );
	}

	/**
	 * Test get_active_settings_tab.
	 *
	 * @covers ::get_active_settings_tab
	 */
	public function test_get_active_settings_tab_default(): void {
		$tabs = [
			'1st_tab' => [],
			'2nd_tab' => [],
			'3rd_tab' => [],
		];

		// Set up data.
		$this->setValue( $this->admin, 'admin_form_tabs', $tabs, Admin_Interface::class );

		unset( $_REQUEST['option_page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_REQUEST['tab'] );         // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Do it.
		$this->assertSame( '1st_tab', $this->invokeMethod( $this->admin, 'get_active_settings_tab' ) );
	}

	/**
	 * Test add_options_page.
	 *
	 * @covers ::add_options_page
	 */
	public function test_add_options_page(): void {
		// Set up data.
		$hookname = 'my-options';
		$tabs     = [
			'1st_tab' => [],
			'2nd_tab' => [],
			'3rd_tab' => [],
		];
		$sections = [
			'section1' => [
				'heading' => 'Heading 1',
				'tab_id'  => '1st_tab',
			],
			'section2' => [
				'heading' => 'Heading 2',
				'tab_id'  => '2nd_tab',
			],
		];

		$this->setValue( $this->admin, 'admin_form_tabs', $tabs, Admin_Interface::class );
		$this->setValue( $this->admin, 'admin_form_sections', $sections, Admin_Interface::class );

		// Set up expectations.
		Functions\expect( 'add_options_page' )->once()->with( 'wp-Typography', 'wp-Typography', 'manage_options', 'wp-typography', m::type( 'callable' ) )->andReturn( $hookname );
		Functions\expect( 'add_settings_section' )->times( count( $tabs ) )->with( m::type( 'string' ), '', [ $this->admin, 'print_settings_section' ], m::type( 'string' ) );
		Functions\expect( 'add_settings_section' )->times( count( $sections ) )->with( m::type( 'string' ), m::type( 'string' ), [ $this->admin, 'print_settings_section' ], m::type( 'string' ) );

		Actions\expectAdded( "load-$hookname" )->once()->with( [ $this->admin, 'add_context_help' ] );
		Actions\expectAdded( "admin_print_styles-$hookname" )->once()->with( [ $this->admin, 'print_admin_styles' ] );

		// Do it.
		$this->admin->add_options_page();
	}

	/**
	 * Test add_options_page when the user doesn't have enough permissions to show the page.
	 *
	 * @covers ::add_options_page
	 */
	public function test_add_options_page_not_enough_permissions(): void {
		// Set up expectations.
		Functions\expect( 'add_options_page' )->once()->with( 'wp-Typography', 'wp-Typography', 'manage_options', 'wp-typography', m::type( 'callable' ) )->andReturn( false );

		// Do it.
		$this->admin->add_options_page();
	}


	/**
	 * Test add_context_help.
	 *
	 * @covers ::add_context_help
	 */
	public function test_add_context_help(): void {
		// Set up data.
		$screen = m::mock( \WP_Screen::class );
		$help   = [
			'1st_tab' => [
				'heading' => 'Help Page 1',
				'content' => 'Bla bla.',
			],
			'2nd_tab' => [
				'heading' => 'Help Page 2',
				'content' => 'Bla bla.',
			],
			'3rd_tab' => [
				'heading' => 'Help Page 3',
				'content' => 'Bla bla.',
			],
		];

		$this->setValue( $this->admin, 'admin_help_pages', $help, Admin_Interface::class );

		// Set up expectations.
		Functions\expect( 'get_current_screen' )->once()->andReturn( $screen );
		Functions\expect( '__' )->zeroOrMoreTimes()->with( m::type( 'string' ), 'wp-typography' )->andReturn( 'foo' );
		Functions\expect( 'esc_url' )->zeroOrMoreTimes()->with( m::type( 'string' ) )->andReturn( 'my:url' );

		$screen->shouldReceive( 'add_help_tab' )->times( count( $help ) )->with( m::type( 'array' ) );
		$screen->shouldReceive( 'set_help_sidebar' )->once()->with( m::type( 'string' ) );

		// Do it.
		$this->admin->add_context_help();
	}

	/**
	 * Test add_context_help (failure path).
	 *
	 * @covers ::add_context_help
	 */
	public function test_add_context_help_no_screen(): void {
		// Set up data.
		$screen = m::mock( \WP_Screen::class );
		$help   = [
			'1st_tab' => [
				'heading' => 'Help Page 1',
				'content' => 'Bla bla.',
			],
			'2nd_tab' => [
				'heading' => 'Help Page 2',
				'content' => 'Bla bla.',
			],
			'3rd_tab' => [
				'heading' => 'Help Page 3',
				'content' => 'Bla bla.',
			],
		];

		$this->setValue( $this->admin, 'admin_help_pages', $help, Admin_Interface::class );

		// Set up expectations.
		Functions\expect( 'get_current_screen' )->once()->andReturn( null );
		Functions\expect( '__' )->never();
		Functions\expect( 'esc_url' )->never();

		$screen->shouldReceive( 'add_help_tab' )->never();
		$screen->shouldReceive( 'set_help_sidebar' )->never();

		// Do it.
		$this->admin->add_context_help();
	}

	/**
	 * Test print_settings_section.
	 *
	 * @covers ::print_settings_section
	 */
	public function test_print_settings_section(): void {
		// Set up data.
		$tabs     = [
			'1st_tab' => [
				'heading'     => 'Heading 1',
				'description' => 'Bla bla',
			],
			'2nd_tab' => [
				'heading'     => 'Heading 1',
				'description' => 'Bla bla',
			],
			'3rd_tab' => [
				'heading'     => 'Heading 1',
				'description' => 'Bla bla',
			],
		];
		$sections = [
			'section1' => [
				'heading'     => 'Heading 1',
				'tab_id'      => '1st_tab',
				'description' => 'Bla bla',
			],
			'section2' => [
				'heading'     => 'Heading 2',
				'tab_id'      => '2nd_tab',
				'description' => 'Bla bla',
			],
		];

		$this->setValue( $this->admin, 'admin_form_tabs', $tabs, Admin_Interface::class );
		$this->setValue( $this->admin, 'admin_form_sections', $sections, Admin_Interface::class );

		// Set up expectations.
		$this->expectOutputString( 'SECTION' );

		// Do it.
		$this->admin->print_settings_section( [ 'id' => 'section1' ] );
	}

	/**
	 * Test print_settings_section.
	 *
	 * @covers ::print_settings_section
	 */
	public function test_print_settings_section_with_tab(): void {
		// Set up data.
		$tabs     = [
			'1st_tab' => [
				'heading'     => 'Heading 1',
				'description' => 'Bla bla',
			],
			'2nd_tab' => [
				'heading'     => 'Heading 1',
				'description' => 'Bla bla',
			],
			'3rd_tab' => [
				'heading'     => 'Heading 1',
				'description' => 'Bla bla',
			],
		];
		$sections = [
			'section1' => [
				'heading'     => 'Heading 1',
				'tab_id'      => '1st_tab',
				'description' => 'Bla bla',
			],
			'section2' => [
				'heading'     => 'Heading 2',
				'tab_id'      => '2nd_tab',
				'description' => 'Bla bla',
			],
		];

		$this->setValue( $this->admin, 'admin_form_tabs', $tabs, Admin_Interface::class );
		$this->setValue( $this->admin, 'admin_form_sections', $sections, Admin_Interface::class );

		// Set up expectations.
		$this->expectOutputString( 'SECTION' );

		// Do it.
		$this->admin->print_settings_section( [ 'id' => '3rd_tab' ] );
	}

	/**
	 * Test maybe_add_privacy_notice_content on old WordPress.
	 *
	 * @covers ::maybe_add_privacy_notice_content
	 */
	public function test_maybe_add_privacy_notice_content_old_wp(): void {
		// Function wp_add_privacy_policy_content does not exist, so nothing should happen.
		$this->admin->maybe_add_privacy_notice_content();
	}

	/**
	 * Test maybe_add_privacy_notice_content on newer WordPress versions.
	 *
	 * @covers ::maybe_add_privacy_notice_content
	 */
	public function test_maybe_add_privacy_notice_content_new_wp(): void {
		Functions\expect( 'wp_add_privacy_policy_content' )->once();

		$this->admin->maybe_add_privacy_notice_content();
	}
}
