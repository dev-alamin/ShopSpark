<?php
namespace ShopSpark\Admin;

use ShopSpark\TemplateFunctions;
class GeneralTab{
    public function __construct()
    {
        add_action( 'shopspark_admin_settings_panel_general', [ $this, 'tab' ] );
    }

    public function tab() {
        $modules = apply_filters('shopspark_admin_settings_tabs', [
            // Shop / Archive Page
            'quick_view'               => __('Quick View', 'shopspark'),
            'ajax_load_more'           => __('AJAX Load More or Infinite Scroll', 'shopspark'),
            'product_filters'          => __('Product Filters with AJAX', 'shopspark'),
            'grid_list_toggle'         => __('Grid/List Toggle', 'shopspark'),
    
            // Single Product Page
            'quantity_buttons'         => __('Quantity plus/minus buttons', 'shopspark'),
            'variation_name_title'     => __('Variation name with title', 'shopspark'),
            'description_toggle'       => __('Description with “Read More” toggle + shadow', 'shopspark'),
            'size_guide_popup'         => __('Size guide popup', 'shopspark'),
            'already_in_cart_message'  => __('“This product is already in cart” message', 'shopspark'),
            'stock_quantity_display'   => __('Stock quantity display', 'shopspark'),
            'product_tabs_popup'       => __('Product data tabs as popup', 'shopspark'),
    
            // Cart Page
            'floating_cart'            => __('Floating/Side Cart', 'shopspark'),
            'quantity_update_cart'     => __('Quantity update in cart (with +/- icons)', 'shopspark'),
            'cart_notes'               => __('Cart Notes', 'shopspark'),
    
            // Global
            'custom_hooks'             => __('Push custom hooks (before title, after cart, etc.)', 'shopspark'),
            'lazy_load_images'         => __('Lazy load product images', 'shopspark'),
    
            // Account / Security
            'honeypot_field'           => __('Honeypot field for My Account page', 'shopspark'),
        ]);
    
        $settings = get_option('shopspark_general_settings', []);
    
        // Grouping keys by section
        $grouped = [
            'Shop / Archive Page' => [
                'quick_view', 'ajax_load_more', 'product_filters', 'grid_list_toggle'
            ],
            'Single Product Page' => [
                'quantity_buttons', 'variation_name_title', 'description_toggle',
                'size_guide_popup', 'already_in_cart_message',
                'stock_quantity_display', 'product_tabs_popup'
            ],
            'Cart Page' => [
                'floating_cart', 'quantity_update_cart', 'cart_notes'
            ],
            'Global Features' => [
                'custom_hooks', 'lazy_load_images'
            ],
            'Account / Security' => [
                'honeypot_field'
            ]
        ];
        ?>
<div class="max-w-5xl mx-auto">
    <form method="post" action="options.php" x-data='{
    modules: <?php echo json_encode(array_reduce(array_keys($modules), function($carry, $key) use ($settings) {
        $carry[$key] = !empty($settings[$key]);
        return $carry;
    }, [])); ?>
}' class="space-y-10">
        <?php settings_fields('shopspark_general_settings'); ?>

        <?php foreach ($grouped as $section => $keys): ?>
        <div>
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php echo esc_html($section); ?>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($keys as $key): ?>
                <div class="flex items-center justify-between bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
                    <span class="text-gray-800 font-medium"><?php echo esc_html($modules[$key]); ?></span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <!-- Hidden input for saving the checkbox value -->
                        <input type="hidden" name="shopspark_general_settings[<?php echo esc_attr($key); ?>]"
                            :value="modules['<?php echo esc_attr($key); ?>'] ? 1 : 0" />
                        <input type="checkbox" class="sr-only peer" x-model="modules['<?php echo esc_attr($key); ?>']"
                            :checked="modules['<?php echo esc_attr($key); ?>']"
                            <?php checked( !empty($settings[$key]), true ); ?> />
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer transition-all peer-checked:bg-blue-600">
                        </div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all"
                            :class="modules['<?php echo esc_attr($key); ?>'] ? 'translate-x-5' : 'translate-x-0'"></div>
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