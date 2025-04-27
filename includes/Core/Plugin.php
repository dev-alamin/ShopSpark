<?php
namespace ShopSpark\Core;
use ShopSpark\Assets;
use ShopSpark\Admin\Menu;

class Plugin {

    protected string $plugin_file;
    protected Assets $assets;

    public function __construct(string $plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->assets = new Assets($plugin_file);
        // Admin Menu
         new Menu($plugin_file);
        
        add_action('wp_enqueue_scripts', [$this, 'load_global_assets']);
    }

    /**
     * Load global assets
     * @return void
     */
    public function load_global_assets(): void {
        // $this->assets->enqueue('shopspark-script', 'assets/js/script.js', ['jquery'], '1.0.0', true);
        $this->assets->enqueue_style('shopspark-style', 'assets/css/style.css', [], '1.0.0');
    }

    public function init(): void {
        $this->load_textdomain();

        $this->register_modules([
            \ShopSpark\Modules\QuickView\QuickViewServiceProvider::class,
            \ShopSpark\Modules\LoadMore\LoadMoreServiceProvider::class,
            // \ShopSpark\Modules\SideCart\SideCartServiceProvider::class,
            // Add more modules here
        ]);
    }

    protected function load_textdomain(): void {
        load_plugin_textdomain(
            'shopspark',
            false,
            dirname(plugin_basename($this->plugin_file)) . '/languages'
        );
    }

    protected function register_modules(array $providers): void {
        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                (new $provider())->register();
            }
        }
    }
}
