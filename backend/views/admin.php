<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   SoMeCaptions_WPClient
 * @author    Mindell <mindell.zamora@gmail.com>
 * @copyright N/A
 * @license   GPL 2.0+
 * @link      https://github.com/mindell/
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php
	// Get settings and status
	$api_key = cmb2_get_option('somecaptions-client' . '-settings', 'api_key', '');
	$domain_verified = get_option('somecaptions-client' . '-domain-verified', false);
	$initialized = get_option('somecaptions-client' . '-init');
	?>

	<!-- Add tab navigation -->
	<h2 class="nav-tab-wrapper">
		<a href="#general-settings" class="nav-tab nav-tab-active">General Settings</a>
		<?php if (!empty($api_key)): ?>
		<a href="#domain-verification" class="nav-tab">Domain Verification</a>
		<?php endif; ?>
	</h2>

	<div class="tab-content">
		<!-- General Settings Tab -->
		<div id="general-settings" class="tab-pane active">
			<?php require_once plugin_dir_path( __FILE__ ) . 'settings-general.php'; ?>
		</div>

		<!-- Domain Verification Tab -->
		<?php if (!empty($api_key)): ?>
		<div id="domain-verification" class="tab-pane">
			<?php require_once plugin_dir_path( __FILE__ ) . 'settings-domain.php'; ?>
		</div>
		<?php endif; ?>
	</div>

	<!-- Add improved tab switching JavaScript -->
	<script>
	jQuery(document).ready(function($) {
		// Show the first tab by default
		$('.tab-pane').hide();
		$('.tab-pane.active').show();

		// Handle tab clicks with improved animation
		$(document).on('click', '.nav-tab', function(e) {
			e.preventDefault();
			var target = $(this).attr('href');

			// Update active tab
			$('.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');

			// Show the selected tab content with fade effect
			$('.tab-pane').hide();
			$(target).fadeIn(300);
			
			// Save the active tab in session storage
			sessionStorage.setItem('somecaptions_active_tab', target);
		});
		
		// Restore active tab from session storage if available
		var activeTab = sessionStorage.getItem('somecaptions_active_tab');
		if (activeTab && $(activeTab).length) {
			$('.nav-tab[href="' + activeTab + '"]').trigger('click');
		}
		
		// Listen for the custom event when domain tab is loaded
		$(document).on('somecaptions_domain_tab_loaded', function() {
			// Re-initialize any scripts that might be needed for the domain tab
			console.log('Domain tab loaded and initialized');
		});
	});
	</script>

	<!-- Add enhanced tab styling -->
	<style>
	.tab-content {
		padding: 20px 0;
	}
	.tab-pane {
		display: none;
	}
	.tab-pane.active {
		display: block;
	}
	.nav-tab {
		transition: background-color 0.3s ease;
	}
	.nav-tab:hover {
		background-color: #f0f0f1;
	}
	.nav-tab-active {
		border-bottom: 1px solid #f0f0f1;
		background: #f0f0f1;
	}
	.settings-updated {
		margin-top: 15px;
	}
	</style>

</div>