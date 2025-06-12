<?php
/*
 * Retrieve these settings on front end in either of these ways:
 * phpcs:ignore $my_setting = cmb2_get_option( 'somecaptions-client' . '-settings', 'some_setting', 'default' );
 * phpcs:ignore  $my_settings = get_option( 'somecaptions-client' . '-settings', 'default too' );
 * CMB2 Snippet: https://github.com/CMB2/CMB2-Snippet-Library/blob/master/options-and-settings-pages/theme-options-cmb.php
 */

 $parsed_url = \wp_parse_url(site_url());
 $initialized = \get_option( 'somecaptions-client' . '-init' );
 $gsc_connected = \get_option( 'somecaptions-client' . '-gsc-connected' );
?>
<div id="tabs-1" class="wrap">
			<?php
			$cmb = new_cmb2_box(
				array(
					'id'           => 'somecaptions-client' . '_options',
					'hookup'       => false,
					'show_on'      => array( 'key' => 'options-page', 'value' => array( 'somecaptions-client' ) ),
					'show_names'   => true,
					'object_types' => array( 'options-page' ),
					'option_key'   => 'somecaptions-client' . '_options',
				)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'API endpoint', 'somecaptions-client' ),
					'id'      => 'endpoint',
					'type'    => 'text',
					'default' => 'https://api.somecaptions.dk/',
				)
			);
			$cmb->add_field(
				array(
					'name'    => __( 'API key', 'somecaptions-client' ),
					// phpcs:ignore 'desc'    => __( 'SomeCaptions API key', 'somecaptions-client' ),
					'id'      => 'api_key',
					'type'    => 'text',
					'default' => '',
				)
			);
            // Add verification section (only show if API key is set)
			$api_key = cmb2_get_option('somecaptions-client' . '-settings', 'api_key', '');
			if (!empty($api_key)) {
				$cmb->add_field(
					array(
						'name'    => __( 'Domain Verification', 'somecaptions-client' ),
						'id'      => 'verification_section',
						'type'    => 'title',
						'desc'    => __( 'Verify your domain ownership to enable full integration with SomeCaptions', 'somecaptions-client' ),
					)
				);
				
				$cmb->add_field(
					array(
						'name'    => __( 'Verification Code', 'somecaptions-client' ),
						'id'      => 'verification_code',
						'type'    => 'text',
						'desc'    => __( 'Enter the verification code from your SomeCaptions dashboard', 'somecaptions-client' ),
					)
				);
				
				$domain_verified = get_option('somecaptions-client' . '-domain-verified', false);
				$verify_button_html = '<button type="button" id="verify-domain-btn" class="button button-primary">' . esc_html__('Verify Domain', 'somecaptions-client') . '</button>';
				$verify_status_html = '<span id="verification-status" style="margin-left: 10px;">';
				
				if ($domain_verified) {
					$verify_status_html .= '<span style="color:green;">' . esc_html__('✓ Domain verified', 'somecaptions-client') . '</span>';
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
											$("#verification-status").html("<span style=\'color:red\'>' . esc_js(__('Please enter a verification code', 'somecaptions-client')) . '</span>");
											return;
										}
										
										$("#verification-status").html("<span style=\'color:blue\'>' . esc_js(__('Verifying...', 'somecaptions-client')) . '</span>");
										
										$.ajax({
											url: ajaxurl,
											type: "POST",
											data: {
												action: "somecaptions_verify_domain",
												verification_code: verification_code,
												nonce: "' . wp_create_nonce('somecaptions-client-verify-domain') . '"
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
												$("#verification-status").html("<span style=\'color:red\'>' + esc_js(__('Verification failed. Please try again.', 'somecaptions-client')) + '</span>");
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
			cmb2_metabox_form( 'somecaptions-client' . '_options', 'somecaptions-client' . '-settings' );
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
						<?php esc_html_e('GSC is not connected.', 'somecaptions-client'); ?>
				<?php elseif( $gsc_connected && $initialized ):?>
						<?php esc_html_e('GSC is connected.', 'somecaptions-client'); ?>
				<?php endif;?>
			</p>
		</div>