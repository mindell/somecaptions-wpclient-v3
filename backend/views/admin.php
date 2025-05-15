<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   SomeCaptions_WPClient
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
	$api_key = cmb2_get_option(SW_TEXTDOMAIN . '-settings', 'api_key', '');
	$domain_verified = get_option(SW_TEXTDOMAIN . '-domain-verified', false);
	$gsc_connected = get_option(SW_TEXTDOMAIN . '-gsc-connected');
	$initialized = get_option(SW_TEXTDOMAIN . '-init');
	?>

	<!-- Add tab navigation -->
	<h2 class="nav-tab-wrapper">
		<a href="#general-settings" class="nav-tab nav-tab-active">General Settings</a>
		<?php if (!empty($api_key)): ?>
		<a href="#domain-verification" class="nav-tab">Domain Verification</a>
		<?php endif; ?>
		<?php if ($initialized): ?>
		<a href="#google-search-console" class="nav-tab">Google Search Console</a>
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

		<!-- Google Search Console Tab -->
		<?php if ($initialized): ?>
		<div id="google-search-console" class="tab-pane">
			<?php require_once plugin_dir_path( __FILE__ ) . 'settings-gsc.php'; ?>
		</div>
		<?php endif; ?>
	</div>

	<!-- Add tab switching JavaScript -->
	<script>
	jQuery(document).ready(function($) {
		// Show the first tab by default
		$('.tab-pane').hide();
		$('.tab-pane.active').show();

		// Handle tab clicks
		$('.nav-tab').on('click', function(e) {
			e.preventDefault();
			var target = $(this).attr('href');

			// Update active tab
			$('.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');

			// Show the selected tab content
			$('.tab-pane').hide();
			$(target).show();
		});
	});
	</script>

	<!-- Add tab styling -->
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
	</style>

</div>