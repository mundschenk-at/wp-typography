<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2015-2017 Peter Putzer.
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
 *  @package wpTypography/Tests
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace PHP_Typography\Tests\Fixes\Token_Fixes;

use \PHP_Typography\Fixes\Token_Fixes;
use \PHP_Typography\Settings;

/**
 * Hyphenate_Fix unit test.
 *
 * @coversDefaultClass \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
 * @usesDefaultClass \PHP_Typography\Fixes\Token_Fixes\Hyphenate_Fix
 *
 * @uses ::__construct
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\Settings\Dash_Style
 * @uses PHP_Typography\Settings\Quote_Style
 * @uses PHP_Typography\Settings\Simple_Dashes
 * @uses PHP_Typography\Settings\Simple_Quotes
 * @uses PHP_Typography\Strings
 * @uses PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix
 */
class Hyphenate_Fix_Test extends Token_Fix_Testcase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { // @codingStandardsIgnoreLine
		parent::setUp();

		$this->fix = new Token_Fixes\Hyphenate_Fix();
	}

	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Strings
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_do_hyphenate() {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'de' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( false );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( true );

		$tokens = $this->tokenize( mb_convert_encoding( 'Änderungsmeldung', 'ISO-8859-2' ) );
		$hyphenated = $this->invokeMethod( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assertTokensSame( $hyphenated, $tokens );

		$tokens = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->invokeMethod( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assertTokensNotSame( $hyphenated, $tokens, 'Different encodings should not be equal.' );
	}


	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Strings
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_do_hyphenate_no_title_case() {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'de' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( false );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( false );

		$tokens = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->invokeMethod( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assertEquals( $tokens, $hyphenated );
	}


	/**
	 * Test do_hyphenate.
	 *
	 * @covers ::do_hyphenate
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser\Token
	 */
	public function test_do_hyphenate_invalid() {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( 'de' );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( false );
		$this->s->set_hyphenate_all_caps( true );
		$this->s->set_hyphenate_title_case( false );

		$this->s['hyphenMinBefore'] = 0; // invalid value.

		$tokens = $this->tokenize( 'Änderungsmeldung' );
		$hyphenated = $this->invokeMethod( $this->fix, 'do_hyphenate', [ $tokens, $this->s ] );
		$this->assertEquals( $tokens, $hyphenated );
	}

	/**
	 * Test get_hyphenator.
	 *
	 * @covers ::get_hyphenator()
	 *
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Hyphenator::set_language
	 * @uses PHP_Typography\Hyphenator::get_object_hash
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 */
	public function test_get_hyphenator() {
		$this->s['hyphenMinLength']             = 2;
		$this->s['hyphenMinBefore']             = 2;
		$this->s['hyphenMinAfter']              = 2;
		$this->s['hyphenationCustomExceptions'] = [ 'foo-bar' ];
		$this->s['hyphenLanguage']              = 'en-US';
		$h = $this->fix->get_hyphenator( $this->s );

		$this->assertInstanceOf( \PHP_Typography\Hyphenator::class, $h );

		$this->s['hyphenationCustomExceptions'] = [ 'bar-foo' ];
		$h = $this->fix->get_hyphenator( $this->s );

		$this->assertInstanceOf( \PHP_Typography\Hyphenator::class, $h );
	}

	/**
	 * Test set_hyphenator.
	 *
	 * @covers ::set_hyphenator()
	 *
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Hyphenator::__construct
	 * @uses PHP_Typography\Hyphenator::set_custom_exceptions
	 * @uses PHP_Typography\Hyphenator::set_language
	 */
	public function test_set_hyphenator() {

		// Initial set-up.
		$h1 = $this->fix->get_hyphenator( $this->s );

		// Create external Hyphenator.
		$h2 = new \PHP_Typography\Hyphenator();
		$this->fix->set_hyphenator( $h2 );

		// Retrieve Hyphenator and assert results.
		$this->assertEquals( $h2, $this->fix->get_hyphenator( $this->s ) );
		$this->assertNotEquals( $h1, $this->fix->get_hyphenator( $this->s ) );
	}


	/**
	 * Provide data for testing hyphenation.
	 *
	 * @return array
	 */
	public function provide_hyphenate_data() {
		return [
			[ 'A few words to hyphenate, like KINGdesk Really, there should be more hyphenation here!', 'A few words to hy&shy;phen&shy;ate, like KING&shy;desk Re&shy;al&shy;ly, there should be more hy&shy;phen&shy;ation here!', 'en-US', true, true, true ],
			// Not working with new de pattern file: [ 'Sauerstofffeldflasche', 'Sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche', 'de', true, true, true, false ],.
			[ 'Sauerstofffeldflasche', 'Sauer&shy;stoff&shy;feld&shy;fla&shy;sche', 'de', true, true, true ],
			// Not working with new de pattern file: [ 'Sauerstoff-Feldflasche', 'Sau&shy;er&shy;stoff-Feld&shy;fla&shy;sche', 'de', true, true, true, true ],.
			[ 'Geschäftsübernahme', 'Ge&shy;sch&auml;fts&shy;&uuml;ber&shy;nah&shy;me', 'de', true, true, true ],
			[ 'Trinkwasserinstallation', 'Trink&shy;was&shy;ser&shy;in&shy;stal&shy;la&shy;ti&shy;on', 'de', true, true, true ],
		];
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::do_hyphenate
	 * @uses ::get_hyphenator
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_hyphenate_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 * @param string $lang                 Language code.
	 * @param bool   $hyphenate_headings   Hyphenate headings.
	 * @param bool   $hyphenate_all_caps   Hyphenate words in ALL caps.
	 * @param bool   $hyphenate_title_case Hyphenate words in Title Case.
	 */
	public function test_apply( $input, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case ) {
		$this->s->set_hyphenation( true );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertFixResultSame( $input, $result );
	}

	/**
	 * Test apply.
	 *
	 * @covers ::apply
	 *
	 * @uses ::do_hyphenate
	 * @uses PHP_Typography\Hyphenator
	 * @uses PHP_Typography\Hyphenator\Trie_Node
	 * @uses PHP_Typography\Text_Parser
	 * @uses PHP_Typography\Text_Parser\Token
	 *
	 * @dataProvider provide_hyphenate_data
	 *
	 * @param string $input  HTML input.
	 * @param string $result Expected result.
	 * @param string $lang                 Language code.
	 * @param bool   $hyphenate_headings   Hyphenate headings.
	 * @param bool   $hyphenate_all_caps   Hyphenate words in ALL caps.
	 * @param bool   $hyphenate_title_case Hyphenate words in Title Case.
	 */
	public function test_apply_off( $input, $result, $lang, $hyphenate_headings, $hyphenate_all_caps, $hyphenate_title_case ) {
		$this->s->set_hyphenation( false );
		$this->s->set_hyphenation_language( $lang );
		$this->s->set_min_length_hyphenation( 2 );
		$this->s->set_min_before_hyphenation( 2 );
		$this->s->set_min_after_hyphenation( 2 );
		$this->s->set_hyphenate_headings( $hyphenate_headings );
		$this->s->set_hyphenate_all_caps( $hyphenate_all_caps );
		$this->s->set_hyphenate_title_case( $hyphenate_title_case );
		$this->s->set_hyphenation_exceptions( [ 'KING-desk' ] );

		$this->assertFixResultSame( $input, $input );
	}
}
