<?php
namespace ShopSpark\Modules\TabPopup;
use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\TemplateFunctions;

/**
 * Class TabPopupServiceProvider
 *
 * @package ShopSpark\Modules\TabPopup
 */
class TabPopupServiceProvider implements ServiceProviderInterface
{
    protected array $settings;
    protected $available_tabs = [];

    public function __construct()
    {
        $this->settings = get_option('shopspark_product_page_settings', []);
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register(): void
    {

        add_action( 'shopspark_admin_settings_panel_product_page', array( $this, 'settings' ) );

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

        $settings = get_option('shopspark_product_page_settings', []);
        $tab_hook  = $settings['tab_popup_button_hook'];
        $buttonPosition = !empty($tab_hook) ? $tab_hook : 'woocommerce_after_single_product_summary';
        
        // Add the button to the product page
        add_action($buttonPosition, [$this, 'renderPopupTabs'], 15);

        // Remove all product tabs
        add_filter( 'woocommerce_product_tabs', [ $this, 'replace_product_tab'] );
        
        // Remove the default WooCommerce product tabs
        add_action( 'template_redirect', [ $this, 'remove_default_tab' ] );
    }

    /**
     * Remove default product tabs
     *
     * @return void
     */
    public function remove_default_tab(): void {
            // Step 1: Try default position first (most themes)
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

            // Step 2: If still attached anywhere else, do deep search
            if ( has_action( 'woocommerce_output_product_data_tabs' ) ) {
                global $wp_filter;

                foreach ( $wp_filter as $hook_name => $hook ) {
                    if ( ! is_a( $hook, 'WP_Hook' ) ) continue;

                    foreach ( $hook->callbacks as $priority => $callbacks ) {
                        foreach ( $callbacks as $id => $callback ) {
                            if ( is_array( $callback['function'] ) ) continue;

                            if ( $callback['function'] === 'woocommerce_output_product_data_tabs' ) {
                                remove_action( $hook_name, 'woocommerce_output_product_data_tabs', $priority );
                            }
                        }
                    }
                }
            }
        }

    public function replace_product_tab( $tabs ) {
        $this->available_tabs = $tabs;
        // Remove all product tabs
        return $tabs;
    }
    
    public function renderPopupTabs() {
        if ( empty( $this->available_tabs ) ) {
            $this->available_tabs = apply_filters( 'woocommerce_product_tabs', [] );
            $tabs = $this->available_tabs; // Used in the view
        }

        include __DIR__ . '/views/popup-tab.php';
    }

    
    /**
     * Enqueue module assets
     *
     * @return void
     */
    public function enqueueAssets( $hook ): void
    {
        if ( is_product() ) {

            // custom css
            wp_enqueue_style(
                'shopspark-tab-popup',
                SHOP_SPARK_PLUGIN_ASSETS_URL . 'tab-popup/tab-popup.css',
                [],
                SHOP_SPARK_VERSION
            );

            // Alpine JS CDN
            // we need to delay the loading of alpine js
            wp_enqueue_script( 'shopspark-alpine' );

            wp_enqueue_script(
                'shopspark-tab-popup',
                SHOP_SPARK_PLUGIN_ASSETS_URL . 'tab-popup/tab-popup.js',
                ['jquery'],
                SHOP_SPARK_VERSION,
                true
            );
        }
    }

    /**
     * Settings for the module
     *
     * @return void
     */
    public function settings(): void
    {
        $options = $this->settings;
        ?>
        <div class="max-w-5xl mx-auto">
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php _e( 'Single Product Page Settings', 'shopspark' ); ?>
            </h2>
            <form method="post" action="options.php" class="space-y-6" x-data="{ 
                                btnText: '<?php echo esc_js( $options['quick_view_text'] ?? 'Quick View' ); ?>', 
                                tabPopupHook: '<?php echo esc_js( $options['tab_popup_button_hook'] ?? 'medium' ); ?>',
                                tabPopupAlign: '<?php echo esc_js( $options['tab_popup_button_alignment'] ?? 'medium' ); ?>',
                                buttonColor: '<?php echo esc_js( $options['quick_view_button_color'] ?? '#3b82f6' ); ?>'
                            }">
                <?php settings_fields( 'shopspark_product_page_settings' ); ?>

                <?php
                    // Enable Popup Tabs
                    echo TemplateFunctions::moduleCheckboxField(
                        'shopspark_product_page_settings[enable_tab_popups]',
                        __( 'Enable Product Data Tabs as Popup/Side Panel', 'shopspark' ),
                        $options['enable_tab_popups'] ?? false
                    );

                    // Popup Button Hook Position (same hook dropdown, reused)
                    echo TemplateFunctions::moduleDropdownField(
                        'shopspark_product_page_settings[tab_popup_button_hook]',
                        __( 'Tab Popup Button Hook Position', 'shopspark' ),
                        array(
                            'woocommerce_before_single_product_summary' => __( 'Before Summary', 'shopspark' ),
                            'woocommerce_single_product_summary'        => __( 'In Summary', 'shopspark' ),
                            'woocommerce_before_add_to_cart_form'       => __( 'Before Add to Cart', 'shopspark' ),
                            'woocommerce_before_variations_form'        => __( 'Before Variations', 'shopspark' ),
                            'woocommerce_before_add_to_cart_button'     => __( 'Before Add to Cart Button', 'shopspark' ),
                            'woocommerce_before_single_variation'       => __( 'Before Single Variation', 'shopspark' ),
                            'woocommerce_single_variation'              => __( 'Single Variation', 'shopspark' ),
                            'woocommerce_before_add_to_cart_quantity'   => __( 'Before Add to Cart Quantity', 'shopspark' ),
                            'woocommerce_after_add_to_cart_quantity'    => __( 'After Add to Cart Quantity', 'shopspark' ),
                            'woocommerce_after_single_variation'        => __( 'After Single Variation', 'shopspark' ),
                            'woocommerce_after_add_to_cart_button'      => __( 'After Add to Cart Button', 'shopspark' ),
                            'woocommerce_after_variations_form'         => __( 'After Variations', 'shopspark' ),
                            'woocommerce_after_add_to_cart_form'        => __( 'After Add to Cart Form', 'shopspark' ),
                            'woocommerce_product_meta_start'            => __( 'Product Meta Start', 'shopspark' ),
                            'woocommerce_product_meta_end'              => __( 'Product Meta End', 'shopspark' ),
                            'woocommerce_share'                         => __( 'Share', 'shopspark' ),
                            'woocommerce_after_single_product_summary'  => __( 'After Single Product Summary', 'shopspark' ),
                            'woocommerce_after_single_product'          => __( 'After Single Product', 'shopspark' ),
                        ),
                        $options['tab_popup_button_hook'] ?? 'woocommerce_after_single_product_summary',
                        'tabPopupHook'
                    );

                    // Button Alignment
                    echo TemplateFunctions::moduleDropdownField(
                        'shopspark_product_page_settings[tab_popup_button_alignment]',
                        __( 'Tab Popup Button Alignment', 'shopspark' ),
                        array(
                            'left'   => __( 'Left', 'shopspark' ),
                            'right'  => __( 'Right', 'shopspark' ),
                            'center' => __( 'Center', 'shopspark' ),
                        ),
                        $options['tab_popup_button_alignment'] ?? 'center',
                        'tabPopupAlign'
                    );

                    // Select Tabs to Show as Popups (Checkbox group)
                    echo TemplateFunctions::moduleCheckboxGroup(
                        'shopspark_product_page_settings[tab_popup_tabs]',
                        __( 'Tabs to Show as Popups', 'shopspark' ),
                        array(
                            'description'       => __( 'Description', 'shopspark' ),
                            'additional_info'   => __( 'Additional Info', 'shopspark' ),
                            'reviews'           => __( 'Reviews', 'shopspark' ),
                        ),
                        $options['tab_popup_tabs'] ?? ['description', 'additional_info', 'reviews']
                    );
                    ?>


                <!-- Save Button -->
                <?php echo TemplateFunctions::saveButton(); ?>

            </form>
        </div>
        <?php
    }
    
}