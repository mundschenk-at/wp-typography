<div class='wrap'>
	<div id='icon-options-general' class='icon32'><br /></div>
	<h1><?php echo $this->plugin_name; ?></h1>

	<div id='poststuff' class='metabox-holder'>
		<div id="resource-links" class='postbox' >
			<h2><span><?php _e( 'Resource Links', 'wp-typography' ); ?></span></h2>

			<div class='inside'>
			<?php $i=0; ?>
			<?php foreach( $this->admin_resource_links as $anchor => $url ) { ?>
				<?php if ( $i++ > 0 ) echo " | ";?><a href="<?php echo $url; ?>"><?php _e( $anchor, 'wp-typography' ); ?></a>
			<?php } ?>
			</div>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( $this->option_group ); ?>

			<?php foreach ( $this->admin_form_sections as $section_id => $heading ): ?>
			<div id="<?php echo $section_id; ?>" class='postbox submitdiv' >
				<h2><span><?php _e( $heading, 'wp-typography' ); ?></span></h2>
				<div class='inside'>
					<div class='submitbox'>
						<div class='publishing-settings'>
						<?php
							$fieldset_id = NULL;
							foreach ( $this->admin_form_controls as $control_id => $admin_form_control ) {
								if ( $admin_form_control['section'] === $section_id ) {
									if ( empty( $admin_form_control['fieldset'] ) ) {
										// close previous fieldset (if it existed
										if ( ! empty( $fieldset_id ) ) {
											echo "</fieldset>\r\n\r\n";
											$fieldset_id = null;
										}
									} elseif ( $admin_form_control['fieldset'] !== $fieldset_id ) {
										// close previous fieldset
										echo "</fieldset>\r\n\r\n";

										// ... and open new one
										echo "\r\n<fieldset id='" . $admin_form_control['fieldset'] . "'>\r\n";
										echo "<legend>" . __( $this->admin_form_section_fieldsets[ $admin_form_control['fieldset'] ]['heading'], 'wp-typography' ) . "</legend>\r\n";

										$fieldset_id = $admin_form_control['fieldset'];
									}

									echo $this->get_admin_form_control( $control_id, // mandatory
																		$admin_form_control['control'], // mandatory
																		isset( $admin_form_control['input_type'] )    ? $admin_form_control['input_type']    : null,
																		isset( $admin_form_control['label'] )         ? $admin_form_control['label']         : null,
																		isset( $admin_form_control['help_text'] )     ? $admin_form_control['help_text']     : null,
																		isset( $admin_form_control['option_values'] ) ? $admin_form_control['option_values'] : null,
																		isset( $admin_form_control['attributes'] )    ? $admin_form_control['attributes']    : null );
								}
							}
							if ( ! empty( $fieldset_id ) ) { // we have an unclosed fieldset
								echo "</fieldset>\r\n\r\n";
								$fieldset_id = null;
							}
						?>
						</div><!-- .publishing-settings -->

						<div class='publishing-actions'>
							<?php echo $this->get_admin_form_control( 'save_changes',          'input', 'submit' ); ?>
							<?php echo $this->get_admin_form_control( 'typo_restore_defaults', 'input', 'submit' ); ?>
							<?php echo $this->get_admin_form_control( 'typo_clear_cache',      'input', 'submit' ); ?>
							<div class='clear'></div>
						</div><!-- .publishing-actions -->
					</div><!-- .submitbox -->
				</div><!-- .inside -->
			</div><!-- .postbox.submitdiv -->
			<?php endforeach; //admin_form_sections ?>
		</form>

	</div><!-- #poststuff.metabox-holder -->
</div><!-- .wrap -->
<div class='clear'></div>