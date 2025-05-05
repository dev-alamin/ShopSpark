<?php
namespace ShopSpark\Modules\QuatityMinPul;
use ShopSpark\Core\ServiceProviderInterface;

/**
 * Class QualityServiceProvider
 *
 * @package ShopSpark\Modules\QuatityMinPul
 */
class QualityServiceProvider implements ServiceProviderInterface {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        add_action( 'woocommerce_before_single_product', [ $this, 'button_scripts'] );

        add_action( 'woocommerce_before_quantity_input_field', [ $this, 'minus_button'] );
        add_action( 'woocommerce_after_quantity_input_field', [ $this, 'plus_button'] );
    }

    /**
     * Enqueue the scripts and styles.
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( ! is_product() ) return;
        wp_enqueue_style( 'shopspark-quantity-buttons', SHOP_SPARK_PLUGIN_ASSETS_URL . 'quantity-button/quantitybutton.css', array(), SHOP_SPARK_VERSION );
    }

    /**
     * Enqueue the scripts and styles.
     *
     * @return void
     */
    public function minus_button() {
        if ( ! is_product() ) return;
        echo '<button type="button" class="minus shopspark-minus" >-</button>';
    }
    
    /**
     * Enqueue the scripts and styles.
     *
     * @return void
     */
    public function plus_button() {
        if ( ! is_product() ) return;
        echo '<button type="button" class="plus shopspark-plus" >+</button>';
    }
    
    /**
     * Enqueue the scripts and styles.
     *
     * @return void
     */
    public function button_scripts() {
        wc_enqueue_js( "
            $('form.cart').on( 'click', 'button.plus, button.minus', function() {
                    var qty = $( this ).closest( 'form.cart' ).find( '.qty' );
                    var val   = parseFloat(qty.val());
                    var max = parseFloat(qty.attr( 'max' ));
                    var min = parseFloat(qty.attr( 'min' ));
                    var step = parseFloat(qty.attr( 'step' ));
                    if ( $( this ).is( '.plus' ) ) {
                    if ( max && ( max <= val ) ) {
                        qty.val( max );
                    } else {
                        qty.val( val + step );
                    }
                    } else {
                    if ( min && ( min >= val ) ) {
                        qty.val( min );
                    } else if ( val > 1 ) {
                        qty.val( val - step );
                    }
                    }
                });
        " );
    }
}