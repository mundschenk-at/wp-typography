<?php
/**
 *  This file is part of wp-Typography.
 *
 *  Copyright 2014-2023 Peter Putzer.
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
 *  @package mundschenk-at/wp-typography
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace WP_Typography\Partials\Admin;

/**
 * Required template variables:
 *
 * @var string $plugin_name             The plugin name.
 * @var string $active_tab              The active tab.
 * @var string $option_group            The active option group name.
 * @var array  $admin_form_tabs         The admin form tabs.
 * @var string $restore_defaults_button The `name` attribute of the Restore Defaults submit button.
 * @var string $clear_cache_button      The `name` attribute of the Clear Cache submit button.
 *
 * @phpstan-var array<string, array{heading:string, description:string}> $admin_form_tabs
 */

?><div class='wrap'>
	<h1><?php echo \esc_html( \sprintf( /* translators: settings page headline, %s is the plugin name */ \__( '%s Settings', 'wp-typography' ), $plugin_name ) ); ?></h1>
	<h2 class="nav-tab-wrapper">
	<?php foreach ( $admin_form_tabs as $tab_id => $settings_tab ) : ?>
		<?php
			$query_string = '?page=' . \strtolower( $plugin_name ) . '&tab=' . $tab_id;
			$classes      = 'nav-tab' . ( $tab_id === $active_tab ? ' nav-tab-active' : '' );
		?>
		<a href="<?php echo \esc_url( $query_string ); ?>" class="<?php echo \esc_attr( $classes ); ?>"><?php echo \esc_html( $settings_tab['heading'] ); ?></a>
	<?php endforeach; ?>
	</h2>

	<form method="post" action="options.php">
		<?php \settings_fields( $option_group ); ?>
		<?php \do_settings_sections( $option_group ); ?>

		<p class="submit">
			<?php \submit_button( \__( 'Save Changes', 'wp-typography' ), 'primary', 'save_changes', false, [ 'tabindex' => 1 ] ); ?>
			<span class="aux-buttons">
				<?php \submit_button( \__( 'Restore Defaults', 'wp-typography' ), 'delete', $restore_defaults_button, false, [ 'tabindex' => 2 ] ); ?>
				<?php // The whitespace is necessary. ?>
				<?php \submit_button( \__( 'Clear Cache', 'wp-typography' ), 'secondary', $clear_cache_button, false, [ 'tabindex' => 3 ] ); ?>
			</span>
		</p><!-- .submit -->
	</form>

</div><!-- .wrap -->
<?php
