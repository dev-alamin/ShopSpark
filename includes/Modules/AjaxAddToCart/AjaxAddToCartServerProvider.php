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

        add_action( 'wp_ajax_shopspark_ajax_add_to_cart', array( $this, 'ajaxAddToCart' ) );
        add_action( 'wp_ajax_nopriv_shopspark_ajax_add_to_cart', array( $this, 'ajaxAddToCart' ) );
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
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'shopspark-ajax-add-to-cart' ),
            'productId'   => get_the_ID(),
            'cartCount'   => WC()->cart->get_cart_contents_count(),
            'buttonColor' => $buttonColor,                                      // Need to handle later
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

    /**
     * Handle the AJAX request to add a product to the cart
     *
     * @return void
     */
    public function ajaxAddToCart(): void
{
    check_ajax_referer('shopspark-ajax-add-to-cart', '_wpnonce');

    if (empty($_POST['product_id'])) {
        wp_send_json_error(['message' => __('Product ID is required', 'shopspark')]);
        wp_die();
    }

    $product_id = intval($_POST['product_id']);
    $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // Decode quantities for grouped products if sent, otherwise empty array
    $quantities = [];
    if (!empty($_POST['quantities'])) {
        $quantities = json_decode(stripslashes($_POST['quantities']), true);
        if (!is_array($quantities)) {
            $quantities = [];
        }
    }

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error(['message' => __('Product not found', 'shopspark')]);
        wp_die();
    }

    // Handle grouped products
    if ($product->is_type('grouped')) {
        $added_items = 0;

        foreach ($quantities as $child_id => $qty) {
            $child_id = intval($child_id);
            $qty = intval($qty);
            if ($qty > 0 && wc_get_product($child_id)) {
                WC()->cart->add_to_cart($child_id, $qty);
                $added_items++;
            }
        }

        if ($added_items > 0) {
            $message = __('Grouped products added to cart.', 'shopspark');
            $notice_html = '<div class="woocommerce-message" role="alert">' . $message . '</div>';

            wp_send_json_success([
                'notice'     => $notice_html,
                'cart_count' => WC()->cart->get_cart_contents_count(),
            ]);
        } else {
            wp_send_json_error(['message' => __('No grouped products quantity selected.', 'shopspark')]);
        }

        wp_die();
    }

    // Handle simple or variable products
    $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);

    if ($cart_item_key) {
        $message = sprintf(
            __('"%s" has been added to your cart.', 'shopspark'),
            $product->get_name()
        );

        $cart_url = wc_get_cart_url();
        $message .= ' <a href="' . esc_url($cart_url) . '" class="button wc-forward">' . __('View cart', 'shopspark') . '</a>';
        $notice_html = '<div class="woocommerce-message" role="alert">' . $message . '</div>';

        wp_send_json_success([
            'notice'     => $notice_html,
            'cart_url'   => $cart_url,
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ]);
    } else {
        wp_send_json_error(['message' => __('Failed to add product to cart', 'shopspark')]);
    }

    wp_die();
}




}