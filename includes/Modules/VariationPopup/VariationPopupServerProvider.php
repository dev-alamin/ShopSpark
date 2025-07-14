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
    protected string $settings_field;

    public function __construct()
    {
        $this->settings = get_option('shopspark_product_page_variation_popup', []);
        $this->settings_field = 'shopspark_product_page_variation_popup';
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
        $options = $this->settings ?? [];
        $position = $options['variation_popup_alignment'] ?? 'center';

        $base_classes = 'shopspark-variation-wrapper shopspark-fixed shopspark-bg-white shopspark-shadow-xl shopspark-z-[99999] shopspark-p-6 shopspark-overflow-y-auto shopspark-space-y-4 shopspark-hidden';

        switch ($position) {
            case 'left':
                $position_classes = 'shopspark-left-0 shopspark-top-0 shopspark-h-full shopspark-w-full sm:shopspark-w-96 shopspark-rounded-r-2xl';
                break;
            case 'right':
                $position_classes = 'shopspark-right-0 shopspark-top-0 shopspark-h-full shopspark-w-full sm:shopspark-w-96 shopspark-rounded-l-2xl';
                break;
            case 'center':
            default:
                $position_classes = 'shopspark-left-1/2 shopspark-top-1/2 shopspark-transform shopspark--translate-x-1/2 shopspark--translate-y-1/2 shopspark-w-11/12 sm:shopspark-w-[500px] shopspark-rounded-2xl';
                break;
        }
        ?>
        <div 
            id="shopspark-variation-container"
            class="<?php echo esc_attr("$base_classes $position_classes"); ?>"
        >
            <div class="shopspark-flex shopspark-items-center shopspark-justify-between">
                <p class="shopspark-text-lg shopspark-font-semibold shopspark-text-gray-700">
                    <?php esc_html_e( 'Please select an option:', 'shopspark' ); ?>
                </p>
                <button 
                    type="button"
                    class="shopspark-text-gray-500 hover:shopspark-text-red-600 shopspark-text-xl shopspark-font-bold shopspark-leading-none shopspark-rounded-full"
                    @click="openTab = null"
                >✕</button>
            </div>

            <ul class="shopspark-variation-list shopspark-space-y-2 !shopspark-ml-[0px]">
                <!-- Options will be injected here -->
            </ul>

            <button class="shopspark-mt-4 shopspark-w-full shopspark-bg-red-500 hover:shopspark-bg-red-600 shopspark-text-white shopspark-font-medium shopspark-py-2 shopspark-px-4 shopspark-rounded shopspark-transition">
                <?php esc_html_e( 'Close', 'shopspark' ); ?>
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
        $options = $this->settings ?? [];
        $buttonColor = $options['button_color'] ?? '#3b82f6';
        
        wp_enqueue_script( 'shopspark-alpine' );

        wp_enqueue_style( 'shopspark-frontend-tailwind' );

        wp_enqueue_style( 'shopspark-variation-popup-css', SHOP_SPARK_PLUGIN_ASSETS_URL . 'variation-popup/variation-popup.css', array(), SHOP_SPARK_VERSION );
        wp_enqueue_script( 'shopspark-variation-popup-js', SHOP_SPARK_PLUGIN_ASSETS_URL . 'variation-popup/variation-popup.js', array( 'jquery', 'shopspark-alpine' ), SHOP_SPARK_VERSION, true );

        wp_localize_script( 'shopspark-variation-popup-js', 'shopspark_variation_popup', array(
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
                    variationPopupAlign: '<?php echo esc_js( $options['variation_popup_alignment'] ?? 'center' ); ?>'
                }">

                <?php settings_fields( $this->settings_field ); ?>
                    <?php
                    echo TemplateFunctions::moduleCheckboxField(
                       $this->settings_field . '[enable_variation_popup]',
                        __( 'Enable Variation Selection as Popup/Sidebar', 'shopspark' ),
                        $options['enable_variation_popup'] ?? false
                    );

                    echo TemplateFunctions::moduleDropdownField(
                       $this->settings_field . '[variation_popup_alignment]',
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