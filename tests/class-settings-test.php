<?php


/**
 *
 * @coversDefaultClass \PHP_Typography\Settings
 * @usesDefaultClass \PHP_Typography\Settings
 *
 * @uses PHP_Typography\Settings
 * @uses PHP_Typography\get_ancestors
 * @uses PHP_Typography\has_class
 * @uses PHP_Typography\nodelist_to_array
 * @uses PHP_Typography\uchr
 * @uses PHP_Typography\arrays_intersect
 * @uses PHP_Typography\is_odd
 * @uses PHP_Typography\mb_str_split
 */
class Settings_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->settings = new \PHP_Typography\Settings( false );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * Helper function to generate a valid token list from strings.
     *
     * @param string $value
     * @param string $type Optional. Default 'word'.
     *
     * @return array
     */
    protected function tokenize( $value, $type = 'word' ) {
    	return array(
    		array(
	    		'type'  => $type,
	    		'value' => $value
    		)
    	);
    }

    /**
     *
     * @param string $expected_value
     * @param array $actual_tokens
     * @param string $message
     */
    protected function assertTokenSame( $expected_value, $actual_tokens, $message = '' ) {
    	foreach ( $actual_tokens as &$actual ) {
    		$actual['value'] = clean_html( $actual['value'] );
    	}

    	return $this->assertSame( $this->tokenize( $expected_value ) , $actual_tokens, $message );
    }

    /**
     * @covers ::set_defaults
     */
	public function test_set_defaults()
	{
		$second_settings = new \PHP_Typography\Settings( false );
		$this->assertAttributeEmpty( 'data', $second_settings );
		$second_settings->set_defaults();
		$this->assertAttributeNotEmpty( 'data', $second_settings );
	}


    /**
     * @covers ::init
     * @covers ::initialize_components
     * @covers ::initialize_patterns

     * @covers ::__construct
     *
     * @uses PHP_Typography\Hyphenator
     */
    public function test_init() {
    	$second_settings = new \PHP_Typography\Settings( false );
    	$this->assertAttributeNotEmpty( 'chr', $second_settings );
    	$this->assertAttributeNotEmpty( 'regex', $second_settings );
    	$this->assertAttributeNotEmpty( 'components', $second_settings );
    	$this->assertAttributeEmpty( 'data', $second_settings );

    	$second_settings->init( true );
		$this->assertAttributeNotEmpty( 'chr', $second_settings );
    	$this->assertAttributeNotEmpty( 'regex', $second_settings );
    	$this->assertAttributeNotEmpty( 'components', $second_settings );
    	$this->assertAttributeNotEmpty( 'data', $second_settings );
    }


    /**
     * @covers ::__get
     *
     * @uses ::offsetGet
     */
    public function test___get() {
    	$s = $this->settings;

    	$s[ 'newKey' ] = 42;
    	$this->assertEquals( 42, $s->newKey );
    }

    /**
     * @covers ::__set
     *
     * @uses ::__get
     * @uses ::__isset
     */
    public function test___set() {
    	$s = $this->settings;

    	$this->assertFalse( isset( $s->newKey ) );
    	$s->newKey = 42;
    	$this->assertTrue( isset( $s->newKey ) );
    }

    /**
     * @covers ::__isset
     */
    public function test___isset() {
    	$s = $this->settings;

    	$this->assertFalse( isset( $s->newKey ) );
    	$s->newKey = 42;
    	$this->assertTrue( isset( $s->newKey ) );
    }

    /**
     * @covers ::__unset
     */
    public function test___unset() {
    	$s = $this->settings;

    	$s->newKey = 42;
    	$this->assertTrue( isset( $s->newKey ) );

    	unset( $s->newKey );
    	$this->assertFalse( isset( $s->newKey ) );
    }

    /**
     * @covers ::offsetSet
     *
     * @uses ::offsetGet
     * @uses ::offsetExists
     */
    public function test_offsetSet() {
    	$s = $this->settings;

    	$this->assertFalse( isset( $s[0] ) );
    	$s[] = 666;
    	$this->assertEquals( 666, $s[0] );

    	$this->assertFalse( isset( $s['newKey'] ) );
    	$s[ 'newKey' ] = 42;
    	$this->assertEquals( 42, $s['newKey'] );
    }

    /**
     * @covers ::offsetExists
     *
     * @uses ::offsetSet
     */
    public function test_offsetExists() {
    	$s = $this->settings;

    	$this->assertFalse( isset( $s['newKey'] ) );
    	$s[ 'newKey' ] = 42;
    	$this->assertTrue( isset( $s['newKey'] ) );

    }

    /**
     * @covers ::offsetUnset
     *
     * @uses ::offsetSet
     * @uses ::offsetGet
     * @uses ::offsetExists
     */
    public function test_offsetUnset() {
    	$s = $this->settings;

    	$s[ 'newKey' ] = 42;
    	$this->assertTrue( isset( $s['newKey'] ) );

    	unset( $s['newKey'] );
    	$this->assertFalse( isset( $s['newKey'] ) );
    }

    /**
     * @covers ::offsetGet
     *
     * @uses ::offsetSet
     */
    public function test_offsetGet() {
    	$s = $this->settings;
    	$this->assertNull( $s['newKey'] );

    	$s[ 'newKey' ] = 42;
    	$this->assertEquals( 42, $s['newKey'] );
    }


    /**
     * @covers ::chr
     */
    public function test_chr() {
		$s = $this->settings;

		$this->assertFalse( $s->chr( 'DoesNotExist' ) );
		$this->assertEquals( $s->chr( 'noBreakSpace' ), \PHP_Typography\uchr( 160 ) );
		$this->assertEquals( $s->chr( 'emDash' ), \PHP_Typography\uchr( 8212 ) );
    }

    /**
     * @covers ::get_components
     */
    public function test_get_components() {
    	$s = $this->settings;
    	$c = $s->get_components();

    	$this->assertTrue( is_array( $c ) );
    	$this->assertGreaterThan( 0, count( $c ) );
    }

    /**
     * @covers ::component
     */
    public function test_component() {
    	$s = $this->settings;

    	$this->assertFalse( $s->component( 'DoesNotExist' ) );
    	$this->assertEquals( $s->component( 'numbersPrime' ), '\b(?:\d+\/)?\d{1,3}' );
    	$this->assertEquals( $s->component( 'urlScheme' ), '(?:https?|ftps?|file|nfs|feed|itms|itpc)' );
    }

    /**
     * @covers ::get_regular_expressions
     */
    public function test_get_regular_expressions() {
    	$s = $this->settings;
    	$regexs = $s->get_regular_expressions();

    	$this->assertTrue( is_array( $regexs ) );
    	$this->assertGreaterThan( 0, count( $regexs ) );
    }

    /**
     * @covers ::regex
     */
    public function test_regex() {
    	$s = $this->settings;

    	$this->assertFalse( $s->regex( 'DoesNotExist' ) );
    	$this->assertEquals( $s->regex( 'smartQuotesSingleQuotedNumbers' ), "/(?<=\W|\A)'([^\"]*\d+)'(?=\W|\Z)/uS" );
    	$this->assertEquals( $s->regex( 'smartDashesEnDashNumbers' ), "/(\b\d+)\-(\d+\b)/S" );
    }

    /**
     * @covers ::get_named_characters
     */
    public function test_get_named_characters() {
    	$s = $this->settings;
    	$c = $s->get_named_characters();

    	$this->assertTrue( is_array( $c ) );
    	$this->assertGreaterThan( 0, count( $c ) );
    }

    /**
     * @covers ::set_tags_to_ignore
     */
    public function test_set_tags_to_ignore()
    {
    	$s = $this->settings;
    	$always_ignore = array( 'iframe', 'textarea', 'button', 'select', 'optgroup', 'option', 'map',
    							'style', 'head', 'title', 'script', 'applet', 'object', 'param' );
    	$self_closing_tags = array('area', 'base', 'basefont', 'br', 'frame', 'hr', 'img', 'input', 'link', 'meta');

    	// default tags
		$s->set_tags_to_ignore( array( 'code', 'head', 'kbd', 'object', 'option', 'pre',	'samp',
									   'script', 'noscript', 'noembed', 'select', 'style', 'textarea',
									   'title',	'var', 'math' ) );
		$this->assertArraySubset( array( 'code', 'head', 'kbd', 'object', 'option',	'pre', 'samp',
										 'script', 'noscript', 'noembed', 'select', 'style', 'textarea',
 								 		 'title', 'var', 'math' ), $s['ignoreTags'] );
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}
		foreach ( $self_closing_tags as $tag ) {
			$this->assertNotContains( $tag, $s['ignoreTags'] );
		}

		// auto-close tag and something else
		$s->set_tags_to_ignore( array( 'img', 'foo' ) );
 		$this->assertContains( 'foo', $s['ignoreTags'] );
    	foreach ( $self_closing_tags as $tag ) {
			$this->assertNotContains( $tag, $s['ignoreTags'] );
		}
		foreach ( $always_ignore as $tag ) {
			$this->assertContains( $tag, $s['ignoreTags'] );
		}

		$s->set_tags_to_ignore( "img foo  \    " ); // should not result in an error
    }

    /**
     * @covers ::set_classes_to_ignore
     */
    public function test_set_classes_to_ignore()
    {
		$s = $this->settings;

		$s->set_classes_to_ignore( 'foo bar' );
		$this->assertContains( 'foo', $this->settings['ignoreClasses'] );
		$this->assertContains( 'bar', $this->settings['ignoreClasses'] );
    }

    /**
     * @covers ::set_ids_to_ignore
     */
    public function test_set_ids_to_ignore()
    {
		$s = $this->settings;

		$s->set_ids_to_ignore( 'foobar barfoo' );
		$this->assertContains( 'foobar', $this->settings['ignoreIDs'] );
		$this->assertContains( 'barfoo', $this->settings['ignoreIDs'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_quotes
     */
    public function test_set_smart_quotes()
    {
		$this->settings->set_smart_quotes( true );
		$this->assertTrue( $this->settings['smartQuotes'] );

		$this->settings->set_smart_quotes( false );
		$this->assertFalse( $this->settings['smartQuotes'] );
    }

    /**
     * @covers ::set_smart_quotes_primary
     */
    public function test_set_smart_quotes_primary()
    {
        $s = $this->settings;

    	$quote_styles = array(
    		'doubleCurled',
    		'doubleCurledReversed',
    		'doubleLow9',
    		'doubleLow9Reversed',
    		'singleCurled',
    		'singleCurledReversed',
    		'singleLow9',
    		'singleLow9Reversed',
    		'doubleGuillemetsFrench',
    		'doubleGuillemets',
    		'doubleGuillemetsReversed',
    		'singleGuillemets',
    		'singleGuillemetsReversed',
    		'cornerBrackets',
    		'whiteCornerBracket'
    	);

    	foreach ( $quote_styles as $style ) {
    		$s->set_smart_quotes_primary( $style );
    		$chr = $s->get_named_characters();

    		$this->assertSmartQuotesStyle( $style, $chr['doubleQuoteOpen'], $chr['doubleQuoteClose'] );
    	}
    }

    /**
     * @covers ::set_smart_quotes_primary
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessageRegExp /^Invalid quote style \w+\.$/
     */
    public function test_set_smart_quotes_primary_invalid()
    {
    	$s = $this->settings;

    	$s->set_smart_quotes_primary( 'invalidStyleName' );
    }

    /**
     * @covers ::set_smart_quotes_secondary
     */
    public function test_set_smart_quotes_secondary()
    {
    	$s = $this->settings;
    	$quote_styles = array(
    		'doubleCurled',
    		'doubleCurledReversed',
    		'doubleLow9',
    		'doubleLow9Reversed',
    		'singleCurled',
    		'singleCurledReversed',
    		'singleLow9',
    		'singleLow9Reversed',
    		'doubleGuillemetsFrench',
    		'doubleGuillemets',
    		'doubleGuillemetsReversed',
    		'singleGuillemets',
    		'singleGuillemetsReversed',
    		'cornerBrackets',
    		'whiteCornerBracket'
    	);

    	foreach ( $quote_styles as $style ) {
    		$s->set_smart_quotes_secondary( $style );
    		$chr = $s->get_named_characters();

    		$this->assertSmartQuotesStyle( $style, $chr['singleQuoteOpen'], $chr['singleQuoteClose'] );
    	}
    }

    /**
     * @covers ::set_smart_quotes_secondary
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessageRegExp /^Invalid quote style \w+\.$/
     */
    public function test_set_smart_quotes_secondary_invalid()
    {
    	$s = $this->settings;

    	$s->set_smart_quotes_secondary( 'invalidStyleName' );
    }

    /**
     * @covers ::update_smart_quotes_brackets
     *
     * @uses ::set_smart_quotes_primary
     * @uses ::set_smart_quotes_secondary
     * @uses PHP_Typography\mb_str_split
     */
    public function test_update_smart_quotes_brackets()
    {
    	$s = $this->settings;
    	$quote_styles = array(
    		'doubleCurled',
    		'doubleCurledReversed',
    		'doubleLow9',
    		'doubleLow9Reversed',
    		'singleCurled',
    		'singleCurledReversed',
    		'singleLow9',
    		'singleLow9Reversed',
    		// 'doubleGuillemetsFrench', // test doesn't work for this because it's actually two characters
    		'doubleGuillemets',
    		'doubleGuillemetsReversed',
    		'singleGuillemets',
    		'singleGuillemetsReversed',
    		'cornerBrackets',
    		'whiteCornerBracket'
    	);

    	foreach ( $quote_styles as $primary_style ) {
    		$s->set_smart_quotes_primary( $primary_style );

    		foreach ( $quote_styles as $secondary_style ) {
    			$s->set_smart_quotes_secondary( $secondary_style );

    			$comp = PHPUnit_Framework_Assert::readAttribute( $s, 'components' );

    			$this->assertSmartQuotesStyle( $secondary_style,
    										   \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["['"] )[1],
    										   \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["']"] )[0] );
    			$this->assertSmartQuotesStyle( $secondary_style,
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["('"] )[1],
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["')"] )[0] );
    			$this->assertSmartQuotesStyle( $secondary_style,
     				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["{'"] )[1],
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["'}"] )[0] );
    			$this->assertSmartQuotesStyle( $secondary_style,
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["\"'"] )[1],
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["'\""] )[0] );

    			$this->assertSmartQuotesStyle( $primary_style,
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["[\""] )[1],
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["\"]"] )[0] );
    			$this->assertSmartQuotesStyle( $primary_style,
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["(\""] )[1],
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["\")"] )[0] );
    			$this->assertSmartQuotesStyle( $primary_style,
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["{\""] )[1],
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["\"}"] )[0] );
    			$this->assertSmartQuotesStyle( $primary_style,
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["\"'"] )[0],
    				                           \PHP_Typography\mb_str_split( $comp['smartQuotesBrackets']["'\""] )[1] );
    		}
    	}
    }

    /**
     * Assert that the given quote styles match
     *
     * @param string $style
     * @param string $open
     * @param string $close
     */
	private function assertSmartQuotesStyle( $style, $open, $close ) {
    	switch ( $style ) {
    		case 'doubleCurled':
    			$this->assertSame( \PHP_Typography\uchr(8220), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8221), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'doubleCurledReversed':
    			$this->assertSame( \PHP_Typography\uchr(8221), $open,  "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8221), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'doubleLow9':
    			$this->assertSame( \PHP_Typography\uchr(8222), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8221), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'doubleLow9Reversed':
    			$this->assertSame( \PHP_Typography\uchr(8222), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8220), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'singleCurled':
    			$this->assertSame( \PHP_Typography\uchr(8216), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8217), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'singleCurledReversed':
    			$this->assertSame( \PHP_Typography\uchr(8217), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8217), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'singleLow9':
    			$this->assertSame( \PHP_Typography\uchr(8218), $open,  "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8217), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'singleLow9Reversed':
    			$this->assertSame( \PHP_Typography\uchr(8218), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8216), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'doubleGuillemetsFrench':
    			$this->assertSame( \PHP_Typography\uchr(171) . \PHP_Typography\uchr(160), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(160) . \PHP_Typography\uchr(187), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'doubleGuillemets':
    			$this->assertSame( \PHP_Typography\uchr(171), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(187), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'doubleGuillemetsReversed':
    			$this->assertSame( \PHP_Typography\uchr(187), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(171), $close, "Closeing quote $close did not match quote style $style." );
    			break;

    		case 'singleGuillemets':
    			$this->assertSame( \PHP_Typography\uchr(8249), $open, "Opening quote $open did not match quote style $style." );
    			$this->assertSame( \PHP_Typography\uchr(8250), $close, "Closeing quote $close did not match quote style $style." );
    			break;

   			case 'singleGuillemetsReversed':
   				$this->assertSame( \PHP_Typography\uchr(8250), $open, "Opening quote $open did not match quote style $style." );
   				$this->assertSame( \PHP_Typography\uchr(8249), $close, "Closeing quote $close did not match quote style $style." );
   				break;

   			case 'cornerBrackets':
   				$this->assertSame( \PHP_Typography\uchr(12300), $open, "Opening quote $open did not match quote style $style." );
   				$this->assertSame( \PHP_Typography\uchr(12301), $close, "Closeing quote $close did not match quote style $style." );
   				break;

   			case 'whiteCornerBracket':
   				$this->assertSame( \PHP_Typography\uchr(12302), $open, "Opening quote $open did not match quote style $style." );
   				$this->assertSame( \PHP_Typography\uchr(12303), $close, "Closeing quote $close did not match quote style $style." );
   				break;

    		default:
    			$this->assertTrue( false, "Invalid quote style $style." );
    	}
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_dashes
     */
    public function test_set_smart_dashes()
    {
		$this->settings->set_smart_dashes( true );
		$this->assertTrue( $this->settings['smartDashes'] );

		$this->settings->set_smart_dashes( false );
		$this->assertFalse( $this->settings['smartDashes'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_dashes_style
     */
    public function test_set_smart_dashes_style()
    {
		$s   = $this->settings;

		$s->set_smart_dashes_style( 'traditionalUS' );
		$chr = $s->get_named_characters();
		$this->assertEquals( $chr['emDash'], $chr['parentheticalDash'] );
		$this->assertEquals( $chr['enDash'], $chr['intervalDash'] );
		$this->assertEquals( $chr['thinSpace'], $chr['parentheticalDashSpace'] );
		$this->assertEquals( $chr['thinSpace'], $chr['intervalDashSpace'] );

		$s->set_smart_dashes_style( 'international' );
		$chr = $s->get_named_characters();
		$this->assertEquals( $chr['enDash'], $chr['parentheticalDash'] );
		$this->assertEquals( $chr['enDash'], $chr['intervalDash'] );
		$this->assertEquals( ' ', $chr['parentheticalDashSpace'] );
		$this->assertEquals( $chr['hairSpace'], $chr['intervalDashSpace'] );
    }

    /**
     * @covers ::set_smart_dashes_style
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessageRegExp /^Invalid dash style \w+.$/
     */
    public function test_set_smart_dashes_style_invalid()
    {
    	$s = $this->settings;

    	$s->set_smart_dashes_style( 'invalidStyleName' );
    }

    /**
     * @covers ::set_smart_ellipses
     */
    public function test_set_smart_ellipses()
    {
		$this->settings->set_smart_ellipses( true );
		$this->assertTrue( $this->settings['smartEllipses'] );

		$this->settings->set_smart_ellipses( false );
		$this->assertFalse( $this->settings['smartEllipses'] );
    }

    /**
     * @covers ::set_smart_diacritics
     */
    public function test_set_smart_diacritics()
    {
		$this->settings->set_smart_diacritics( true );
		$this->assertTrue( $this->settings['smartDiacritics'] );

		$this->settings->set_smart_diacritics( false );
		$this->assertFalse( $this->settings['smartDiacritics'] );
    }

    /**
     * @covers ::set_diacritic_language
	 * @covers ::update_diacritics_replacement_arrays
     */
    public function test_set_diacritic_language()
    {
		$this->settings->set_diacritic_language( 'en-US' );
		$this->assertGreaterThan( 0, count( $this->settings['diacriticWords'] ) );

		$this->settings->set_diacritic_language( 'foobar' );
		$this->assertFalse( isset( $this->settings['diacriticWords'] ) );

		$this->settings->set_diacritic_language( 'de-DE' );
		$this->assertTrue( isset( $this->settings['diacriticWords'] ) );
		$this->assertGreaterThan( 0, count( $this->settings['diacriticWords'] ) );

		// nothing changed since the last call
		$this->settings->set_diacritic_language( 'de-DE' );
		$this->assertTrue( isset( $this->settings['diacriticWords'] ) );
		$this->assertGreaterThan( 0, count( $this->settings['diacriticWords'] ) );
    }

    /**
     * @covers ::set_diacritic_custom_replacements
     * @covers ::update_diacritics_replacement_arrays
     */
    public function test_set_diacritic_custom_replacements()
    {
    	$s = $this->settings;

    	$s->set_diacritic_custom_replacements( '"foo" => "fóò", "bar" => "bâr"' . ", 'ha' => 'hä'" );
     	$this->assertArrayHasKey( 'foo', $s['diacriticCustomReplacements'] );
     	$this->assertArrayHasKey( 'bar', $s['diacriticCustomReplacements'] );
     	$this->assertArrayHasKey( 'ha', $s['diacriticCustomReplacements'] );
     	$this->assertContains( 'fóò', $s['diacriticCustomReplacements'] );
     	$this->assertContains( 'bâr', $s['diacriticCustomReplacements'] );
     	$this->assertContains( 'hä', $s['diacriticCustomReplacements'] );

     	$s->set_diacritic_custom_replacements( array( 'fööbar' => 'fúbar' ) );
     	$this->assertArrayNotHasKey( 'foo', $s['diacriticCustomReplacements'] );
     	$this->assertArrayNotHasKey( 'bar', $s['diacriticCustomReplacements'] );
     	$this->assertArrayHasKey( 'fööbar', $s['diacriticCustomReplacements'] );
     	$this->assertContains( 'fúbar', $s['diacriticCustomReplacements'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_marks
     */
    public function test_set_smart_marks()
    {
		$this->settings->set_smart_marks( true );
		$this->assertTrue( $this->settings['smartMarks'] );

		$this->settings->set_smart_marks( false );
		$this->assertFalse( $this->settings['smartMarks'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_math
     */
    public function test_set_smart_math()
    {
		$this->settings->set_smart_math( true );
		$this->assertTrue( $this->settings['smartMath'] );

		$this->settings->set_smart_math( false );
		$this->assertFalse( $this->settings['smartMath'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_exponents
     */
    public function test_set_smart_exponents()
    {
		$this->settings->set_smart_exponents( true );
		$this->assertTrue( $this->settings['smartExponents'] );

		$this->settings->set_smart_exponents( false );
		$this->assertFalse( $this->settings['smartExponents'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_fractions
     */
    public function test_set_smart_fractions()
    {
		$this->settings->set_smart_fractions( true );
		$this->assertTrue( $this->settings['smartFractions'] );

		$this->settings->set_smart_fractions( false );
		$this->assertFalse( $this->settings['smartFractions'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_smart_ordinal_suffix
     */
    public function test_set_smart_ordinal_suffix()
    {
		$this->settings->set_smart_ordinal_suffix( true );
		$this->assertTrue( $this->settings['smartOrdinalSuffix'] );

		$this->settings->set_smart_ordinal_suffix( false );
		$this->assertFalse( $this->settings['smartOrdinalSuffix'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_single_character_word_spacing
     */
    public function test_set_single_character_word_spacing()
    {
		$this->settings->set_single_character_word_spacing( true );
		$this->assertTrue( $this->settings['singleCharacterWordSpacing'] );

		$this->settings->set_single_character_word_spacing( false );
		$this->assertFalse( $this->settings['singleCharacterWordSpacing'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_fraction_spacing
     */
    public function test_set_fraction_spacing()
    {
		$this->settings->set_fraction_spacing( true );
		$this->assertTrue( $this->settings['fractionSpacing'] );

		$this->settings->set_fraction_spacing( false );
		$this->assertFalse( $this->settings['fractionSpacing'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_unit_spacing
     */
    public function test_set_unit_spacing()
    {
		$this->settings->set_unit_spacing( true );
		$this->assertTrue( $this->settings['unitSpacing'] );

		$this->settings->set_unit_spacing( false );
		$this->assertFalse( $this->settings['unitSpacing'] );
    }

        /**
     * @covers ::set_french_punctuation_spacing
     */
    public function test_set_french_punctuation_spacing()
    {
    	$this->settings->set_french_punctuation_spacing( true );
    	$this->assertTrue( $this->settings['frenchPunctuationSpacing'] );

    	$this->settings->set_french_punctuation_spacing( false );
    	$this->assertFalse( $this->settings['frenchPunctuationSpacing'] );
    }

    /**
     * @covers ::set_units
     * @covers ::update_unit_pattern
     */
    public function test_set_units()
    {
    	$units_as_array = array( 'foo', 'bar', 'xx/yy');
    	$units_as_string = implode( ', ', $units_as_array );

		$this->settings->set_units( $units_as_array );
		foreach( $units_as_array as $unit ) {
			$this->assertContains( $unit, $this->settings['units'] );
		}

		$this->settings->set_units( array() );
		foreach( $units_as_array as $unit ) {
			$this->assertNotContains( $unit, $this->settings['units'] );
		}

		$this->settings->set_units( $units_as_string );
		foreach( $units_as_array as $unit ) {
			$this->assertContains( $unit, $this->settings['units'] );
		}
    }

    /**
     * @covers \PHP_Typography\Settings::set_dash_spacing
     */
    public function test_set_dash_spacing()
    {
		$this->settings->set_dash_spacing( true );
		$this->assertTrue( $this->settings['dashSpacing'] );

		$this->settings->set_dash_spacing( false );
		$this->assertFalse( $this->settings['dashSpacing'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_space_collapse
     */
    public function test_set_space_collapse()
    {
		$this->settings->set_space_collapse( true );
		$this->assertTrue( $this->settings['spaceCollapse'] );

		$this->settings->set_space_collapse( false );
		$this->assertFalse( $this->settings['spaceCollapse'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_dewidow
     */
    public function test_set_dewidow()
    {
		$this->settings->set_dewidow( true );
		$this->assertTrue( $this->settings['dewidow'] );

		$this->settings->set_dewidow( false );
		$this->assertFalse( $this->settings['dewidow'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_max_dewidow_length
     */
    public function test_set_max_dewidow_length()
    {
		$this->settings->set_max_dewidow_length( 10 );
		$this->assertSame( 10, $this->settings['dewidowMaxLength'] );

		$this->settings->set_max_dewidow_length( 1 );
		$this->assertSame( 5, $this->settings['dewidowMaxLength'] );

		$this->settings->set_max_dewidow_length( 2 );
		$this->assertSame( 2, $this->settings['dewidowMaxLength'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_max_dewidow_pull
     */
    public function test_set_max_dewidow_pull()
    {
		$this->settings->set_max_dewidow_pull( 10 );
		$this->assertSame( 10, $this->settings['dewidowMaxPull'] );

		$this->settings->set_max_dewidow_pull( 1 );
		$this->assertSame( 5, $this->settings['dewidowMaxPull'] );

		$this->settings->set_max_dewidow_pull( 2 );
		$this->assertSame( 2, $this->settings['dewidowMaxPull'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_wrap_hard_hyphens
     */
    public function test_set_wrap_hard_hyphens()
    {
		$this->settings->set_wrap_hard_hyphens( true );
		$this->assertTrue( $this->settings['hyphenHardWrap'] );

		$this->settings->set_wrap_hard_hyphens( false );
		$this->assertFalse( $this->settings['hyphenHardWrap'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_url_wrap
     */
    public function test_set_url_wrap()
    {
		$this->settings->set_url_wrap( true );
		$this->assertTrue( $this->settings['urlWrap'] );

		$this->settings->set_url_wrap( false );
		$this->assertFalse( $this->settings['urlWrap'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_email_wrap
     */
    public function test_set_email_wrap()
    {
		$this->settings->set_email_wrap( true );
		$this->assertTrue( $this->settings['emailWrap'] );

		$this->settings->set_email_wrap( false );
		$this->assertFalse( $this->settings['emailWrap'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_min_after_url_wrap
     */
    public function test_set_min_after_url_wrap()
    {
		$this->settings->set_min_after_url_wrap( 10 );
		$this->assertSame( 10, $this->settings['urlMinAfterWrap'] );

		$this->settings->set_min_after_url_wrap( 0 );
		$this->assertSame( 5, $this->settings['urlMinAfterWrap'] );

		$this->settings->set_min_after_url_wrap( 1 );
		$this->assertSame( 1, $this->settings['urlMinAfterWrap'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_style_ampersands
     */
    public function test_set_style_ampersands()
    {
		$this->settings->set_style_ampersands( true );
		$this->assertTrue( $this->settings['styleAmpersands'] );

		$this->settings->set_style_ampersands( false );
		$this->assertFalse( $this->settings['styleAmpersands'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_style_caps
     */
    public function test_set_style_caps()
    {
		$this->settings->set_style_caps( true );
		$this->assertTrue( $this->settings['styleCaps'] );

		$this->settings->set_style_caps( false );
		$this->assertFalse( $this->settings['styleCaps'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_style_initial_quotes
     */
    public function test_set_style_initial_quotes()
    {
		$this->settings->set_style_initial_quotes( true );
		$this->assertTrue( $this->settings['styleInitialQuotes'] );

		$this->settings->set_style_initial_quotes( false );
		$this->assertFalse( $this->settings['styleInitialQuotes'] );
    }

    /**
     * @covers ::set_style_numbers
     */
    public function test_set_style_numbers()
    {
		$this->settings->set_style_numbers( true );
		$this->assertTrue( $this->settings['styleNumbers'] );

		$this->settings->set_style_numbers( false );
		$this->assertFalse( $this->settings['styleNumbers'] );
    }

    /**
     * @covers ::set_style_hanging_punctuation
     */
    public function test_set_style_hanging_punctuation()
    {
    	$this->settings->set_style_hanging_punctuation( true );
    	$this->assertTrue( $this->settings['styleHangingPunctuation'] );

    	$this->settings->set_style_hanging_punctuation( false );
    	$this->assertFalse( $this->settings['styleHangingPunctuation'] );
    }

    /**
     * @covers \PHP_Typography\Settings::set_initial_quote_tags
     */
    public function test_set_initial_quote_tags()
    {
       	$tags_as_array = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'div' );
    	$tags_as_string = implode( ', ', $tags_as_array );

		$this->settings->set_initial_quote_tags( $tags_as_array );
		foreach( $tags_as_array as $tag ) {
			$this->assertArrayHasKey( $tag, $this->settings['initialQuoteTags'] );
		}

		$this->settings->set_initial_quote_tags( array() );
		foreach( $tags_as_array as $tag ) {
			$this->assertArrayNotHasKey( $tag, $this->settings['initialQuoteTags'] );
		}

		$this->settings->set_initial_quote_tags( $tags_as_string );
		foreach( $tags_as_array as $tag ) {
			$this->assertArrayHasKey( $tag, $this->settings['initialQuoteTags'] );
		}
    }

    /**
     * @covers \PHP_Typography\Settings::set_hyphenation
     */
    public function test_set_hyphenation()
    {
		$this->settings->set_hyphenation( true );
		$this->assertTrue( $this->settings['hyphenation'] );

		$this->settings->set_hyphenation( false );
		$this->assertFalse( $this->settings['hyphenation'] );
    }

    public function provide_hyphenation_language_data() {
    	return array(
    		array( 'en-US',  true ),
    		array( 'foobar', false ),
    		array( 'no',     true ),
    		array( 'de',     true ),
    	);
    }

    /**
     * @covers ::set_hyphenation_language
     *
     * @uses PHP_Typography\Hyphenator::__construct
     * @uses PHP_Typography\Hyphenator::set_language
     *
     * @dataProvider provide_hyphenation_language_data
     */
    public function test_set_hyphenation_language( $lang, $success )
    {
    	$s = $this->settings;
    	$s['hyphenationExceptions'] = array(); // necessary for full coverage

		$s->set_hyphenation_language( $lang );

    	// if the hyphenator object has not instantiated yet, hyphenLanguage will be set nonetheless
    	if ( $success || ! isset( $s->hyphenator ) ) {
			$this->assertSame( $lang, $s['hyphenLanguage'] );
		} else {
			$this->assertFalse( isset( $s['hyphenLanguage'] ) );
		}
    }

    /**
     * @covers ::set_hyphenation_language
     *
     * @uses PHP_Typography\Hyphenator::__construct
     * @uses PHP_Typography\Hyphenator::set_language
     * @uses PHP_Typography\Hyphenator::build_trie
     *
     * @dataProvider provide_hyphenation_language_data
     */
    public function test_set_hyphenation_language_again( $lang, $success )
    {
    	$s = $this->settings;
    	$s['hyphenationExceptions'] = array(); // necessary for full coverage

    	for ( $i = 0; $i < 2; ++$i ) {
	    	$s->set_hyphenation_language( $lang );

	    	// if the hyphenator object has not instantiated yet, hyphenLanguage will be set nonetheless
	    	if ( $success ) {
	    		$this->assertSame( $lang, $s['hyphenLanguage'], "Round $i, success" );
	    	} elseif ( ! isset( $s->hyphenator ) ) {
	    		$this->assertSame( $lang, $s['hyphenLanguage'], "Round $i, no hyphenator" );
	    		// Clear hyphenation language if there was no hypehnator object.
	    		unset( $s['hyphenLanguage'] );
 	    	} else {
 	    		$this->assertFalse( isset( $s['hyphenLanguage'] ), "Round $i, unsuccessful" );
 	    	}
    	}
    }


    /**
     * @covers ::set_min_length_hyphenation
     *
     * @uses PHP_Typography\Hyphenator::__construct
     */
    public function test_set_min_length_hyphenation()
    {
		$this->settings->set_min_length_hyphenation( 1 ); // too low, resets to default 5
		$this->assertSame( 5, $this->settings['hyphenMinLength'] );

		$this->settings->set_min_length_hyphenation( 2 );
		$this->assertSame( 2, $this->settings['hyphenMinLength'] );

		$this->settings->set_min_length_hyphenation( 66 );
		$this->assertSame( 66, $this->settings['hyphenMinLength'] );
    }

    /**
     * @covers ::set_min_before_hyphenation
     */
    public function test_set_min_before_hyphenation()
    {
		$this->settings->set_min_before_hyphenation( 0 ); // too low, resets to default 3
		$this->assertSame( 3, $this->settings['hyphenMinBefore'] );

		$this->settings->set_min_before_hyphenation( 1 );
		$this->assertSame( 1, $this->settings['hyphenMinBefore'] );

		$this->settings->set_min_before_hyphenation( 66 );
		$this->assertSame( 66, $this->settings['hyphenMinBefore'] );

    }

    /**
     * @covers ::set_min_after_hyphenation
     */
    public function test_set_min_after_hyphenation()
    {
		$this->settings->set_min_after_hyphenation( 0 ); // too low, resets to default 2
		$this->assertSame( 2, $this->settings['hyphenMinAfter'] );

		$this->settings->set_min_after_hyphenation( 1 );
		$this->assertSame( 1, $this->settings['hyphenMinAfter'] );

		$this->settings->set_min_after_hyphenation( 66 );
		$this->assertSame( 66, $this->settings['hyphenMinAfter'] );
    }

    /**
     * @covers ::set_hyphenate_headings
     */
    public function test_set_hyphenate_headings()
    {
    	$this->settings->set_hyphenate_headings( true );
		$this->assertTrue( $this->settings['hyphenateTitle'] );

		$this->settings->set_hyphenate_headings( false );
		$this->assertFalse( $this->settings['hyphenateTitle'] );
    }

    /**
     * @covers ::set_hyphenate_all_caps
     */
    public function test_set_hyphenate_all_caps()
    {
    	$this->settings->set_hyphenate_all_caps( true );
		$this->assertTrue( $this->settings['hyphenateAllCaps'] );

		$this->settings->set_hyphenate_all_caps( false );
		$this->assertFalse( $this->settings['hyphenateAllCaps'] );
    }

    /**
     * @covers ::set_hyphenate_title_case
     */
    public function test_set_hyphenate_title_case()
    {
    	$this->settings->set_hyphenate_title_case( true );
		$this->assertTrue( $this->settings['hyphenateTitleCase'] );

		$this->settings->set_hyphenate_title_case( false );
		$this->assertFalse( $this->settings['hyphenateTitleCase'] );
    }

    /**
     * @covers ::set_hyphenate_compounds
     */
    public function test_set_hyphenate_compounds()
    {
    	$this->settings->set_hyphenate_compounds( true );
    	$this->assertTrue( $this->settings['hyphenateCompounds'] );

    	$this->settings->set_hyphenate_compounds( false );
    	$this->assertFalse( $this->settings['hyphenateCompounds'] );
    }

    /**
     * @covers ::set_hyphenation_exceptions
     *
     * @uses PHP_Typography\Hyphenator::__construct
     * @uses PHP_Typography\Hyphenator::set_custom_exceptions
     */
    public function test_set_hyphenation_exceptions_array()
    {
 		$s = $this->settings;

 		$exceptions = array( "Hu-go", "Fö-ba-ß" );
 		$s->set_hyphenation_exceptions( $exceptions );
 		$this->assertContainsOnly( 'string', $s['hyphenationCustomExceptions'] );
 		$this->assertCount( 2, $s['hyphenationCustomExceptions'] );

 		$exceptions = array( "bar-foo" );
 		$s->set_hyphenation_exceptions( $exceptions );
 		$this->assertContainsOnly( 'string', $s['hyphenationCustomExceptions'] );
 		$this->assertCount( 1, $s['hyphenationCustomExceptions'] );
    }

    /**
     * @covers ::set_hyphenation_exceptions
     *
     * @uses PHP_Typography\Hyphenator::__construct
     * @uses PHP_Typography\Hyphenator::set_custom_exceptions
     */
    public function test_set_hyphenation_exceptions_string()
    {
    	$s = $this->settings;
    	$exceptions = "Hu-go, Fö-ba-ß";

    	$s->set_hyphenation_exceptions( $exceptions );
    	$this->assertContainsOnly( 'string', $s['hyphenationCustomExceptions'] );
    	$this->assertCount( 2, $s['hyphenationCustomExceptions'] );
    }

    /**
     * @covers \PHP_Typography\Settings::get_hash
     */
    public function test_get_hash()
    {
    	$s = $this->settings;

    	$s->set_smart_quotes(true);
    	$hash1 = $s->get_hash(10);
    	$this->assertEquals( 10, strlen( $hash1 ) );

  		$s->set_smart_quotes(false);
  		$hash2 = $s->get_hash(10);
  		$this->assertEquals( 10, strlen( $hash2 ) );

  		$this->assertNotEquals( $hash1, $hash2 );
    }

    /**
     * @covers ::set_true_no_break_narrow_space
     */
    public function test_set_true_no_break_narrow_space() {
    	$s   = $this->settings;

    	$s->set_true_no_break_narrow_space(); // defaults to false
		$chr = $s->get_named_characters();
    	$this->assertSame( $chr['noBreakNarrowSpace'], \PHP_Typography\uchr( 160 ) );
     	$this->assertAttributeContains( array( 'open'  => \PHP_Typography\uchr(171) . \PHP_Typography\uchr( 160 ),
     									       'close' => \PHP_Typography\uchr( 160 ) . \PHP_Typography\uchr(187) ), 'quote_styles', $s );

    	$s->set_true_no_break_narrow_space( true ); // defaults to false
		$chr = $s->get_named_characters();
    	$this->assertSame( $chr['noBreakNarrowSpace'], \PHP_Typography\uchr( 8239 ) );
    	$this->assertAttributeContains( array( 'open'  => \PHP_Typography\uchr(171) . \PHP_Typography\uchr( 8239 ),
    		                                   'close' => \PHP_Typography\uchr( 8239 ) . \PHP_Typography\uchr(187) ), 'quote_styles', $s );
    }


    /**
     * @covers ::get_top_level_domains_from_file
     */
    public function test_get_top_level_domains_from_file()
    {
    	$default = 'ac|ad|aero|ae|af|ag|ai|al|am|an|ao|aq|arpa|ar|asia|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|biz|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|cat|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|com|coop|co|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|info|int|in|io|iq|ir|is|it|je|jm|jobs|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mobi|mo|mp|mq|mr|ms|mt|museum|mu|mv|mw|mx|my|mz|name|na|nc|net|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pro|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|travel|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw';
    	$invalid_result = $this->settings->get_top_level_domains_from_file( '/some/invalid/path/to_a_non_existent_file.txt' );
    	$valid_result = $this->settings->get_top_level_domains_from_file( dirname( __DIR__ ) . '/vendor/IANA/tlds-alpha-by-domain.txt' );

    	$this->assertSame( $default, $invalid_result );
    	$this->assertNotSame( $valid_result, $invalid_result );
    	$this->assertNotEmpty( $valid_result );
    }

}
