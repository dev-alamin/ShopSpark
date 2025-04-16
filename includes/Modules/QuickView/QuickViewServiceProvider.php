<?php
namespace ShopSpark\Modules\QuickView;

use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\Modules\QuickView\Admin\Settings;

class QuickViewServiceProvider implements ServiceProviderInterface {

    public function __construct(){
        // add_filter( 'shopspark_admin_settings_tabs', [ $this, 'tab' ] );
        add_action( 'shopspark_admin_settings_panel_quick_view', [ $this, 'settings'] );
    }

    public function register(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('woocommerce_after_shop_loop_item', [$this, 'add_quick_view_button']);
    }

    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'shopspark-quickview',
            plugin_dir_url(__FILE__) . 'assets/quickview.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    public function add_quick_view_button(): void {
        echo '<a href="#" class="shopspark-quickview-btn">Quick View</a>';
    }

    public function tab(){
        $tabs['quick_view'] = __('Quick View', 'shopspark');
        return $tabs;
    }
    
    public function settings() {
        $options = get_option('shopspark_quick_view_settings', []);
        ?>
        <div class="max-w-5xl mx-auto">
         <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
            <?php _e('Quick View Settings', 'shopspark'); ?>
        </h2>
        <form method="post" action="options.php" class="space-y-6" x-data="{ 
            btnText: '<?php echo esc_js($options['quick_view_text'] ?? 'Quick View'); ?>', 
            modalSize: '<?php echo esc_js($options['quick_view_modal_size'] ?? 'medium'); ?>',
            imageSize: '<?php echo esc_js($options['quick_view_image_size'] ?? 'medium'); ?>',
            buttonColor: '<?php echo esc_js($options['quick_view_button_color'] ?? '#3b82f6'); ?>'
        }">
        <?php settings_fields('shopspark_quick_view_settings'); ?>
    
        <!-- Quick View Button Text Input -->
        <div>
            <div class="mb-4">
                <label for="quick_view_text" class="block text-sm font-medium text-gray-700 mb-1">
                    <?php _e('Quick View Button Text', 'shopspark'); ?>
                </label>
                <input
                    type="text"
                    id="quick_view_text"
                    name="shopspark_quick_view_settings[quick_view_text]"
                    x-model="btnText"
                    placeholder="<?php _e('e.g., Quick View', 'shopspark'); ?>"
                    class="w-full max-w-md px-4 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
            </div>
        </div>
    
        <!-- Quick View Modal Size -->
        <div>
            <label for="quick_view_modal_size" class="block text-sm font-medium text-gray-700 mb-1">
                <?php _e('Quick View Modal Size', 'shopspark'); ?>
            </label>
            <select 
                id="quick_view_modal_size" 
                name="shopspark_quick_view_settings[quick_view_modal_size]" 
                x-model="modalSize"
                class="w-full max-w-md px-4 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            >
                <option value="small"><?php _e('Small', 'shopspark'); ?></option>
                <option value="medium"><?php _e('Medium', 'shopspark'); ?></option>
                <option value="large"><?php _e('Large', 'shopspark'); ?></option>
            </select>
        </div>
    
        <!-- Quick View Image Size -->
        <div>
            <label for="quick_view_image_size" class="block text-sm font-medium text-gray-700 mb-1">
                <?php _e('Quick View Image Size', 'shopspark'); ?>
            </label>
            <select 
                id="quick_view_image_size" 
                name="shopspark_quick_view_settings[quick_view_image_size]" 
                x-model="imageSize"
                class="w-full max-w-md px-4 py-2 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            >
                <option value="small"><?php _e('Small', 'shopspark'); ?></option>
                <option value="medium"><?php _e('Medium', 'shopspark'); ?></option>
                <option value="large"><?php _e('Large', 'shopspark'); ?></option>
            </select>
        </div>
    
        <!-- Quick View Button Color -->
        <div>
            <label for="quick_view_button_color" class="block text-sm font-medium text-gray-700 mb-1">
                <?php _e('Quick View Button Color', 'shopspark'); ?>
            </label>
            <input 
                type="color" 
                id="quick_view_button_color" 
                name="shopspark_quick_view_settings[quick_view_button_color]" 
                x-model="buttonColor" 
                class="w-16 h-10 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            />
        </div>
    
        <!-- Save Button -->
        <button type="submit"
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition">
            <?php _e('Save Settings', 'shopspark'); ?>
        </button>
        </form>
        </div>
        <?php
    }
    
}
