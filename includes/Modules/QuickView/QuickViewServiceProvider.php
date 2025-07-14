<?php
namespace ShopSpark\Modules\QuickView;

use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\TemplateFunctions;

class QuickViewServiceProvider implements ServiceProviderInterface {

	protected array $settings = array();

	public function __construct() {
		$this->settings = get_option( 'shopspark_quick_view_settings', array() );

		// add_filter( 'shopspark_admin_settings_tabs', [ $this, 'tab' ] );
		add_action( 'shopspark_admin_shop_page_panel_quick_view', array( $this, 'settings' ) );
	}

	public function register(): void {
        
		$options = $this->settings;
        $hook = $options['quick_view_button_position'];

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( $hook, array( $this, 'add_quick_view_button' ) );

		add_action( 'wp_head', array( $this, 'render_quick_view_modal' ) );

		add_action( 'wp_ajax_shopspark_quick_view', array( $this, 'shopspark_handle_quick_view' ) );
		add_action( 'wp_ajax_nopriv_shopspark_quick_view', array( $this, 'shopspark_handle_quick_view' ) );

		add_action( 'wp_ajax_shopspark_add_to_cart', array( $this, 'shopspark_ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_shopspark_add_to_cart', array( $this, 'shopspark_ajax_add_to_cart' ) );
	}

	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'shopspark-quickview',
			SHOP_SPARK_PLUGIN_ASSETS_URL . 'quick-view/quickview.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			SHOP_SPARK_VERSION,
			true
		);

		// Enqueue WooCommerce scripts, need to use condition later on
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		wp_enqueue_style(
			'shopspark-quickview',
			SHOP_SPARK_PLUGIN_ASSETS_URL . 'quick-view/quickview.css',
			array(),
			SHOP_SPARK_VERSION,
			'all'
		);

		wp_localize_script(
			'shopspark-quickview',
			'shopspark_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);

		// Tailwind CDN
		wp_enqueue_style( 'shopspark-frontend-tailwind' );

		wp_enqueue_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css' );

		wp_enqueue_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true );
	}

	public function add_quick_view_button(): void {
        global $product;
        if ( ! $product ) {
            return;
        }

        $options   = $this->settings;
        $color     = $options['quick_view_button_color'] ?? '#3b82f6';
        $default_text = '<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.88 99.41"><defs><style>.cls-1{fill-rule:evenodd;}</style></defs><title>'. esc_html__( 'Quick View', 'shopspark' ) .'</title><path class="cls-1" d="M61.38,31.76a18.28,18.28,0,1,1-12.93,5.36,18.24,18.24,0,0,1,12.93-5.36Zm52.9,45a4.3,4.3,0,1,1,8.6,0V95.1a4.31,4.31,0,0,1-4.3,4.31l-.39,0H92.86a4.29,4.29,0,0,1,0-8.57h21.42V76.72Zm8.6-54a4.3,4.3,0,1,1-8.6,0V8.59H92.86a4.29,4.29,0,0,1,0-8.57h25.33l.39,0a4.31,4.31,0,0,1,4.3,4.3V22.69ZM8.6,22.69a4.3,4.3,0,0,1-8.6,0V4.3A4.31,4.31,0,0,1,4.3,0l.39,0H30a4.29,4.29,0,0,1,0,8.57H8.6v14.1ZM0,76.72a4.3,4.3,0,1,1,8.6,0v14.1H30a4.29,4.29,0,0,1,0,8.57H4.69l-.39,0A4.31,4.31,0,0,1,0,95.1V76.72ZM6.92,47.43a97.18,97.18,0,0,1,9.3-9.8c13-11.81,27.73-18.12,43-18.44S90,24.54,104.67,36.64A109.75,109.75,0,0,1,116,47.46a3.7,3.7,0,0,1,.25,4.66A72.48,72.48,0,0,1,102.34,67a62.8,62.8,0,0,1-39.1,13.93,68.67,68.67,0,0,1-40-13A81.15,81.15,0,0,1,6.73,52.23a3.67,3.67,0,0,1,.19-4.8Zm14.26-4.36A86.77,86.77,0,0,0,14.41,50,73.11,73.11,0,0,0,27.5,61.91,61.4,61.4,0,0,0,63.21,73.55,55.49,55.49,0,0,0,97.76,61.2a64,64,0,0,0,10.73-11A99.33,99.33,0,0,0,100,42.31c-13.27-10.94-27.2-16-40.66-15.77S32.82,32.47,21.18,43.07Zm35.05-7.78a7.09,7.09,0,1,1-7.09,7.09,7.1,7.1,0,0,1,7.09-7.09Z"/></svg>';
        $text      = empty( $options['quick_view_text'] ) ? $default_text :  $options['quick_view_text'];
        $alignment = $options['quick_view_button_alignment'] ?? 'center';

        $product_id = $product->get_id();

        $alignment_class = '';
        switch ( $alignment ) {
            case 'Left':
                $alignment_class = 'shopspark-absolute shopspark-left-0 shopspark-text-left';
                break;
            case 'Right':
                $alignment_class = 'shopspark-absolute shopspark-right-0 shopspark-text-right';
                break;
            case 'Center':
            default:
                $alignment_class = 'shopspark-absolute shopspark-left-0 shopspark-right-0 shopspark-mx-auto shopspark-text-center';
                break;
        }

        printf(
            '<div class="%s"> 
                <button
                    title="%s"
                    class="shopspark-quick-view-btn shopspark-px-2 shopspark-h-9 shopspark-w-9 shopspark-py-1.5 shopspark-text-sm shopspark-rounded-full shopspark-top-0 shopspark-right-0 shopspark-mt-4" 
                    style="background-color: %s; color: #fff;" 
                    data-product-id="%d">
                    %s
                </button>
            </div>',
            esc_attr( $alignment_class ?: 'shopspark-text-left' ), // fallback prefixed class
            esc_attr__( 'Quick View', 'shopspark' ),
            esc_attr( $color ),
            esc_attr( $product_id ),
            shopspark_sanitize_svg_html( $text ), // output sanitized SVG or HTML directly, no escaping here
        );

    }

    public function render_quick_view_modal(): void {
        ?>
        
        <div id="shopspark-quick-view-modal" class="tailwind-wrapper shopspark-fixed shopspark-inset-0 shopspark-bg-black/50 shopspark-z-[9999] shopspark-hidden shopspark-flex shopspark-items-center shopspark-justify-center">

            <div id="shopspark-toast-container" class="shopspark-fixed shopspark-top-5 shopspark-right-5 shopspark-space-y-3 shopspark-z-[99999]"></div>

            <div class="shopspark-bg-white shopspark-w-full shopspark-max-w-3xl shopspark-rounded-2xl shopspark-p-6 shopspark-shadow-xl shopspark-relative">
                <span class="shopspark-absolute shopspark-top-0 shopspark-cursor-pointer shopspark-right-0 shopspark-text-red-500 shopspark-text-lg shopspark-font-semibold shopspark-w-[30px] shopspark-bg-red-500 shopspark-z-50 shopspark-text-white shopspark-text-center"
                    id="shopspark-quick-view-close">
                    &times;
                </span>
                <div id="shopspark-quick-view-content" class="shopspark-min-h-[200px] md:shopspark-flex md:shopspark-justify-center md:shopspark-gap-5">

                    <!-- Product content will load here -->

                </div>

            </div>
        </div>

        <?php
    }

	public function shopspark_handle_quick_view() {
		$product_id = absint( $_GET['product_id'] ?? 0 );

		if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
			wp_send_json_error( 'Invalid product.' );
			wp_die();
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( 'Product not found.' );
			wp_die();
		}

		// Output the full content for the quick view modal
		$this->render_product_images( $product );
		$this->render_product_info( $product );

		wp_die();
	}

	// Render product gallery images with small images below main image
	private function render_product_images( $product ) {
        $main_image_id  = $product->get_image_id();
        $gallery_images = $product->get_gallery_image_ids();

        if ( $gallery_images && is_array( $gallery_images ) ) :

            if ( $main_image_id ) {
                array_unshift( $gallery_images, $main_image_id );
            }

            ?>
            <div class="shopspark-product-gallery shopspark-w-full md:shopspark-max-w-[60%]">
                <!-- Main Gallery -->
                <div class="swiper main-gallery shopspark-mb-4 shopspark-max-w-xl shopspark-mx-auto">
                    <div class="swiper-wrapper">
                        <?php foreach ( $gallery_images as $img_id ) : ?>
                            <div class="swiper-slide">
                                <?php
                                echo wp_get_attachment_image(
                                    $img_id,
                                    'large',
                                    false,
                                    array(
                                        'class' => 'shopspark-rounded-xl shopspark-max-h-[400px] shopspark-w-full shopspark-object-cover shopspark-mx-auto',
                                    )
                                );
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
        
                <!-- Thumbnail Gallery (Below Main Image) -->
                <div class="swiper thumb-gallery shopspark-mt-4 shopspark-max-w-xl shopspark-mx-auto">
                    <div class="swiper-wrapper">
                        <?php foreach ( $gallery_images as $img_id ) : ?>
                            <div class="swiper-slide shopspark-w-24 shopspark-h-24 !shopspark-flex shopspark-items-center shopspark-justify-center shopspark-rounded-lg shopspark-overflow-hidden hover:shopspark-border-purple-600 shopspark-transition">
                                <?php
                                echo wp_get_attachment_image(
                                    $img_id,
                                    'thumbnail',
                                    false,
                                    array(
                                        'class' => 'shopspark-object-cover shopspark-max-h-full shopspark-max-w-full',
                                    )
                                );
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            <?php
        else :
            echo wp_get_attachment_image(
                $main_image_id,
                'large',
                false,
                array(
                    'class' => 'shopspark-rounded-xl shopspark-max-h-[400px] shopspark-w-full shopspark-object-cover shopspark-mx-auto',
                )
            );
        endif;
    }

	// Render product info (name, price, etc.)
    private function render_product_info( $product ) {
        ?>
        <div class="shopspark-product-info shopspark-mt-6 md:shopspark-mt-0 md:shopspark-w-1/2 shopspark-max-h-[500px] shopspark-overflow-y-auto">
            <h2 class="shopspark-text-xl shopspark-font-bold shopspark-mb-2"><?php echo esc_html( $product->get_name() ); ?></h2>
            <div class="shopspark-text-purple-600 shopspark-font-semibold shopspark-mb-4"><?php echo $product->get_price_html(); ?></div>
            <div class="shopspark-text-sm shopspark-text-gray-700 shopspark-mb-4"><?php echo wpautop( $product->get_short_description() ); ?></div>

            <?php
                $this->render_product_variations( $product );
                $this->render_product_description( $product );
            ?>

            <!-- Read More Button -->
            <div class="shopspark-mt-4">
                <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="button shopspark-inline-block shopspark-px-4 shopspark-py-2 shopspark-bg-inherit shopspark-text-white shopspark-rounded hover:shopspark-bg-primary-dark shopspark-transition shopspark-flex shopspark-items-center shopspark-justify-center shopspark-gap-2">
                    <?php _e( 'Read More', 'shopspark' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    // Render product variations (for variable products)
    private function render_product_variations( $product ) {
        if ( $product->is_type( 'variable' ) ) {
            $this->render_variation_product_add_to_cart_data( $product );
        } elseif ( $product->is_type( 'grouped' ) ) {
            $this->render_grouped_product_add_to_cart_data( $product );
        } else {
            // For simple products
            ?>
            <form class="shopspark_simple_add_to_cart_form">
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">

                <div class="shopspark-quantity shopspark-mb-4">
                    <label for="quantity_<?php echo esc_attr( $product->get_id() ); ?>" class="shopspark-block shopspark-font-medium shopspark-mb-1">
                        <?php esc_html_e( 'Quantity', 'shopspark' ); ?>
                    </label>
                    <?php $this->quantity_btn( $product ); ?>
                </div>

                <button type="submit"
                    class="add-to-cart-btn shopspark-mt-4 shopspark-inline-block shopspark-px-4 shopspark-py-2 shopspark-bg-gray-700 shopspark-rounded hover:shopspark-bg-primary-dark shopspark-transition shopspark-flex shopspark-items-center shopspark-justify-center shopspark-gap-2">
                    <?php esc_html_e( 'Add to Cart', 'shopspark' ); ?>
                </button>
            </form>
            <?php
        }
    }

	/**
	 * Render grouped product add to cart data
	 *
	 * @param $product
	 * @return void
	 */
	private function render_grouped_product_add_to_cart_data( $product ) {
        $grouped_products = $product->get_children(); // Get associated product IDs
        ?>
        <div class="grouped-products-form shopspark-mt-6">
            <h3 class="shopspark-text-lg shopspark-font-semibold shopspark-mb-2"><?php esc_html_e( 'Select Products', 'shopspark' ); ?></h3>

            <form class="grouped_products_form cart" method="post" enctype="multipart/form-data">
                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">

                <?php
                foreach ( $grouped_products as $child_id ) :
                    $child_product = wc_get_product( $child_id );

                    if ( ! $child_product || ! $child_product->is_purchasable() ) {
                        continue;
                    }
                    ?>
                    <div class="grouped-product-item shopspark-mb-4">
                        <label class="shopspark-flex shopspark-items-center shopspark-space-x-2">
                            <?php if ( $child_product->is_sold_individually() ) : ?>
                                <!-- If the product is sold individually, display a checkbox -->
                                <input 
                                    type="checkbox" 
                                    name="quantity[<?php echo esc_attr( $child_id ); ?>]" 
                                    value="1" 
                                    <?php echo ( $child_product->is_in_stock() ? '' : 'disabled' ); ?>
                                >
                            <?php else : ?>
                                <!-- For non-individually sold products, display a quantity input with plus/minus buttons -->
                                <div class="quantity-adjuster shopspark-flex shopspark-items-center shopspark-space-x-2">
                                    <!-- Decrease Button -->
                                    <button 
                                        type="button" 
                                        class="quantity-btn decrease" 
                                        onclick="adjustQuantity('decrease', <?php echo esc_attr( $child_id ); ?>)"
                                    >-</button>

                                    <!-- Number Input for Quantity -->
                                    <input 
                                        type="number"
                                        id="quantity_<?php echo esc_attr( $child_id ); ?>"
                                        name="quantity[<?php echo esc_attr( $child_id ); ?>]"
                                        value="0"
                                        min="0"
                                        class="shopspark-w-16 shopspark-border shopspark-border-gray-300 shopspark-rounded shopspark-px-2 shopspark-py-1 shopspark-text-center"
                                        <?php echo ( $child_product->is_in_stock() ? '' : 'disabled' ); ?>
                                    >

                                    <!-- Increase Button -->
                                    <button 
                                        type="button" 
                                        class="quantity-btn increase" 
                                        onclick="adjustQuantity('increase', <?php echo esc_attr( $child_id ); ?>)"
                                    >+</button>
                                </div>
                            <?php endif; ?>

                            <span>
                                <?php echo esc_html( $child_product->get_name() ); ?> 
                                - <?php echo wc_price( $child_product->get_price() ); ?>
                            </span>
                        </label>
                    </div>

                <?php endforeach; ?>

                <button type="submit"
                    class="add-to-cart-btn shopspark-mt-4 shopspark-inline-block shopspark-px-4 shopspark-py-2 shopspark-bg-purple-600 shopspark-text-white shopspark-rounded-lg hover:shopspark-bg-purple-700 shopspark-transition">
                    <?php esc_html_e( 'Add Selected to Cart', 'shopspark' ); ?>
                </button>
            </form>
        </div>
        <?php
    }

	/**
	 * Render variation product add to cart data
	 *
	 * @param $product
	 * @return void
	 */
	private function render_variation_product_add_to_cart_data( $product ) {
        $attributes           = $product->get_variation_attributes();
        $available_variations = $product->get_available_variations();
        $product_id           = $product->get_id();
        ?>
        <div class="product-variations shopspark-mt-6">
            <h3 class="shopspark-text-lg shopspark-font-semibold shopspark-mb-2"><?php esc_html_e( 'Select Variation', 'shopspark' ); ?></h3>
            <form class="shopspark_variations_form cart" method="post" enctype="multipart/form-data"
                data-product_id="<?php echo esc_attr( $product_id ); ?>"
                data-product_variations="<?php echo esc_attr( json_encode( $available_variations, JSON_HEX_TAG ) ); ?>">

                <?php foreach ( $attributes as $attr_name => $options ) : ?>
                    <div class="variation-option shopspark-mb-4">
                        <label for="<?php echo esc_attr( $attr_name ); ?>" class="shopspark-block shopspark-font-medium shopspark-mb-1">
                            <?php echo wc_attribute_label( $attr_name ); ?>
                        </label>
                        <select name="attribute_<?php echo esc_attr( sanitize_title( $attr_name ) ); ?>"
                                id="<?php echo esc_attr( $attr_name ); ?>"
                                class="shopspark-w-full shopspark-border shopspark-border-gray-300 shopspark-rounded shopspark-px-3 shopspark-py-2 custom-variation-select">
                            <option value=""><?php esc_html_e( 'Choose an option', 'shopspark' ); ?></option>
                            <?php foreach ( $options as $option ) : ?>
                                <option value="<?php echo esc_attr( $option ); ?>">
                                    <?php echo esc_html( ucfirst( wc_attribute_label( $option, $product ) ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>

                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
                <input type="hidden" name="variation_id" class="variation_id" value="">
                <?php $this->quantity_btn( $product ); ?>
                <button type="submit"
                    class="add-to-cart-btn shopspark-mt-4 shopspark-inline-block shopspark-px-4 shopspark-py-2 shopspark-bg-purple-600 shopspark-text-white shopspark-rounded-lg hover:shopspark-bg-purple-700 shopspark-transition">
                    <?php esc_html_e( 'Add to Cart', 'shopspark' ); ?>
                </button>
            </form>
        </div>
        <?php
    }

    private function quantity_btn( $product ) {
        // Check if the product is a variation or a simple product
        $is_variation = $product->is_type( 'variation' );
        $is_grouped   = $product->is_type( 'grouped' );
        ?>
        <div class="shopspark-mb-4 shopspark-flex shopspark-items-center shopspark-space-x-2">
            <button 
                type="button" 
                class="shopspark-bg-gray-200 shopspark-text-gray-700 shopspark-px-3 shopspark-py-2 shopspark-rounded-l-full hover:shopspark-bg-gray-300 shopspark-transition !important"
                onclick="adjustQuantity('decrease', <?php echo esc_attr( $product->get_id() ); ?>)"
            >-</button>
            
            <input
                type="number"
                id="quantity_<?php echo esc_attr( $product->get_id() ); ?>"
                class="shopspark-w-16 shopspark-text-center shopspark-py-2 focus:shopspark-outline-none !important"
                name="quantity"
                value="1"
                min="1"
                <?php if ( ! $is_grouped && ! $is_variation && $product->managing_stock() && $product->get_stock_quantity() > 0 ) : ?>
                    max="<?php echo esc_attr( $product->get_stock_quantity() ); ?>"
                <?php elseif ( $is_variation ) : ?>
                    max="<?php echo esc_attr( $product->get_variation()->get_stock_quantity() ); ?>"
                <?php elseif ( $is_grouped ) : ?>
                    max="99" <!-- Adjust as necessary for grouped product -->
                <?php endif; ?>
            >
        
            <button 
                type="button" 
                class="shopspark-bg-gray-200 shopspark-text-gray-700 shopspark-px-3 shopspark-py-2 shopspark-rounded-r-full hover:shopspark-bg-gray-300 shopspark-transition !important"
                onclick="adjustQuantity('increase', <?php echo esc_attr( $product->get_id() ); ?>)"
            >+</button>
        </div>
        <?php
    }


	// Render full product description
	private function render_product_description( $product ) {
		?>
		<div class="product-description shopspark-mt-6">
			<h3 class="shopspark-text-lg shopspark-font-semibold shopspark-mb-2"><?php esc_html_e( 'Product Description', 'shopspark' ); ?></h3>
			<div class="description-text shopspark-text-sm">
				<?php echo wpautop( wp_trim_words( $product->get_description(), 50, ' ' ) ); ?>
			</div>
		</div>
		<?php
	}

	public function tab() {
		$tabs['quick_view'] = __( 'Quick View', 'shopspark' );
		return $tabs;
	}

	public function settings() {
			$options = $this->settings;
		?>
			<div class="max-w-5xl mx-auto">
				<h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
					<?php _e( 'Quick View Settings', 'shopspark' ); ?>
				</h2>
				<form method="post" action="options.php" class="space-y-6" x-data="{ 
									btnText: '<?php echo esc_js( $options['quick_view_text'] ?? 'Quick View' ); ?>', 
									modalSize: '<?php echo esc_js( $options['quick_view_modal_size'] ?? 'medium' ); ?>',
									imageSize: '<?php echo esc_js( $options['quick_view_image_size'] ?? 'medium' ); ?>',
									buttonColor: '<?php echo esc_js( $options['quick_view_button_color'] ?? '#3b82f6' ); ?>'
								}">
					<?php settings_fields( 'shopspark_quick_view_settings' ); ?>

                        <!-- Quick View Button Text Input -->
                        <?php
                        echo TemplateFunctions::moduleInputField(
                            'shopspark_quick_view_settings[quick_view_text]',
                            __( 'Quick View Button Text', 'shopspark' ),
                            'btnText',
                            'Quick View',
                            '!important',
                            __( 'e.g., Quick View', 'shopspark' ),
                            '',
                            '',
                            false
                        );

                        // Quick View Modal Size Dropdown
                        echo TemplateFunctions::moduleDropdownField(
                            'shopspark_quick_view_settings[quick_view_modal_size]',
                            __( 'Quick View Modal Size', 'shopspark' ),
                            array( 'small', 'medium', 'large' ),
                            $options['quick_view_modal_size'] ?? 'medium',
                            'modalSize'
                        );

                    // Quick View Button Position
                        echo TemplateFunctions::moduleDropdownField(
                            'shopspark_quick_view_settings[quick_view_button_position]',
                            __( 'Quick View Button Position', 'shopspark' ),
                            array(
                                'woocommerce_before_shop_loop_item'        => __( 'Before Product Link Start', 'shopspark' ),
                                'woocommerce_before_shop_loop_item_title'  => __( 'Before Product Title', 'shopspark' ),
                                'woocommerce_shop_loop_item_title'         => __( 'Product Title', 'shopspark' ),
                                'woocommerce_after_shop_loop_item_title'   => __( 'After Product Title', 'shopspark' ),
                                'woocommerce_after_shop_loop_item'         => __( 'After Product Link End', 'shopspark' ),
                            ),
                            $options['quick_view_button_position'] ?? 'woocommerce_after_shop_loop_item',
                            'buttonPosition'
                        );

                        // Quick View Button Position, Left or Right, center
                        echo TemplateFunctions::moduleDropdownField(
                            'shopspark_quick_view_settings[quick_view_button_alignment]',
                            __( 'Quick View Button Position', 'shopspark' ),
                            array(
                                'left'   => __( 'Left', 'shopspark' ),
                                'right'  => __( 'Right', 'shopspark' ),
                                'center' => __( 'Center', 'shopspark' ),
                            ),
                            $options['quick_view_button_alignment'] ?? 'center',
                            'buttonPosition'
                        );

                        // Quick View Image Size Dropdown
                        echo TemplateFunctions::moduleDropdownField(
                            'shopspark_quick_view_settings[quick_view_image_size]',
                            __( 'Quick View Image Size', 'shopspark' ),
                            array( 'small', 'medium', 'large' ),
                            $options['quick_view_image_size'] ?? 'medium',
                            'imageSize'
                        );
                        ?>

						<!-- Quick View Button Color -->
						<div>
							<label for="quick_view_button_color" class="block text-sm font-medium text-gray-700 mb-1">
								<?php _e( 'Quick View Button Color', 'shopspark' ); ?>
							</label>
							<input type="color" id="quick_view_button_color"
								name="shopspark_quick_view_settings[quick_view_button_color]" x-model="buttonColor"
								class="w-16 h-10 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
						</div>

					<!-- Save Button -->
					<?php echo TemplateFunctions::saveButton(); ?>

				</form>
			</div>
		<?php
	}

	public function shopspark_ajax_add_to_cart() {
		$product_id   = intval( $_POST['product_id'] ?? 0 );
		$variation_id = intval( $_POST['variation_id'] ?? 0 );
		$quantity     = intval( $_POST['quantity'] ?? 1 );

		$attributes = array_filter(
			$_POST,
			function ( $key ) {
				return strpos( $key, 'attribute_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product.', 'shopspark' ) ) );
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( ! $passed_validation ) {
			wp_send_json_error( array( 'message' => __( 'Failed validation.', 'shopspark' ) ) );
		}

		if ( wc_get_product( $product_id )->is_type( 'variable' ) ) {
			if ( ! $variation_id ) {
				wp_send_json_error( array( 'message' => __( 'No variation selected or This variation isn\'t availe', 'shopspark' ) ) );
			}
		}

		$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes );

		if ( $added ) {
					// Get product name
			$product = wc_get_product( $product_id );
			$message = sprintf(
				__( '%1$s x %2$s added to cart!', 'shopspark' ),
				$quantity,
				$product->get_name()
			);

			$total_in_cart_message = __( 'Total items in cart', 'shopspark' );

			// Show success message
			wp_send_json_success(
				array(
					'added_message' => __( 'Added to cart!', 'shopspark' ),
					'message'       => $message,
					'tot_message'   => $total_in_cart_message,
					'cart_count'    => WC()->cart->get_cart_contents_count(),
					'cart_url'      => wc_get_cart_url(),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Could not add to cart.', 'shopspark' ) ) );
		}

		wp_die();
	}
}