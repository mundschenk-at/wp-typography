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

namespace WP_Typography\Tests;

use WP_Typography\Data_Storage\Options;
use WP_Typography\Settings\Plugin_Configuration as Config;

use PHP_Typography\Hyphenator_Cache;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * WP_Typography unit test.
 *
 * @coversDefaultClass \WP_Typography
 * @usesDefaultClass \WP_Typography
 *
 * @uses \WP_Typography\Components\Admin_Interface::__construct
 * @uses \WP_Typography\Components\Setup::__construct
 * @uses \WP_Typography\Components\Common::__construct
 */
class WP_Typography_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography
	 */
	protected $wp_typo;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Transients
	 */
	protected $transients;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Cache
	 */
	protected $cache;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Data_Storage\Options
	 */
	protected $options;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {

		// Mock WP_Typography\Data_Storage\Options instance.
		$this->options = m::mock( \WP_Typography\Data_Storage\Options::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Data_Storage\Transients instance.
		$this->transients = m::mock( \WP_Typography\Data_Storage\Transients::class )
			->shouldReceive( 'get' )->byDefault()->andReturn( false )
			->shouldReceive( 'get_large_object' )->byDefault()->andReturn( false )
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'set_large_object' )->andReturn( false )->byDefault()
			->getMock();

		// Mock WP_Typography\Data_Storage\Cache instance.
		$this->cache = m::mock( \WP_Typography\Data_Storage\Cache::class )
			->shouldReceive( 'get' )->andReturn( false )->byDefault()
			->shouldReceive( 'set' )->andReturn( false )->byDefault()
			->shouldReceive( 'invalidate' )->byDefault()
			->getMock();

		// Create instance.
		$this->wp_typo = m::mock( \WP_Typography\Implementation::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		parent::set_up();
	}

	/**
	 * Necesssary clean-up work.
	 */
	protected function tear_down() {

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, 'instance', null );

		parent::tear_down();
	}

	/**
	 * Test get_user_settings.
	 *
	 * @covers ::get_user_settings
	 *
	 * @uses WP_Typography::get_instance
	 */
	public function test_get_user_settings() {
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );

		$object = new \stdClass();

		$this->wp_typo->shouldReceive( 'get_settings' )->once()->andReturn( $object );

		$s = \WP_Typography::get_user_settings();

		$this->assertNotSame( $object, $s );

		// Reset singleton.
		$this->setStaticValue( \WP_Typography::class, 'instance', null );
	}

	/**
	 * Test a static call to get_hyphenation_languages
	 *
	 * @covers ::__callStatic
	 *
	 * @uses ::get_instance
	 */
	public function test_call_static_get_hyphenation_languages() {
		// Set up singleton.
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );

		$this->wp_typo->shouldReceive( 'get_hyphenation_languages' )->once()->andReturn( [ 'de' => 'German' ] );

		$langs = \WP_Typography::get_hyphenation_languages();

		$this->assertContainsOnly( 'string', $langs, 'The languages array should only contain strings.' );
		$this->assertContainsOnly( 'string', array_keys( $langs ), 'The languages array should be indexed by language codes.' );
	}

	/**
	 * Test a static call to foobar(), failing.
	 *
	 * @covers ::__callStatic
	 *
	 * @uses ::get_instance
	 *
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage Static method WP_Typography::foobar does not exist.
	 */
	public function test_call_static_foobar() {
		// Set up singleton.
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );

		$this->wp_typo->shouldNotReceive( 'foobar' );

		$this->assertNull( \WP_Typography::foobar() );
	}

	/**
	 * Test filter.
	 *
	 * @covers ::filter
	 *
	 * @uses ::get_instance
	 */
	public function test_filter() {
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process' )->once()->with( 'foobar', false, false, null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter( 'foobar', null ) );
	}

	/**
	 * Test filter_title.
	 *
	 * @covers ::filter_title
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_title() {
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_title' )->once()->with( 'foobar', null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_title( 'foobar', null ) );
	}

	/**
	 * Test filter_title_parts.
	 *
	 * @covers ::filter_title_parts
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_title_parts() {
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_title_parts' )->once()->with( 'foobar', null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_title_parts( 'foobar', null ) );
	}

	/**
	 * Test filter_feed.
	 *
	 * @covers ::filter_feed
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_feed() {
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_feed' )->once()->with( 'foobar', false, null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_feed( 'foobar', null ) );
	}

	/**
	 * Test filter_feed_title.
	 *
	 * @covers ::filter_feed_title
	 *
	 * @uses ::get_instance
	 */
	public function test_filter_feed_title() {
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'process_feed_title' )->once()->with( 'foobar', null )->andReturn( 'barfoo' );
		$this->assertSame( 'barfoo', \WP_Typography::filter_feed_title( 'foobar', null ) );
	}

	/**
	 * Test get_version_hash.
	 *
	 * @covers ::get_version_hash
	 * @covers ::hash_version_string
	 *
	 * @uses ::get_instance
	 */
	public function test_get_version_hash() {
		$this->setStaticValue( \WP_Typography::class, 'instance', $this->wp_typo );
		$this->wp_typo->shouldReceive( 'get_version' )->once()->andReturn( '7.6.6' );

		$this->assertSame( 'GFF', $this->wp_typo->get_version_hash() );
	}

}
