<?php
namespace ShopSpark\Modules\Global;

use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\Traits\HelperTrait;
use ShopSpark\TemplateFunctions;


class GlobalServiceProvider {
    use HelperTrait;

    protected array $settings;
    protected string $settings_field;

    public function __construct() {
        $this->settings = get_option( 'shopspark_global_settings', [] );
        $this->settings_field = 'shopspark_global_settings';
        add_action( 'shopspark_admin_settings_panel_global', array( $this, 'settings' ) );
    }

    public function register(): void {
        // Register settings
        register_setting( 'shopspark_global_settings', 'shopspark_global_settings' );
    }

    public function settings(): void {
        $options = $this->settings;
        ?>
        <div class="max-w-5xl mx-auto">
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php _e( 'Global Enhancements Settings', 'shopspark' ); ?>
            </h2>

            <form method="post" action="options.php" class="space-y-6"
                x-data="{ 
                    btnText: '<?php echo esc_js( $options['text'] ?? __( 'Buy Now', 'shopspark' ) ); ?>', 
                    buttonColor: '<?php echo esc_js( $options['button_color'] ?? '#3b82f6' ); ?>',
                    buttonBgColor: '<?php echo esc_js( $options['button_color_text_color'] ?? '#ffffff' ); ?>',
                    variationPopupAlign: '<?php echo esc_js( $options['ajax_add_to_cart_alignment'] ?? 'center' ); ?>',
                    fontSize: '<?php echo esc_js( $options['button_font_size'] ?? '16px' ); ?>',
                    buttonStyle: '<?php echo esc_js( $options['button_style'] ?? 'solid' ); ?>',
                    buttonPosition: '<?php echo esc_js( $options['button_position'] ?? 'woocommerce_after_add_to_cart_form' ); ?>'
                }">

                <?php settings_fields( $this->settings_field ); ?>

                <!-- Buy Now Button -->
                <?php
                echo TemplateFunctions::moduleCheckboxField(
                    $this->settings_field . '[enable_buy_now]',
                    __( 'Enable Buy Now Button (Shop & Product Page)', 'shopspark' ),
                    $options['enable_buy_now'] ?? false
                );
                ?>

                <!-- Honeypot Field -->
                <?php
                echo TemplateFunctions::moduleCheckboxField(
                    $this->settings_field . '[enable_honeypot]',
                    __( 'Enable Honeypot Field for Anti-Spam', 'shopspark' ),
                    $options['enable_honeypot'] ?? false
                );
                ?>

                <!-- Insert Custom Element -->
                <?php
                echo TemplateFunctions::moduleDropdownField(
                    $this->settings_field . '[custom_element_hook]',
                    __( 'Insert Custom Element At', 'shopspark' ),
                    array(
                        'woocommerce_before_main_content' => __( 'Before Title', 'shopspark' ),
                        'woocommerce_after_shop_loop_item' => __( 'After Add to Cart', 'shopspark' ),
                        'wp_footer' => __( 'Footer', 'shopspark' ),
                        'wp_header' => __( 'Header', 'shopspark' ),
                    ),
                    $options['custom_element_hook'] ?? 'woocommerce_before_main_content'
                );
                ?>

                <!-- Free Shipping Progress -->
                <?php
                echo TemplateFunctions::moduleCheckboxField(
                    $this->settings_field . '[enable_free_shipping_progress]',
                    __( 'Enable "X More Item for Free Shipping" Notice', 'shopspark' ),
                    $options['enable_free_shipping_progress'] ?? false
                );
                ?>

                <!-- Seasonal Countdown -->
                <?php
                echo TemplateFunctions::moduleCheckboxField(
                    $this->settings_field . '[enable_seasonal_offer]',
                    __( 'Enable Seasonal Offer Countdown Banner', 'shopspark' ),
                    $options['enable_seasonal_offer'] ?? false
                );
                ?>

                <!-- Existing Buy Now Customizations (Color, Text, etc.) -->
                <?php
                echo TemplateFunctions::moduleInputField(
                    $this->settings_field . '[text]',
                    __( 'Buy Now Button Text', 'shopspark' ),
                    'btnText',
                    'Buy Now'
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
                    '16px'
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

                echo TemplateFunctions::saveButton();
                ?>
            </form>
        </div>
        <?php
    }

}