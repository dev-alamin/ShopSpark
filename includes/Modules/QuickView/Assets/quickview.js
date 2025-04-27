
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('shopspark-quick-view-modal');
    const modalContent = document.getElementById('shopspark-quick-view-content');
    const closeBtn = document.getElementById('shopspark-quick-view-close');

    // Open modal on click
    document.querySelectorAll('.shopspark-quick-view-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            
            // Show the modal
            modal.classList.remove('hidden');

            
            modalContent.innerHTML = `
            <div class="text-center text-gray-700">
                <svg class="animate-spin h-6 w-6 mx-auto text-purple-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
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
    


            
            // TODO: Fetch and insert actual product data using AJAX
        });
    });

    // Close modal
    closeBtn.addEventListener('click', function () {
        modal.classList.add('hidden');
        modalContent.innerHTML = '';
    });

    // Close on backdrop click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modalContent.innerHTML = '';
        }
    });

});

// function initQuickViewGallery() {
//     jQuery(document).on('click', '.gallery-thumbnail img', function () {
//         var fullImageUrl = jQuery(this).data('full-image');
//         jQuery('#main-product-image').attr('src', fullImageUrl);
//         jQuery('#main-product-image').attr('srcset', fullImageUrl);
//     });
// }

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
  
  /**
   * Initialize the variation form for product variations.
   * This function listens for changes in the variation select fields
   * and updates the variation ID accordingly.
   */
  function initVariationForm() {
    // Listen to change events on variation selects
    document.body.addEventListener('change', function(e) {
        if (e.target.closest('.variations_form')) {
            const form           = e.target.closest('.variations_form');
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
  