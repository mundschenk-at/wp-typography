<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2017-2018 Peter Putzer.
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

if ( ! empty( $this->grouped_controls ) ) : ?>
	<fieldset><legend class="screen-reader-text"><?php echo \esc_html( $this->short ); ?></legend>
<?php endif; // grouped_controls. ?>
<?php if ( ! empty( $this->label ) ) : ?>
	<label for="<?php echo \esc_attr( $this->get_id() ); ?>"><?php echo \wp_kses( $this->get_label(), self::ALLOWED_HTML ); ?></label>
<?php elseif ( $this->has_inline_help() ) : ?>
	<label for="<?php echo \esc_attr( $this->get_id() ); ?>">
<?php endif; ?>
<?php
	// Control-specific markup.
if ( ! $this->label_has_placeholder() ) :
	$this->render_element();
endif;
?>
<?php if ( $this->has_inline_help() ) : ?>
	<span class="description"><?php echo \wp_kses( $this->help_text, [ 'code' => [] ] ); ?></span></label>
<?php elseif ( ! empty( $this->help_text ) ) : ?>
	<p class="description"><?php echo \wp_kses( $this->help_text, [ 'code' => [] ] ); ?></p>
<?php endif; ?>

<?php if ( ! empty( $this->grouped_controls ) ) : ?>

	<?php foreach ( $this->grouped_controls as $control ) : ?>
		<br />
		<?php $control->render(); ?>
	<?php endforeach; ?>
	</fieldset>

<?php endif; // grouped_controls. ?>
<?php
