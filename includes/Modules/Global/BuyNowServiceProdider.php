<?php 
namespace ShopSpark\Modules\Global;

use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\TemplateFunctions;
use ShopSpark\Traits\HelperTrait;

/**
 * Class BuyNowServiceProdider
 * 
 * @package ShopSpark
 */
class BuyNowServiceProdider implements ServiceProviderInterface {
    protected array $settings;
    protected string $settings_field;

    use HelperTrait;

    public function __construct() {
        $this->settings = get_option( 'shopspark_global_buy_now', [] );
        $this->settings_field = 'shopspark_global_buy_now';
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register(): void {
        add_action( 'shopspark_admin_global_panel_buy_now', array( $this, 'settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueueAssets' ) );

        $button_position_hook = $this->settings['button_position'] ?? 'woocommerce_after_shop_loop_item_title';
        
        // Add the button to the product page using hooks
        add_action( $button_position_hook, [ $this, 'render_button' ]);

        /**
         * Add the shortcode
         * init hook is needed to ensure we are ready to register shortcodes
         * and that WooCommerce is loaded
         * Otherwise, we may get a fatal error
         */
        add_action( 'init', function() {
            if( ! shortcode_exists( 'shopspark_buy_now_button' ) ) {
                add_shortcode( 'shopspark_buy_now_button', [ $this, 'render_shortcode' ] );
            }
        });
    }

    /**
     * Render the shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_shortcode( $atts ): string {
        global $product;

        // Only show for simple and variable products
        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) return '';

        ob_start();
        $this->render_button();
        return ob_get_clean();
    }

    /**
     * Render the Button
     *
     * @return void
     */
    function render_button() {
        global $product;

        // Only show for simple and variable products
        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) return;

        $product_id = $product->get_id();
        $is_variable = $product->is_type('variable');

        // Optional: Get custom settings from your plugin options
        $options    = $this->settings;                                   // Replace with your actual settings field
        $btn_text   = $options['text'] ?? __('Buy Now', 'shopspark');
        $color      = $options['button_color'] ?? '#3b82f6';
        $text_color = $options['button_color_text_color'] ?? '#ffffff';
        $font_size  = $options['button_font_size'] ?? '16px';
        $position   = $options['button_position'] ?? 'center';
        $style      = $options['button_style'] ?? 'solid';

        // Classes based on style
        $style_class = match($style) {
            'outline' => 'border border-current bg-transparent text-blue-600',
            'rounded' => 'rounded-full',
            default => 'bg-blue-600 text-white',
        };

        $align_class = match($position) {
            'left' => 'text-left',
            'right' => 'text-right',
            default => 'text-center',
        };

        if ( $is_variable ) {
            // We handle this via JS (because variation must be selected first)
            echo "<div class='$align_class mt-2'>
                <button 
                    type='button' 
                    class='shopspark-buy-now-variable px-4 py-2 $style_class'
                    style='background-color: $color; font-size: $font_size; color: $text_color;' 
                    data-product-id='$product_id'>
                    $btn_text
                </button>
            </div>";
        } else {
            // For simple products, we can directly send to checkout
            $checkout_url = esc_url( add_query_arg( array(
                'add-to-cart' => $product_id,
                'quantity'    => 1
            ), wc_get_checkout_url() ) );

            echo "<div class='$align_class mt-2'>
                <a 
                    href='$checkout_url' 
                    class='shopspark-buy-now-simple px-4 py-2 inline-block $style_class'
                    style='background-color: $color; font-size: $font_size; color: $text_color; text-decoration: none;'>
                    $btn_text
                </a>
            </div>";
        }
    }

    /**
     * Enqueue assets
     *
     * @return void
     */
    public function enqueueAssets(): void {
        wp_enqueue_style( 'shopspark-buy-now-button-css', SHOP_SPARK_PLUGIN_ASSETS_URL . 'buy-now-button/buy-now-button.css', array(), SHOP_SPARK_VERSION );
        wp_enqueue_script( 'shopspark-buy-now-button-js', SHOP_SPARK_PLUGIN_ASSETS_URL . 'buy-now-button/buy-now-button.js', array( 'jquery' ), SHOP_SPARK_VERSION, true );
    }

    /**
     * Settings
     *
     * @return void
     */
    public function settings(){
        $options = $this->settings ?? [];

        ?>
        <div class="max-w-5xl mx-auto">
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php _e( 'Shop Page - Buy Now Button', 'shopspark' ); ?>
            </h2>

            <form method="post" action="options.php" class="space-y-6"
                x-data="{ 
                    btnText: '<?php echo esc_js( $options['text'] ?? __( 'Buy Now', 'shopspark' ) ); ?>', 
                    buttonColor: '<?php echo esc_js( $options['button_color'] ?? '#3b82f6' ); ?>',
                    buttonBgColor: '<?php echo esc_js( $options['button_color_text_color'] ?? '#ffffff' ); ?>',
                    variationPopupAlign: '<?php echo esc_js( $options['ajax_add_to_cart_alignment'] ?? 'center' ); ?>',
                    fontSize: '<?php echo esc_js( $options['button_font_size'] ?? '16px' ); ?>',
                    buttonStyle: '<?php echo esc_js( $options['button_style'] ?? 'solid' ); ?>',
                    buttonPosition: '<?php echo esc_js( $options['button_position'] ?? 'woocommerce_after_shop_loop_item_title' ); ?>'
                }">

                <?php settings_fields( $this->settings_field ); ?>

                <?php
                echo TemplateFunctions::moduleCheckboxField(
                    $this->settings_field . '[enable_ajax_add_to_cart]',
                    __( 'Enable Variation Selection as Popup/Sidebar', 'shopspark' ),
                    $options['enable_ajax_add_to_cart'] ?? false
                );

                echo TemplateFunctions::moduleDropdownField(
                    $this->settings_field . '[ajax_add_to_cart_alignment]',
                    __( 'Popup Alignment', 'shopspark' ),
                    array(
                        'center' => __( 'Center Modal', 'shopspark' ),
                        'right'  => __( 'Sidebar Right', 'shopspark' ),
                        'left'   => __( 'Sidebar Left', 'shopspark' ),
                    ),
                    $options['ajax_add_to_cart_alignment'] ?? 'center',
                    'variationPopupAlign'
                );

                echo TemplateFunctions::moduleInputField(
                    $this->settings_field . '[text]',
                    __( 'Button Text', 'shopspark' ),
                    'btnText',
                    'Buy Now',
                    '!important',
                    __( 'e.g., Select Option', 'shopspark' ),
                    '',
                    '',
                    true
                );

                echo TemplateFunctions::moduleDropdownField(
                    $this->settings_field . '[button_style]',
                    __( 'Button Style', 'shopspark' ),
                    array(
                        'solid'   => __( 'Solid', 'shopspark' ),
                        'outline' => __( 'Outline', 'shopspark' ),
                        'rounded' => __( 'Rounded', 'shopspark' ),
                    ),
                    $options['button_style'] ?? 'solid',
                    'buttonStyle'
                );

                echo TemplateFunctions::moduleInputField(
                    $this->settings_field . '[button_font_size]',
                    __( 'Button Font Size (e.g., 14px or 1rem)', 'shopspark' ),
                    'fontSize',
                    '16px',
                    '!important',
                    '',
                    '',
                    '',
                    true
                );

                echo TemplateFunctions::moduleDropdownField(
                    $this->settings_field . '[button_position]',
                    __( 'Button Position', 'shopspark' ),
                    $this->All_WC_Archive_Loop_Hooks(),
                    $options['button_position'] ?? 'woocommerce_after_shop_loop_item_title',
                    'buttonPosition'
                );

                echo TemplateFunctions::moduleColorPickerField(
                    $this->settings_field . '[button_color]',
                    __( 'Button Background Color', 'shopspark' ),
                    $options['button_color'] ?? '#3b82f6',
                    'buttonColor'
                );

                echo TemplateFunctions::moduleColorPickerField(
                    $this->settings_field . '[button_color_text_color]',
                    __( 'Button Text Color', 'shopspark' ),
                    $options['button_color_text_color'] ?? '#fff',
                    'buttonBgColor'
                );
                ?>

                <?php echo TemplateFunctions::saveButton(); ?>
            </form>
        </div>
        <?php
    }
}