<?php
namespace ShopSpark\Modules\LoadMore;

use ShopSpark\Core\ServiceProviderInterface;

class LoadMoreServiceProvider implements ServiceProviderInterface {
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_load_more_products', array( $this, 'load_more_products' ) );
		add_action( 'wp_ajax_nopriv_load_more_products', array( $this, 'load_more_products' ) );
	}

	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'shopspark-loadmore',
			plugin_dir_url( __FILE__ ) . 'Assets/loadmore.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'shopspark-loadmore',
			'loadmore_params',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'load_more_products_nonce' ),
			)
		);

		wp_enqueue_style(
			'shopspark-loadmore',
			plugin_dir_url( __FILE__ ) . 'Assets/loadmore.css',
			array(),
			'1.0.0'
		);
	}

	public function load_more_products(): void {
		$paged = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

		$total_rows = (int) get_option( 'woocommerce_catalog_rows' );
		$per_row    = (int) get_option( 'woocommerce_catalog_columns' );
		$per_page   = $total_rows * $per_row;

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $per_page ?: 10,
			'paged'          => $paged,
			'post_status'    => 'publish',
			'orderby'        => isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'menu_order',
			'order'          => isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'ASC',

		);

		$query = new \WP_Query( $args );

		ob_start(); // Start output buffering to capture product HTML

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				do_action( 'woocommerce_shop_loop' );

				if ( wp_is_block_theme() ) {
					echo do_blocks( '<!-- wp:woocommerce/product {"id":' . get_the_ID() . '} /-->' );
				} else {
					wc_get_template_part( 'content', 'product' );
				}
			}
		} else {
			echo '';
		}

		$products_html = ob_get_clean(); // Get the buffered content

		wp_reset_postdata();

		wp_send_json_success(
			array(
				'post_per_page' => $per_page,
				'current_page'  => $paged,
				'offset'        => ( $paged - 1 ) * ( $per_page ?: 10 ),
				'content'       => $products_html,
				'total_pages'   => $query->max_num_pages, // ✅ Best way!
			)
		);
	}
}
