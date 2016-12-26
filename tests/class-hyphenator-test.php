<?php

/**
 * Test Hyphenator class.
 *
 * @coversDefaultClass \PHP_Typography\Hyphenator
 * @usesDefaultClass \PHP_Typography\Hyphenator
 *
 * @uses PHP_Typography\Hyphenator
 */
class Hyphenator_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Hyphenator
     */
    protected $h;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->h = new \PHP_Typography\Hyphenator();
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
     * Helper function to generate a valid token list from strings.
     *
     * @param string $value
     * @param string $type Optional. Default 'word'.
     *
     * @return array
     */
    protected function tokenize_sentence( $value ) {
    	$words = explode( ' ', $value );
    	$tokens = array();

    	foreach ( $words as $word ) {
    		$tokens[] = array(
    			'type'  => 'word',
    			'value' => $word,
    		);
    	}

    	return $tokens;
    }

    /**
     * Reports an error identified by $message if the combined token values differ from the expected value.
     *
     * @param string $expected_value
     * @param array $actual_tokens
     * @param string $message
     */
    protected function assertTokensSame( $expected_value, $actual_tokens, $message = '' ) {
    	foreach ( $actual_tokens as $index => $token ) {
    		$actual_tokens[ $index ]['value'] = clean_html( $actual_tokens[ $index ]['value'] );
    	}

		if ( false !== strpos( $expected_value, ' ' ) ) {
			$expected = $this->tokenize_sentence( $expected_value );
		} else {
			$expected = $this->tokenize( $expected_value );
		}

		$this->assertSame( count( $expected ), count( $actual_tokens ) );

		foreach ( $actual_tokens as $key => $token ) {
			$this->assertSame( $expected[ $key ]['value'], $token['value'], $message );
		}

		return true;
    }

    /**
     * Reports an error identified by $message if $attribute in $object does not have the $key.
     *
     * @param string $key
     * @param string $attribute
     * @param object $object
     * @param string $message
     */
    protected function assertAttributeArrayHasKey( $key, $attribute, $object, $message = '' ) {
    	$ref = new ReflectionClass( get_class( $object ) );
    	$prop = $ref->getProperty( $attribute );
    	$prop->setAccessible( true );

    	return $this->assertArrayHasKey( $key, $prop->getValue( $object ), $message );
    }

    /**
     * Reports an error identified by $message if $attribute in $object does have the $key.
     *
     * @param string $key
     * @param string $attribute
     * @param object $object
     * @param string $message
     */
    protected function assertAttributeArrayNotHasKey( $key, $attribute, $object, $message = '' ) {
    	$ref = new ReflectionClass( get_class( $object ) );
    	$prop = $ref->getProperty( $attribute );
    	$prop->setAccessible( true );

    	return $this->assertArrayNotHasKey( $key, $prop->getValue( $object ), $message );
    }


    /**
     * @covers ::__construct
     *
     * @uses PHP_Typography\mb_str_split
     */
    public function test_constructor() {
    	$h = $this->h;

    	$this->assertNotNull( $h );
    	$this->assertInstanceOf( '\PHP_Typography\Hyphenator', $h );

    	$h2 = new \PHP_Typography\Hyphenator( 'en-US', array( 'foo-bar' ) );
    	$this->assertNotNull( $h2 );
    	$this->assertInstanceOf( '\PHP_Typography\Hyphenator', $h2 );
    	$this->assertAttributeSame( 'en-US', 'language', $h2 );
    	$this->assertAttributeCount( 1, 'custom_exceptions', $h2 );
    }

    /**
     * @covers ::set_language
	 * @covers ::build_trie
	 *
     * @uses PHP_Typography\mb_str_split
     */
    public function test_set_language()
    {
    	$h = $this->h;
		$h->set_language( 'en-US' );
		$this->assertAttributeNotEmpty( 'pattern_trie', $h, 'Empty English-US pattern array' );
		$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

 		$h->set_language( 'foobar' );
 		$this->assertFalse( isset( $h->pattern ) );
 		$this->assertFalse( isset( $h->pattern_exceptions ) );

 		$h->set_language( 'no' );
		$this->assertAttributeNotEmpty( 'pattern_trie', $h, 'Empty Norwegian pattern array' );
 		$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' ); // Norwegian has exceptions

 		$h->set_language( 'de' );
		$this->assertAttributeNotEmpty( 'pattern_trie', $h, 'Empty German pattern array' );
 		$this->assertAttributeEmpty( 'pattern_exceptions', $h, 'Unexpected pattern exceptions found' ); // no exceptions in the German pattern file
    }

    /**
     * @covers ::set_language
     *
     * @uses ::set_custom_exceptions
     * @uses ::merge_hyphenation_exceptions
     * @uses PHP_Typography\mb_str_split
     */
    public function test_set_language_with_custom_exceptions()
    {
    	$h = $this->h;

    	$h->set_custom_exceptions( array( 'KINGdesk' => 'KING-desk' ) );
    	$h->set_language( 'en-US' );
    	$h->merge_hyphenation_exceptions();
    	$this->assertAttributeNotEmpty( 'pattern_trie', $h, 'Empty pattern array' );
    	$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

    	$h->set_language( 'de' );
    	$this->assertAttributeNotEmpty( 'pattern_trie', $h, 'Empty pattern array' );
    	$this->assertAttributeEmpty( 'pattern_exceptions', $h, 'Unexpected pattern exceptions found' ); // no exceptions in the German pattern file
    }

    /**
     * @covers ::set_language
     *
     * @uses PHP_Typography\mb_str_split
     */
    public function test_set_same_hyphenation_language()
    {
    	$h = $this->h;

    	$h->set_language( 'en-US' );
    	$this->assertAttributeNotEmpty( 'pattern_trie', $h, 'Empty pattern array' );
    	$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );

    	$h->set_language( 'en-US' );
    	$this->assertAttributeNotEmpty( 'pattern_trie', $h, 'Empty pattern array' );
    	$this->assertAttributeNotEmpty( 'pattern_exceptions', $h, 'Empty pattern exceptions array' );
    }


    /**
     * @covers ::set_custom_exceptions
     */
    public function test_set_custom_exceptions()
    {
 		$h = $this->h;
 		$exceptions = array( "Hu-go", "Fö-ba-ß" );
 		$h->set_custom_exceptions( $exceptions );

 		$this->assertAttributeContainsOnly( 'string', 'custom_exceptions', $h );
 		$this->assertAttributeContains( 'hu-go', 'custom_exceptions', $h );
 		$this->assertAttributeContains( 'fö-ba-ß', 'custom_exceptions', $h );
 		$this->assertAttributeCount( 2, 'custom_exceptions', $h );
    }

    /**
     * @covers ::set_custom_exceptions
     *
     * @uses ::merge_hyphenation_exceptions
     * @uses PHP_Typography\mb_str_split
     */
    public function test_set_custom_exceptions_again()
    {
    	$h = $this->h;
    	$exceptions = array( "Hu-go", "Fö-ba-ß" );
    	$h->set_custom_exceptions( $exceptions );
    	$h->set_language( 'de' ); // German has no pattern exceptions.
    	$h->merge_hyphenation_exceptions();
		$this->assertAttributeNotEmpty( 'merged_exception_patterns', $h );

    	$exceptions = array( "Hu-go" );
    	$h->set_custom_exceptions( $exceptions );
		$this->assertAttributeEmpty( 'merged_exception_patterns', $h );

    	$this->assertAttributeContainsOnly( 'string', 'custom_exceptions', $h );
    	$this->assertAttributeContains( 'hu-go', 'custom_exceptions', $h );
    	$this->assertAttributeCount( 1, 'custom_exceptions', $h );
    }

    /**
     * @covers ::set_custom_exceptions
     */
    public function test_set_custom_exceptions_unknown_encoding()
    {
    	$h = $this->h;
    	$exceptions = array( "Hu-go", mb_convert_encoding( "Fö-ba-ß", 'ISO-8859-2' ) );
    	$h->set_custom_exceptions( $exceptions );

    	$this->assertAttributeContainsOnly( 'string', 'custom_exceptions', $h );
 		$this->assertAttributeContains( 'hu-go', 'custom_exceptions', $h );
 		$this->assertAttributeNotContains( 'fö-ba-ß', 'custom_exceptions', $h );
    	$this->assertAttributeCount( 1, 'custom_exceptions', $h );
    }

    public function provide_hyphenate_data() {
    	return array(
    		array( 'A few words to hyphenate like KINGdesk Really there should be more hyphenation here', 'A few words to hy|phen|ate like KING|desk Re|al|ly there should be more hy|phen|ation here', 'en-US', true ), // fake tokenizer doesn't split off punctuation
    		array( 'Sauerstofffeldflasche', 'Sau|er|stoff|feld|fla|sche', 'de', true ),
    		array( 'Sauerstoff Feldflasche', 'Sau|er|stoff Feld|fla|sche', 'de', true ), // Compound words would not be hyphenated separately
    		array( 'Sauerstoff-Feldflasche', 'Sauerstoff-Feldflasche', 'de', false ),
    		array( 'A', 'A', 'de', true ),
    		array( 'table', 'ta|ble', 'en-US', false ),
    		array( 'KINGdesk', 'KINGdesk', 'en-US', false ),
    	);
    }

    /**
     * @covers ::hyphenate
     *
     * @uses PHP_Typography\is_odd
     * @uses PHP_Typography\mb_str_split
     *
     * @dataProvider provide_hyphenate_data
     */
    public function test_hyphenate( $html, $result, $lang, $hyphenate_title_case )
    {
    	$h = $this->h;
    	$h->set_language( $lang );
    	$h->set_custom_exceptions( array( 'KING-desk' ) );

    	$this->assertTokensSame( $result, $h->hyphenate( $this->tokenize_sentence( $html ), '|', $hyphenate_title_case, 2, 2, 2 ) );
    }

    public function provide_hyphenate_with_exceptions_data() {
    	return array(
    			array( 'KINGdesk', 'KING|desk', array('KING-desk'), 'en-US', true ),
    			array( 'Geschäftsübernahme', 'Ge|sch&auml;fts|&uuml;ber|nah|me', array(), 'de', true ),
    			array( 'Geschäftsübernahme', 'Ge|sch&auml;fts|&uuml;ber|nah|me', array( 'Ge-schäfts-über-nah-me' ), 'de', true ),
    			array( 'Trinkwasserinstallation', 'Trink|was|ser|in|stal|la|ti|on', array(), 'de', true, true, true, false ),
    			array( 'Trinkwasserinstallation', 'Trink|wasser|in|stal|la|tion', array( 'Trink-wasser-in-stal-la-tion' ), 'de', true ),
    			array( 'Trinkwasserinstallation', 'Trink|wasser|in|stal|la|tion', array( 'Trink-wasser-in-stal-la-tion' ), 'en-US', true ),
    	);
    }

    /**
     * @covers ::hyphenate
     *
     * @uses PHP_Typography\is_odd
     * @uses PHP_Typography\mb_str_split
     *
     * @dataProvider provide_hyphenate_with_exceptions_data
     */
    public function test_hyphenate_with_exceptions( $html, $result, $exceptions, $lang, $hyphenate_title_case )
    {
    	$h = $this->h;
    	$h->set_language( $lang );
    	$h->set_custom_exceptions( $exceptions );

    	$this->assertTokensSame( $result, $h->hyphenate( $this->tokenize_sentence( $html ), '|', $hyphenate_title_case, 2, 2, 2 ) );
    }

    /**
     * @covers ::hyphenate
     *
     * @uses \PHP_Typography\is_odd
     * @uses \PHP_Typography\mb_str_split
     * @uses \mb_convert_encoding
     */
    public function test_hyphenate_wrong_encoding()
    {
    	$this->h->set_language( 'de' );

    	$tokens = $this->tokenize( mb_convert_encoding( 'Änderungsmeldung', 'ISO-8859-2' ) );
    	$hyphenated  = $this->h->hyphenate( $tokens, '|', true, 2, 2, 2 );
	   	$this->assertSame( $hyphenated, $tokens, 'Wrong encoding, value should be unchanged' );

	   	$tokens = $this->tokenize( 'Änderungsmeldung' );
	   	$hyphenated  = $this->h->hyphenate( $tokens, '|', true, 2, 2, 2 );
	   	$this->assertNotSame( $hyphenated, $tokens, 'Correct encoding, string should have been hyphenated' );
    }

    /**
     * @covers ::hyphenate
     *
     * @uses PHP_Typography\mb_str_split
     */
    public function test_hyphenate_no_title_case()
    {
    	$this->h->set_language( 'de' );

    	$tokens = $this->tokenize( 'Änderungsmeldung' );
    	$hyphenated  = $this->h->hyphenate( $tokens, '|', false, 2, 2, 2 );
    	$this->assertEquals( $tokens, $hyphenated);
    }

    /**
     * @covers ::hyphenate
     *
     * @uses PHP_Typography\mb_str_split
     */
    public function test_hyphenate_invalid()
    {
    	$this->h->set_language( 'de' );

    	$tokens = $this->tokenize( 'Änderungsmeldung' );
    	$hyphenated  = $this->h->hyphenate( $tokens,  '|', true, 2, 0, 2 );
    	$this->assertEquals( $tokens, $hyphenated);
    }

    /**
     * @covers ::hyphenate
     *
     * @uses PHP_Typography\is_odd
     * @uses PHP_Typography\mb_str_split
     */
    public function test_hyphenate_no_custom_exceptions()
    {
    	$this->h->set_language( 'en-US' );

    	// Again, no punctuation due to the fake tokenization.
    	$this->assertTokensSame(
    		'A few words to hy|phen|ate like KINGdesk Re|al|ly there should be more hy|phen|ation here',
    		$this->h->hyphenate( $this->tokenize_sentence( 'A few words to hyphenate like KINGdesk Really there should be more hyphenation here' ), '|', true, 2, 2, 2 )
    	);
    }

    /**
     * @covers ::hyphenate
     *
     * @uses ReflectionClass
     * @uses ReflectionProperty
     * @uses PHP_Typography\is_odd
     * @uses PHP_Typography\mb_str_split
     */
    public function test_hyphenate_no_exceptions_at_all()
    {
    	$this->h->set_language( 'en-US' );

    	// Unset some internal stuff.
    	$ref = new ReflectionClass( '\PHP_Typography\Hyphenator' );
    	$prop = $ref->getProperty( 'pattern_exceptions' );
    	$prop->setAccessible( true );
    	$prop->setValue( $this->h, array() );
    	$prop = $ref->getProperty( 'merged_exception_patterns' );
    	$prop->setAccessible( true );
    	$prop->setValue( $this->h, null );

    	// Again, no punctuation due to the fake tokenization.
    	$this->assertTokensSame(
    		'A few words to hy|phen|ate like KINGdesk Re|al|ly there should be more hy|phen|ation here',
    		$this->h->hyphenate( $this->tokenize_sentence( 'A few words to hyphenate like KINGdesk Really there should be more hyphenation here' ), '|', true, 2, 2, 2 )
    	);
    }

    /**
     * @covers ::convert_hyphenation_exception_to_pattern
     */
    public function test_convert_hyphenation_exception_to_pattern() {
    	$h = $this->h;
    	$this->assertSame( array( 4 => 9 ), $h->convert_hyphenation_exception_to_pattern( 'KING-desk' ) );
    	$this->assertSame( array( 2 => 9 ), $h->convert_hyphenation_exception_to_pattern( 'ta-ble' ) );
    }

    /**
     * @covers ::convert_hyphenation_exception_to_pattern
     *
     * @uses \mb_convert_encoding
     */
    public function test_convert_hyphenation_exception_to_pattern_unknown_encoding() {
    	$h = $this->h;
        $exception = mb_convert_encoding( "Fö-ba-ß" , 'ISO-8859-2' );

    	$this->assertNull( $h->convert_hyphenation_exception_to_pattern( $exception ) );
    }

    /**
     * @covers ::merge_hyphenation_exceptions
     *
     * @uses PHP_Typography\mb_str_split
     */
    public function test_merge_hyphenation_exceptions() {
    	$h = $this->h;
    	$h->set_custom_exceptions( array( 'Hu-go', 'Fä-vi-ken' ) );

    	$h->set_language( 'en-US' ); // w/ pattern exceptions.
    	$h->merge_hyphenation_exceptions();
    	$this->assertAttributeNotCount( 0, 'merged_exception_patterns', $h );
    	$this->assertAttributeNotCount( 1, 'merged_exception_patterns', $h );
    	$this->assertAttributeNotCount( 2, 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayHasKey( 'hugo', 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayHasKey( 'fäviken', 'merged_exception_patterns', $h );

    	$h->set_language( 'de' ); // w/o pattern exceptions.
    	$h->merge_hyphenation_exceptions();
    	$this->assertAttributeCount( 2, 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayHasKey( 'hugo', 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayHasKey( 'fäviken', 'merged_exception_patterns', $h );

    	$h->set_language( 'en-US' ); // w/ pattern exceptions.
    	$h->set_custom_exceptions( array() );
    	$h->merge_hyphenation_exceptions();
    	$this->assertAttributeNotCount( 0, 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayNotHasKey( 'hugo', 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayNotHasKey( 'fäviken', 'merged_exception_patterns', $h );

    	$h->set_language( 'de' ); // w/o pattern exceptions.
    	$h->merge_hyphenation_exceptions();
    	$this->assertAttributeCount( 0, 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayNotHasKey( 'hugo', 'merged_exception_patterns', $h );
    	$this->assertAttributeArrayNotHasKey( 'fäviken', 'merged_exception_patterns', $h );
    }
}
