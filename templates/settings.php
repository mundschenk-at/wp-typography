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
	textarea {
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
	
	.control {
		margin: 0 1em;
	}
	fieldset .control {
		margin: 0;
	}
</style>

<div class='wrap'>
	<div id='icon-options-general' class='icon32'><br /></div>
	<h1><?php echo $this->plugin_name; ?></h1>
	
	<div id='poststuff' class='metabox-holder'>
		<div id="resource-links" class='postbox' >
			<h2><span><?php _e( 'Resource Links', 'wp-typography' ); ?></span></h2>
		
			<div class='inside'>	
			<?php $i=0; ?>
			<?php foreach( $this->admin_resource_links as $anchor => $url ) { ?>
				<?php if ( $i++ > 0 ) echo " | ";?><a href="<?php echo $url; ?>"><?php echo $anchor; ?></a>
			<?php } ?>
			</div>
		</div>
		
		<form method="post" action="options.php">
			<?php  settings_fields( $this->option_group ); ?>
				
			<?php foreach ( $this->admin_form_sections as $sectionID => $heading ): ?>
			<div id="<?php echo $sectionID; ?>" class='postbox submitdiv' >
				<h2><span><?php echo $heading; ?></span></h2>
				<div class='inside'>
					<div class='submitbox'>
						<div class='publishing-settings'>		
						<?php
							$fieldsetID = NULL;
							foreach ($this->admin_form_controls as $controlID => $admin_form_control) {
								if ($admin_form_control["section"] == $sectionID ) {
									if ($admin_form_control["fieldset"] != $fieldsetID) {
										if ($fieldsetID) { // close previous fieldset (if it existed)
											echo "</fieldset>\r\n\r\n";
										}
										if ($admin_form_control["fieldset"]) { // start any new fieldset (if it exists)
											echo "\r\n<fieldset id='".$admin_form_control["fieldset"]."'>\r\n";
											echo "<legend>".$this->admin_form_section_fieldsets[$admin_form_control["fieldset"]]["heading"]."</legend>\r\n";
										}
										$fieldsetID = $admin_form_control["fieldset"];
									}
																	
									echo $this->get_admin_form_control(
													$controlID,
													$admin_form_control['control'],
													$admin_form_control['input_type'],
													$admin_form_control['label'],
													$admin_form_control['help_text'],
													$admin_form_control['option_values']
												);
								}
							}
							if ($fieldsetID) { // we have an unclosed fieldset
								echo "</fieldset>\r\n\r\n";
							}
						?>				
						</div><!-- .publishing-settings -->
						
						<div class='publishing-actions'>
							<?php echo $this->get_admin_form_control('saveChanges', 'input', 'submit'); ?>
							<?php echo $this->get_admin_form_control('typoRestoreDefaults', 'input', 'submit'); ?>
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