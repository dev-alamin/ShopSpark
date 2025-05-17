<?php
namespace ShopSpark\Core;

use ShopSpark\Assets;
use ShopSpark\Admin\Menu;

class Plugin {
	protected string $plugin_file;
	protected Assets $assets;

	public function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->assets      = new Assets( $plugin_file );
		// Admin Menu
		new Menu( $plugin_file );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_global_assets' ) );
	}

	/**
	 * Load global assets
	 *
	 * @return void
	 */
	public function load_global_assets(): void {
		// $this->assets->enqueue('shopspark-script', 'assets/js/script.js', ['jquery'], '1.0.0', true);
		$this->assets->enqueue_style( 'shopspark-style', 'assets/css/style.css', array(), '1.0.0' );
	}

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init(): void {
        $this->load_textdomain();
    
        // Get general settings with safety check
        $settings = get_option( 'shopspark_general_settings', array() );
        $settings = is_array( $settings ) ? $settings : array();
    
        // Safely read module flags
        $quickView           = ! empty( $settings['quick_view'] )            && (int) $settings['quick_view']            === 1;
        $loadMore            = ! empty( $settings['ajax_load_more'] )        && (int) $settings['ajax_load_more']        === 1;
        $quantityButtons     = ! empty( $settings['quantity_buttons'] )      && (int) $settings['quantity_buttons']      === 1;
        $enhanceProductTitle = ! empty( $settings['variation_name_title'] )  && (int) $settings['variation_name_title']  === 1;
        $tabPopup            = ! empty( $settings['product_tabs_popup'] )    && (int) $settings['product_tabs_popup']    === 1;
        $variationPopup      = ! empty( $settings['variation_popup'] )       && (int) $settings['variation_popup']       === 1;

        $providers = array();
    
        if ( $quickView ) {
            $providers[] = \ShopSpark\Modules\QuickView\QuickViewServiceProvider::class;
        }
    
        if ( $loadMore ) {
            $providers[] = \ShopSpark\Modules\LoadMore\LoadMoreServiceProvider::class;
        }

        if ( $quantityButtons ) {
            $providers[] = \ShopSpark\Modules\QuatityMinPul\QualityServiceProvider::class;
        }

        if ( $enhanceProductTitle ) {
            $providers[] = \ShopSpark\Modules\EnhanceProductTitle\EnhanceProductTitleServiceProvider::class;
        }

        if ( $tabPopup ) {
            $providers[] = \ShopSpark\Modules\TabPopup\TabPopupServiceProvider::class;
        }

        if ( $variationPopup ) {
            $providers[] = \ShopSpark\Modules\VariationPopup\VariationPopupServerProvider::class;
        }

        $providers[] = \ShopSpark\Modules\AjaxAddToCart\AjaxAddToCartServerProvider::class;

        /**
         * Allow filtering the enabled modules for future extensibility.
         *
         * @param array $providers The list of service provider classes to load.
         * @param array $settings  The full general settings array.
         */
        $providers = apply_filters( 'shopspark_enabled_modules', $providers, $settings );
    
        // Register modules with class existence check
        foreach ( $providers as $provider ) {
            if ( class_exists( $provider ) ) {
                $this->register_modules( [ $provider ] );
            } else {
                error_log( "ShopSpark module not found or not autoloaded: {$provider}" );
            }
        }
    }    

    /**
     * Load plugin textdomain
     *
     * @return void
     */
	protected function load_textdomain(): void {
		load_plugin_textdomain(
			'shopspark',
			false,
			dirname( plugin_basename( $this->plugin_file ) ) . '/languages'
		);
	}

    /**
     * Register modules
     *
     * @param array $providers The list of service provider classes to load.
     * @return mixed
     */
	protected function register_modules( array $providers ): void {
		foreach ( $providers as $provider ) {
			if ( class_exists( $provider ) ) {
				( new $provider() )->register();
			}
		}
	}
}
