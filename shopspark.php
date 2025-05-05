<?php
/**
 * Plugin Name: ShopSpark
 * Plugin URI:  https://www.shopspark.com
 * Description: A Plugin to enhance your Woocommerce Shop Experience, Add multiple much needed features to your website.
 * Version:     1.0.0
 * Author:      ShalikTheme
 * Author URI:  https://www.shaliktheme.com
 * Author URI:  Author URL
 * Text Domain: shopspark
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Requires at least: 5.4
 * Requires PHP: 7.0
 * Requires Plugins: Woocommerce
 *
 * @package     ShopSpark
 * @author      ShalikTheme
 * Author URI:  https://www.shaliktheme.com
 * @copyright   2025 ShalikTheme
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 *
 * Prefix:      ShopSpark
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'SHOP_SPARK_VERSION', '1.0.0' );
define( 'SHOP_SPARK_PLUGIN', __FILE__ );
define( 'SHOP_SPARK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SHOP_SPARK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHOP_SPARK_PLUGIN_ASSETS_URL', SHOP_SPARK_PLUGIN_URL . 'assets/' );
define( 'SHOP_SPARK_PLUGIN_ADMIN_PATH', SHOP_SPARK_PLUGIN_PATH . 'includes/Admin/' );
define( 'SHOP_SPARK_PLUGIN_INLUCDES_PATH', SHOP_SPARK_PLUGIN_PATH . 'includes/' );
define( 'SHOP_SPARK_PLUGIN_TEMPLATE_PATH', SHOP_SPARK_PLUGIN_PATH . 'Templates/' );
define( 'SHOP_SPARK_PLUGIN_ADMIN_TEMPLATE_PATH', SHOP_SPARK_PLUGIN_PATH . 'Templates/Admin/' );
define( 'SHOP_SPARK_PLUGIN_FRONTEND_TEMPLATE_PATH', SHOP_SPARK_PLUGIN_PATH . 'Templates/Frontend/' );

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use ShopSpark\Core\Plugin;

function shopspark_bootstrap() {
    $plugin = new Plugin(__FILE__);
    $plugin->init();
}
add_action('plugins_loaded', 'shopspark_bootstrap');