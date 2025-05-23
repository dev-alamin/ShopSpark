<?php
namespace ShopSpark\Admin;

use ShopSpark\Admin\GeneralTab;
use ShopSpark\Admin\Helper;

class Menu {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'shopspark_register_settings' ) );
        new GeneralTab();
    }

    public function add_admin_menu(): void {
        add_menu_page(
            __( 'ShopSpark Settings', 'shopspark' ),
            __( 'ShopSpark', 'shopspark' ),
            'manage_options',
            'shopspark',
            array( $this, 'page' ),
            'dashicons-admin-generic',
            100
        );
    }

    public function page(): void {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        $modules    = apply_filters(
            'shopspark_admin_settings_tabs',
            array(
                'general'       => __( 'General', 'shopspark' ),
                'global'        => __( 'Global', 'shopspark' ),
                'product_page'  => __( 'Product Page', 'shopspark' ),
                'shop_page'     => __( 'Shop Page', 'shopspark' ),
                'cart_page'     => __( 'Cart Page', 'shopspark' ),
                'checkout_page' => __( 'Checkout Page', 'shopspark' ),
                'security'      => __( 'Performance & Security', 'shopspark' ),
            )
        );
        ?>
        <div class="wrap bg-gradient-to-r from-white via-white to-gray-50 rounded-lg shadow-md" x-data="{ tab: '<?php echo esc_attr( $active_tab ); ?>' }">

            <h1 class="!text-4xl !font-bold !mb-4 !mt-4 !pt-6 text-center"><?php _e( 'ShopSpark Settings', 'shopspark' ); ?></h1>

            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <?php foreach ( $modules as $key => $label ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=shopspark&tab=' . $key ) ); ?>"
                        class="px-4 py-2 font-medium text-lg border-b-2 <?php echo $active_tab === $key ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-transparent hover:border-gray-300'; ?>">
                            <?php echo esc_html( $label ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="bg-gradient-to-r from-indigo-50 via-purple-100 to-pink-50 p-6 rounded-lg">

                <?php
                if ( 'shop_page' === $active_tab ) {
                $nested_tabs = apply_filters(
                    'shopspark_admin_shop_page_nested_tabs',
                    [
                        'quick_view' => __( 'Quick View', 'shopspark' ),
                        'pagination' => __( 'Pagination', 'shopspark' ),
                    ]
                );


                Helper::render_nested_tabs('admin_shop_page', $nested_tabs);
            }
            elseif( 'product_page' === $active_tab ) {
                    // Get nested tabs via filter, fallback to example tabs
                    $nested_tabs = apply_filters(
                        'shopspark_admin_product_page_nested_tabs',
                        array(
                            'data_tab'             => __( 'Tabs', 'shopspark' ),
                            'ajax_add_to_cart'     => __( 'Add To Cart', 'shopspark' ),
                            'buy_now_button'       => __( 'Buy Now', 'shopspark' ),
                            'variation_popup'      => __( 'Variation popup', 'shopspark' ),
                            'qty_plus_minus'       => __( 'Qty +/-', 'shopspark' ),
                            'variation_name_title' => __( 'Variation in title', 'shopspark' ),
                            'variation_individual' => __( 'Each variation', 'shopspark' ),
                            'product_data_popup'   => __( 'Details popup', 'shopspark' ),
                            'other_variations'     => __( 'More options', 'shopspark' ),
                            'desc_read_more'       => __( 'Read More', 'shopspark' ),
                            'notify_me'            => __( 'Notify me', 'shopspark' ),
                            'ask_question'         => __( 'Ask', 'shopspark' ),
                            'size_guide'           => __( 'Size guide', 'shopspark' ),
                            'better_gallery'       => __( 'Gallery', 'shopspark' ),
                            'already_in_cart'      => __( 'In cart', 'shopspark' ),
                            'custom_data_tab'      => __( 'Extra tabs', 'shopspark' ),
                            'users_want_this'      => __( 'Wishlist count', 'shopspark' ),
                            'share_product'        => __( 'Share', 'shopspark' ),
                            'x_person_is_viewing'  => __( 'X Person is viewing', 'shopspark' ),
                            'countdown_fomo'       => __( 'Countdown', 'shopspark' ),
                            'styles'               => __( 'Styles', 'shopspark' ),
                        )
                    );

                    Helper::render_nested_tabs('admin_product_page', $nested_tabs);
                }elseif( 'global' === $active_tab ) {
                    // Get nested tabs via filter, fallback to example tabs
                    $nested_tabs = apply_filters(
                        'shopspark_admin_global_nested_tabs',
                        array(
                            'buy_now'             => __( 'Buy Now', 'shopspark' ),
                            'element_pusher'      => __( 'Element Pusher', 'shopspark' ),
                            'x_more_items_for_fs' => __( 'X More Items for Free Shipping', 'shopspark' ),
                            'seasonal_countdown'  => __( 'Seasonal Countdown', 'shopspark' ),
                            'honeypot_field'      => __( 'Honeypot Field', 'shopspark' ),
                            'lazy_load'           => __( 'Lazy Load', 'shopspark' ),
                        )
                    );

                    Helper::render_nested_tabs('admin_global', $nested_tabs);
                }elseif( 'checkout_page' === $active_tab ) {
                    // Get nested tabs via filter, fallback to example tabs
                    $nested_tabs = apply_filters(
                        'shopspark_admin_checkout_page_nested_tabs',
                        array(
                            'delivery_time_selection' => __( 'Delivery Time Selection', 'shopspark' ),
                            'trust_badge'             => __( 'Trust Badge', 'shopspark' ),
                            'post_purchase_upsell'    => __( 'Post Purchase Upsell', 'shopspark' ),
                        )
                    );

                    Helper::render_nested_tabs('admin_checkout_page', $nested_tabs);
                }elseif( 'cart_page' === $active_tab ) {
                    // Get nested tabs via filter, fallback to example tabs
                    $nested_tabs = apply_filters(
                        'shopspark_admin_cart_page_nested_tabs',
                        array(
                            'floating_cart'        => __( 'Floating Cart', 'shopspark' ),
                            'quantity_update_cart' => __( 'Quantity Update', 'shopspark' ),
                            'order_bump'           => __( 'Order Bump (AI)', 'shopspark' ),
                            'edit_cart_popup'      => __( 'Edit Cart Item (Popup)', 'shopspark' ),
                            'save_cart_later'      => __( 'Save Cart for Later', 'shopspark' ),
                            'cart_abandon_timer'   => __( 'Cart Abandonment Timer', 'shopspark' ),
                        )
                    );

                    Helper::render_nested_tabs('admin_cart_page', $nested_tabs);
                }
                else {
                    // Default: load tab content for other main tabs
                    do_action( "shopspark_admin_settings_panel_{$active_tab}" );
                }
                ?>

            </div>
        </div>
        <?php
    }


    function shopspark_register_settings() {
        // General tab
        register_setting( 'shopspark_general_settings', 'shopspark_general_settings' );

        // Shop Page tab
        register_setting( 'shopspark_quick_view_settings', 'shopspark_quick_view_settings' );

        // Product Page tab
        register_setting( 'shopspark_product_page_tab_popup', 'shopspark_product_page_tab_popup' );
        register_setting( 'shopspark_product_page_variation_popup', 'shopspark_product_page_variation_popup' );
        register_setting( 'shopspark_product_page_qty_plus_minus', 'shopspark_product_page_qty_plus_minus' );
        register_setting( 'shopspark_product_page_variation_name_title', 'shopspark_product_page_variation_name_title' );
        register_setting( 'shopspark_product_page_variation_individual', 'shopspark_product_page_variation_individual' );
        register_setting( 'shopspark_product_page_product_data_popup', 'shopspark_product_page_product_data_popup' );
        register_setting( 'shopspark_product_page_other_variations', 'shopspark_product_page_other_variations' );
        register_setting( 'shopspark_product_page_desc_read_more', 'shopspark_product_page_desc_read_more' );
        register_setting( 'shopspark_product_page_notify_me', 'shopspark_product_page_notify_me' );
        register_setting( 'shopspark_product_page_ask_question', 'shopspark_product_page_ask_question' );
        register_setting( 'shopspark_product_page_buy_now_button', 'shopspark_product_page_buy_now_button' );

        // Global tab
        register_setting( 'shopspark_global_settings', 'shopspark_global_settings' );
        register_setting( 'shopspark_global_buy_now', 'shopspark_global_buy_now' );
        register_setting( 'shopspark_global_element_pusher', 'shopspark_global_element_pusher' );
        register_setting( 'shopspark_global_x_more_items_for_fs', 'shopspark_global_x_more_items_for_fs' );
        register_setting( 'shopspark_global_seasonal_countdown', 'shopspark_global_seasonal_countdown' );
        register_setting( 'shopspark_global_honeypot_field', 'shopspark_global_honeypot_field' );
        register_setting( 'shopspark_global_lazy_load', 'shopspark_global_lazy_load' );

        // Cart Page tab
        register_setting( 'shopspark_cart_page_floating_cart', 'shopspark_cart_page_floating_cart' );
        register_setting( 'shopspark_cart_page_quantity_update_cart', 'shopspark_cart_page_quantity_update_cart' );
        register_setting( 'shopspark_cart_page_order_bump', 'shopspark_cart_page_order_bump' );
        register_setting( 'shopspark_cart_page_edit_cart_popup', 'shopspark_cart_page_edit_cart_popup' );
        register_setting( 'shopspark_cart_page_save_cart_later', 'shopspark_cart_page_save_cart_later' );
        register_setting( 'shopspark_cart_page_cart_abandon_timer', 'shopspark_cart_page_cart_abandon_timer' );

        // Checkout Page tab
        register_setting( 'shopspark_checkout_page_delivery_time_selection', 'shopspark_checkout_page_delivery_time_selection' );
        register_setting( 'shopspark_checkout_page_trust_badge', 'shopspark_checkout_page_trust_badge' );
        register_setting( 'shopspark_checkout_page_post_purchase_upsell', 'shopspark_checkout_page_post_purchase_upsell' );

        // Security tab
        register_setting( 'shopspark_security_settings', 'shopspark_security_settings' );
    }
}