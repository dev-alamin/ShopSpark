<?php
namespace ShopSpark\Admin;

use ShopSpark\Admin\GeneralTab;

class Menu {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'shopspark_register_settings' ) );
		new GeneralTab();
	}

	public function add_admin_menu(): void {
		add_menu_page(
			__( 'ShopSpark Settings', 'shopspark' ),
			__( 'ShopSpark', 'shopspark' ),
			'manage_options',
			'shopspark',
			array( $this, 'page' ),
			'dashicons-admin-generic',
			100
		);
	}

	public function page(): void {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        $modules    = apply_filters(
            'shopspark_admin_settings_tabs',
            array(
                'general'      => __( 'General', 'shopspark' ),
                'product_page' => __( 'Product Page', 'shopspark' ),
                'quick_view'   => __( 'Quick View', 'shopspark' ),
                'wishlist'     => __( 'Wishlist', 'shopspark' ),
                'compare'      => __( 'Compare', 'shopspark' ),
            )
        );
        ?>
        <div class="wrap bg-gradient-to-r from-white via-white to-gray-50 rounded-lg shadow-md" x-data="{ tab: '<?php echo esc_attr( $active_tab ); ?>' }">

            <h1 class="!text-4xl !font-bold !mb-4 !mt-4 !pt-6 text-center"><?php _e( 'ShopSpark Settings', 'shopspark' ); ?></h1>

            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <?php foreach ( $modules as $key => $label ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=shopspark&tab=' . $key ) ); ?>"
                        class="px-4 py-2 font-medium text-lg border-b-2 <?php echo $active_tab === $key ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-transparent hover:border-gray-300'; ?>">
                            <?php echo esc_html( $label ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="bg-gradient-to-r from-indigo-50 via-purple-100 to-pink-50 p-6 rounded-lg">

                <?php
                // If the active tab is 'product_page', show nested tabs
                if ( 'product_page' === $active_tab ) {
                    // Get nested tabs via filter, fallback to example tabs
                    $nested_tabs = apply_filters(
                        'shopspark_admin_product_page_nested_tabs',
                        array(
                            'general_info' => __( 'General Info', 'shopspark' ),
                            'advanced'     => __( 'Advanced Settings', 'shopspark' ),
                            'styles'       => __( 'Styles', 'shopspark' ),
                        )
                    );

                    // Pick nested tab from query var or default
                    $active_nested_tab = isset( $_GET['subtab'] ) ? sanitize_text_field( $_GET['subtab'] ) : key($nested_tabs);
                    ?>

                    <div x-data="{ nestedTab: '<?php echo esc_attr( $active_nested_tab ); ?>' }" class="mb-6">
                        <nav class="flex space-x-4 border-b border-gray-300 mb-4">
                            <?php foreach ( $nested_tabs as $subkey => $sublabel ) : ?>
                                <button
                                    @click.prevent="nestedTab = '<?php echo esc_attr( $subkey ); ?>'"
                                    :class="nestedTab === '<?php echo esc_attr( $subkey ); ?>' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'"
                                    class="pb-2 px-3"
                                >
                                    <?php echo esc_html( $sublabel ); ?>
                                </button>
                            <?php endforeach; ?>
                        </nav>

                        <div>
                            <?php
                            // Render nested tab content via hook to allow modular extension
                            foreach ( $nested_tabs as $subkey => $sublabel ) {
                                ?>
                                <div x-show="nestedTab === '<?php echo esc_attr( $subkey ); ?>'" x-cloak>
                                    <?php do_action( "shopspark_admin_product_page_panel_{$subkey}" ); ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                <?php
                } else {
                    // Default: load tab content for other main tabs
                    do_action( "shopspark_admin_settings_panel_{$active_tab}" );
                }
                ?>

            </div>
        </div>
        <?php
    }


	function shopspark_register_settings() {
		// die( 'we are getting' );
		register_setting( 'shopspark_general_settings', 'shopspark_general_settings' );
		// shopspark_quick_view_settings
		register_setting( 'shopspark_quick_view_settings', 'shopspark_quick_view_settings' );
        // shopspark_product_page_settings
        register_setting( 'shopspark_product_page_settings', 'shopspark_product_page_settings' );
	}
}