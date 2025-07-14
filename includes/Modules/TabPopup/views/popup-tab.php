<div x-data="{ openTab: null }" class="shopspark-relative shopspark-z-[999]">
    <!-- Trigger Buttons -->
    <div class="shopspark-space shopspark-mb-4">
        <?php foreach ( $tabs as $key => $tab ) : ?>
            <button
                @click="openTab = '<?php echo esc_attr( $key ); ?>'"
                class="shopspark-bg-blue-600 shopspark-text-white shopspark-px-4 shopspark-py-2 shopspark-rounded shopspark-mt-3 shopspark-min-w-[49%]"
            >
                <?php echo esc_html( $tab['title'] ); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ( $tabs as $key => $tab ) : ?>
        <!-- Popup Panel -->
        <div
            x-show="openTab === '<?php echo esc_attr( $key ); ?>'"
            x-transition:enter="shopspark-transition shopspark-ease-out shopspark-duration-300"
            x-transition:enter-start="shopspark-translate-x-full"
            x-transition:enter-end="shopspark-translate-x-0"
            x-transition:leave="shopspark-transition shopspark-ease-in shopspark-duration-200"
            x-transition:leave-start="shopspark-translate-x-0"
            x-transition:leave-end="shopspark-translate-x-full"
            x-cloak
            class="shopspark-fixed shopspark-inset-0 shopspark-z-50"
        >
            <!-- Overlay -->
            <div
                class="shopspark-fixed shopspark-inset-0 shopspark-bg-gray-900 shopspark-opacity-30 shopspark-backdrop-blur-sm"
                @click="openTab = null"
            ></div>

            <!-- Slide-in Panel -->
            <div
                class="shopspark-fixed shopspark-right-0 shopspark-top-0 shopspark-h-full shopspark-w-full sm:shopspark-w-96 shopspark-max-w-full shopspark-bg-white shopspark-shadow-xl shopspark-z-50 shopspark-p-6 shopspark-rounded-l-2xl shopspark-overflow-y-auto"
            >
                <button
                    class="shopspark-text-red-500 shopspark-float-right shopspark-mb-4"
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
