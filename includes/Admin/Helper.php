<?php 
namespace ShopSpark\Admin;

class Helper {
    /**
     * Render reusable nested admin tabs.
     *
     * @param string $hook_prefix      Prefix for do_action hook and filter.
     * @param array  $tabs             Array of tab keys and labels.
     * @param string $query_var_name   The query variable to track active tab (default: subtab).
     */
    public static function render_nested_tabs(string $hook_prefix, array $tabs, string $query_var = 'subtab') {
        $active_tab = isset($_GET[$query_var]) ? sanitize_text_field($_GET[$query_var]) : key($tabs);
        ?>
        <div x-data="{ nestedTab: '<?php echo esc_attr($active_tab); ?>' }" class="mb-6">
            <nav class="flex space-x-4 border-b border-gray-300 mb-4 flex-wrap">
                <?php foreach ($tabs as $key => $label) : ?>
                    <button
                        @click.prevent="nestedTab = '<?php echo esc_attr($key); ?>'"
                        :class="nestedTab === '<?php echo esc_attr($key); ?>' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600'"
                        class="pb-2 px-3"
                    >
                        <?php echo esc_html($label); ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <div>
                <?php foreach ($tabs as $key => $label) : ?>
                    <div x-show="nestedTab === '<?php echo esc_attr($key); ?>'" x-cloak>
                        <?php do_action("shopspark_{$hook_prefix}_panel_{$key}"); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}