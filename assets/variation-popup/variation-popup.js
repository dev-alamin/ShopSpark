document.addEventListener('DOMContentLoaded', function () {
    const variationContainer = document.getElementById('shopspark-variation-container');
    const variationList = document.querySelector('.shopspark-variation-list');
    const wcSelects = document.querySelectorAll('.single-product div.product table.variations select');

    if (!variationContainer || !variationList || wcSelects.length === 0) return;

    let activeSelect = null;
    let overlay = null;
    let justOpened = false;

    wcSelects.forEach(select => {
        // Prevent default dropdown behavior
        select.addEventListener('mousedown', e => e.preventDefault());

        select.addEventListener('click', function (e) {
            e.preventDefault();

            if (overlay) overlay.remove(); // clean previous overlay
            overlay = document.createElement('div');
            overlay.className = 'shopspark-overlay';
            Object.assign(overlay.style, {
                position: 'fixed',
                top: '0',
                left: '0',
                width: '100%',
                height: '100%',
                backgroundColor: 'rgba(0,0,0,0.5)',
                zIndex: '9999',
                backdropFilter: 'blur(5px)',
            });

            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';

            overlay.addEventListener('click', closePopup);

            activeSelect = this;
            variationList.innerHTML = '';

            Array.from(this.options).forEach(option => {
                if (option.value === '') return; // Only skip empty string

                const li = document.createElement('li');
                li.textContent = option.text;
                li.dataset.value = option.value;
                li.className = 'shopspark-option-item shopspark-cursor-pointer shopspark-w-full shopspark-bg-gray-100 hover:shopspark-bg-blue-500 hover:shopspark-text-white shopspark-px-4 shopspark-py-2 shopspark-rounded shopspark-transition shopspark-duration-200 shopspark-ease-in-out shopspark-text-sm shopspark-list-none';
                variationList.appendChild(li);
            });


            variationContainer.classList.remove('shopspark-hidden');
            justOpened = true;
            setTimeout(() => (justOpened = false), 100);
        });
    });

    // Handle user click on variation option
    variationList.addEventListener('click', function (e) {
        if (e.target.tagName !== 'LI' || !activeSelect) return;

        const selectedValue = e.target.dataset.value;
        activeSelect.value = selectedValue;
        activeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        closePopup();
    });

    // Close if clicked outside
    document.addEventListener('click', function (e) {
        if (justOpened) return;
        if (
            overlay &&
            !variationContainer.contains(e.target) &&
            !Array.from(wcSelects).includes(e.target)
        ) {
            closePopup();
        }
    });

    function closePopup() {
        variationContainer.classList.add('shopspark-hidden');
        if (overlay) overlay.remove();
        overlay = null;
        activeSelect = null;
        document.body.style.overflow = 'auto';
    }

    variationContainer.querySelector('button').addEventListener('click', function () {
        closePopup();
    }
    );
});
