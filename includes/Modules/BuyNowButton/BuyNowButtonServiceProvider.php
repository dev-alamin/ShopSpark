<?php 
namespace ShopSpark\Modules\BuyNowButton;

use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\TemplateFunctions;
use ShopSpark\Traits\HelperTrait;

/**
 * Class BuyNowButtonServiceProvider
 * 
 * @package ShopSpark
 */
class BuyNowButtonServiceProvider implements ServiceProviderInterface {
    protected array $settings;
    protected string $settings_field;

    use HelperTrait;

    public function __construct() {
        $this->settings = get_option( 'shopspark_product_page_buy_now_button', [] );
        $this->settings_field = 'shopspark_product_page_buy_now_button';
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register(): void {
        add_action( 'shopspark_admin_product_page_panel_buy_now_button', array( $this, 'settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueueAssets' ) );

        $button_position_hook = $this->settings['button_position'] ?? 'woocommerce_after_add_to_cart_form';
        
        // Add the button to the product page using hooks
        add_action( $button_position_hook, [ $this, 'render_button' ]);
    }

    /**
     * Render the Button
     *
     * @return void
     */
    public function render_button() {
        global $product;

        // Only show for purchasable and in-stock products
        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            return;
        }

        $product_id = $product->get_id();
        $is_variable = $product->is_type('variable');

        // Plugin options (replace with your actual settings)
        $options    = $this->settings;
        $btn_text   = $options['text'] ?? __('Buy Now', 'shopspark');
        $color      = $options['button_color'] ?? '#3b82f6';
        $text_color = $options['button_color_text_color'] ?? '#ffffff';
        $font_size  = $options['button_font_size'] ?? '16px';
        $position   = $options['button_position'] ?? 'center';
        $style      = $options['button_style'] ?? 'solid';
        $width      = $options['button_width'] ?? 'inherit';
        $height     = $options['button_height'] ?? 'inherit';
        $alignment  = $options['buy_now_btn_alignment'] ?? 'center';
        $margin     = $options['button_margin'] ?? '2px';
        $padding    = $options['button_padding'] ?? '8px';

        // Classes based on style
        $border_class = match($style) {
            'outline' => 'shopspark-border shopspark-border-current shopspark-bg-transparent shopspark-text-blue-600',
            'rounded' => 'shopspark-rounded-full',
            default => 'shopspark-bg-blue-600 shopspark-text-white',
        };

        $position_class = match($position) {
            'left' => 'shopspark-text-left',
            'right' => 'shopspark-text-right',
            default => 'shopspark-text-center',
        };

        $width_class = match($width) {
            'full' => 'shopspark-w-full',
            'auto' => 'shopspark-w-auto',
            default => (!empty($width) ? "shopspark-w-[" . trim($width) . "]" : ''),
        };

        $height_class = match($height) {
            'full' => 'shopspark-h-full',
            'auto' => 'shopspark-h-auto',
            default => (!empty($height) ? "shopspark-h-[" . trim($height) . "]" : ''),
        };

        // Alignment class
        $align_class = match($alignment) {
            'left'   => 'shopspark-text-left',
            'right'  => 'shopspark-text-right',
            'center' => 'shopspark-text-center',
            default  => (!empty($alignment) ? "shopspark-text-[" . trim($alignment) . "]" : ''),
        };

        $margin_class = TemplateFunctions::generate_tailwind_spacing_class( $margin, 'shopspark-m' );
        $padding_class = TemplateFunctions::generate_tailwind_spacing_class( $padding, 'shopspark-p' );

        $html_class = implode( " ", array_filter([
            $height_class,
            $width_class,
            $border_class,
            $align_class,
            $margin_class,
            $padding_class
        ]));

        if ( $is_variable ) {
            // Button triggers JS for variable product selection
            echo "<div class='{$position_class} shopspark-mt-2'>
                <button 
                    type='button' 
                    class='shopspark-buy-now-variable shopspark-px-4 shopspark-py-2 {$html_class}'
                    style='background-color: " . esc_attr($color) . "; font-size: " . esc_attr($font_size) . "; color: " . esc_attr($text_color) . ";' 
                    data-product-id='" . esc_attr($product_id) . "'>
                    " . esc_html($btn_text) . "
                </button>
            </div>";
        } else {
            // For simple products, direct checkout link
            $checkout_url = esc_url( add_query_arg( array(
                'add-to-cart' => $product_id,
                'quantity'    => 1,
            ), wc_get_checkout_url() ) );

            echo "<div class='{$position_class} shopspark-mt-2'>
                <a 
                    href='{$checkout_url}' 
                    class='shopspark-buy-now-simple shopspark-px-4 shopspark-py-2 shopspark-inline-block {$html_class}'
                    style='background-color: " . esc_attr($color) . "; font-size: " . esc_attr($font_size) . "; color: " . esc_attr($text_color) . "; text-decoration: none;'>
                    " . esc_html($btn_text) . "
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
        wp_enqueue_style( 'shopspark-frontend-tailwind' );
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
                <?php _e( 'Product Page – Variation & Tab Popup Settings', 'shopspark' ); ?>
            </h2>

            <form method="post" action="options.php" class="space-y-6"
                x-data="{ 
                    btnText: '<?php echo esc_js( $options['text'] ?? __( 'Buy Now', 'shopspark' ) ); ?>', 
                    buttonColor: '<?php echo esc_js( $options['button_color'] ?? '#3b82f6' ); ?>',
                    buttonBgColor: '<?php echo esc_js( $options['button_color_text_color'] ?? '#ffffff' ); ?>',
                    ButtonAlign: '<?php echo esc_js( $options['button_alignment'] ?? 'center' ); ?>',
                    width: '<?php echo esc_js( $options['button_width'] ?? '92px' ); ?>',
                    padding: '<?php echo esc_js( $options['button_padding'] ?? '12px' ); ?>',
                    margin: '<?php echo esc_js( $options['button_margin'] ?? '2px' ); ?>',
                    fontSize: '<?php echo esc_js( $options['button_font_size'] ?? '16px' ); ?>',
                    buttonStyle: '<?php echo esc_js( $options['button_style'] ?? 'solid' ); ?>',
                    buttonPosition: '<?php echo esc_js( $options['button_position'] ?? 'woocommerce_after_add_to_cart_form' ); ?>'
                }">

                <?php settings_fields( $this->settings_field ); ?>

                <?php
                echo TemplateFunctions::moduleDropdownField(
                    $this->settings_field . '[button_alignment]',
                    __( 'Button Alignment', 'shopspark' ),
                    array(
                        'center' => __( 'Center', 'shopspark' ),
                        'right'  => __( 'Right', 'shopspark' ),
                        'left'   => __( 'Left', 'shopspark' ),
                    ),
                    $options['button_alignment'] ?? 'center',
                    'ButtonAlign'
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
                    $this->settings_field . '[button_width]',
                    __( 'Button Width (e.g., 92px or 1rem)', 'shopspark' ),
                    'width',
                    '92px',
                    '!important',
                    '',
                    '',
                    '',
                    true
                );

                echo TemplateFunctions::moduleInputField(
                    $this->settings_field . '[button_padding]',
                    __( 'Button Padding (e.g., 10px or 1rem)', 'shopspark' ),
                    'padding',
                    '10px',
                    '!important',
                    '',
                    '',
                    '',
                    true
                );

                 echo TemplateFunctions::moduleInputField(
                    $this->settings_field . '[button_margin]',
                    __( 'Button Margin (e.g., 5px - Sequence top-right-bottom-left)', 'shopspark' ),
                    'margin',
                    '2px',
                    '!important',
                    '',
                    '',
                    '',
                    true
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
                    $this->All_WC_Product_Hooks(),
                    $options['button_position'] ?? 'woocommerce_after_add_to_cart_form',
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