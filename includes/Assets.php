<?php
namespace ShopSpark;

class Assets
{
    protected string $plugin_file;

    public function __construct(string $plugin_file)
    {
        $this->plugin_file = $plugin_file;
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue(string $handle, string $src, array $deps = [], string $ver = '', bool $in_footer = false): void
    {
        wp_enqueue_script($handle, plugins_url($src, $this->plugin_file), $deps, $ver ?: null, $in_footer);
    }

    public function enqueue_style(string $handle, string $src, array $deps = [], string $ver = '', string $media = 'all'): void
    {
        wp_enqueue_style($handle, plugins_url($src, $this->plugin_file), $deps, $ver ?: null, $media);
    }

    public function localize_script(string $handle, string $object_name, array $l10n): void
    {
        wp_localize_script($handle, $object_name, $l10n);
    }

    public function register_script(string $handle, string $src, array $deps = [], string $ver = '', bool $in_footer = false): void
    {
        wp_register_script($handle, plugins_url($src, $this->plugin_file), $deps, $ver ?: null, $in_footer);
    }

    public function register_style(string $handle, string $src, array $deps = [], string $ver = '', string $media = 'all'): void
    {
        wp_register_style($handle, plugins_url($src, $this->plugin_file), $deps, $ver ?: null, $media);
    }

    public function enqueue_admin_assets(string $hook): void
    {
        // Load only on your plugin's admin page
        if (strpos($hook, 'shopspark') === false) {
            return;
        }

        // Tailwind CDN
        wp_enqueue_style('shopspark-tailwind', '//cdn.jsdelivr.net/npm/@tailwindcss/browser@4', [], '3.4.1');

        // Tailwind JS 
        wp_enqueue_script( 'shopspark-tailwindjs', '//cdn.jsdelivr.net/npm/@tailwindcss/browser@4' );

        // Alpine JS CDN
        wp_enqueue_script('shopspark-alpine', '//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true);

        // Your own admin JS
        wp_enqueue_script('shopspark-admin-js', plugins_url('admin/assets/js/admin.js', $this->plugin_file), ['shopspark-alpine'], '1.0', true);
    }
}