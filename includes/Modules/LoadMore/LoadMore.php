<?php
namespace ShopSpark\Modules\LoadMore;
use ShopSpark\Core\ServiceProviderInterface;

class LoadMoreServiceProvider implements ServiceProviderInterface {
    public function register(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_load_more_products', [$this, 'load_more_products']);
        add_action('wp_ajax_nopriv_load_more_products', [$this, 'load_more_products']);
    }

    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'shopspark-loadmore',
            plugin_dir_url(__FILE__) . 'assets/loadmore.js',
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
            plugin_dir_url(__FILE__) . 'assets/loadmore.css',
            [],
            '1.0.0'
        );
    }

    public function load_more_products(): void {
        $paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $args = [
            'post_type' => 'product',
            'posts_per_page' => 10,
            'paged' => $paged,
        ];

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product');
            }
        }

        wp_reset_postdata();
        die();
    }
}
