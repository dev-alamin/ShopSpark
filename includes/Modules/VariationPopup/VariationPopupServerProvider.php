<?php
namespace ShopSpark\Modules\VariationPopup;
use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\TemplateFunctions;

/**
 * Class VariationPopupServerProvider
 *
 * @package ShopSpark\Modules\VariationPopup
 */
class VariationPopupServerProvider implements ServiceProviderInterface
{
    protected array $settings;
    protected $available_variations = [];

    public function __construct()
    {
        $this->settings = get_option('shopspark_product_page_settings', []);
        // Throw an error if the settings are not an array
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register(): void
    {
        add_action( 'shopspark_admin_product_page_panel_variation_popup', array( $this, 'settings' ) );

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

        $settings = get_option('shopspark_product_page_settings', []);
        $options = $settings['variation_popup'] ?? [];
        
        // add action to inside body
        add_action( 'wp_body_open', [$this, 'renderVariationPopup'], 15 );
    }

    /**
     * Render the variation popup
     *
     * @return void
     */
    public function renderVariationPopup(): void
    {
        ?>
    <div 
        id="shopspark-variation-container" 
        class="shopspark-variation-wrapper fixed right-0 top-0 h-full w-full sm:w-96 max-w-full bg-white shadow-xl z-50 p-6 rounded-l-2xl overflow-y-auto space-y-4 hidden z-9999"
    >
        <p class="text-lg font-semibold text-gray-700">Please select an option:</p>

        <ul class="shopspark-variation-list space-y-2 !ml-[0px]">
            <!-- Options will be injected here -->
        </ul>

        <button 
            class="mt-4 w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded transition"
            onclick="document.getElementById('shopspark-variation-wrapper').classList.add('hidden')"
        >
            Close
        </button>
    </div>

        <?php 
    }

    /**
     * Enqueue assets
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        $settings = get_option('shopspark_product_page_settings', []);
        $options = $settings['variation_popup'] ?? [];
        $buttonColor = $options['button_color'] ?? '#3b82f6';
        
        wp_enqueue_script( 'shopspark-alpine' );

        wp_enqueue_style( 'shopspark-variation-popup-css', SHOP_SPARK_PLUGIN_ASSETS_URL . 'variation-popup/variation-popup.css', array(), SHOP_SPARK_VERSION );
        wp_enqueue_script( 'shopspark-variation-popup-js', SHOP_SPARK_PLUGIN_ASSETS_URL . 'variation-popup/variation-popup.js', array( 'jquery', 'shopspark-alpine' ), SHOP_SPARK_VERSION, true );

        wp_localize_script( 'shopspark-variation-popup-js', 'shopspark_variation_popup', array(
            'buttonColor' => $buttonColor,
        ) );
    }

    /**
     * Add Content to the settings tab
     * 
     * @return void
     */
    public function settings(): void
    {
        $options = $this->settings['variation_popup'] ?? [];

        ?>
        <div class="max-w-5xl mx-auto">
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php _e( 'Product Page – Variation & Tab Popup Settings', 'shopspark' ); ?>
            </h2>

            <form method="post" action="options.php" class="space-y-6" 
                x-data="{ 
                    btnText: '<?php echo esc_js( $options['text'] ?? 'Choose Options' ); ?>', 
                    tabPopupHook: '<?php echo esc_js( $options['tab_popup_button_hook'] ?? 'medium' ); ?>',
                    tabPopupAlign: '<?php echo esc_js( $options['tab_popup_button_alignment'] ?? 'medium' ); ?>',
                    buttonColor: '<?php echo esc_js( $options['button_color'] ?? '#3b82f6' ); ?>',
                    variationPopupAlign: '<?php echo esc_js( $options['variation_popup_alignment'] ?? 'center' ); ?>'
                }">

                <?php settings_fields( 'shopspark_product_page_settings' ); ?>
                    <?php
                    echo TemplateFunctions::moduleCheckboxField(
                        'shopspark_product_page_settings[variation_popup][enable_variation_popup]',
                        __( 'Enable Variation Selection as Popup/Sidebar', 'shopspark' ),
                        $options['enable_variation_popup'] ?? false
                    );

                    echo TemplateFunctions::moduleDropdownField(
                        'shopspark_product_page_settings[variation_popup][variation_popup_alignment]',
                        __( 'Popup Alignment', 'shopspark' ),
                        array(
                            'center' => __( 'Center Modal', 'shopspark' ),
                            'right'  => __( 'Sidebar Right', 'shopspark' ),
                            'left'   => __( 'Sidebar Left', 'shopspark' ),
                        ),
                        $options['variation_popup_alignment'] ?? 'center',
                        'variationPopupAlign'
                    );

                    echo TemplateFunctions::moduleInputField(
                        'shopspark_product_page_settings[variation_popup][text]',
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
                        'shopspark_product_page_settings[variation_popup][button_color]',
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