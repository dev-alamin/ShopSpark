<?php
add_action(
	'shopspark_admin_settings_panel_product_page',
	function () {
		?>
	<h2 class="text-xl font-semibold mb-4"><?php _e( 'Product Page Settings', 'shopspark' ); ?></h2>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'shopspark_product_page_settings' );
		do_settings_sections( 'shopspark_product_page_settings' );
		submit_button( __( 'Save Settings', 'shopspark' ) );
		?>
	</form>
		<?php
	}
);
