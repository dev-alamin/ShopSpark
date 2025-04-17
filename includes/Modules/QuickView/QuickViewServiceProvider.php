<?php
namespace ShopSpark\Modules\QuickView;

use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\Modules\QuickView\Admin\Settings;
use ShopSpark\TemplateFunctions;

class QuickViewServiceProvider implements ServiceProviderInterface {

    protected array $settings = [];

    public function __construct(){
        $this->settings = get_option('shopspark_quick_view_settings', []);

        // add_filter( 'shopspark_admin_settings_tabs', [ $this, 'tab' ] );
        add_action( 'shopspark_admin_settings_panel_quick_view', [ $this, 'settings'] );
    }

    public function register(): void {
        $hook = $this->mapHook() ? $this->mapHook() : 'woocommerce_after_shop_loop_item';

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);


        add_action( $hook, [$this, 'add_quick_view_button']);

        add_action( 'wp_head', [ $this, 'render_quick_view_modal' ] );

        add_action('wp_ajax_shopspark_quick_view', [ $this, 'shopspark_handle_quick_view' ]);
        add_action('wp_ajax_nopriv_shopspark_quick_view', [ $this, 'shopspark_handle_quick_view' ]);
    }

    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'shopspark-quickview',
            plugin_dir_url(__FILE__) . 'Assets/quickview.js',
            ['jquery'],
            '1.0.0',
            true
        );
        

        wp_localize_script('shopspark-quickview', 'shopspark_ajax', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);        

        // Tailwind CDN
        wp_enqueue_style('shopspark-tailwind', '//cdn.jsdelivr.net/npm/@tailwindcss/browser@4', [], '3.4.1');

        // Tailwind JS 
        wp_enqueue_script( 'shopspark-tailwindjs', '//cdn.jsdelivr.net/npm/@tailwindcss/browser@4' );

        // Alpine JS CDN
        wp_enqueue_script('shopspark-alpine', '//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);

        wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
        
        wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);

    }

    public function add_quick_view_button(): void {
        global $product;
        if ( ! $product ) {
            return;
        }
    
        $options = $this->settings;
        $color = $options['quick_view_button_color'] ?? '#3b82f6';
        $text = $options['quick_view_text'] ?? __('Quick View', 'shopspark');
        $alignment = $options['quick_view_button_alignment'] ?? 'center';
        
        $product_id = $product->get_id();
        
        $alignment_class = '';
        switch ($alignment) {
            case 'Left':
            $alignment_class = 'text-left';
            break;
            case 'Right':
            $alignment_class = 'text-right';
            break;
            case 'Center':
            default:
            $alignment_class = 'text-center';
            break;
        }
        
        echo sprintf(
            '<div class="%s">
            <button 
                class="shopspark-quick-view-btn px-3 py-1.5 text-sm rounded-lg" 
                style="background-color: %s; color: #fff;" 
                data-product-id="%d">
                %s
            </button>
            </div>',
            esc_attr( $alignment_class ? $alignment_class : 'text-left' ),
            esc_attr( $color ),
            esc_attr( $product_id ),
            esc_html( $text )
        );
    }

    public function render_quick_view_modal(): void {
        ?>
        <div id="shopspark-quick-view-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">

            <div class="bg-white w-full max-w-3xl rounded-2xl p-6 shadow-xl relative">
                <button class="absolute top-4 right-4 text-gray-500 hover:text-black text-2xl font-bold"
                    id="shopspark-quick-view-close">
                    &times;
                </button>
                <div id="shopspark-quick-view-content" class="min-h-[200px] flex items-center justify-center gap-5">
                    <!-- Product content will load here -->

                </div>

            </div>
        </div>

        <?php
    }

    public function shopspark_handle_quick_view() {
        $product_id = absint($_GET['product_id'] ?? 0);
        
        if (!$product_id || !function_exists('wc_get_product')) {
            wp_send_json_error('Invalid product.');
            wp_die();
        }
    
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error('Product not found.');
            wp_die();
        }
    
        // Output the full content for the quick view modal
        $this->render_product_images($product);
        $this->render_product_info($product);
        
        wp_die();
    }
    
    // Render product gallery images with small images below main image
    private function render_product_images($product) {
        $main_image_id = $product->get_image_id();
        $gallery_images = $product->get_gallery_image_ids();
    
        if ($main_image_id) {
            array_unshift($gallery_images, $main_image_id);
        }
    
        ?>
        <div class="product-gallery">
            <!-- Main Gallery -->
            <div class="swiper main-gallery mb-4 max-w-xl mx-auto">
                <div class="swiper-wrapper">
                    <?php foreach ($gallery_images as $img_id): ?>
                        <div class="swiper-slide">
                            <?php echo wp_get_attachment_image($img_id, 'large', false, [
                                'class' => 'rounded-xl max-h-[400px] w-full object-contain mx-auto'
                            ]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
    
            <!-- Thumbnail Gallery (Below Main Image) -->
            <div class="swiper thumb-gallery mt-4 max-w-xl mx-auto">
                <div class="swiper-wrapper flex gap-3">
                    <?php foreach ($gallery_images as $img_id): ?>
                        <div class="swiper-slide w-24 h-24 !flex items-center justify-center border rounded-lg overflow-hidden hover:border-purple-600 transition">
                            <?php echo wp_get_attachment_image($img_id, 'thumbnail', false, [
                                'class' => 'object-cover max-h-full max-w-full'
                            ]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Render product info (name, price, etc.)
    private function render_product_info($product) {
        ?>
        <div class="product-info mt-6 md:mt-0 md:w-1/2">
            <h2 class="text-xl font-bold mb-2"><?php echo esc_html($product->get_name()); ?></h2>
            <div class="text-purple-600 font-semibold mb-4"><?php echo $product->get_price_html(); ?></div>
            <div class="text-sm text-gray-700 mb-4"><?php echo wpautop($product->get_short_description()); ?></div>

            <?php 
                    $this->render_product_variations($product);
                    $this->render_product_description($product);
            ?>
        </div>
        <?php
    }
    
    // Render product variations (for variable products)
    private function render_product_variations($product) {
        if ($product->is_type('variable')) {
            $attributes = $product->get_variation_attributes();
            $available_variations = $product->get_available_variations();
            $product_id = $product->get_id();
            ?>
            <div class="product-variations mt-6">
                <h3 class="text-lg font-semibold mb-2">Select Variation</h3>
                <form class="variations_form" method="post" enctype="multipart/form-data"
                    data-product_id="<?php echo esc_attr($product_id); ?>"
                    data-product_variations="<?php echo esc_attr(json_encode($available_variations)); ?>">

                    <?php foreach ($attributes as $attr_name => $options) : ?>
                        <div class="variation-option mb-4">
                            <label for="<?php echo esc_attr($attr_name); ?>" class="block font-medium mb-1">
                                <?php echo wc_attribute_label($attr_name); ?>
                            </label>
                            <select name="attribute_<?php echo esc_attr(sanitize_title($attr_name)); ?>"
                                    id="<?php echo esc_attr($attr_name); ?>"
                                    class="w-full border border-gray-300 rounded px-3 py-2">
                                <option value="">Choose an option</option>
                                <?php foreach ($options as $option) : ?>
                                    <option value="<?php echo esc_attr($option); ?>">
                                        <?php echo esc_html(wc_attribute_label($option, $product)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>

                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
                    <input type="hidden" name="variation_id" class="variation_id" value="">

                    <button type="submit"
                        class="add-to-cart-btn mt-4 inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <?php esc_html_e( 'Add to Cart', 'shopspark' ); ?>
                    </button>
                </form>
            </div>
            <?php
        } else {
            // For simple products
            ?>
            <form method="post" class="simple_add_to_cart_form">
                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>">
                <button type="submit"
                    class="add-to-cart-btn mt-4 inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <?php esc_html_e( 'Add to Cart', 'shopspark' ); ?>
                </button>
            </form>
            <?php
        }
    }

    
    // Render full product description
    private function render_product_description($product) {
        ?>
        <div class="product-description mt-6">
            <h3 class="text-lg font-semibold mb-2"><?php esc_html_e( 'Product Description', 'shopspark' ); ?></h3>
            <div class="description-text text-sm">
                <?php echo wpautop(wp_trim_words( $product->get_description(), 20, ' ' )); ?>
            </div>
        </div>
        <?php
    }
    

    private function mapHook(){
        $options = $this->settings;
        $position = $options['quick_view_button_position'];
        $hook = '';

        // Map the human-readable value to WooCommerce hooks
        switch ($position) {
            case 'Before Product Link Start':
                $hook = 'woocommerce_before_shop_loop_item';
                break;

            case 'Before Product Title':
                $hook = 'woocommerce_before_shop_loop_item_title';
                break;

            case 'Before Product Price':
                $hook = 'woocommerce_before_shop_loop_item_title';
                break;

            case 'After Product Title':
                $hook = 'woocommerce_after_shop_loop_item_title';
                break;

            case 'After Product Link End':
                $hook = 'woocommerce_after_shop_loop_item';
                break;

            default:
                $hook = 'woocommerce_after_shop_loop_item';
                break;
        }

        return $hook;
    }
    

    public function tab(){
        $tabs['quick_view'] = __('Quick View', 'shopspark');
        return $tabs;
    }
    
    public function settings() {
            $options = $this->settings;
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
        <?php
                    echo TemplateFunctions::moduleInputField(
                        'shopspark_quick_view_settings[quick_view_text]',
                        __('Quick View Button Text', 'shopspark'),
                        'btnText',
                        'Quick View',
                        '!important',
                        __('e.g., Quick View', 'shopspark'),
                        '',
                        '',
                        true
                    );

                    // Quick View Modal Size Dropdown
                    echo TemplateFunctions::moduleDropdownField(
                        'shopspark_quick_view_settings[quick_view_modal_size]',
                        __('Quick View Modal Size', 'shopspark'),
                        ['small', 'medium', 'large'],
                        $options['quick_view_modal_size'] ?? 'medium',
                        'modalSize'
                    );

                 // Quick View Button Position
                    echo TemplateFunctions::moduleDropdownField(
                        'shopspark_quick_view_settings[quick_view_button_position]',
                        __('Quick View Button Position', 'shopspark'),
                        [
                            'woocommerce_before_shop_loop_item'        => __('Before Product Link Start', 'shopspark'),
                            'woocommerce_before_shop_loop_item_title'  => __('Before Product Title', 'shopspark'),
                            'woocommerce_shop_loop_item_title'         => __('Product Title', 'shopspark'),
                            'woocommerce_after_shop_loop_item_title'   => __('After Product Title', 'shopspark'),
                            'woocommerce_after_shop_loop_item'         => __('After Product Link End', 'shopspark'),
                        ],
                        $options['quick_view_button_position'] ?? 'woocommerce_after_shop_loop_item',
                        'buttonPosition'
                    );

                    // Quick View Button Position, Left or Right, center
                    echo TemplateFunctions::moduleDropdownField(
                        'shopspark_quick_view_settings[quick_view_button_alignment]',
                        __('Quick View Button Position', 'shopspark'),
                        [
                            'left' => __('Left', 'shopspark'),
                            'right' => __('Right', 'shopspark'),
                            'center' => __('Center', 'shopspark'),
                        ],
                        $options['quick_view_button_alignment'] ?? 'center',
                        'buttonPosition'
                    );

                    // Quick View Image Size Dropdown
                    echo TemplateFunctions::moduleDropdownField(
                        'shopspark_quick_view_settings[quick_view_image_size]',
                        __('Quick View Image Size', 'shopspark'),
                        ['small', 'medium', 'large'],
                        $options['quick_view_image_size'] ?? 'medium',
                        'imageSize'
                    );
                    ?>

        <!-- Quick View Button Color -->
        <div>
            <label for="quick_view_button_color" class="block text-sm font-medium text-gray-700 mb-1">
                <?php _e('Quick View Button Color', 'shopspark'); ?>
            </label>
            <input type="color" id="quick_view_button_color"
                name="shopspark_quick_view_settings[quick_view_button_color]" x-model="buttonColor"
                class="w-16 h-10 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
        </div>

        <!-- Save Button -->
        <?php echo TemplateFunctions::saveButton(); ?>

    </form>
</div>
<?php
    }
    
}