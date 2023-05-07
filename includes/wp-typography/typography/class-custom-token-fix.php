<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2023 Peter Putzer.
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

namespace WP_Typography\Typography;

use PHP_Typography\Settings;

use PHP_Typography\Text_Parser\Token;

use PHP_Typography\Fixes\Token_Fixes\Abstract_Token_Fix;

/**
 * A token fix with WordPress hooks.
 *
 * @since  5.4.0
 * @since  5.9.0 Return type declarations added.
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Custom_Token_Fix extends Abstract_Token_Fix {

	/**
	 * The token type part of the filter hook.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Creates a new fix instance.
	 *
	 * @param string $type The word type targetted by the fix, i.e. either `mixed_words`, 'compound_words`, `words`, or `other`.
	 *
	 * @throws \InvalidArgumentException The exception is thrown if `$type` is any other string.
	 */
	public function __construct( $type ) {
		$valid_types = [
			'mixed_words'    => self::MIXED_WORDS,
			'compound_words' => self::COMPOUND_WORDS,
			'words'          => self::WORDS,
			'other'          => self::OTHER,
		];

		if ( ! isset( $valid_types[ $type ] ) ) {
			throw new \InvalidArgumentException( "$type is not a valid word type." );
		}

		parent::__construct( $valid_types[ $type ], true );

		$this->type = $type;
	}


	/**
	 * Apply the tweak to a given textnode.
	 *
	 * @param Token[]       $tokens   Required.
	 * @param Settings      $settings Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param \DOMText|null $textnode Optional. Default null.
	 *
	 * @return Token[] An array of tokens.
	 */
	public function apply( array $tokens, Settings $settings, $is_title = false, \DOMText $textnode = null ): array {

		/**
		 * Filters the tokenized text node content (limited to a certain "word" type).
		 *
		 * @param Token[]  $tokens   The relevant "words" of the tokenized content.
		 * @param \DOMText $textnode The processed node.
		 * @param Settings $settings The typography settings to apply.
		 * @param bool     $is_title Whether the content occurs in a title/heading context.
		 */
		return \apply_filters( "typo_custom_{$this->type}_token_fix", $tokens, $textnode, $settings, $is_title );
	}
}
