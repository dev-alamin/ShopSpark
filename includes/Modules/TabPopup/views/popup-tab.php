<div x-data="{ openTab: null }" class="relative z-50">
    <!-- Trigger Buttons -->
    <div class="space-x-2 mb-4">
        <?php foreach ( $tabs as $key => $tab ) : ?>
            <button
                @click="openTab = '<?php echo esc_attr( $key ); ?>'"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                <?php echo esc_html( $tab['title'] ); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ( $tabs as $key => $tab ) : ?>
        <!-- Popup Panel -->
        <div
            x-show="openTab === '<?php echo esc_attr( $key ); ?>'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            x-cloak
            class="fixed inset-0 z-50"
        >
            <!-- Overlay -->
            <div
                class="fixed inset-0 bg-gray-900 opacity-30 backdrop-blur-sm"
                @click="openTab = null"
            ></div>

            <!-- Slide-in Panel -->
            <div
                class="fixed right-0 top-0 h-full w-full sm:w-96 max-w-full bg-white shadow-xl z-50 p-6 rounded-l-2xl overflow-y-auto"
            >
                <button
                    class="text-red-500 float-right mb-4"
                    @click="openTab = null"
                >✕</button>
                <?php
                if ( is_callable( $tab['callback'] ) ) {
                    call_user_func( $tab['callback'], $key, $tab );
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>