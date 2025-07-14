document.addEventListener('DOMContentLoaded', function () {
    const backendData = shopspark_ajax_add_to_cart;
    const URL = backendData.ajaxUrl;
    const nonce = backendData.nonce;

    const addToCartButton = document.querySelector('.single-product .single_add_to_cart_button');

    if (!addToCartButton) {
        console.warn('Add to Cart button not found on the page.');
        return;
    }

    addToCartButton.addEventListener('click', function (event) {
    event.preventDefault();

    const container = this.closest('.variations_button') || this.closest('form.cart');

    if (!container) {
        console.error('Could not find container for add to cart button.');
        return;
    }

    // Product IDs
    const productIdInput = container.querySelector('input[name="add-to-cart"], input[name="product_id"], button[name="add-to-cart"]');
    const productId = productIdInput ? productIdInput.value : null;

    // Variation ID (if any)
    const variationIdInput = container.querySelector('input.variation_id');
    const variationId = variationIdInput ? variationIdInput.value : 0;

    // Quantity input (for simple or variable)
    const quantityInput = container.querySelector('input.qty');
    const quantity = quantityInput ? quantityInput.value : 1;

    // Check if grouped product form exists on the page (usually the grouped product page)
    const groupedForm = document.querySelector('form.cart.grouped_form');
    let quantities = {};

    if (groupedForm && productId == groupedForm.querySelector('input[name="add-to-cart"]').value) {
        // Collect quantities for grouped product child items
        quantities = {};
        groupedForm.querySelectorAll('input[name^="quantity["]').forEach(input => {
            const name = input.getAttribute('name');  // e.g. quantity[68]
            const match = name.match(/quantity\[(\d+)\]/);
            if (match) {
                const childId = match[1];
                const qty = parseInt(input.value, 10) || 0;
                if (qty > 0) {
                    quantities[childId] = qty;
                }
            }
        });

        if (Object.keys(quantities).length === 0) {
            alert('Please enter quantity for at least one product.');
            return;
        }
    }

    this.disabled = true;

    // Build POST data
    const postData = {
        action: 'shopspark_ajax_add_to_cart',
        product_id: productId,
        variation_id: variationId,
        quantity: quantity,
        _wpnonce: nonce,
    };

    // If grouped product quantities exist, add them as JSON string
    if (Object.keys(quantities).length > 0) {
        postData.quantities = JSON.stringify(quantities);
    }

    fetch(URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: new URLSearchParams(postData),
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            jQuery(document.body).trigger('wc_fragment_refresh');

            const noticesWrapper = document.querySelector('.woocommerce-notices-wrapper');
            if (noticesWrapper) {
                noticesWrapper.innerHTML = data.data.notice;
            }
            //console.log('Product added to cart:', data);
        } else {
            //console.error('Error adding to cart:', data.data?.message || data.message);
            alert(data.data?.message || data.message || 'Failed to add product to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        this.disabled = false;
    });
});

});
