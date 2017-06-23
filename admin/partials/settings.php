<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2017 Peter Putzer.
 *  Copyright 2009-2011 KINGdesk, LLC.
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
 *  @package wpTypography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

?><div class='wrap'>
	<h1><?php echo esc_html( sprintf( /* translators: settings page headline, %s is the plugin name */ __( '%s Settings', 'wp-typography' ), $this->plugin_name ) ); ?></h1>
	<?php
		// Check active tab.
		$active_tab = $this->get_active_settings_tab();
	?>
	<h2 class="nav-tab-wrapper">
	<?php foreach ( $this->admin_form_tabs as $tab_id => $tab ) : ?>
		<a href="?page=<?php echo esc_attr( strtolower( $this->plugin_name ) ); ?>&amp;tab=<?php echo esc_attr( $tab_id ); ?>" class="nav-tab<?php echo $tab_id === $active_tab ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $tab['heading'] ); ?></a>
	<?php endforeach; ?>
	</h2>

	<form method="post" action="options.php">
		<?php settings_fields( $this->option_group . $active_tab ); ?>
		<?php do_settings_sections( $this->option_group . $active_tab ); ?>

		<p class="submit">
			<?php
				submit_button( __( 'Save Changes', 'wp-typography' ), 'primary', 'save_changes', false, [
					'tabindex' => 1,
				] );
			?>
			<span class="aux-buttons">
				<?php
					submit_button( __( 'Restore Defaults', 'wp-typography' ), 'delete', 'typo_restore_defaults', false, [
						'tabindex' => 2,
					] );
				?>
				<?php
					// The whitespace is necessary.
					submit_button( __( 'Clear Cache', 'wp-typography' ), 'secondary', 'typo_clear_cache', false, [
						'tabindex' => 3,
					] );
				?>
			</span>
		</p><!-- .submit -->
	</form>

</div><!-- .wrap -->
<?php
