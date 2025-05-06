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
        $settings = get_option('shopspark_general_settings', []);
        $buttonPosition = !empty($settings['product_tabs_popup_position']) ? $settings['product_tabs_popup_position'] : 'woocommerce_after_single_product_summary';

        add_action($buttonPosition, [$this, 'renderPopupTabs'], 15);
        
    }
    
    public function renderPopupTabs(): void
    {
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
                                modalSize: '<?php echo esc_js( $options['quick_view_modal_size'] ?? 'medium' ); ?>',
                                imageSize: '<?php echo esc_js( $options['quick_view_image_size'] ?? 'medium' ); ?>',
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
                            'woocommerce_before_single_product'        => __( 'Before Single Product', 'shopspark' ),
                            'woocommerce_before_single_product_summary' => __( 'Before Summary', 'shopspark' ),
                            'woocommerce_single_product_summary'        => __( 'In Summary', 'shopspark' ),
                            'woocommerce_after_single_product_summary'  => __( 'After Summary', 'shopspark' ),
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