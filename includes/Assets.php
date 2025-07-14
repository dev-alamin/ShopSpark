<?php
namespace ShopSpark;

use ShopSpark\Traits\HelperTrait;
class Assets {
    use HelperTrait;

	protected string $plugin_file;

	public function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	public function enqueue( string $handle, string $src, array $deps = array(), string $ver = '', bool $in_footer = false ): void {
		wp_enqueue_script( $handle, plugins_url( $src, $this->plugin_file ), $deps, $ver ?: null, $in_footer );
	}

	public function enqueue_style( string $handle, string $src, array $deps = array(), string $ver = '', string $media = 'all' ): void {
		wp_enqueue_style( $handle, plugins_url( $src, $this->plugin_file ), $deps, $ver ?: null, $media );
	}

	public function localize_script( string $handle, string $object_name, array $l10n ): void {
		wp_localize_script( $handle, $object_name, $l10n );
	}

	public function register_script( string $handle, string $src, array $deps = array(), string $ver = '', bool $in_footer = false ): void {
		wp_register_script( $handle, plugins_url( $src, $this->plugin_file ), $deps, $ver ?: null, $in_footer );
	}

	public function register_style( string $handle, string $src, array $deps = array(), string $ver = '', string $media = 'all' ): void {
		wp_register_style( $handle, plugins_url( $src, $this->plugin_file ), $deps, $ver ?: null, $media );
	}

	public function enqueue_admin_assets( string $hook ): void {
		// Load only on your plugin's admin page
		if ( strpos( $hook, 'shopspark' ) === false ) {
			return;
		}

        wp_register_style( 'shopspark-tailwind-css',  SHOP_SPARK_PLUGIN_ASSETS_URL . 'css/tailwind.min.css', array(), '3.4.1' );
        wp_enqueue_style( 'shopspark-tailwind-css' );

		// Tailwind CDN
		wp_register_script( 'shopspark-tailwind', SHOP_SPARK_PLUGIN_ASSETS_URL . 'js/tailwind-minimal.js', array(), '5.39.0' );
        wp_enqueue_script( 'shopspark-tailwind' ); // Enqueue it


		// Alpine JS CDN
        wp_register_script( 'shopspark-alpine', SHOP_SPARK_PLUGIN_ASSETS_URL . 'js/alpine.min.js', array(), '3.0.0', true );

		// wp_enqueue_script( 'shopspark-alpine', '//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', array(), '3.0.0', true );
		wp_enqueue_script( 'shopspark-admin', SHOP_SPARK_PLUGIN_ASSETS_URL . 'admin/admin.js', array( 'shopspark-alpine' ), '1.0', true );

		// Your own admin JS

        $hook_labels = $this->AllHooksListFlat(); // or your grouped/filtered hooks
        wp_localize_script( 'shopspark-admin', 'ShopSparkHookLabels', $hook_labels );
	}

    public function enqueue_frontend_assets( string $hook ): void {
        // Enqueue your frontend assets here
        // Tailwind CDN
		wp_register_style( 'shopspark-frontend-tailwind', SHOP_SPARK_PLUGIN_ASSETS_URL . 'dist/output.css', array(), time(), 'all' );

        wp_enqueue_style( 'shopspark-frontend-css', plugins_url( 'assets/css/frontend.css', $this->plugin_file ), array(), '1.0.0' );
        wp_enqueue_script( 'shopspark-frontend-js', plugins_url( 'assets/js/frontend.js', $this->plugin_file ), array( 'jquery' ), '1.0.0', true );

        wp_register_script( 'shopspark-alpine', SHOP_SPARK_PLUGIN_ASSETS_URL . 'js/alpine.min.js', array(), '3.0.0', true );
    }
}
