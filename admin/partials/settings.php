<div class='wrap'>
	<?php /* translators: settings page headline, %s is the plugin name */ ?>
	<h1><?php echo esc_html( sprintf( __( '%s Settings', 'wp-typography' ), $this->plugin_name ) ); ?></h1><?php

	// Check active tab.
	$all_tabs   = array_keys( $this->admin_form_tabs ); // PHP 5.3 workaround.
	$active_tab = ! empty( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : $all_tabs[0];

	?>
	<h2 class="nav-tab-wrapper">
	<?php foreach ( $this->admin_form_tabs as $tab_id => $heading ): ?>
		<a href="?page=<?php echo esc_attr( strtolower( $this->plugin_name ) ); ?>&amp;tab=<?php echo esc_attr( $tab_id ); ?>" class="nav-tab<?php echo $tab_id === $active_tab ? ' nav-tab-active' : ''; ?>"><?php _e( $heading, 'wp-typography' ); ?></a>
	<?php endforeach; ?>
	</h2>

	<form method="post" action="options.php">
		<?php foreach ( $this->admin_form_tabs as $tab_id => $heading ) : ?>
			<?php if ( $active_tab === $tab_id ) : ?>
				<?php settings_fields( $this->option_group . $tab_id ); ?>
				<?php do_settings_sections( $this->option_group . $tab_id ); ?>
			<?php endif; // active_tab ?>
		<?php endforeach; // admin_form_sections ?>

		<p class="submit">
			<?php submit_button( __( 'Save Changes', 'wp-typography' ),     'primary',   'save_changes',          false, array( 'tabindex' => 1 ) ); ?>
			<span class="aux-buttons">
				<?php submit_button( __( 'Restore Defaults', 'wp-typography' ), 'delete',    'typo_restore_defaults', false, array( 'tabindex' => 2 ) ); ?>
				<?php submit_button( __( 'Clear Cache', 'wp-typography' ),      'secondary', 'typo_clear_cache',      false, array( 'tabindex' => 3 ) ); ?>
			</span>
		</p><!-- .submit -->
	</form>

</div><!-- .wrap -->
