<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2018-2024 Peter Putzer.
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

use PHP_Typography\Fixes\Default_Registry;

/**
 * An extension of the default PHP-Typography registry.
 *
 * @since 5.4.0
 *
 * @author Peter Putzer <github@mundschenk.at>
 */
class Custom_Registry extends Default_Registry {

	/**
	 * Creates new registry instance with custom fixes.
	 */
	public function __construct() {
		parent::__construct();

		// Register additional node fixes.
		$this->register_node_fix( new Custom_Node_Fix( 'characters' ), self::CHARACTERS );
		$this->register_node_fix( new Custom_Node_Fix( 'spacing_pre' ), self::SPACING_PRE_WORDS );
		$this->register_node_fix( new Custom_Node_Fix( 'spacing_post' ), self::SPACING_POST_WORDS );
		$this->register_node_fix( new Custom_Node_Fix( 'html_insertion' ), self::HTML_INSERTION );

		// Register additional token fix.
		$this->register_token_fix( new Custom_Token_Fix( 'mixed_words' ) );
		$this->register_token_fix( new Custom_Token_Fix( 'compound_words' ) );
		$this->register_token_fix( new Custom_Token_Fix( 'words' ) );
		$this->register_token_fix( new Custom_Token_Fix( 'other' ) );
	}
}
