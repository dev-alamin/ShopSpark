<?php
namespace ShopSpark\Modules\Global;

use Dom\Element;
use ShopSpark\TemplateFunctions;
use ShopSpark\Core\ServiceProviderInterface;
use ShopSpark\Traits\HelperTrait;

class ElementPusherServiceProvider implements ServiceProviderInterface{
    protected array $settings;
    protected string $settings_field;

    use HelperTrait;

    public function __construct() {
        $this->settings = (array) get_option( 'shopspark_global_element_pusher', [] );
        $this->settings_field = 'shopspark_global_element_pusher';
    }

    public function register(): void {
        add_action( 'shopspark_admin_global_panel_element_pusher', array( $this, 'settings' ) );
        add_action( 'wp_enqueue_assets', [ $this, 'enqueueAssets' ] );

        /**
         * Iterates over the 'text_repeater_items' settings array to dynamically register WordPress actions.
         *
         * For each active hook configuration, this code attaches an anonymous function to the specified WordPress hook.
         * The callback processes the provided content by executing shortcodes and then safely outputs the result,
         * allowing only permitted HTML tags using wp_kses_post().
         *
         * @var array $hooks Array of hook configurations, each containing:
         *                   - 'hook'   (string): The WordPress hook name to attach to.
         *                   - 'content' (string): The content to output, possibly containing shortcodes.
         *                   - 'active' (string|int): Whether the hook is active ('1' for active, '0' for inactive).
         *
         * @see do_shortcode() For processing shortcodes within the content.
         * @see wp_kses_post() For safe output of HTML content.
         */
        // Dynamic Hook
        $hooks = $this->settings['text_repeater_items'] ?? [];

        foreach ( $hooks as $hook ) {
            if( $hook['active'] === '0' ) continue;

            add_action( $hook['hook'], function() use ( $hook ) {
                $content = $hook['content'];

                // Process shortcodes and then escape
                $processed_content = do_shortcode( $content );

                // Output safely (allowing HTML)
                echo wp_kses_post( $processed_content );
            } );
        }


    }

    /**
     * Enqueue assets
     *
     * @return void
     */
    public function enqueueAssets(): void {
        wp_enqueue_style( 'shopspark-buy-now-button-css', SHOP_SPARK_PLUGIN_ASSETS_URL . 'buy-now-button/buy-now-button.css', array(), SHOP_SPARK_VERSION );
        wp_enqueue_script( 'shopspark-buy-now-button-js', SHOP_SPARK_PLUGIN_ASSETS_URL . 'buy-now-button/buy-now-button.js', array( 'jquery' ), SHOP_SPARK_VERSION, true );
    }

    /**
     * Settings
     *
     * @return void
     */
    public function settings() {
        $options = $this->settings ?? [];
        $hooks   = $this->AllHooksListFlat();

        foreach ( $options['text_repeater_items'] ?? [] as &$item ) {
            if ( ! isset( $item['active'] ) ) {
                $item['active'] = true;
            }
        }
        $items_json = json_encode($options['text_repeater_items'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
        ?>
        <div class="max-w-5xl mx-auto">
            <h2 class="!text-3xl !font-semibold text-gray-800 mb-4 border-b border-gray-300 pb-2">
                <?php _e( 'Element Pusher Repeater Options', 'shopspark' ); ?>
            </h2>

        <form method="post" action="options.php"
                x-data='{
                items: (<?php echo $items_json ?? "[]" ?>).map(item => ({
                    ...item,
                    active: item.active == "1" || item.active === true
                }))

                    }'

                class="space-y-6"
            >
                <?php settings_fields( $this->settings_field ); ?>
                <input type="hidden" name="shopspark_global_element_pusher[text_repeater_items]" x-model="JSON.stringify(items)">

            <template x-for="(item, index) in items" :key="index">
                <div class="border rounded bg-white">
                    <!-- Accordion Header -->
                    <button 
                        type="button" 
                        class="w-full text-left p-4 font-semibold flex justify-between items-center"
                        @click="item.open = !item.open"
                        x-init="item.open = false"
                    >
                        <span
                            x-text="item.title || 'Untitled Text Block'"
                            class="text-lg font-semibold text-gray-900 truncate max-w-xs block"
                            >
                        </span>


                        <span
                            x-text="item.active ? 'Active' : 'Inactive'"
                            :class="item.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            class="ml-2 px-2 py-1 rounded text-xs font-semibold"
                            >
                        </span>



                        <svg 
                            :class="{'transform rotate-180': item.open}" 
                            class="w-5 h-5 transition-transform duration-200" 
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
                        >
                            <path d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Accordion Content -->
                    <div 
                        x-show="item.open" 
                        x-transition 
                        class="p-6 space-y-5 bg-gray-50 rounded-md shadow-sm"
                    >
                        <label class="block">
                            <span class="text-gray-800 font-medium mb-1 block text-base">Title (Optional)</span>
                            <input 
                                type="text"
                                :name="'shopspark_global_element_pusher[text_repeater_items][' + index + '][title]'"
                                x-model="item.title"
                                class="form-input mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                placeholder="Enter title here..."
                            />
                        </label>

                        <label class="flex items-center mt-3 space-x-2">
                            <input type="hidden"
                                :name="'shopspark_global_element_pusher[text_repeater_items][' + index + '][active]'"
                                value="0" />
                            <input 
                                type="checkbox"
                                :name="'shopspark_global_element_pusher[text_repeater_items][' + index + '][active]'"
                                x-model="item.active"
                                :value="1"
                                class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                            />
                            <span class="text-gray-800 text-base font-medium">Active</span>
                        </label>

                        <label class="block">
                            <span class="text-gray-800 font-medium mb-1 block text-base">Text/HTML Content</span>
                            <textarea
                                :name="'shopspark_global_element_pusher[text_repeater_items][' + index + '][content]'"
                                x-model="item.content"
                                class="form-textarea mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                rows="5"
                                placeholder="Enter HTML or text content here..."
                            ></textarea>
                        </label>

                        <label class="block">
                            <span class="text-gray-800 font-medium mb-1 block text-base">Hook Location</span>
                            <select
                                :name="'shopspark_global_element_pusher[text_repeater_items][' + index + '][hook]'"
                                x-model="item.hook"
                                class="form-select mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >
                                <?php foreach ( $hooks as $hook => $label ) : ?>
                                    <option :selected="item.hook === '<?php echo esc_js( $hook ); ?>'" value="<?php echo esc_attr( $hook ); ?>">
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-gray-800 font-medium mb-1 block text-base">Priority (default: 10)</span>
                            <input 
                                type="number"
                                :name="'shopspark_global_element_pusher[text_repeater_items][' + index + '][priority]'"
                                x-model="item.priority"
                                class="form-input mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                min="0"
                            />
                        </label>

                        <button 
                            type="button" 
                            @click="items.splice(index, 1)" 
                            class="text-red-600 text-sm hover:underline mt-3"
                        >
                            <?php _e( 'Remove This Item', 'shopspark' ); ?>
                        </button>
                    </div>

                </div>
            </template>

            <button type="button" @click="items.push({ title: '', content: '', hook: '', priority: 10, open: true })"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4">
                <?php _e( 'Add New Block', 'shopspark' ); ?>
            </button>

            <div class="mt-6">
                <?php echo TemplateFunctions::saveButton(); ?>
            </div>
        </form>

        </div>
        <?php
    }
}