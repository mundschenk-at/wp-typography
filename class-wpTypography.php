<?php

// TO DO
// internationalize
// test for compatiblity

class wpTypography {
	var $pluginName = "wp-Typography";
	var $installRequirements = array(
			"PHP Version" 		=> "5.0.0",
			"WordPress Version"	=> "2.7",
			"Multibyte" 		=> TRUE,
			"UTF-8"				=> TRUE,
		);
	var $localPluginPath = "wp-typography/wp-typography.php"; // relative from plugin folder
	var $pluginPath = ""; // we will assign WP_PLUGIN_DIR base in __construct
	var $remoteFileURL = 'http://a.kingdesk.com/wp-typography.php';
	var $option_group = "typo_options"; //used to register options for option page
	var $settings;
	var $phpTypo; // this will be a class within a class
	var $adminResourceLinks = array(
			/*
			"anchor text"			=> string URL,		// REQUIRED
			*/
			"Plugin Home"	 		=> "http://kingdesk.com/projects/wp-typography/",
			"FAQs"			 		=> "http://kingdesk.com/projects/wp-typography-faqs/",
			"Change Log"			=> "http://kingdesk.com/projects/wp-typography-change-log/",
			"License"				=> "http://kingdesk.com/projects/wp-typography-license/",
			"PHP Typography (sister project)"	=> "http://kingdesk.com/projects/php-typography/",
		);
	var $adminFormSections = array( // sections will be displayed in the order included
			/*
			"id" 					=> string heading,		// REQUIRED
			*/
			"general-scope" 		=> "General Scope",
			"hyphenation" 			=> "Hyphenation", 
			"character-replacement"	=> "Intelligent Character Replacement", 
			"space-control" 		=> "Space Control", 
			"css-hooks" 			=> "Add CSS Hooks", 
		);
	var $adminFormSectionFieldsets = array( // fieldsets will be displayed in the order included
			/*
			"id" => array(
				"heading" 	=> string Fieldset Name,	// REQUIRED
				"sectionID" 	=> string Parent Section ID,	// REQUIRED
			),
			*/
			"smart-quotes" => array(
				"heading" 	=> "Quotemarks", 
				"sectionID" 	=> "character-replacement",
			),
			"diacritics" => array(
				"heading" 	=> "Diacritics", 
				"sectionID" 	=> "diacritics",
			),
			"values-and-units" => array(
				"heading" 	=> "Values &amp; Units", 
				"sectionID" 	=> "space-control",
			),
			"enable-wrapping" => array(
				"heading" 	=> "Enable Wrapping", 
				"sectionID" 	=> "space-control",
			),
			"widows" => array(
				"heading" 	=> "Widows", 
				"sectionID" 	=> "space-control",
			),
		);
	var $adminFormControls = array(
			/*
			"id" => array(
				"section" 		=> string Section ID, 		// REQUIRED
				"fieldset" 		=> string Fieldset ID,		// OPTIONAL
				"labelBefore" 		=> string Label Content,	// OPTIONAL
				"labelAfter"	=> string Label Content,	// OPTIONAL, only for controls of type "select", where the control is in the middle of a label
				"helpText" 		=> string Help Text,		// OPTIONAL
				"control" 		=> string Control,			// REQUIRED
				"inputType" 	=> string Control Type,		// OPTIONAL
				"optionValues"	=> array(value=>text, ... )	// OPTIONAL, only for controls of type "select"
				"default" 		=> string Default Value,	// REQUIRED (although it may be an empty string)
			),
			*/
			"typoIgnoreTags" => array(
				"section"		=> "general-scope",
				"labelBefore" 	=> "Do not process the content of these <strong>HTML elements</strong>:",
				"helpText" 		=> "Separate tag names with spaces; do not include the <samp>&lt;</samp> or <samp>&gt;</samp>.",
				"control" 		=> "textarea",
				"default" 		=> "code head kbd object option pre samp script style textarea title var math",
			),
			"typoIgnoreClasses" => array(
				"section" 		=> "general-scope",
				"labelBefore" 	=> "Do not process elements of <strong>class</strong>:",
				"helpText" 		=> "Separate class names with spaces.",
				"control" 		=> "textarea",
				"default" 		=> "vcard noTypo",
			),
			"typoIgnoreIDs" => array(
				"section" 		=> "general-scope",
				"labelBefore" 	=> "Do not process elements of <strong>ID</strong>:",
				"helpText" 		=> "Separate ID names with spaces.",
				"control" 		=> "textarea",
				"default" 		=> "",
			),
			"typoEnableHyphenation" => array(
				"section" 		=> "hyphenation",
				"labelAfter" 	=> "Enable hyphenation.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoHyphenateLanguages" => array(
				"section"		=> "hyphenation",
				"labelBefore" 	=> "Language for hyphenation rules:",
				"control" 		=> "select",
				"optionValues"	=> array(), // automatically detected and listed in __construct
				"default" 		=> "en-US",
			),
			"typoHyphenateHeadings" => array(
				"section" 		=> "hyphenation",
				"labelAfter" 	=> "Hyphenate headings.",
				"helpText" 		=> "Unchecking will disallow hyphenation of headings, even if allowed in the general scope.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoHyphenateTitleCase" => array(
				"section" 		=> "hyphenation",
				"labelAfter" 	=> "Allow hyphenation of words that begin with a capital letter.",
				"helpText" 		=> "Uncheck to avoid hyphenation of proper nouns.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoHyphenateCaps" => array(
				"section" 		=> "hyphenation",
				"labelAfter" 	=> "Hyphenate words in ALL CAPS.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoHyphenateMinLength" => array(
				"section"		=> "hyphenation",
				"labelBefore" 	=> "Do not hyphenate words with less than",
				"labelAfter"	=> "letters.",
				"control" 		=> "select",
				"optionValues"	=> array(4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10),
				"default" 		=> 5,
			),
			"typoHyphenateMinBefore" => array(
				"section"		=> "hyphenation",
				"labelBefore" 	=> "Keep at least",
				"labelAfter"	=> "letters before hyphenation.",
				"control" 		=> "select",
				"optionValues"	=> array(2=>2,3=>3,4=>4,5=>5),
				"default" 		=> 3,
			),
			"typoHyphenateMinAfter" => array(
				"section"		=> "hyphenation",
				"labelBefore" 	=> "Keep at least",
				"labelAfter"	=> "letters after hyphenation.",
				"control" 		=> "select",
				"optionValues"	=> array(2=>2,3=>3,4=>4,5=>5),
				"default" 		=> 2,
			),
			"typoHyphenateExceptions" => array(
				"section" 		=> "hyphenation",
				"labelBefore" 	=> "Exception List:",
				"helpText" 		=> "Mark allowed hyphenations with \"-\"; separate words with spaces.",
				"control" 		=> "textarea",
				"default" 		=> "KING-desk",
			),
			"typoSmartCharacters" => array(
				"section"		=> "character-replacement",
				"labelAfter" 	=> "Override WordPress' automatic character handling with your preferences here.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoSmartQuotes" => array(
				"section"		=> "character-replacement",
				"fieldset" 		=> "smart-quotes",
				"labelAfter" 	=> "Transform straight quotes [ <samp>'</samp> <samp>\"</samp> ] to typographically correct characters as detailed below.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),

			"typoSmartQuotesPrimary" => array(
				"section"		=> "character-replacement",
				"fieldset" 		=> "smart-quotes",
				"labelBefore" 	=> "Convert <samp>\"foo\"</samp> to:",
				"helpText" 		=> "Primary quotation style.",
				"control" 		=> "select",
				"optionValues"	=> array(
											"doubleCurled" => "&ldquo;foo&rdquo;",
											"doubleCurledReversed" => "&rdquo;foo&rdquo;",
											"doubleLow9" => "&bdquo;foo&rdquo;",
											"doubleLow9Reversed" => "&bdquo;foo&ldquo;",
											"singleCurled" => "&lsquo;foo&rsquo;",
											"singleCurledReversed" => "&rsquo;foo&rsquo;",
											"singleLow9" => "&sbquo;foo&rsquo;",
											"singleLow9Reversed" => "&sbquo;foo&lsquo;",
											"doubleGuillemetsFrench" => "&laquo;&nbsp;foo&nbsp;&raquo;",
											"doubleGuillemets" => "&laquo;foo&raquo;",
											"doubleGuillemetsReversed" => "&raquo;foo&laquo;",
											"singleGuillemets" => "&lsaquo;foo&rsaquo;",
											"singleGuillemetsReversed" => "&rsaquo;foo&lsaquo;",
											"cornerBrackets" => "&#x300c;foo&#x300d;",
											"whiteCornerBracket" => "&#x300e;foo&#x300f;",
											),
				"default" 		=> "doubleCurled",
			),
			"typoSmartQuotesSecondary" => array(
				"section"		=> "character-replacement",
				"fieldset" 		=> "smart-quotes",
				"labelBefore" 	=> "Convert <samp>'foo'</samp> to:",
				"helpText" 		=> "Secondary quotation style.",
				"control" 		=> "select",
				"optionValues"	=> array(
											"doubleCurled" => "&ldquo;foo&rdquo;",
											"doubleCurledReversed" => "&rdquo;foo&rdquo;",
											"doubleLow9" => "&bdquo;foo&rdquo;",
											"doubleLow9Reversed" => "&bdquo;foo&ldquo;",
											"singleCurled" => "&lsquo;foo&rsquo;",
											"singleCurledReversed" => "&rsquo;foo&rsquo;",
											"singleLow9" => "&sbquo;foo&rsquo;",
											"singleLow9Reversed" => "&sbquo;foo&lsquo;",
											"doubleGuillemetsFrench" => "&laquo;&nbsp;foo&nbsp;&raquo;",
											"doubleGuillemets" => "&laquo;foo&raquo;",
											"doubleGuillemetsReversed" => "&raquo;foo&laquo;",
											"singleGuillemets" => "&lsaquo;foo&rsaquo;",
											"singleGuillemetsReversed" => "&rsaquo;foo&lsaquo;",
											"cornerBrackets" => "&#x300c;foo&#x300d;",
											"whiteCornerBracket" => "&#x300e;foo&#x300f;",
											),
				"default" 		=> "singleCurled",
			),

			"typoSmartDashes" => array(
				"section"			=> "character-replacement",
				"labelAfter" 		=> "Transform minus-hyphens [ <samp>-</samp> <samp>--</samp> ] to contextually appropriate dashes, minus signs, and hyphens [ <samp>&ndash;</samp> <samp>&mdash;</samp> <samp>&#8722;</samp> <samp>&#8208;</samp> ].",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoSmartEllipses" => array(
				"section"		=> "character-replacement",
				"labelAfter" 	=> "Transform three periods [ <samp>...</samp> ] to  ellipses [ <samp>&hellip;</samp> ].",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			
			
			"typoSmartDiacritics" => array(
				"section"		=> "character-replacement",
				"fieldset" 		=> "diacritics",
				"labelAfter" 	=> "Force diacritics where appropriate.",
				"helpText" 		=> "i.e. <samp>creme brulee</samp> becomes <samp>crème brûlée</samp>",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoDiacriticLanguages" => array(
				"section"		=> "character-replacement",
				"fieldset" 		=> "diacritics",
				"labelBefore" 	=> "Language for diacritic replacements:",
				"helpText" 		=> "Language definitions will purposefully <strong>not</strong> process words that have alternate meaning without diacritics like <samp>resume &amp; résumé</samp>, <samp>divorce &amp; divorcé</samp>, and <samp>expose &amp; exposé</samp>.",
				"control" 		=> "select",
				"optionValues"	=> array(), // automatically detected and listed in __construct
				"default" 		=> "en-US",
			),
			"typoDiacriticCustomReplacements" => array(
				"section" 		=> "character-replacement",
				"fieldset" 		=> "diacritics",
				"labelBefore" 	=> "Custom diacritic word replacements:",
				"helpText" 		=> "Must be formatted <samp>\"word to replace\"=>\"replacement word\",</samp>; This is case-sensitive.",
				"control" 		=> "textarea",
				"default" 		=> '"cooperate"=>"coöperate", "Cooperate"=>"Coöperate", "cooperation"=>"coöperation", "Cooperation"=>"Coöperation", "cooperative"=>"coöperative", "Cooperative"=>"Coöperative", "coordinate"=>"coördinate", "Coordinate"=>"Coördinate", "coordinated"=>"coördinated", "Coordinated"=>"Coördinated", "coordinating"=>"coördinating", "Coordinating"=>"Coördinating", "coordination"=>"coördination", "Coordination"=>"Coördination", "coordinator"=>"coördinator", "Coordinator"=>"Coördinator", "coordinators"=>"coördinators", "Coordinators"=>"Coördinators", "continuum"=>"continuüm", "Continuum"=>"Continuüm", "debacle"=>"débâcle", "Debacle"=>"Débâcle", "elite"=>"élite", "Elite"=>"Élite",',
			),
			
			
			"typoSmartMarks" => array(
				"section"		=> "character-replacement",
				"labelAfter" 	=> "Transform registration marks [ <samp>(c)</samp> <samp>(r)</samp> <samp>(tm)</samp> <samp>(sm)</samp> <samp>(p)</samp> ] to  proper characters [ <samp>©</samp> <samp>®</samp> <samp>™</samp> <samp>℠</samp> <samp>℗</samp> ].",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoSmartMath" => array(
				"section"		=> "character-replacement",
				"labelAfter" 	=> "Transform exponents [ <samp>3^2</samp> ] to pretty exponents [ <samp>3<sup>2</sup></samp> ] and math symbols [ <samp>(2x6)/3=4</samp> ] to correct symbols [ <samp>(2&#215;6)&#247;3=4</samp> ].",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoSmartFractions" => array(
				"section"		=> "character-replacement",
				"labelAfter" 	=> "Transform fractions [ <samp>1/2</samp> ] to  pretty fractions [ <samp><sup>1</sup>&#8260;<sub>2</sub></samp> ].<br>WARNING: If you use a font (like Lucida Grande) that does not have a fraction-slash character, this may cause a missing line between the numerator and denominator.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoSmartOrdinals" => array(
				"section"		=> "character-replacement",
				"labelAfter" 	=> "Transform ordinal suffixes [ <samp>1st</samp> ] to  pretty ordinals [ <samp>1<sup>st</sup></samp> ].",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoSingleCharacterWordSpacing" => array(
				"section"		=> "space-control",
				"labelAfter" 	=> "Prevent single character words from residing at the end of a line of text (unless it is a widow).",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoDashSpacing" => array(
				"section"		=> "space-control",
				"labelAfter" 	=> "Force thin spaces between em &amp; en dashes and adjoining words.  This will display poorly in IE6 with some fonts (like Tahoma) and in rare instances in WebKit browsers (Safari and Chrome).",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoFractionSpacing" => array(
				"section"		=> "space-control",
				"labelAfter" 	=> "Keep integers with adjoining fractions.",
				"helpText" 		=> "i.e. <samp>1 1/2</samp> or <samp>1 <sup>1</sup>&#8260;<sub>2</sub></samp>",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoSpaceCollapse" => array(
				"section"		=> "space-control",
				"labelAfter" 	=> "Collapse adjacent spacing to a single character.",
				"helpText" 		=> "Normal HTML processing collapses basic spaces.  This option will additionally collapse no-break spaces, zero-width spaces, figure spaces, etc.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoUnitSpacing" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "values-and-units",
				"labelAfter" 	=> "Keep values and units together.",
				"helpText" 		=> "i.e. <samp>1 in.</samp> or <samp>10 m<sup>2</sup></samp>",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoUnits" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "values-and-units",
				"labelBefore" 	=> "Unit names:",
				"helpText" 		=> "Separate unit names with spaces. We already look for a large list; fill in any holes here.",
				"control" 		=> "textarea",
				"default" 		=> "hectare fortnight",
			),
			"typoPreventWidows" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "widows",
				"labelAfter" 	=> "Prevent widows",
				"helpText" 		=> "Widows are the last word in a block of text that wraps to its own line.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoWidowMinLength" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "widows",
				"labelBefore" 	=> "Only protect widows with",
				"labelAfter"	=> "or fewer letters.",
				"control" 		=> "select",
				"optionValues"	=> array(4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,100=>100),
				"default" 		=> 5,
			),
			"typoWidowMaxPull" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "widows",
				"labelBefore" 	=> "Pull at most",
				"labelAfter"	=> "letters from the previous line to keep the widow company.",
				"control" 		=> "select",
				"optionValues"	=> array(4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,100=>100),
				"default" 		=> 5,
			),
			"typoWrapHyphens" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "enable-wrapping",
				"labelAfter" 	=> "Enable wrapping after hard hyphens.",
				"helpText" 		=> "Adds zero-width spaces after hard hyphens (like in &ldquo;zero-width&rdquo;).",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoWrapEmails" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "enable-wrapping",
				"labelAfter" 	=> "Enable wrapping of long emails.",
				"helpText" 		=> "Adds zero-width spaces throughout the email.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoWrapURLs" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "enable-wrapping",
				"labelAfter" 	=> "Enable wrapping of long URLs.",
				"helpText" 		=> "Adds zero-width spaces throughout the URL.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoWrapMinAfter" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "enable-wrapping",
				"labelBefore" 	=> "Keep at least the last",
				"labelAfter"	=> "characters of a URL together.",
				"control" 		=> "select",
				"optionValues"	=> array(3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10),
				"default" 		=> 3,
			),
			"typoRemoveIE6" => array(
				"section"		=> "space-control",
				"fieldset" 		=> "enable-wrapping",
				"labelAfter" 	=> "Remove zero-width spaces from IE6.",
				"helpText" 		=> "IE6 displays mangles zero-width spaces with some fonts like Tahoma (uses JavaScript).",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoStyleAmps" => array(
				"section" 		=> "css-hooks",
				"labelAfter" 	=> "Wrap ampersands [ <samp>&amp;</samp> ] with <samp>&lt;span class=\"amp\"&gt;</samp>.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoStyleCaps" => array(
				"section" 		=> "css-hooks",
				"labelAfter" 	=> "Wrap acronyms (all capitals) with <samp>&lt;span class=\"caps\"&gt;</samp>.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoStyleNumbers" => array(
				"section" 		=> "css-hooks",
				"labelAfter" 	=> "Wrap digits [ <samp>0123456789</samp> ] with <samp>&lt;span class=\"numbers\"&gt;</samp>.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 0,
			),
			"typoStyleInitialQuotes" => array(
				"section" 		=> "css-hooks",
				"labelAfter" 	=> "Wrap initial quotes",
				"helpText" 		=> "Note: matches quotemarks at the beginning of blocks of text, <strong>not</strong> all opening quotemarks. <br />Single quotes [ <samp>&lsquo;</samp> <samp>&#8218;</samp> ] are wrapped with <samp>&lt;span class=\"quo\"&gt;</samp>. <br />Double quotes [ <samp>&ldquo;</samp> <samp>&#8222;</samp> ] are wrapped with <samp>&lt;span class=\"dquo\"&gt;</samp>. <br />Guillemets [ <samp>&laquo;</samp> <samp>&raquo;</samp> ] are wrapped with <samp>&lt;span class=\"dquo\"&gt;</samp>.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoInitialQuoteTags" => array(
				"section" 		=> "css-hooks",
				"labelBefore" 	=> "Limit styling of initial quotes to these <strong>HTML elements</strong>:",
				"helpText" 		=> "Separate tag names with spaces; do not include the <samp>&lt;</samp> or <samp>&gt;</samp>.",
				"control" 		=> "textarea",
				"default" 		=> "p h1 h2 h3 h4 h5 h6 blockquote li dd dt",
			),
			"typoStyleCSSInclude" => array(
				"section" 		=> "css-hooks",
				"labelAfter" 	=> "Include Styling for CSS Hooks",
				"helpText" 		=> "Attempts to inject the CSS specified below.  If you are familiar with CSS, it is recommended you not use this option, and maintain all styles in your main stylesheet.",
				"control" 		=> "input",
				"inputType" 	=> "checkbox",
				"default" 		=> 1,
			),
			"typoStyleCSS" => array(
				"section"		=> "css-hooks",
				"labelBefore" 	=> "Styling for CSS Hooks:",
				"helpText" 		=> "This will only be applied if explicitly selected with the preceding option.",
				"control" 		=> "textarea",
				"default" 		=> 'sup {
	vertical-align: 60%;
	font-size: 75%;
	line-height: 100%;
}
sub {
	vertical-align: -10%;
	font-size: 75%;
	line-height: 100%;
}
.amp {
	font-family: Baskerville, "Goudy Old Style", "Palatino", "Book Antiqua", "Warnock Pro", serif;
	font-weight: normal;
	font-style: italic;
	font-size: 1.1em;
	line-height: 1em;
}
.caps {
	font-size: 90%;
}
.dquo {
	margin-left:-.40em;
}
.quo {
	margin-left:-.2em;
}
/* because formatting .numbers should consider your current font settings, we will not style it here */
'
			),

		);
	
	//PHP 4 constructor
	function wpTypography() {
		if(is_admin()) {
			$this->add_action_admin_notices_phpVersionIncompatible();
		}
	}
	
	function __construct(){
		global $wp_version;
		$abortLoad = FALSE;
		if (version_compare($wp_version, $this->installRequirements['WordPress Version'], '<' ) ) {
			if(is_admin()) add_action('admin_notices', array(&$this, 'add_action_admin_notices_wpVersionIncompatible'));
			$abortLoad = TRUE;
		} elseif (version_compare(PHP_VERSION, $this->installRequirements['PHP Version'], '<')) {
			if(is_admin()) add_action('admin_notices', array(&$this, 'add_action_admin_notices_phpVersionIncompatible'));
			$abortLoad = TRUE;
		} elseif (!function_exists('mb_strlen') || !function_exists('mb_strtolower') || !function_exists('mb_substr') || !function_exists('mb_detect_encoding')) {
			if(is_admin()) add_action('admin_notices', array(&$this, 'add_action_admin_notices_mbstringIncompatible'));
			$abortLoad = TRUE;
		} elseif (get_bloginfo('charset') != 'UTF-8' && get_bloginfo('charset') != 'utf-8') {
			if(is_admin()) add_action('admin_notices', array(&$this, 'add_action_admin_notices_charsetIncompatible'));
			$abortLoad = TRUE;
		}

		if($abortLoad == TRUE) return;
		
		$this->pluginPath = WP_PLUGIN_DIR."/".$this->localPluginPath;
		// include needed files
		require_once(WP_PLUGIN_DIR.'/wp-typography/php-typography/php-typography.php');
		
		$typoRestoreDefaults = FALSE;
		if(get_option('typoRestoreDefaults') == TRUE) {
			$typoRestoreDefaults = TRUE;
			echo " TRUE ";
		}
		$this->register_plugin($typoRestoreDefaults);

		foreach($this->adminFormControls as $key => $value) {
			$this->settings[$key] = get_option($key);
		}
	
		// dynamically generate the list of hyphenation language patterns
		$this->phpTypo = new phpTypography(FALSE);
		$this->adminFormControls['typoHyphenateLanguages']['optionValues'] = $this->phpTypo->get_languages();
		$this->adminFormControls['typoDiacriticLanguages']['optionValues'] = $this->phpTypo->get_diacritic_languages();

		// load configuration variables into our phpTypography class
		$this->phpTypo->set_tags_to_ignore($this->settings['typoIgnoreTags']);
		$this->phpTypo->set_classes_to_ignore($this->settings['typoIgnoreClasses']);
		$this->phpTypo->set_ids_to_ignore($this->settings['typoIgnoreIDs']);
		if($this->settings['typoSmartCharacters']) {
			$this->phpTypo->set_smart_dashes($this->settings['typoSmartDashes']);
			$this->phpTypo->set_smart_ellipses($this->settings['typoSmartEllipses']);
			$this->phpTypo->set_smart_math($this->settings['typoSmartMath']);
			$this->phpTypo->set_smart_exponents($this->settings['typoSmartMath']); // note smart_exponents was grouped with smart_math for the WordPress plugin, but does not have to be done that way for other ports
			$this->phpTypo->set_smart_fractions($this->settings['typoSmartFractions']);
			$this->phpTypo->set_smart_ordinal_suffix($this->settings['typoSmartOrdinals']);
			$this->phpTypo->set_smart_marks($this->settings['typoSmartMarks']);
			$this->phpTypo->set_smart_quotes($this->settings['typoSmartQuotes']);
			
			$this->phpTypo->set_smart_diacritics($this->settings['typoSmartDiacritics']);
			$this->phpTypo->set_diacritic_language($this->settings['typoDiacriticLanguages']);
			$this->phpTypo->set_diacritic_custom_replacements($this->settings['typoDiacriticCustomReplacements']);

			$this->phpTypo->set_smart_quotes_primary($this->settings['typoSmartQuotesPrimary']);
			$this->phpTypo->set_smart_quotes_secondary($this->settings['typoSmartQuotesSecondary']);

		} else {
			$this->phpTypo->set_smart_dashes(FALSE);
			$this->phpTypo->set_smart_ellipses(FALSE);
			$this->phpTypo->set_smart_math(FALSE);
			$this->phpTypo->set_smart_exponents(FALSE);
			$this->phpTypo->set_smart_fractions(FALSE);
			$this->phpTypo->set_smart_ordinal_suffix(FALSE);
			$this->phpTypo->set_smart_marks(FALSE);
			$this->phpTypo->set_smart_quotes(FALSE);
			$this->phpTypo->set_smart_diacritics(FALSE);
		}
		$this->phpTypo->set_single_character_word_spacing($this->settings['typoSingleCharacterWordSpacing']);
		$this->phpTypo->set_dash_spacing($this->settings['typoDashSpacing']);
		$this->phpTypo->set_fraction_spacing($this->settings['typoFractionSpacing']);
		$this->phpTypo->set_unit_spacing($this->settings['typoUnitSpacing']);
		$this->phpTypo->set_units($this->settings['typoUnits']);
		$this->phpTypo->set_space_collapse($this->settings['typoSpaceCollapse']);
		$this->phpTypo->set_dewidow($this->settings['typoPreventWidows']);
		$this->phpTypo->set_max_dewidow_length($this->settings['typoWidowMinLength']);
		$this->phpTypo->set_max_dewidow_pull($this->settings['typoWidowMaxPull']);
		$this->phpTypo->set_wrap_hard_hyphens($this->settings['typoWrapHyphens']);
		$this->phpTypo->set_email_wrap($this->settings['typoWrapEmails']);
		$this->phpTypo->set_url_wrap($this->settings['typoWrapURLs']);
		$this->phpTypo->set_min_after_url_wrap($this->settings['typoWrapMinAfter']);
		$this->phpTypo->set_style_ampersands($this->settings['typoStyleAmps']);
		$this->phpTypo->set_style_caps($this->settings['typoStyleCaps']);
		$this->phpTypo->set_style_numbers($this->settings['typoStyleNumbers']);
		$this->phpTypo->set_style_initial_quotes($this->settings['typoStyleInitialQuotes']);
		$this->phpTypo->set_initial_quote_tags($this->settings['typoInitialQuoteTags']);
		if($this->settings['typoEnableHyphenation']) {
			$this->phpTypo->set_hyphenation($this->settings['typoEnableHyphenation']);
			$this->phpTypo->set_hyphenate_headings($this->settings['typoHyphenateHeadings']);
			$this->phpTypo->set_hyphenate_all_caps($this->settings['typoHyphenateCaps']);
			$this->phpTypo->set_hyphenate_title_case($this->settings['typoHyphenateTitleCase']);
			$this->phpTypo->set_hyphenation_language($this->settings['typoHyphenateLanguages']);
			$this->phpTypo->set_min_length_hyphenation($this->settings['typoHyphenateMinLength']);
			$this->phpTypo->set_min_before_hyphenation($this->settings['typoHyphenateMinBefore']);
			$this->phpTypo->set_min_after_hyphenation($this->settings['typoHyphenateMinAfter']);
			$this->phpTypo->set_hyphenation_exceptions($this->settings['typoHyphenateExceptions']);
		} else { // save some cycles
			$this->phpTypo->set_hyphenation($this->settings['typoEnableHyphenation']);
		}
		

		// set up the plugin options page
		register_activation_hook($this->pluginPath, array(&$this, 'register_plugin'));
		add_action('admin_menu', array(&$this, 'add_options_page'));
		add_action('admin_init', array(&$this, 'register_the_settings'));

		global $wp_version;
		if ( version_compare($wp_version, '2.7', '>=' ) ) {
			add_filter( "plugin_action_links_".$this->localPluginPath, array(&$this, 'add_filter_plugin_action_links'));
		}

		// Remove default Texturize filter if it conflicts.
		if($this->settings['typoSmartCharacters']) {
			remove_filter('category_description', 'wptexturize');
			remove_filter('comment_author', 'wptexturize');
			remove_filter('comment_text', 'wptexturize');
			remove_filter('the_content', 'wptexturize');
			remove_filter('single_post_title', 'wptexturize');
			remove_filter('the_title', 'wptexturize');
			remove_filter('the_excerpt', 'wptexturize');
			remove_filter('widget_text', 'wptexturize');
			remove_filter('widget_title', 'wptexturize');
		}

		// apply filters

/*	// removed because it caused issues for feeds
		add_filter('bloginfo', array(&$this, 'processBloginfo'), 9999);
		add_filter('wp_title', 'strip_tags', 9999);
		add_filter('single_post_title', 'strip_tags', 9999);
*/
		add_filter('comment_author', array(&$this, 'process'), 9999);
		add_filter('comment_text', array(&$this, 'process'), 9999);
		add_filter('the_title', array(&$this, 'processTitle'), 9999);
		add_filter('the_content', array(&$this, 'process'), 9999);
		add_filter('the_excerpt', array(&$this, 'process'), 9999);
		add_filter('widget_text', array(&$this, 'process'), 9999);
		add_filter('widget_title', array(&$this, 'processTitle'), 9999);

		// add IE6 zero-width-space removal CSS Hook styling
		add_action('wp_head', array(&$this, 'add_wp_head'));

		return;
	}
	

/*	// removed because it caused issues for feeds
	function processBloginfo($text) {
		if( get_bloginfo( 'name' ) == $text || get_bloginfo( 'description' ) == $text) {
				return $this->process($text, TRUE);
		}
		return $text;
	}
*/
	function processTitle($text) {
		return $this->process($text, TRUE);
	}

	function process($text, $isTitle = FALSE) {
		
		if(is_feed()) { //feed readers can be pretty stupid
			return $this->phpTypo->process_feed($text, $isTitle);
		} else {
			return $this->phpTypo->process($text, $isTitle);
		}
	}

	function register_plugin($update = FALSE) {
		// grab configuration variables
		foreach($this->adminFormControls as $key => $value) {
			if($update || !is_string(get_option($key))) {
				update_option($key, $value["default"]);
			}
		}
		update_option("typoRestoreDefaults", 0);

		return;
	}

	function register_the_settings() {
		foreach($this->adminFormControls as $controlID => $control){
			register_setting( $this->option_group, $controlID );
		}
		register_setting( $this->option_group, "typoRestoreDefaults" );
	}

	function add_options_page() {
		add_options_page($this->pluginName, $this->pluginName, 9, strtolower($this->pluginName), array(&$this, 'get_admin_page_content'));
		return;
	}

	function add_filter_plugin_action_links($links) {
		if (function_exists('admin_url')) {	// since WP 2.6.0
			$adminurl = trailingslashit(admin_url());			
		} else {
			$adminurl = trailingslashit(get_settings('siteurl')).'wp-admin/';
		}
	
		// Add link "Settings" to the plugin in /wp-admin/plugins.php
		$settings_link = '<a href="'.$adminurl.'options-general.php?page='.strtolower($this->pluginName).'">' . __('Settings') . '</a>';
		echo $settings_link;
		array_push($links, $settings_link);
		return $links;
	}


	// admin page content
	function get_admin_page_content() {
?>

<style type="text/css">
	#poststuff .inside {
		margin: 2em;
	}
	.submitdiv .inside {
		margin:  0 !important;
		padding-top: 2em;
	}
	.publishing-settings {
		border-bottom-color:#DDDDDD;
		border-bottom-style:solid;
		border-bottom-width:1px;
		padding: 0 1em 1em;
	}
	.publishing-actions {
		background:#EAF2FA none repeat scroll 0 0;
		border-top:medium none;
		clear:both;
		padding:6px 1em;
	}
	.publishing-action {
		float:right;
		text-align:right;
	}
	fieldset {
		margin:2em -1px 1em;
		padding: 2em 1em 1em;
		border: 1px solid #dfdfdf;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		border-radius: 5px;
		background-color: #fbfbfb;
	}
	legend {
		font-size: 111%;
		font-weight: 700;
		font-style: italic;
	}
	span.helpText {
		color: gray;
		font-size: 90%;
		margin: .3125em 0 0 1.875em;
	}
	samp {
		border: 1px solid #eee;
		padding: .35em .25em .2em;
		background-color:#fbfbfb;
		color: #000;
		font-family: Consolas, Monaco, "Lucida Console", "Courier New", Courier, monospace !important;
	}
	select, option, input, textarea {
		font-family: Consolas, Monaco, "Lucida Console", "Courier New", Courier, monospace !important;
	}
	span.helpText samp {
		font-size: 111%;
	}
	fieldset samp {
		background-color:#f9f9f9;
	}
	textarea{
		width: 100%;
		margin: -.75em 0 1em;
		background-color:#fff;
	}
	label {
		font-size: 111%;
		display: block;
		margin-bottom: 1em;
		line-height: 1.5em;
	}
	select, input {
		margin-top: -.1em;
	}
	.publishing-action input {
		margin-top: 0;
	}

	.control {
		margin: 0 1em;
	}
	fieldset .control {
		margin: 0;
	}
	.text-button {
		background: none;
		border: none;
		text-decoration: underline;
	}
	.text-button:hover {
		cursor: pointer;
	}
</style>

<div class='wrap'>
<div id='icon-options-general' class='icon32'><br /></div>
<h2><?php echo $this->pluginName; ?></h2>

<?php echo $this->get_admin_page_alert(); ?>

<div id='poststuff' class='metabox-holder'>

<div id="resource-links" class='postbox' >
<h3><span>Resource Links</span></h3>
<div class='inside'>

<?php $i=0; ?>
<?php foreach($this->adminResourceLinks as $anchor => $url) { ?>
	<?php if($i++ > 0) echo " | ";?><a href="<?php echo $url; ?>"><?php echo __("$anchor") ?></a>
<?php } ?>

</div>
</div>

<form method="post" action="options.php">
<?php  settings_fields($this->option_group); ?>
	
<?php foreach($this->adminFormSections as $sectionID => $heading): ?>
<div id="<?php echo $sectionID; ?>" class='postbox submitdiv' >
<h3><span><?php echo $heading; ?></span></h3>
<div class='inside'>
<div class='submitbox'>
<div class='publishing-settings'>

<?php
	$fieldsetID = NULL;
	foreach($this->adminFormControls as $controlID => $adminFormControl) {
		if($adminFormControl["section"] == $sectionID ) {
			if($adminFormControl["fieldset"] != $fieldsetID) {
				if($fieldsetID) { // close previous fieldset (if it existed)
					echo "</fieldset>\r\n\r\n";
				}
				if($adminFormControl["fieldset"]) { // start any new fieldset (if it exists)
					echo "\r\n<fieldset id='".$adminFormControl["fieldset"]."'>\r\n";
					echo "<legend>".$this->adminFormSectionFieldsets[$adminFormControl["fieldset"]]["heading"]."</legend>\r\n";
				}
				$fieldsetID = $adminFormControl["fieldset"];
			}
		
		
			echo $this->get_admin_form_control(
					$controlID,
					$adminFormControl['control'],
					$adminFormControl['inputType'],
					$adminFormControl['labelBefore'],
					$adminFormControl['labelAfter'],
					$adminFormControl['helpText'],
					$adminFormControl['optionValues']
					);
		}
	}
	if($fieldsetID) { // we have an unclosed fieldset
		echo "</fieldset>\r\n\r\n";
	}
?>

</div><!-- .publishing-settings -->
<div class='publishing-actions'>
<?php echo $this->get_admin_form_control("saveChanges", "input", "submit"); ?>
<?php echo $this->get_admin_form_control("typoRestoreDefaults", "input", "submit"); ?>
<div class='clear'></div>
</div><!-- .publishing-actions -->
</div><!-- .submitbox -->
</div><!-- .inside -->
</div><!-- .postbox.submitdiv -->

<?php endforeach; //adminFormSections ?>

</form>
		
</div><!-- #poststuff.metabox-holder -->
</div><!-- .wrap -->
<div class='clear'></div>

<?php
		return;
	}
	
	function get_admin_page_alert() {
		if(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
			curl_setopt($ch, CURLOPT_URL, $this->remoteFileURL);
			$content = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($httpCode == 404) {
				$content = "";
			}
			curl_close($ch);
			if ($content) {
				return "<div class='updated fade'>".$content."</div><!-- .updated.fade -->\r\n";
			}
		}
				
		return FALSE;
	}
	
	//	parameter	$id REQUIRED STRING
	//				$control REQUIRED STRING, must be: "input", "select", or "textarea"; not implemented: "button"
	//				$inputType OPTIONAL STRING, for $control = "input"; must be: "text", "password", "checkbox", "submit", "hidden"; not implemented: "radio", "image", "reset", "button", "file"
	//				$labelBefore OPTIONAL STRING, set this to the text that should appear before the control
	//				$labelAfter OPTIONAL STRING, set this to the text that should appear after the control; not for $control = "textarea"
	//				$helpText OPTIONAL STRING, requires an accompanying label
	//				$optionValues OPTIONAL ARRAY, in the form array($value => $display)
	function get_admin_form_control($id, $control="input", $inputType="text", $labelBefore=NULL, $labelAfter=NULL, $helpText=NULL, $optionValues=NULL) {
		$helpTextClass = "helpText";
		if($inputType != "submit") {
			$value = get_option($id);
		} elseif ($id == "typoRestoreDefaults") {
			$value = "Restore Defaults";
		} else {
			$value = "Save Changes";
		}

		if($inputType == "checkbox") {
			$checked = "";
			if($value) $checked = 'checked="checked" ';
			
		}
		
		//make sure $value is in $optionValues if $optionValues is set
		if($optionValues && !isset($optionValues[$value])) {
			$value = NULL;
		}
		
	
		if($inputType=="submit"){
			$controlMarkup = "<div class='publishing-action'>";
		} else {
			$controlMarkup = "<div class='control'>";
		}
		
		if(($labelBefore || $labelAfter) && $inputType != "hidden" && $inputType != "submit"){
			$controlMarkup .= "<label for='$id'>";
			if($labelBefore) {
				$controlMarkup .= "$labelBefore ";
			}
			if($control == "textarea") {
				if($helpText) {
					$controlMarkup .= "<span class='$helpTextClass'>$helpText</span>";
				}
				$controlMarkup .= "</label>";
			}
		}
		
		$controlMarkup .= "<$control ";
		
		if($control == "input") {
			$controlMarkup .= "type='$inputType' ";
		}
		
		if($inputType=="submit" && $value == "Restore Defaults") {
			$controlMarkup .= "name='$id' class='text-button'"; //to avoid duplicate ids and some pretty stylin'
		} elseif($inputType=="submit") {
			$controlMarkup .= "name='$id' class='button-primary'"; //to avoid duplicate ids and some pretty stylin'
		} else {
			$controlMarkup .= "id='$id' name='$id' ";
		}

		if($value && $control != "select" && $control != "textarea" && $inputType != "checkbox") {
			$controlMarkup .= "value='$value' ";
		} elseif($inputType == "checkbox") {
			$controlMarkup .= "value='1' $checked";
		}
		
		if($control != "select" && $control != "textarea") {
			$controlMarkup .= " />";
		} elseif($control == "textarea") {
			$controlMarkup .= " >";
			if($value) {
				$controlMarkup .= $value;
			}
			$controlMarkup .= "</$control>";
		} elseif($control == "select") {
			$controlMarkup .= " >";
			foreach($optionValues as $optionValue => $display){
				$selected = "";
				if($value == $optionValue) $selected = "selected='selected'";
				$controlMarkup .= "<option value='$optionValue' $selected>$display</option>";
			}
			$controlMarkup .= "</$control>";
		}
		
		if(($labelBefore || $labelAfter) && $control != "textarea") {
			if($labelAfter) {
				$controlMarkup .= " $labelAfter";
			}
			if($helpText) {
				$controlMarkup .= "<span class='$helpTextClass'>$helpText</span>";
			}
			$controlMarkup .= "</label>";
		}
		
		$controlMarkup .= "</div>\r\n";

		return $controlMarkup;
	}

	function add_action_admin_notices_wpVersionIncompatible() { 
		global $wp_version;
		echo '<div class="error"><p>'.__('The activated plugin ').'<strong>'.$this->pluginName.'</strong>'.__(' requires WordPress version ').$this->installRequirements['WordPress Version'].__(' or later.  You are running WordPress version ').$wp_version.__('. Please deactivate this plugin, or upgrade your installation of WordPress.').'</p></div>'; 
	}
	function add_action_admin_notices_phpVersionIncompatible() { 
		echo '<div class="error"><p>'.__('The activated plugin ').'<strong>'.$this->pluginName.'</strong>'.__(' requires PHP ').$this->installRequirements['PHP Version'].__(' or later.  Your server is running PHP ').phpversion().__('. Please deactivate this plugin, or upgrade your server\'s installation of PHP.').'</p></div>'; 
	}
	function add_action_admin_notices_mbstringIncompatible() { 
		echo '<div class="error"><p>'.__('The activated plugin ').'<strong>'.$this->pluginName.'</strong>'.__(' requires the mbstring PHP extension be enabled on your server.  It is not. Please deactivate this plugin, or ').'<a href="http://www.php.net/manual/en/mbstring.installation.php">'.__('enable this extension').'</a>.'.'</p></div>'; 
	}
	function add_action_admin_notices_charsetIncompatible() { 
		echo '<div class="error"><p>'.__('The activated plugin ').'<strong>'.$this->pluginName.'</strong>'.__(' requires your blog use the UTF-8 character encoding.  You have set your blogs encoding to ').get_bloginfo('charset').__('. Please deactivate this plugin, or ').'<a href="/wp-admin/options-reading.php">'.__('change your character encoding to UTF-8').'</a>.'.'</p></div>'; 
	}
	function add_wp_head() {
		if($this->settings['typoStyleCSSInclude'] && trim($this->settings['typoStyleCSS']) != "") {
			echo '<style type="text/css">'."\r\n";
			echo $this->settings['typoStyleCSS']."\r\n";
			echo "</style>\r\n";
		}
		if($this->settings['typoRemoveIE6']) {
			echo "<!--[if lt IE 7]>\r\n";
			echo "<script type='text/javascript'>";
			echo "function stripZWS() { document.body.innerHTML = document.body.innerHTML.replace(/\u200b/gi,''); }";
			echo "window.onload = stripZWS;";
			echo "</script>\r\n";
			echo "<![endif]-->\r\n";
		}
	}
}
