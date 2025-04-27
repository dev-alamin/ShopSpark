<?php
namespace ShopSpark\Modules\LoadMore;
use ShopSpark\Core\ServiceProviderInterface;

class LoadMoreServiceProvider implements ServiceProviderInterface {
    public function register(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_load_more_products', [$this, 'load_more_products']);
        add_action('wp_ajax_nopriv_load_more_products', [$this, 'load_more_products']);

        add_action( 'woocommerce_after_shop_loop', [ $this, 'add_load_more_button' ], 20 );
    }

        // Hook to add Load More button after the product loop
    function add_load_more_button() {
        if ( is_shop() || is_product_category() ) {  // Show button only on shop or category pages
            ?>
            <button id="load-more-products" data-page="1" class="load-more-btn">
                <?php _e( 'Load More Products', 'shopspark' ); ?>
            </button>
            <?php
        }
    }


    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'shopspark-loadmore',
            plugin_dir_url(__FILE__) . 'Assets/loadmore.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('shopspark-loadmore', 'loadmore_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('load_more_products_nonce'),
        ]);
        
        wp_enqueue_style(
            'shopspark-loadmore',
            plugin_dir_url(__FILE__) . 'Assets/loadmore.css',
            [],
            '1.0.0'
        );
    }

    public function load_more_products(): void {
        // Get the current page for pagination
        $paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
        
        // Set up query arguments with pagination
        $args = [
            'post_type' => 'product',
            'posts_per_page' => 3,  // Number of products per page
            'paged' => $paged,       // Page number for pagination
            'offset' => ($paged - 1) * 3, // Skip the products already loaded
        ];        
    
        $query = new \WP_Query($args);
    
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                do_action( 'woocommerce_shop_loop' );
                // Check if you're in a block theme and use a block template
                if (wp_is_block_theme()) {
                    // Optionally, use WooCommerce block template for products

                    echo '<!-- wp:woocommerce/legacy-template {"template":"single-product"} /-->';

                } else {
                    // Fallback to traditional WooCommerce template part
                    wc_get_template_part('content', 'product');
                }
            }
        } else {
            // No more products to load, return an empty response
            echo ''; 
        }
    
        wp_reset_postdata();
        die(); // Terminate to avoid returning unnecessary data
    }
    
}
