<?php
namespace ShopSpark\Traits;

/**
 * HelperTrait for Frontend
 *
 * @package ShopSpark\Traits
 */
trait HelperTrait
{
    /**
     * Returns an array of WooCommerce hook locations for block or classic themes.
     *
     * @return array Hook => Label mappings.
     */
    public function All_WC_Product_Hooks(): array
    {
        $hooks = [];

        if( wp_is_block_theme() ) {
            $hooks = array(
                'woocommerce_before_main_content'           => __( 'Before Main Content', 'shopspark' ),
                'woocommerce_after_main_content'            => __( 'After Main Content', 'shopspark' ),
                'woocommerce_sidebar'                       => __( 'Sidebar', 'shopspark' ),
                'woocommerce_before_single_product'         => __( 'Before Single Product', 'shopspark' ),
                'woocommerce_before_single_product_summary' => __( 'Before Summary', 'shopspark' ),
                'woocommerce_single_product_summary'        => __( 'Summary', 'shopspark' ),
                'woocommerce_after_single_product_summary'  => __( 'After Summary', 'shopspark' ),
                'woocommerce_after_single_product'          => __( 'After Single Product', 'shopspark' ),
                'woocommerce_product_meta_start'            => __( 'Product Meta Start', 'shopspark' ),
                'woocommerce_product_meta_end'              => __( 'Product Meta End', 'shopspark' ),
                'woocommerce_share'                         => __( 'Share', 'shopspark' ),
                'woocommerce_before_add_to_cart_form'       => __( 'Before Add to Cart Form', 'shopspark' ),
                'woocommerce_after_add_to_cart_form'        => __( 'After Add to Cart Form', 'shopspark' ),
            );
        }

        $hooks = array(
            'woocommerce_before_single_product_summary' => __( 'Before Summary', 'shopspark' ),
            'woocommerce_single_product_summary'        => __( 'In Summary', 'shopspark' ),
            'woocommerce_before_add_to_cart_form'       => __( 'Before Add to Cart', 'shopspark' ),
            'woocommerce_before_variations_form'        => __( 'Before Variations', 'shopspark' ),
            'woocommerce_before_add_to_cart_button'     => __( 'Before Add to Cart Button', 'shopspark' ),
            'woocommerce_before_single_variation'       => __( 'Before Single Variation', 'shopspark' ),
            'woocommerce_single_variation'              => __( 'Single Variation', 'shopspark' ),
            'woocommerce_before_add_to_cart_quantity'   => __( 'Before Add to Cart Quantity', 'shopspark' ),
            'woocommerce_after_add_to_cart_quantity'    => __( 'After Add to Cart Quantity', 'shopspark' ),
            'woocommerce_after_single_variation'        => __( 'After Single Variation', 'shopspark' ),
            'woocommerce_after_add_to_cart_button'      => __( 'After Add to Cart Button', 'shopspark' ),
            'woocommerce_after_variations_form'         => __( 'After Variations', 'shopspark' ),
            'woocommerce_after_add_to_cart_form'        => __( 'After Add to Cart Form', 'shopspark' ),
            'woocommerce_product_meta_start'            => __( 'Product Meta Start', 'shopspark' ),
            'woocommerce_product_meta_end'              => __( 'Product Meta End', 'shopspark' ),
            'woocommerce_share'                         => __( 'Share', 'shopspark' ),
            'woocommerce_after_single_product_summary'  => __( 'After Single Product Summary', 'shopspark' ),
            'woocommerce_after_single_product'          => __( 'After Single Product', 'shopspark' ),
        );

        return apply_filters( 'shopspark_wc_product_hooks', $hooks );
    }

    /**
     * Get the Shop/Archive Page Hooks
     * 
     * @return array Hook => Label mappings.
     */
    public function All_WC_Archive_Hooks(): array
    {
        $hooks = array(
            'woocommerce_before_main_content'           => __( 'Before Main Content', 'shopspark' ),
            'woocommerce_archive_description'           => __( 'Archive Description', 'shopspark' ),
            'woocommerce_before_shop_loop'              => __( 'Before Shop Loop', 'shopspark' ),
            'woocommerce_no_products_found'             => __( 'No Products Found', 'shopspark' ),
            'woocommerce_before_shop_loop_item'         => __( 'Before Shop Loop Item', 'shopspark' ),
            'woocommerce_before_shop_loop_item_title'   => __( 'Before Product Title', 'shopspark' ),
            'woocommerce_shop_loop_item_title'          => __( 'Product Title', 'shopspark' ),
            'woocommerce_after_shop_loop_item_title'    => __( 'After Product Title', 'shopspark' ),
            'woocommerce_after_shop_loop_item'          => __( 'After Shop Loop Item', 'shopspark' ),
            'woocommerce_after_shop_loop'               => __( 'After Shop Loop', 'shopspark' ),
            'woocommerce_after_main_content'            => __( 'After Main Content', 'shopspark' ),
            'woocommerce_sidebar'                       => __( 'Sidebar', 'shopspark' ),
        );

        return apply_filters( 'shopspark_wc_shop_hooks', $hooks );
    }

    /**
     * Get Shop/Archive Page Loop Hooks
     * 
     * @return array Hook => Label mappings.
     */
    public function All_WC_Archive_Loop_Hooks(){
        
        $hooks = array(
            'woocommerce_after_shop_loop_item_title' => __( 'After Product Title', 'shopspark' ),
            'woocommerce_after_shop_loop_item'       => __( 'After Product Item', 'shopspark' ),
            'woocommerce_before_shop_loop_item_title'=> __( 'Before Product Title', 'shopspark' ),
            'woocommerce_before_shop_loop_item'      => __( 'Before Product Item', 'shopspark' ),
        );

        return apply_filters( 'shopspark_wc_shop_hooks', $hooks );
    }
}