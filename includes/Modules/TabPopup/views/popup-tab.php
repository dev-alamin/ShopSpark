<div x-data="{ openDescription: false, openAdditional: false, openReviews: false }" class="relative z-50">
    <!-- Trigger Buttons -->
    <div class="space-x-2 mb-4">
        <button @click="openDescription = true" class="bg-blue-600 text-white px-4 py-2 rounded">Description</button>
        <button @click="openAdditional = true" class="bg-green-600 text-white px-4 py-2 rounded">Additional Info</button>
        <button @click="openReviews = true" class="bg-purple-600 text-white px-4 py-2 rounded">Reviews</button>
    </div>

    <!-- Description Popup -->
    <div x-show="openDescription"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        x-cloak
        class="fixed inset-0 z-50">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-900 opacity-30 backdrop-blur-sm" @click="openDescription = false"></div>
        
        <!-- Slide-in Panel -->
        <div class="fixed right-0 top-0 h-full w-full sm:w-96 max-w-full bg-white shadow-xl z-50 p-6 rounded-l-2xl overflow-y-auto">
            <button class="text-red-500 float-right mb-4" @click="openDescription = false">✕</button>
            <h2 class="text-xl font-bold mb-4">Description</h2>
            <?php the_content(); ?>
        </div>
    </div>


    <!-- Additional Info Popup -->
    <div x-show="openAdditional"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        x-cloak
        class="fixed inset-0 z-50">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-900 opacity-30 backdrop-blur-sm" @click="openAdditional = false"></div>
        
        <!-- Slide-in Panel -->
        <div class="fixed right-0 top-0 h-full w-full sm:w-96 max-w-full bg-white shadow-xl z-50 p-6 rounded-l-2xl overflow-y-auto">
            <button class="text-red-500 float-right mb-4" @click="openAdditional = false">✕</button>
            <h2 class="text-xl font-bold mb-4">Additional Info</h2>
            <?php wc_display_product_attributes( wc_get_product() ); ?>
        </div>
    </div>

    <!-- Reviews Popup -->
    <div x-show="openReviews"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        x-cloak
        class="fixed inset-0 z-50">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-900 opacity-30 backdrop-blur-sm" @click="openReviews = false"></div>

        <!-- Slide-in Panel -->
        <div class="fixed right-0 top-0 h-full w-full sm:w-96 max-w-full bg-white shadow-xl z-50 p-6 rounded-l-2xl overflow-y-auto">
            <button class="text-red-500 float-right mb-4" @click="openReviews = false">✕</button>
            <h2 class="text-xl font-bold mb-4">Reviews</h2>
            <?php comments_template(); ?>
        </div>
    </div>

</div>
