<?php
namespace ShopSpark\Modules\TabPopup;
use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\TemplateFunctions;
use ShopSpark\Traits\HelperTrait;

/**
 * Class TabPopupServiceProvider
 *
 * @package ShopSpark\Modules\TabPopup
 */
class TabPopupServiceProvider implements ServiceProviderInterface
{
    use HelperTrait;
    protected array $settings;
    protected $available_tabs = [];
    protected string $settings_field;

    public function __construct()
    {
        $this->settings = get_option('shopspark_product_page_tab_popup', []);
        $this->settings_field = 'shopspark_product_page_tab_popup';
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register(): void
    {

        add_action( 'shopspark_admin_product_page_panel_data_tab', array( $this, 'settings' ) );

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

        $settings = get_option('shopspark_product_page_tab_popup', []);
        $options = $settings ?? [];

        $tab_hook  = $options['tab_popup_button_hook'] ?? 'woocommerce_after_single_product_summary';
        
        // Add the button to the product page
        add_action($tab_hook, [$this, 'renderPopupTabs'], 15);

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

        $tab_view = __DIR__ . '/views/popup-tab.php';
        if(  file_exists( $tab_view ) ) {
            include $tab_view;
        } else {
            error_log(sprintf(
                'Tab view file not found: %s in %s on line %d',
                $tab_view,
                __FILE__,
                __LINE__
            ));
        }
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
        $options = $this->settings ?? [];

        ?>
        <div class="max-w-5xl mx-auto">
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php _e( 'Product Page – Data Tab Settings', 'shopspark' ); ?>
            </h2>
            <form method="post" action="options.php" class="space-y-6" 
                x-data="{ 
                        btnText: '<?php echo esc_js( $options['quick_view_text'] ?? 'Quick View' ); ?>', 
                        tabPopupHook: '<?php echo esc_js( $options['tab_popup_button_hook'] ?? 'woocommerce_after_single_product_summary' ); ?>',
                        tabPopupAlign: '<?php echo esc_js( $options['tab_popup_button_alignment'] ?? 'medium' ); ?>',
                        buttonColor: '<?php echo esc_js( $options['quick_view_button_color'] ?? '#3b82f6' ); ?>'
                }">
                
                <?php settings_fields( $this->settings_field ); ?>

                <?php
                    echo TemplateFunctions::moduleCheckboxField(
                        $this->settings_field . '[enable_tab_popups]',
                        __( 'Enable Product Data Tabs as Popup/Side Panel', 'shopspark' ),
                        $options['enable_tab_popups'] ?? false
                    );

                    echo TemplateFunctions::moduleDropdownField(
                        $this->settings_field . '[tab_popup_button_hook]',
                        __( 'Tab Popup Button Hook Position', 'shopspark' ),
                        $this->All_WC_Product_Hooks(),
                        $options['tab_popup_button_hook'] ?? 'woocommerce_after_single_product_summary',
                        'tabPopupHook'
                    );

                    echo TemplateFunctions::moduleDropdownField(
                        $this->settings_field . '[tab_popup_button_alignment]',
                        __( 'Tab Popup Button Alignment', 'shopspark' ),
                        array(
                            'left'   => __( 'Left', 'shopspark' ),
                            'right'  => __( 'Right', 'shopspark' ),
                            'center' => __( 'Center', 'shopspark' ),
                        ),
                        $options['tab_popup_button_alignment'] ?? 'center',
                        'tabPopupAlign'
                    );

                    echo TemplateFunctions::moduleCheckboxGroup(
                        $this->settings_field . '[tab_popup_tabs]',
                        __( 'Tabs to Show as Popups', 'shopspark' ),
                        array(
                            'description'       => __( 'Description', 'shopspark' ),
                            'additional_info'   => __( 'Additional Info', 'shopspark' ),
                            'reviews'           => __( 'Reviews', 'shopspark' ),
                        ),
                        $options['tab_popup_tabs'] ?? ['description', 'additional_info', 'reviews']
                    );
                ?>

                <?php echo TemplateFunctions::saveButton(); ?>

            </form>
        </div>
        <?php
    }
}