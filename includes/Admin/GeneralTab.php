<?php
namespace ShopSpark\Admin;

use ShopSpark\TemplateFunctions;
class GeneralTab {
	public function __construct() {
		add_action( 'shopspark_admin_settings_panel_general', array( $this, 'tab' ) );
	}

	public function tab() {
		$modules = apply_filters(
			'shopspark_admin_settings_module',
			array(
                // Global 
                'buy_now'             => __( 'Buy Now', 'shopspark' ),
                'element_pusher'      => __( 'Element Pusher', 'shopspark' ),
                'x_more_items_for_fs' => __( 'X More Items for Free Shipping', 'shopspark' ),
                'seasonal_countdown'  => __( 'Seasonal Countdown', 'shopspark' ),
                'honeypot_field'      => __( 'Honeypot Field', 'shopspark' ),
                'lazy_load'           => __( 'Lazy Load', 'shopspark' ),
				// Shop / Archive Page
				'quick_view'              => __( 'Quick View', 'shopspark' ),
				'ajax_load_more'          => __( 'AJAX Load More or Infinite Scroll', 'shopspark' ),
				'product_filters'         => __( 'Product Filters with AJAX', 'shopspark' ),
				'grid_list_toggle'        => __( 'Grid/List Toggle', 'shopspark' ),

				// Single Product Page
                'variation_popup'         => __( 'Variation Popup', 'shopspark' ),
                'quantity_buttons'        => __( 'Quantity plus/minus buttons', 'shopspark' ),
                'variation_name_title'    => __( 'Variation name with title', 'shopspark' ),
                'description_toggle'      => __( 'Description with “Read More” toggle + shadow', 'shopspark' ),
                'size_guide_popup'        => __( 'Size guide popup', 'shopspark' ),
                'already_in_cart_message' => __( '“This product is already in cart” message', 'shopspark' ),
                'stock_quantity_display'  => __( 'Stock quantity display', 'shopspark' ),
                'product_tabs_popup'      => __( 'Product data tabs as popup', 'shopspark' ),
                'data_tab'                => __( 'Tabs', 'shopspark' ),
                'ajax_add_to_cart'        => __( 'Add To Cart', 'shopspark' ),
                'buy_now_button'          => __( 'Buy Now Button', 'shopspark' ),
                'qty_plus_minus'          => __( 'Qty +/- Button Beautifier', 'shopspark' ),
                'variation_individual'    => __( 'Each variation', 'shopspark' ),
                'product_data_popup'      => __( 'Details popup', 'shopspark' ),
                'other_variations'        => __( 'More options', 'shopspark' ),
                'desc_read_more'          => __( 'Read More', 'shopspark' ),
                'notify_me'               => __( 'Notify me', 'shopspark' ),
                'ask_question'            => __( 'Ask Question About Item', 'shopspark' ),
                'size_guide'              => __( 'Size guide', 'shopspark' ),
                'better_gallery'          => __( 'Better Gallery', 'shopspark' ),
                'already_in_cart'         => __( 'Item is already In cart', 'shopspark' ),
                'custom_data_tab'         => __( 'Extra tabs', 'shopspark' ),
                'users_want_this'         => __( 'Wishlist count', 'shopspark' ),
                'share_product'           => __( 'Share Options', 'shopspark' ),
                'x_person_is_viewing'     => __( 'X Person is viewing', 'shopspark' ),
                'countdown_fomo'          => __( 'FOMO Countdown', 'shopspark' ),
                'styles'                  => __( 'Styles', 'shopspark' ),

				// Cart Page
                'floating_cart'        => __( 'Floating Cart', 'shopspark' ),
                'quantity_update_cart' => __( 'Quantity Update', 'shopspark' ),
                'order_bump'           => __( 'Order Bump (AI)', 'shopspark' ),
                'edit_cart_popup'      => __( 'Edit Cart Item (Popup)', 'shopspark' ),
                'save_cart_later'      => __( 'Save Cart for Later', 'shopspark' ),
                'cart_abandon_timer'   => __( 'Cart Abandonment Timer', 'shopspark' ),

				// Global
				'custom_hooks'            => __( 'Push custom hooks (before title, after cart, etc.)', 'shopspark' ),
				'lazy_load_images'        => __( 'Lazy load product images', 'shopspark' ),

				// Account / Security
				'honeypot_field'          => __( 'Honeypot field for My Account page', 'shopspark' ),
			)
		);

		$settings = get_option( 'shopspark_general_settings', array() );

		// Grouping keys by section
		$grouped = array(
            'Global Features' => array(
                'buy_now',
                'element_pusher',
                'x_more_items_for_fs',
                'seasonal_countdown',
                'honeypot_field',
                'lazy_load',
            ),
			'Shop / Archive Page' => array(
				'quick_view',
				'ajax_load_more',
				'product_filters',
				'grid_list_toggle',
			),
			'Product Page' => array(
				'quantity_buttons',
                'variation_popup',
				'variation_name_title',
				'description_toggle',
				'size_guide_popup',
				'already_in_cart_message',
				'product_tabs_popup',
                'data_tab',
                'ajax_add_to_cart',
                'buy_now_button',
                'qty_plus_minus',
                'variation_individual',
                'product_data_popup',
                'other_variations',
                'desc_read_more',
                'notify_me',
                'ask_question',
                'size_guide',
                'better_gallery',
                'already_in_cart',
                'custom_data_tab',
                'users_want_this',
                'share_product',
                'x_person_is_viewing',
                'countdown_fomo',
                'styles',
			),
			'Cart Page'           => array(
				'floating_cart',
                'quantity_update_cart',
                'order_bump',
                'edit_cart_popup',
                'save_cart_later',
                'cart_abandon_timer',
			),
			'Account / Security'  => array(
				'honeypot_field',
			),
		);
        
        $grouped = apply_filters( 'shopspark_admin_settings_module_group', $grouped );
		?>
<div class="max-w-5xl mx-auto">
	<form method="post" action="options.php" x-data='{
	modules: 
		<?php
		echo json_encode(
			array_reduce(
				array_keys( $modules ),
				function ( $carry, $key ) use ( $settings ) {
					$carry[ $key ] = ! empty( $settings[ $key ] );
					return $carry;
				},
				array()
			)
		);
		?>
}' class="space-y-10">
		<?php settings_fields( 'shopspark_general_settings' ); ?>

		<?php foreach ( $grouped as $section => $keys ) : ?>
        <div>
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php echo esc_html( $section ); ?>
                <span class="ml-2 inline-block bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded-full">
                <?php
                $label = _n( 'Module', 'Modules', count( $keys ), 'shopspark' );
                $display_feature_count = sprintf( '%d %s', count( $keys ), $label );
                echo esc_html($display_feature_count ); ?></span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ( $keys as $key ) : ?>
                <div class="flex items-center justify-between bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
                    <span class="text-gray-800 font-medium"><?php echo esc_html( $modules[ $key ] ); ?></span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <!-- Hidden input for saving the checkbox value -->
                        <input type="hidden" name="shopspark_general_settings[<?php echo esc_attr( $key ); ?>]"
                            :value="modules['<?php echo esc_attr( $key ); ?>'] ? 1 : 0" />
                        <input type="checkbox" class="sr-only peer" x-model="modules['<?php echo esc_attr( $key ); ?>']"
                            :checked="modules['<?php echo esc_attr( $key ); ?>']"
                            <?php checked( ! empty( $settings[ $key ] ), true ); ?> />
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer transition-all"
                            :class="modules['<?php echo esc_attr( $key ); ?>'] ? 'bg-blue-600' : 'bg-gray-300'">
                        </div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all"
                            :class="modules['<?php echo esc_attr( $key ); ?>'] ? 'translate-x-5 border-blue-600' : 'translate-x-0 border-gray-300'"></div>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
		<?php endforeach; ?>

		<div class="text-right pt-6">
			<?php echo TemplateFunctions::saveButton(); ?>
		</div>
	</form>
</div>
		<?php
	}
}