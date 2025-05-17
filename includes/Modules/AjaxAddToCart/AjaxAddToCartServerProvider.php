<?php
namespace ShopSpark\Modules\AjaxAddToCart;
use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\TemplateFunctions;

/**
 * Class AjaxAddToCartServerProvider
 *
 * @package ShopSpark\Modules\AjaxAddToCart
 */
class AjaxAddToCartServerProvider implements ServiceProviderInterface
{
    protected array $settings;
    protected $available_variations = [];
    protected string $settings_field;

    public function __construct()
    {
        $this->settings = get_option('shopspark_product_page_ajax_add_to_cart', []);
        $this->settings_field = 'shopspark_product_page_ajax_add_to_cart';
        // Throw an error if the settings are not an array
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register(): void
    {
        add_action( 'shopspark_admin_product_page_panel_ajax_add_to_cart', array( $this, 'settings' ) );

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }


    /**
     * Enqueue assets
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        $options = $this->settings ?? [];
        $buttonColor = $options['button_color'] ?? '#3b82f6';
        
        wp_enqueue_script( 'shopspark-alpine' );

        wp_enqueue_style( 'shopspark-ajax-add-to-cart-css', SHOP_SPARK_PLUGIN_ASSETS_URL . 'ajax-add-to-cart/ajax-add-to-cart.css', array(), SHOP_SPARK_VERSION );
        wp_enqueue_script( 'shopspark-ajax-add-to-cart-js', SHOP_SPARK_PLUGIN_ASSETS_URL . 'ajax-add-to-cart/ajax-add-to-cart.js', array( 'jquery', 'shopspark-alpine' ), SHOP_SPARK_VERSION, true );

        wp_localize_script( 'shopspark-ajax-add-to-cart-js', 'shopspark_ajax_add_to_cart', array(
            'buttonColor' => $buttonColor, // Need to handle later
        ) );
    }

    /**
     * Add Content to the settings tab
     * 
     * @return void
     */
    public function settings(): void
    {
        $options = $this->settings ?? [];

        ?>
        <div class="max-w-5xl mx-auto">
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php _e( 'Product Page – Variation & Tab Popup Settings', 'shopspark' ); ?>
            </h2>

            <form method="post" action="options.php" class="space-y-6" 
                x-data="{ 
                    btnText: '<?php echo esc_js( $options['text'] ?? 'Choose Options' ); ?>', 
                    buttonColor: '<?php echo esc_js( $options['button_color'] ?? '#3b82f6' ); ?>',
                    variationPopupAlign: '<?php echo esc_js( $options['ajax_add_to_cart_alignment'] ?? 'center' ); ?>'
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
                        'Quick View',
                        '!important',
                        __( 'e.g., Select Option', 'shopspark' ),
                        '',
                        '',
                        true
                    );

                    echo TemplateFunctions::moduleColorPickerField(
                       $this->settings_field . '[button_color]',
                        __( 'Button Color', 'shopspark' ),
                        $options['button_color'] ?? '#3b82f6',
                        'buttonColor'
                    );
                    ?>

                <?php echo TemplateFunctions::saveButton(); ?>
            </form>
        </div>
        <?php
    }

}