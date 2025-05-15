document.addEventListener('DOMContentLoaded', function () {
    const variationContainer = document.getElementById('shopspark-variation-container');
    const variationList = document.querySelector('.shopspark-variation-list');
    const wcSelects = document.querySelectorAll('.single-product div.product table.variations select');

    if (!variationContainer || !variationList || wcSelects.length === 0) return 'No variation container or list found';

    let activeSelect = null;

    wcSelects.forEach(select => {
        select.addEventListener('mousedown', function (e) {
            e.preventDefault(); // prevent native dropdown

            activeSelect = this; // track which select was clicked

            variationList.innerHTML = ''; // clear old items

            // Loop through options and create custom list items
            Array.from(this.options).forEach(option => {
                if (!option.value) return; // skip placeholder

                const li = document.createElement('li');
                li.textContent = option.text;
                li.dataset.value = option.value;

                li.classList.add(
                    'shopspark-option-item',
                    'cursor-pointer',
                    'bg-gray-100',
                    'hover:bg-blue-500',
                    'hover:text-white',
                    'px-4',
                    'py-2',
                    'rounded',
                    'transition',
                    'duration-200',
                    'ease-in-out',
                    'text-sm',
                    'list-none',
                );

                variationList.appendChild(li);
            });


            variationContainer.classList.remove('hidden');
        });
    });

    // When user clicks on a custom <li>
    variationList.addEventListener('click', function (e) {
        if (e.target.tagName !== 'LI' || !activeSelect) return;

        const selectedValue = e.target.dataset.value;

        // Set the selected option in the correct select
        activeSelect.value = selectedValue;

        // Trigger change event so WooCommerce can react
        activeSelect.dispatchEvent(new Event('change', { bubbles: true }));

        // Hide the custom popup
        variationContainer.classList.add('hidden');

        activeSelect = null;
    });

    // Optional: close popup if clicked outside
    document.addEventListener('click', function (e) {
        if (!variationContainer.contains(e.target) && !Array.from(wcSelects).includes(e.target)) {
            variationContainer.classList.add('hidden');
            activeSelect = null;
        }
    });
});
