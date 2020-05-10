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

use WP_Typography\Components\Multilingual_Support;

use WP_Typography\Settings\Basic_Locale_Settings;
use WP_Typography\Settings\Locale_Settings;
use WP_Typography\Settings\Plugin_Configuration as Config;

use WP_Typography\Tests\TestCase;

use PHP_Typography\Settings;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

use Mockery as m;

/**
 * Multilingual_Support unit test.
 *
 * @coversDefaultClass \WP_Typography\Components\Multilingual_Support
 * @usesDefaultClass \WP_Typography\Components\Multilingual_Support
 *
 * @uses ::__construct
 */
class Multilingual_Support_Test extends TestCase {

	/**
	 * Test fixture.
	 *
	 * @var Multilingual_Support
	 */
	protected $multi;

	/**
	 * Test fixture.
	 *
	 * @var \WP_Typography\Implementation
	 */
	protected $api;

	/**
	 * Test fixture (instance mock).
	 *
	 * @var Basic_Locale_Settings
	 */
	protected $locale;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function set_up() {
		parent::set_up();

		$this->api    = m::mock( \WP_Typography\Implementation::class );
		$this->locale = m::mock( Basic_Locale_Settings::class );

		$locales = [
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
		];

		// Mock WP_Typography\Components\Multilingual_Support instance.
		$this->multi = m::mock( Multilingual_Support::class )
			->shouldAllowMockingProtectedMethods()->makePartial();

		// Split expectations and constructor call from mock definition.
		$this->multi->shouldReceive( 'locale_settings_sort' )
			->times( \count( $locales ) - 1 )
			->with( m::type( Basic_Locale_Settings::class ), m::type( Basic_Locale_Settings::class ) )
			->andReturn( 0 );
		$this->multi->__construct( $this->api, $locales );

		$this->setValue( $this->multi, 'hyphenation_languages', [ 'de' => 'Deutsch' ] );
		$this->setValue( $this->multi, 'diacritic_languages',   [ 'en' => 'English' ] );
	}

	/**
	 * Tests constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$sut = m::mock( Multilingual_Support::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$api     = m::mock( \WP_Typography\Implementation::class );
		$locales = [
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
			m::mock( Basic_Locale_Settings::class ),
		];

		// We don't really care about the exact number of comparisons, some sort
		// implementations take more until giving up (e.g. PHP 5.6).
		$sut->shouldReceive( 'locale_settings_sort' )->atLeast()->times( \count( $locales ) - 1 )->andReturn( 0 );

		$sut->__construct( $api, $locales );

		$this->assert_attribute_same( $api, 'api', $sut );

		$sorted_locales = $this->get_value( $sut, 'locales' );
		$this->assertCount( \count( $locales ), $sorted_locales );
		foreach ( $sorted_locales as $l ) {
			$this->assertTrue( \in_array( $l, $locales, true ) );
		}
	}

	/**
	 * Test run.
	 *
	 * @covers ::run
	 */
	public function test_run() {
		Actions\expectAdded( 'plugins_loaded' )->once()->with( [ $this->multi, 'add_plugin_defaults_filter' ] );
		Actions\expectAdded( 'init' )->once()->with( [ $this->multi, 'enable_automatic_language_settings' ] );

		$this->assertNull( $this->multi->run() );
	}

	/**
	 * Test add_plugin_defaults_filter.
	 *
	 * @covers ::add_plugin_defaults_filter
	 */
	public function test_add_plugin_defaults_filter() {

		$this->api->shouldReceive( 'get_hyphenation_languages' )->once()->andReturn( [ 'de' => 'Deutsch' ] );
		$this->api->shouldReceive( 'get_diacritic_languages' )->once()->andReturn( [ 'en' => 'English' ] );

		Filters\expectAdded( 'typo_plugin_defaults' )->once()->with( [ $this->multi, 'filter_defaults' ] );

		$this->assertNull( $this->multi->add_plugin_defaults_filter() );
	}

	/**
	 * Test enable_automatic_language_settings.
	 *
	 * @covers ::enable_automatic_language_settings
	 */
	public function test_enable_automatic_language_settings() {
		$this->api->shouldReceive( 'get_config' )->once()->andReturn( [ Config::ENABLE_MULTILINGUAL_SUPPORT => true ] );

		Filters\expectAdded( 'typo_settings' )->once()->with( [ $this->multi, 'automatic_language_settings' ] );

		$this->assertNull( $this->multi->enable_automatic_language_settings() );
	}

	/**
	 * Provides data for testing locale_settings_sort.
	 *
	 * @return array
	 */
	public function provide_locale_settings_sort_data() {
		return [
			[ 0, 0, 0 ],
			[ 47, 47, 0 ],
			[ 47, 11, -1 ],
			[ 8, 15, 1 ],
			[ 10, 11, 1 ],
			[ 11, 10, -1 ],
			[ 11, 11, 0 ],
			[ 10, 10, 0 ],
		];
	}

	/**
	 * Tests locale_settings_sort.
	 *
	 * @covers ::locale_settings_sort
	 *
	 * @dataProvider provide_locale_settings_sort_data
	 *
	 * @param int $prio1  Priority of first Locale_Settings object.
	 * @param int $prio2  Priority of second Locale_Settings object.
	 * @param int $result Expected result.
	 */
	public function test_locale_settings_sort( $prio1, $prio2, $result ) {
		$locale1 = m::mock( Locale_Settings::class );
		$locale2 = m::mock( Locale_Settings::class );

		$locale1->shouldReceive( 'priority' )->once()->andReturn( $prio1 );
		$locale2->shouldReceive( 'priority' )->once()->andReturn( $prio2 );

		$this->assertSame( $result, $this->multi->locale_settings_sort( $locale1, $locale2 ) );
	}

	/**
	 * Provide data for testing automatic_language_settings.
	 *
	 * @return array
	 */
	public function provide_automatic_language_settings_data() {
		return [
			[ false, false ],
			[ true, false ],
			[ false, true ],
			[ true, true ],
		];
	}

	/**
	 * Test automatic_language_settings.
	 *
	 * @covers ::automatic_language_settings
	 *
	 * @dataProvider provide_automatic_language_settings_data
	 *
	 * @param  bool $hyphenation Required.
	 * @param  bool $diacritics  Required.
	 */
	public function test_automatic_language_settings( $hyphenation, $diacritics ) {
		$s      = m::mock( Settings::class );
		$locale = 'de-DE-foobar';

		$this->multi->shouldReceive( 'get_current_locale' )->once()->andReturn( [ $locale, 'de', 'DE', 'foobar' ] );

		// Hyphenation.
		if ( ! empty( $hyphenation ) ) {
			$hyphenation_result = 'de';
			$s->shouldReceive( 'set_hyphenation_language' )->once()->with( $hyphenation_result );
		} else {
			$hyphenation_result = false;
			$s->shouldReceive( 'set_hyphenation' )->once()->with( $hyphenation_result );
		}
		$this->multi->shouldReceive( 'match_language' )->once()
			->with( m::type( 'array' ), 'de-DE', 'de', Multilingual_Support::MATCH_TYPE_HYPHENATION )
			->andReturn( $hyphenation_result );

		// Smart diacritics.
		if ( ! empty( $diacritics ) ) {
			$diacritics_result = 'de';
			$s->shouldReceive( 'set_diacritic_language' )->once()->with( $diacritics_result );
		} else {
			$diacritics_result = false;
			$s->shouldReceive( 'set_smart_diacritics' )->once()->with( $diacritics_result );
		}
		$this->multi->shouldReceive( 'match_language' )->once()
			->with( m::type( 'array' ), 'de-DE', 'de', Multilingual_Support::MATCH_TYPE_DIACRITIC )
			->andReturn( $diacritics_result );

		// Other adjustments.
		$this->multi->shouldReceive( 'match_locale' )->once()->with( 'de', 'DE', 'foobar' )->andReturn( $this->locale );
		$this->multi->shouldReceive( 'adjust_french_punctuation_spacing' )->once()->with( m::type( Settings::class ), $locale, $this->locale );
		$this->multi->shouldReceive( 'adjust_dash_style' )->once()->with( m::type( Settings::class ), $locale, $this->locale );
		$this->multi->shouldReceive( 'adjust_quote_styles' )->once()->with( m::type( Settings::class ), $locale, $this->locale );

		// Do it.
		$this->assertInstanceOf( Settings::class, $this->multi->automatic_language_settings( $s ) );
	}

	/**
	 * Test filter_defaults.
	 *
	 * @covers ::filter_defaults
	 *
	 * @dataProvider provide_automatic_language_settings_data
	 *
	 * @param  bool $hyphenation Required.
	 * @param  bool $diacritics  Required.
	 */
	public function test_filter_defaults( $hyphenation, $diacritics ) {
		$locale   = 'de-DE-foobar';
		$defaults = [];

		$this->multi->shouldReceive( 'get_current_locale' )->once()->andReturn( [ $locale, 'de', 'DE', 'foobar' ] );

		// Hyphenation.
		if ( ! empty( $hyphenation ) ) {
			$hyphenation_result = 'de';
		} else {
			$hyphenation_result = false;
		}
		$this->multi->shouldReceive( 'match_language' )->once()
			->with( m::type( 'array' ), 'de-DE', 'de', Multilingual_Support::MATCH_TYPE_HYPHENATION )
			->andReturn( $hyphenation_result );

		// Smart diacritics.
		if ( ! empty( $diacritics ) ) {
			$diacritics_result = 'de';
		} else {
			$diacritics_result = false;
		}
		$this->multi->shouldReceive( 'match_language' )->once()
			->with( m::type( 'array' ), 'de-DE', 'de', Multilingual_Support::MATCH_TYPE_DIACRITIC )
			->andReturn( $diacritics_result );

		// Other adjustments.
		$this->multi->shouldReceive( 'match_locale' )->once()->with( 'de', 'DE', 'foobar' )->andReturn( $this->locale );
		$this->locale->shouldReceive( 'adjust_defaults' )->once()->with( [] )->andReturn( [ 'foo' => 'bar' ] );

		// Do it.
		$result = $this->multi->filter_defaults( $defaults );

		if ( $hyphenation_result ) {
			$this->assertSame( $hyphenation_result, $result[ Config::HYPHENATE_LANGUAGES ] );
		} else {
			$this->assertFalse( $result[ Config::ENABLE_HYPHENATION ] );
		}

		if ( $diacritics_result ) {
			$this->assertSame( $diacritics_result, $result[ Config::DIACRITIC_LANGUAGES ] );
		}

		$this->assertSame( 'bar', $result['foo'] );
	}

	/**
	 * Test adjust_french_punctuation_spacing.
	 *
	 * @covers ::adjust_french_punctuation_spacing
	 */
	public function test_adjust_french_punctuation_spacing() {
		$s      = m::mock( Settings::class );
		$locale = 'de-DE-foobar';

		$this->locale->shouldReceive( 'use_french_punctuation_spacing' )->andReturn( true );
		Filters\expectApplied( 'typo_enable_french_punctuation_spacing_for_locale' )->once()->with( true, $locale )->andReturnFirstArg();
		$s->shouldReceive( 'set_french_punctuation_spacing' )->once()->with( true );

		$this->assertNull( $this->multi->adjust_french_punctuation_spacing( $s, $locale, $this->locale ) );
	}

	/**
	 * Test adjust_french_punctuation_spacing with adjustment = null.
	 *
	 * @covers ::adjust_french_punctuation_spacing
	 */
	public function test_adjust_french_punctuation_spacing_null_adjustment() {
		$s      = m::mock( Settings::class );
		$locale = 'de-DE-foobar';

		Filters\expectApplied( 'typo_enable_french_punctuation_spacing_for_locale' )->once()->with( false, $locale )->andReturnFirstArg();
		$s->shouldReceive( 'set_french_punctuation_spacing' )->once()->with( false );

		$this->assertNull( $this->multi->adjust_french_punctuation_spacing( $s, $locale, null ) );
	}

	/**
	 * Test adjust_dash_style.
	 *
	 * @covers ::adjust_dash_style
	 */
	public function test_adjust_dash_style() {
		$s      = m::mock( Settings::class );
		$locale = 'de-DE-foobar';

		$this->locale->shouldReceive( 'dash_style' )->andReturn( true );
		Filters\expectApplied( 'typo_dash_style_for_locale' )->once()->with( true, $locale )->andReturnFirstArg();
		$s->shouldReceive( 'set_smart_dashes_style' )->once()->with( true );

		$this->assertNull( $this->multi->adjust_dash_style( $s, $locale, $this->locale ) );
	}

	/**
	 * Test adjust_dash_style with adjustment = null.
	 *
	 * @covers ::adjust_dash_style
	 */
	public function test_adjust_dash_style_null_adjustment() {
		$s      = m::mock( Settings::class );
		$locale = 'de-DE-foobar';

		$s->shouldReceive( 'dash_style' )->andReturn( true );
		Filters\expectApplied( 'typo_dash_style_for_locale' )->once()->with( true, $locale )->andReturnFirstArg();
		$s->shouldReceive( 'set_smart_dashes_style' )->once()->with( true );

		$this->assertNull( $this->multi->adjust_dash_style( $s, $locale, null ) );
	}

	/**
	 * Test adjust_quote_styles.
	 *
	 * @covers ::adjust_quote_styles
	 */
	public function test_adjust_quote_styles() {
		$s      = m::mock( Settings::class );
		$locale = 'de-DE-foobar';

		$this->locale->shouldReceive( 'primary_quote_style' )->andReturn( 'primary' );
		$this->locale->shouldReceive( 'secondary_quote_style' )->andReturn( 'secondary' );
		Filters\expectApplied( 'typo_primary_quote_style_for_locale' )->once()->with( 'primary', $locale )->andReturnFirstArg();
		Filters\expectApplied( 'typo_secondary_quote_style_for_locale' )->once()->with( 'secondary', $locale )->andReturnFirstArg();
		$s->shouldReceive( 'set_smart_quotes_primary' )->once()->with( 'primary' );
		$s->shouldReceive( 'set_smart_quotes_secondary' )->once()->with( 'secondary' );

		$this->assertNull( $this->multi->adjust_quote_styles( $s, $locale, $this->locale ) );
	}

	/**
	 * Test adjust_quote_styles with adjustment = null.
	 *
	 * @covers ::adjust_quote_styles
	 */
	public function test_adjust_quote_styles_null_adjustment() {
		$s      = m::mock( Settings::class );
		$locale = 'de-DE-foobar';

		$s->shouldReceive( 'primary_quote_style' )->andReturn( 'primary' );
		$s->shouldReceive( 'secondary_quote_style' )->andReturn( 'secondary' );
		Filters\expectApplied( 'typo_primary_quote_style_for_locale' )->once()->with( 'primary', $locale )->andReturnFirstArg();
		Filters\expectApplied( 'typo_secondary_quote_style_for_locale' )->once()->with( 'secondary', $locale )->andReturnFirstArg();
		$s->shouldReceive( 'set_smart_quotes_primary' )->once()->with( 'primary' );
		$s->shouldReceive( 'set_smart_quotes_secondary' )->once()->with( 'secondary' );

		$this->assertNull( $this->multi->adjust_quote_styles( $s, $locale, null ) );
	}

	/**
	 * Test match_locale.
	 *
	 * @covers ::match_locale
	 */
	public function test_match_locale() {
		$language = 'de';
		$country  = 'DE';
		$modifier = 'foo';

		$locale1 = m::mock( Basic_Locale_Settings::class );
		$locale2 = m::mock( Basic_Locale_Settings::class );
		$locale3 = m::mock( Basic_Locale_Settings::class );
		$this->set_value( $this->multi, 'locales', [ $locale1, $locale2, $locale3 ] );

		$locale1->shouldReceive( 'match' )->once()->with( $language, $country, $modifier )->andReturn( false );
		$locale2->shouldReceive( 'match' )->once()->with( $language, $country, $modifier )->andReturn( true );
		$locale3->shouldReceive( 'match' )->never();

		$this->assertSame( $locale2, $this->invoke_method( $this->multi, 'match_locale', [ $language, $country, $modifier ] ) );
	}

	/**
	 * Test match_locale.
	 *
	 * @covers ::match_locale
	 */
	public function test_match_locale_failing() {
		$language = 'de';
		$country  = 'DE';
		$modifier = 'foo';

		$locale1 = m::mock( Basic_Locale_Settings::class );
		$locale2 = m::mock( Basic_Locale_Settings::class );
		$locale3 = m::mock( Basic_Locale_Settings::class );
		$this->set_value( $this->multi, 'locales', [ $locale1, $locale2, $locale3 ] );

		$locale1->shouldReceive( 'match' )->once()->with( $language, $country, $modifier )->andReturn( false );
		$locale2->shouldReceive( 'match' )->once()->with( $language, $country, $modifier )->andReturn( false );
		$locale3->shouldReceive( 'match' )->once()->with( $language, $country, $modifier )->andReturn( false );

		$this->assertNull( $this->invoke_method( $this->multi, 'match_locale', [ $language, $country, $modifier ] ) );
	}

	/**
	 * Provides data for testing get_current_locale.
	 *
	 * @return array
	 */
	public function provide_get_current_locale_data() {
		return [
			[ 'de_DE_formal', 'de-DE-formal', 'de',  'DE', 'formal' ],
			[ 'rup_MK',       'rup-MK',       'rup', 'MK', '' ],
			[ 'zh_CN',        'zh-CN',        'zh',  'CN', '' ],
			[ 'yor',          'yor',          'yor', '',   '' ],
			[ 'fi',           'fi',           'fi',  '',   '' ],
		];
	}

	/**
	 * Test get_current_locale.
	 *
	 * @covers ::get_current_locale
	 *
	 * @dataProvider provide_get_current_locale_data
	 *
	 * @param  string $input    Raw WordPress locale string.
	 * @param  string $locale   Expected result.
	 * @param  string $language Optional. Expected result. Default ''.
	 * @param  string $country  Optional. Expected result. Default ''.
	 * @param  string $modifier Optional. Expected result. Default ''.
	 */
	public function test_get_current_locale( $input, $locale, $language = '', $country = '', $modifier = '' ) {
		Filters\expectApplied( 'typo_current_locale' )->once()->with( '' )->andReturnFirstArg();
		Functions\expect( 'get_locale' )->once()->andReturn( $input );

		$this->assertSame( [ $locale, $language, $country, $modifier ], $this->invokeMethod( $this->multi, 'get_current_locale' ) );
	}

	/**
	 * Test get_current_locale.
	 *
	 * @covers ::get_current_locale
	 */
	public function test_get_current_locale_filtered() {
		$language    = 'de';
		$country     = 'DE';
		$modifier    = 'bar';
		$locale      = "{$language}-{$country}-{$modifier}";
		$locale_orig = "{$language}_{$country}_{$modifier}";

		Filters\expectApplied( 'typo_current_locale' )->once()->with( '' )->andReturn( $locale_orig );
		Functions\expect( 'get_locale' )->never();

		$this->assertSame( [ $locale, $language, $country, $modifier ], $this->invokeMethod( $this->multi, 'get_current_locale' ) );
	}

	/**
	 * Test match_language.
	 *
	 * @covers ::match_language
	 *
	 * @uses ::normalize
	 */
	public function test_match_language() {
		$type     = 'foobar';
		$language = 'de';
		$country  = 'DE';
		$modifier = 'bar';
		$locale   = "{$language}-{$country}-{$modifier}";

		$languages = [
			'en-US' => 'English (US)',
			'de-DE' => 'Deutsch',
		];

		Filters\expectApplied( "typo_match_{$type}_language" )->once()->with( '', $languages, $locale, $language )->andReturn( '' );

		$this->assertSame( 'de-DE', $this->invokeMethod( $this->multi, 'match_language', [ $languages, $locale, $language, $type ] ) );
	}

	/**
	 * Test match_language.
	 *
	 * @covers ::match_language
	 *
	 * @uses ::normalize
	 */
	public function test_match_language_shortcut_locale() {
		$type     = 'foobar';
		$language = 'de';
		$country  = 'DE';
		$modifier = 'bar';
		$locale   = "{$language}-{$country}-{$modifier}";

		$languages = [
			'en-US' => 'English (US)',
			$locale => 'Deutsch',
		];

		Filters\expectApplied( "typo_match_{$type}_language" )->once()->with( '', $languages, $locale, $language )->andReturn( '' );

		$this->assertSame( $locale, $this->invokeMethod( $this->multi, 'match_language', [ $languages, $locale, $language, $type ] ) );
	}

	/**
	 * Test match_language.
	 *
	 * @covers ::match_language
	 *
	 * @uses ::normalize
	 */
	public function test_match_language_shortcut_language() {
		$type     = 'foobar';
		$language = 'de';
		$country  = 'DE';
		$modifier = 'bar';
		$locale   = "{$language}-{$country}-{$modifier}";

		$languages = [
			'en-US' => 'English (US)',
			'de'    => 'Deutsch',
		];

		Filters\expectApplied( "typo_match_{$type}_language" )->once()->with( '', $languages, $locale, $language )->andReturn( '' );

		$this->assertSame( 'de', $this->invokeMethod( $this->multi, 'match_language', [ $languages, $locale, $language, $type ] ) );
	}

	/**
	 * Test match_language.
	 *
	 * @covers ::match_language
	 *
	 * @uses ::normalize
	 */
	public function test_match_language_multiple_hits() {
		$type     = 'foobar';
		$language = 'de';
		$country  = 'DE';
		$locale   = "{$language}-{$country}";

		$languages = [
			'en-US'      => 'English (US)',
			'de-DE-1901' => 'Deutsch (Deutschland, alte Rechtschreibung)',
			'de-AT'      => 'Deutsch (Österreich)',
		];

		Filters\expectApplied( "typo_match_{$type}_language" )->once()->with( '', $languages, $locale, $language )->andReturn( '' );

		$this->assertSame( 'de-DE-1901', $this->invokeMethod( $this->multi, 'match_language', [ $languages, $locale, $language, $type ] ) );
	}

	/**
	 * Test match_language.
	 *
	 * @covers ::match_language
	 *
	 * @uses ::normalize
	 */
	public function test_match_language_shortcut_filter() {
		$type     = 'foobar';
		$language = 'de';
		$country  = 'DE';
		$modifier = 'bar';
		$locale   = "{$language}-{$country}-{$modifier}";

		$languages = [
			'en-US' => 'English (US)',
			'de'    => 'Deutsch',
		];

		Filters\expectApplied( "typo_match_{$type}_language" )->once()->with( '', $languages, $locale, $language )->andReturn( 'something' );

		$this->assertSame( 'something', $this->invokeMethod( $this->multi, 'match_language', [ $languages, $locale, $language, $type ] ) );
	}

	/**
	 * Provide data for testing match_language.
	 *
	 * @return array
	 */
	public function provide_match_language_data() {
		return [
			[ 'en-US',   'en',    'US' ],
			[ 'en-GB',   'en',    'GB' ],
			[ 'de',      'de',    'CH' ],
			[ 'el-Poly', 'el-po', null ],
			[ 'el-Mono', 'el',    null ],
			[ 'sr-Cyrl', 'sr',    'RS' ],
			[ 'oc',      'oci',   null ],
			[ 'or',      'ory',   null ],
			[ 'ca',      'bal',   null ],
			[ 'mn-Cyrl', 'mn',    null ],
			[ 'mr',      'mr',    null ],
			[ 'nl',      'nl',    'BE' ],
			[ 'tk',      'tuk',   null ],
		];
	}

	/**
	 * Tests the match_language function with the real language list.
	 *
	 * @covers ::normalize
	 *
	 * @uses ::match_language
	 * @uses PHP_Typography\PHP_Typography::get_hyphenation_languages
	 *
	 * @dataProvider provide_match_language_data
	 *
	 * @param  string      $result   Expected result.
	 * @param  string      $language Required.
	 * @param  string      $country  Required.
	 * @param  string|null $modifier Optional.
	 */
	public function test_match_language_real_language_list( $result, $language, $country = null, $modifier = null ) {
		$type      = 'hyphenation';
		$locale    = \join( '-', \array_merge( (array) $language, (array) $country, (array) $modifier ) );
		$languages = \PHP_Typography\PHP_Typography::get_hyphenation_languages();

		Filters\expectApplied( "typo_match_{$type}_language" )->once()->with( '', $languages, $locale, $language )->andReturn( '' );

		$this->assertSame( $result, $this->invokeMethod( $this->multi, 'match_language', [ $languages, $locale, $language, $type ] ) );
	}
}
