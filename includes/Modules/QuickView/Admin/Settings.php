<?php

add_action(
	'shopspark_admin_settings_panel_quick_view',
	function () {
		?>
	<h2 class="text-xl font-semibold mb-4"><?php _e( 'Quick View Settings', 'shopspark' ); ?></h2>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'shopspark_quick_view_settings' );
		do_settings_sections( 'shopspark_quick_view_settings' );
		submit_button( __( 'Save Settings', 'shopspark' ) );
		?>
	</form>
		<?php
	}
);
