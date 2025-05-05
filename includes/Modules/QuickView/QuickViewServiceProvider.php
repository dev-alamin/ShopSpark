<?php
namespace ShopSpark\Modules\QuickView;

use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\Modules\QuickView\Admin\Settings;
use ShopSpark\TemplateFunctions;

class QuickViewServiceProvider implements ServiceProviderInterface {

	protected array $settings = array();

	public function __construct() {
		$this->settings = get_option( 'shopspark_quick_view_settings', array() );

		// add_filter( 'shopspark_admin_settings_tabs', [ $this, 'tab' ] );
		add_action( 'shopspark_admin_settings_panel_quick_view', array( $this, 'settings' ) );
	}

	public function register(): void {
		$hook = $this->mapHook() ? $this->mapHook() : 'woocommerce_after_shop_loop_item';

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
			time(),
			true
		);

		// Enqueue WooCommerce scripts, need to use condition later on
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		wp_enqueue_style(
			'shopspark-quickview',
			SHOP_SPARK_PLUGIN_ASSETS_URL . 'quick-view/quickview.css',
			array(),
			time(),
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
		wp_enqueue_style( 'shopspark-tailwind', '//cdn.jsdelivr.net/npm/@tailwindcss/browser@4', array(), '3.4.1' );

		// Tailwind JS
		wp_enqueue_script( 'shopspark-tailwindjs', '//cdn.jsdelivr.net/npm/@tailwindcss/browser@4' );

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
		$text      = $options['quick_view_text'] ?? __( 'Quick View', 'shopspark' );
		$alignment = $options['quick_view_button_alignment'] ?? 'center';

		$product_id = $product->get_id();

		$alignment_class = '';
		switch ( $alignment ) {
			case 'Left':
				$alignment_class = 'absolute left-0 text-left';
				break;
			case 'Right':
				$alignment_class = 'absolute right-0 text-right';
				break;
			case 'Center':
			default:
				$alignment_class = 'absolute left-0 right-0 ma-auto text-center';
				break;
		}

		printf(
			'<div class="%s">
            <button
                title="%s"
                class="shopspark-quick-view-btn px-3 py-1.5 text-sm rounded-lg top-0 right-0" 
                style="background-color: %s; color: #fff;" 
                data-product-id="%d">
                %s
            </button>
            </div>',
			esc_attr( $alignment_class ? $alignment_class : 'text-left' ),
            esc_attr__( 'Quick View', 'shopspark' ),
			esc_attr( $color ),
			esc_attr( $product_id ),
			esc_html( $text )
		);
	}

	public function render_quick_view_modal(): void {
		?>
		<div id="shopspark-quick-view-modal" class="fixed inset-0 bg-black/50 z-999999 hidden flex items-center justify-center">

		<div id="shopspark-toast-container" class="fixed top-5 right-5 space-y-3 z-50"></div>

			<div class="bg-white w-full max-w-3xl rounded-2xl p-6 shadow-xl relative">
                <span class="absolute top-0 cursor-pointer right-0 text-red-500 text-lg font-semibold w-[30px] bg-red-500 z-50 text-white text-center"
                    id="shopspark-quick-view-close">
                    &times;
                </span>
				<div id="shopspark-quick-view-content" class="min-h-[200px] md:flex md:justify-center md:gap-5">

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
		<div class="product-gallery w-full md:max-w-[60%]">
		<!-- Main Gallery -->
			<div class="swiper main-gallery mb-4 max-w-xl mx-auto">
				<div class="swiper-wrapper">
					<?php foreach ( $gallery_images as $img_id ) : ?>
						<div class="swiper-slide">
							<?php
							echo wp_get_attachment_image(
								$img_id,
								'large',
								false,
								array(
									'class' => 'rounded-xl max-h-[400px] w-full object-cover mx-auto',
								)
							);
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
	
			<!-- Thumbnail Gallery (Below Main Image) -->
			<div class="swiper thumb-gallery mt-4 max-w-xl mx-auto">
				<div class="swiper-wrapper">
					<?php foreach ( $gallery_images as $img_id ) : ?>
						<div class="swiper-slide w-24 h-24 !flex items-center justify-center rounded-lg overflow-hidden hover:border-purple-600 transition">
							<?php
							echo wp_get_attachment_image(
								$img_id,
								'thumbnail',
								false,
								array(
									'class' => 'object-cover max-h-full max-w-full',
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
					'class' => 'rounded-xl max-h-[400px] w-full object-cover mx-auto',
				)
			);
		endif;
	}

	// Render product info (name, price, etc.)
	private function render_product_info( $product ) {
		?>
        <div class="product-info mt-6 md:mt-0 md:w-1/2 max-h-[500px] overflow-y-auto">
			<h2 class="text-xl font-bold mb-2"><?php echo esc_html( $product->get_name() ); ?></h2>
			<div class="text-purple-600 font-semibold mb-4"><?php echo $product->get_price_html(); ?></div>
			<div class="text-sm text-gray-700 mb-4"><?php echo wpautop( $product->get_short_description() ); ?></div>

			<?php
					$this->render_product_variations( $product );
					$this->render_product_description( $product );
			?>

				<!-- Read More Button -->
			<div class="mt-4">
				<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="button inline-block px-4 py-2 bg-inherit text-white rounded hover:bg-primary-dark transition flex items-center justify-center gap-2">
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

				<div class="quantity mb-4">
					<label for="quantity_<?php echo esc_attr( $product->get_id() ); ?>" class="block font-medium mb-1">
						<?php esc_html_e( 'Quantity', 'shopspark' ); ?>
					</label>
					<?php $this->quantity_btn( $product ); ?>
				</div>

				<button type="submit"
					class="add-to-cart-btn mt-4 inline-block px-4 py-2 bg-inherit text-white rounded hover:bg-primary-dark transition flex items-center justify-center gap-2">
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
		<div class="grouped-products-form mt-6">
			<h3 class="text-lg font-semibold mb-2"><?php esc_html_e( 'Select Products', 'shopspark' ); ?></h3>
	
			<form class="grouped_products_form cart" method="post" enctype="multipart/form-data">
				<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
	
				<?php
				foreach ( $grouped_products as $child_id ) :
					$child_product = wc_get_product( $child_id );

					if ( ! $child_product || ! $child_product->is_purchasable() ) {
						continue;
					}
					?>
					<div class="grouped-product-item mb-4">
						<label class="flex items-center space-x-2">
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
								<div class="quantity-adjuster flex items-center space-x-2">
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
										class="w-16 border border-gray-300 rounded px-2 py-1 text-center"
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
					class="add-to-cart-btn mt-4 inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
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
		<div class="product-variations mt-6">
			<h3 class="text-lg font-semibold mb-2"><?php esc_html_e( 'Select Variation', 'shopspark' ); ?></h3>
			<form class="shopspark_variations_form cart" method="post" enctype="multipart/form-data"
			data-product_id="<?php echo esc_attr( $product_id ); ?>"
			data-product_variations="<?php echo esc_attr( json_encode( $available_variations, JSON_HEX_TAG ) ); ?>">


				<?php foreach ( $attributes as $attr_name => $options ) : ?>
					<div class="variation-option mb-4">
						<label for="<?php echo esc_attr( $attr_name ); ?>" class="block font-medium mb-1">
							<?php echo wc_attribute_label( $attr_name ); ?>
						</label>
						<select name="attribute_<?php echo esc_attr( sanitize_title( $attr_name ) ); ?>"
								id="<?php echo esc_attr( $attr_name ); ?>"
								class="w-full border border-gray-300 rounded px-3 py-2 custom-variation-select">
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
					class="add-to-cart-btn mt-4 inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
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
        <div class="quantity-adjuster mb-4 flex items-center space-x-2">
            <button 
            type="button" 
            class="quantity-btn decrease bg-gray-200 text-gray-700 px-3 py-2 rounded-l-full hover:bg-gray-300 transition !important"
            onclick="adjustQuantity('decrease', <?php echo esc_attr( $product->get_id() ); ?>)"
            >-</button>
            
            <input
            type="number"
            id="quantity_<?php echo esc_attr( $product->get_id() ); ?>"
            class="w-16 text-center py-2 focus:outline-none !important"
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
            class="quantity-btn increase bg-gray-200 text-gray-700 px-3 py-2 rounded-r-full hover:bg-gray-300 transition !important"
            onclick="adjustQuantity('increase', <?php echo esc_attr( $product->get_id() ); ?>)"
            >+</button>
        </div>
		<?php
	}

	// Render full product description
	private function render_product_description( $product ) {
		?>
		<div class="product-description mt-6">
			<h3 class="text-lg font-semibold mb-2"><?php esc_html_e( 'Product Description', 'shopspark' ); ?></h3>
			<div class="description-text text-sm">
				<?php echo wpautop( wp_trim_words( $product->get_description(), 50, ' ' ) ); ?>
			</div>
		</div>
		<?php
	}

	private function mapHook() {
		$options  = $this->settings;
		$position = $options['quick_view_button_position'];
		$hook     = '';

		// Map the human-readable value to WooCommerce hooks
		switch ( $position ) {
			case 'Before Product Link Start':
				$hook = 'woocommerce_before_shop_loop_item';
				break;

			case 'Before Product Title':
				$hook = 'woocommerce_before_shop_loop_item_title';
				break;

			case 'Before Product Price':
				$hook = 'woocommerce_before_shop_loop_item_title';
				break;

			case 'After Product Title':
				$hook = 'woocommerce_after_shop_loop_item_title';
				break;

			case 'After Product Link End':
				$hook = 'woocommerce_after_shop_loop_item';
				break;

			default:
				$hook = 'woocommerce_after_shop_loop_item';
				break;
		}

		return $hook;
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
                            true
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