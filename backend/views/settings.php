<?php
/*
 * Retrieve these settings on front end in either of these ways:
 * phpcs:ignore $my_setting = cmb2_get_option( SW_TEXTDOMAIN . '-settings', 'some_setting', 'default' );
 * phpcs:ignore  $my_settings = get_option( SW_TEXTDOMAIN . '-settings', 'default too' );
 * CMB2 Snippet: https://github.com/CMB2/CMB2-Snippet-Library/blob/master/options-and-settings-pages/theme-options-cmb.php
 */

 $parsed_url = parse_url(site_url());
 $initialized = \get_option( SW_TEXTDOMAIN . '-init' );
 $gsc_connected = get_option( SW_TEXTDOMAIN . '-gsc-connected' );
?>
<div id="tabs-1" class="wrap">
			<?php
			$cmb = new_cmb2_box(
				array(
					'id'           => SW_TEXTDOMAIN . '_options',
					'hookup'       => false,
					'show_on'      => array( 'key' => 'options-page', 'value' => array( SW_TEXTDOMAIN ) ),
					'show_names'   => true,
					'object_types' => array( 'options-page' ),
					'option_key'   => SW_TEXTDOMAIN . '_options',
				)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'API endpoint', SW_TEXTDOMAIN ),
					'id'      => 'endpoint',
					'type'    => 'text',
					'default' => 'https://api.somecaptions.dk/',
				)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'API key', SW_TEXTDOMAIN ),
					// phpcs:ignore 'desc'    => __( 'SomeCaptions API key', SW_TEXTDOMAIN ),
					'id'      => 'api_key',
					'type'    => 'text',
					'default' => '',
				)
			);
            // Add verification section (only show if API key is set)
			$api_key = cmb2_get_option(SW_TEXTDOMAIN . '-settings', 'api_key', '');
			if (!empty($api_key)) {
				$cmb->add_field(
					array(
						'name'    => __( 'Domain Verification', SW_TEXTDOMAIN ),
						'id'      => 'verification_section',
						'type'    => 'title',
						'desc'    => __( 'Verify your domain ownership to enable full integration with SomeCaptions', SW_TEXTDOMAIN ),
					)
				);
				
				$cmb->add_field(
					array(
						'name'    => __( 'Verification Code', SW_TEXTDOMAIN ),
						'id'      => 'verification_code',
						'type'    => 'text',
						'desc'    => __( 'Enter the verification code from your SomeCaptions dashboard', SW_TEXTDOMAIN ),
					)
				);
				
				$domain_verified = get_option(SW_TEXTDOMAIN . '-domain-verified', false);
				$verify_button_html = '<button type="button" id="verify-domain-btn" class="button button-primary">Verify Domain</button>';
				$verify_status_html = '<span id="verification-status" style="margin-left: 10px;">';
				
				if ($domain_verified) {
					$verify_status_html .= '<span style="color:green;">✓ Domain verified</span>';
				}
				
				$verify_status_html .= '</span>';
				
				$cmb->add_field(
					array(
						'name' => '',
						'desc' => $verify_button_html . $verify_status_html . '
							<script>
								jQuery(document).ready(function($) {
									$("#verify-domain-btn").on("click", function() {
										const verification_code = $("#verification_code").val();
										if (!verification_code) {
											$("#verification-status").html("<span style=\'color:red\'>Please enter a verification code</span>");
											return;
										}
										
										$("#verification-status").html("<span style=\'color:blue\'>Verifying...</span>");
										
										$.ajax({
											url: ajaxurl,
											type: "POST",
											data: {
												action: "somecaptions_verify_domain",
												verification_code: verification_code,
												nonce: "' . wp_create_nonce('somecaptions_verify_domain') . '"
											},
											success: function(response) {
												if (response.success) {
													$("#verification-status").html("<span style=\'color:green\'>✓ " + response.data.message + "</span>");
													setTimeout(function() {
														window.location.reload();
													}, 1500);
												} else {
													$("#verification-status").html("<span style=\'color:red\'>" + response.data.message + "</span>");
												}
											},
											error: function() {
												$("#verification-status").html("<span style=\'color:red\'>Verification failed. Please try again.</span>");
											}
										});
									});
								});
							</script>',
						'id'   => 'verification_button',
						'type' => 'title',
					)
				);
			}
			cmb2_metabox_form( SW_TEXTDOMAIN . '_options', SW_TEXTDOMAIN . '-settings' );
			?>
			<?php if( $initialized ):?>
				<br />
				<button 
				class="button-primary"
				onClick="open_app_gw('<?php echo SW_SIGNIN_HOST . '/' . $parsed_url['host']; ?>')"
				>Connect Google Search Console</button>
			<?php endif;?>
			<br />
			<p>
				<?php if( !$gsc_connected && $initialized ):?>
						GSC is not connected.
				<?php elseif( $gsc_connected && $initialized ):?>
						GSC is connected.
				<?php endif;?>
			</p>
		</div>