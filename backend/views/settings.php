<?php
/*
 * Retrieve these settings on front end in either of these ways:
 * phpcs:ignore $my_setting = cmb2_get_option( 'somecaptions-wpclient' . '-settings', 'some_setting', 'default' );
 * phpcs:ignore  $my_settings = get_option( 'somecaptions-wpclient' . '-settings', 'default too' );
 * CMB2 Snippet: https://github.com/CMB2/CMB2-Snippet-Library/blob/master/options-and-settings-pages/theme-options-cmb.php
 */

 $parsed_url = \wp_parse_url(site_url());
 $initialized = \get_option( 'somecaptions-wpclient' . '-init' );
 $gsc_connected = \get_option( 'somecaptions-wpclient' . '-gsc-connected' );
?>
<div id="tabs-1" class="wrap">
			<?php
			$cmb = new_cmb2_box(
				array(
					'id'           => 'somecaptions-wpclient' . '_options',
					'hookup'       => false,
					'show_on'      => array( 'key' => 'options-page', 'value' => array( 'somecaptions-wpclient' ) ),
					'show_names'   => true,
					'object_types' => array( 'options-page' ),
					'option_key'   => 'somecaptions-wpclient' . '_options',
				)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'API endpoint', 'somecaptions-wpclient' ),
					'id'      => 'endpoint',
					'type'    => 'text',
					'default' => 'https://api.somecaptions.dk/',
				)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'API key', 'somecaptions-wpclient' ),
					// phpcs:ignore 'desc'    => __( 'SomeCaptions API key', 'somecaptions-wpclient' ),
					'id'      => 'api_key',
					'type'    => 'text',
					'default' => '',
				)
			);
            // Add verification section (only show if API key is set)
			$api_key = cmb2_get_option('somecaptions-wpclient' . '-settings', 'api_key', '');
			if (!empty($api_key)) {
				$cmb->add_field(
					array(
						'name'    => __( 'Domain Verification', 'somecaptions-wpclient' ),
						'id'      => 'verification_section',
						'type'    => 'title',
						'desc'    => __( 'Verify your domain ownership to enable full integration with SomeCaptions', 'somecaptions-wpclient' ),
					)
				);
				
				$cmb->add_field(
					array(
						'name'    => __( 'Verification Code', 'somecaptions-wpclient' ),
						'id'      => 'verification_code',
						'type'    => 'text',
						'desc'    => __( 'Enter the verification code from your SomeCaptions dashboard', 'somecaptions-wpclient' ),
					)
				);
				
				$domain_verified = get_option('somecaptions-wpclient' . '-domain-verified', false);
				$verify_button_html = '<button type="button" id="verify-domain-btn" class="button button-primary">' . esc_html__('Verify Domain', 'somecaptions-wpclient') . '</button>';
				$verify_status_html = '<span id="verification-status" style="margin-left: 10px;">';
				
				if ($domain_verified) {
					$verify_status_html .= '<span style="color:green;">' . esc_html__('✓ Domain verified', 'somecaptions-wpclient') . '</span>';
				}
				
				$verify_status_html .= '</span>';
				
				$cmb->add_field(
					array(
						'name' => '',
						'desc' => wp_kses_post($verify_button_html . $verify_status_html) . '
							<script>
								jQuery(document).ready(function($) {
									$("#verify-domain-btn").on("click", function() {
										const verification_code = $("#verification_code").val();
										if (!verification_code) {
											$("#verification-status").html("<span style=\'color:red\'>' . esc_js(__('Please enter a verification code', 'somecaptions-wpclient')) . '</span>");
											return;
										}
										
										$("#verification-status").html("<span style=\'color:blue\'>' . esc_js(__('Verifying...', 'somecaptions-wpclient')) . '</span>");
										
										$.ajax({
											url: ajaxurl,
											type: "POST",
											data: {
												action: "somecaptions_verify_domain",
												verification_code: verification_code,
												nonce: "' . wp_create_nonce('somecaptions-wpclient-verify-domain') . '"
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
												$("#verification-status").html("<span style=\'color:red\'>' + esc_js(__('Verification failed. Please try again.', 'somecaptions-wpclient')) + '</span>");
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
			cmb2_metabox_form( 'somecaptions-wpclient' . '_options', 'somecaptions-wpclient' . '-settings' );
			?>
			<?php if( $initialized ):?>
				<br />
				<button 
				class="button-primary"
				onClick="open_app_gw('<?php echo esc_url(SW_SIGNIN_HOST . '/' . esc_attr($parsed_url['host'])); ?>')"
				>Connect Google Search Console</button>
			<?php endif;?>
			<br />
			<p>
				<?php if( !$gsc_connected && $initialized ):?>
						<?php esc_html_e('GSC is not connected.', 'somecaptions-wpclient'); ?>
				<?php elseif( $gsc_connected && $initialized ):?>
						<?php esc_html_e('GSC is connected.', 'somecaptions-wpclient'); ?>
				<?php endif;?>
			</p>
		</div>