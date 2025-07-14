<?php
namespace ShopSpark\Modules\EnhanceProductTitle;
use ShopSpark\Core\ServiceProviderInterface;

/**
 * Class EnhanceProductTitleServiceProvider
 *
 * @package ShopSpark\Modules\EnhanceProductTitle
 */
class EnhanceProductTitleServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register(): void
    {
        add_action( 'wp_enqueue_scripts', [$this, 'enqueueAssets']);

    }

    /**
     * Enqueue module assets
     *
     * @return void
     */
    public function enqueueAssets( $hook ): void
    {
        if ( is_product() ) {

            wp_enqueue_script(
                'shopspark-enhance-product-title',
                SHOP_SPARK_PLUGIN_ASSETS_URL . 'product-title/enhance-product-title.js',
                ['jquery'],
                SHOP_SPARK_VERSION,
                true
            );
        }
    }
    
}