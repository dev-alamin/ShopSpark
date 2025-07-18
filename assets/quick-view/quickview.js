
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('shopspark-quick-view-modal');
    const modalContent = document.getElementById('shopspark-quick-view-content');
    const closeBtn = document.getElementById('shopspark-quick-view-close');

    // Open modal on click
    // Use event delegation to handle clicks on dynamically added buttons
    // Use event delegation to handle clicks on dynamically added buttons
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.shopspark-quick-view-btn');

        if (btn) {

            e.preventDefault();
            const productId = btn.getAttribute('data-product-id');
            
            // Show the modal
            modal.classList.remove('shopspark-hidden');

            modalContent.innerHTML = `
                <div class="text-center shopspark-text-gray-700">
                    <svg class="shopspark-animate-spin shopspark-h-6 shopspark-w-6 shopspark-mx-auto shopspark-text-purple-600 shopspark-mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="shopspark-opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="shopspark-opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8z">
                        </path>
                    </svg>
                    Loading product...
                </div>
            `;

        fetch(shopspark_ajax.ajax_url + '?action=shopspark_quick_view&product_id=' + productId)
        .then(res => res.text())
        .then(html => {
            modalContent.innerHTML = html;
    
            // ✅ Run this AFTER content is inserted
            initQuickViewGallery();

            // Initialize the variation form
            initVariationForm();
        });

        }
    });

    // Close modal
    closeBtn.addEventListener('click', function () {
        modal.classList.add('shopspark-hidden');
        modalContent.innerHTML = '';
    });

    // Close on backdrop click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.add('shopspark-hidden');
            modalContent.innerHTML = '';
        }
    });

});

function initQuickViewGallery() {
    if (!window.Swiper) return;
  
    const thumbs = new Swiper('.thumb-gallery', {
      spaceBetween: 10,
      slidesPerView: 4,
      freeMode: true,
      watchSlidesProgress: true,
      watchSlidesVisibility: true, // helps auto-scroll to active
      slideToClickedSlide: true,   // allows clicking a thumb to jump
    });
  
    const main = new Swiper('.main-gallery', {
      spaceBetween: 10,
      loop: true,
      effect: 'fade',
      thumbs: {
        swiper: thumbs,
      },
      on: {
        slideChange: function () {
          // scroll thumbnail into view if needed
          thumbs.slideTo(this.realIndex);
        }
      }
    });
  }
  
  function initVariationForm() {
    document.body.addEventListener('submit', function(e) {
        const form = e.target;

        if (
            form.classList.contains('shopspark_variations_form') || 
            form.classList.contains('shopspark_simple_add_to_cart_form')
        ) {
            e.preventDefault();
            e.stopPropagation();

            const addToCartButton = form.querySelector('.add-to-cart-btn');
            if (!addToCartButton || addToCartButton.disabled) return;

            addToCartButton.disabled = true;
            addToCartButton.classList.add('shopspark-opacity-50', 'pointer-events-none');

            const originalButtonHTML = addToCartButton.innerHTML;

            let formData = new FormData(form);

            fetch(shopspark_ajax.ajax_url, {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'shopspark_add_to_cart',
                    ...Object.fromEntries(formData)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addToCartButton.innerHTML = `
                        <span>${data.data.added_message}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="shopspark-h-5 shopspark-w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    `;
                    showShopSparkToast(data.data.message, 'success');
                    showShopSparkToast(data.data.tot_message + ' ' + data.data.cart_count, 'success', true);
                    jQuery('body').trigger('wc_fragment_refresh');
                } else {
                    showShopSparkToast(data.data.message || 'Failed to add to cart.', 'error');
                    addToCartButton.innerHTML = originalButtonHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                addToCartButton.innerHTML = originalButtonHTML;
                showShopSparkToast('Error adding to cart.', 'error');
            })
            .finally(() => {
                addToCartButton.disabled = false;
                addToCartButton.classList.remove('opacity-50', 'pointer-events-none');
            });
        }
    });

    // Variations change listener (unchanged)
    document.body.addEventListener('change', function(e) {
        if (e.target.closest('.shopspark_variations_form')) {
            const form           = e.target.closest('.shopspark_variations_form');
            const data           = new FormData(form);
            const variationsJSON = form.dataset.product_variations;
            const variations     = JSON.parse(variationsJSON);

            let matching = variations.find(variation => {
                return Object.keys(variation.attributes).every(attrName => {
                    const fieldName = 'attribute_' + attrName.replace('attribute_', '');
                    return (!variation.attributes[attrName] || variation.attributes[attrName] === data.get(fieldName));
                });
            });

            if (matching) {
                form.querySelector('.variation_id').value = matching.variation_id;
            } else {
                form.querySelector('.variation_id').value = '';
            }
        }
    });
}

function showShopSparkToast(message, type = 'success', isCartCountMessage = false) {
    const container = document.getElementById('shopspark-toast-container');

    if (!container) return;

    // Create the toast element
    const toast = document.createElement('div');
    toast.className = `
        toast-message shopspark-flex shopspark-items-center shopspark-max-w-xs shopspark-w-full shopspark-p-4 
        shopspark-text-gray-900 shopspark-bg-white shopspark-rounded-lg shopspark-shadow-lg
        ${type === 'success' ? 'shopspark-border-l-4 shopspark-border-green-500' : 'shopspark-border-l-4 shopspark-border-red-500'}
        shopspark-animate-fade-in-up
        ${isCartCountMessage ? 'cart-count-toast' : 'add-to-cart-toast'}
    `;

    toast.innerHTML = `
        <div class="shopspark-text-sm shopspark-font-medium">${message}</div>
    `;

    // === NEW FIX: Always remove previous same type toast ===
    if (isCartCountMessage) {
        const existingCartToast = container.querySelector('.cart-count-toast');
        if (existingCartToast) {
            existingCartToast.remove();
        }
    } else {
        const existingAddToCartToast = container.querySelector('.add-to-cart-toast');
        if (existingAddToCartToast) {
            existingAddToCartToast.remove();
        }
    }

    // === Add in correct order ===
    if (isCartCountMessage) {
        const existingAddToCartToast = container.querySelector('.add-to-cart-toast');
        if (existingAddToCartToast) {
            container.insertBefore(toast, existingAddToCartToast.nextSibling);
        } else {
            container.appendChild(toast);
        }
    } else {
        container.appendChild(toast);
    }

    // === Auto remove after 3s ===
    setTimeout(() => {
        toast.classList.add('shopspark-opacity-0', 'shopspark-transition', 'shopspark-duration-500');
        setTimeout(() => {
            toast.remove();
        }, 500);
    }, 3000);
}


function adjustQuantity(action, productId) {
    var quantityInput = document.getElementById('quantity_' + productId); 
    var currentValue = parseInt(quantityInput.value);  // Current quantity
    var maxQuantity = parseInt(quantityInput.max) || Infinity;  // Max allowed quantity
    var minQuantity = parseInt(quantityInput.min) || 1;  // Min allowed quantity
    var step = parseInt(quantityInput.step) || 1;  // Step for increment/decrement

    if (action === 'increase') {
        if (currentValue < maxQuantity) {
            // Increase quantity
            quantityInput.value = currentValue + step;
        }
    } else if (action === 'decrease') {
        if (currentValue > minQuantity) {
            // Decrease quantity
            quantityInput.value = currentValue - step;
        }
    }
}

// Event listener for plus and minus buttons
document.querySelectorAll('.plus').forEach(button => {
    button.addEventListener('click', function() {
        var productId = this.getAttribute('data-product-id');
        adjustQuantity('increase', productId);
    });
});

document.querySelectorAll('.minus').forEach(button => {
    button.addEventListener('click', function() {
        var productId = this.getAttribute('data-product-id');
        adjustQuantity('decrease', productId);
    });
});
