// Upon selecting a variation in WooCommerce, this script will update the product title to include the selected variation attributes.
// Not replacing the original title, but appending the selected attributes to it.
document.addEventListener('DOMContentLoaded', function() {
    // Store the original product title
    var originalTitle = document.querySelector('.product_title').textContent;

    // Function to update the product title with selected variation attributes
    function updateProductTitle() {
        // Get the selected variation attributes
        var selectedAttributes = [];
        var selects = document.querySelectorAll('.variations select');
        selects.forEach(function(select) {
            var selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                selectedAttributes.push(selectedOption.textContent);
            }
        });

        // Update the product title with selected attributes
        var productTitle = document.querySelector('.product_title');
        if (selectedAttributes.length > 0) {
            var newTitle = originalTitle + ' - ' + selectedAttributes.join(', ');
            productTitle.textContent = newTitle;
        } else {
            // If no attributes are selected, revert to the original title
            productTitle.textContent = originalTitle;
        }
    }

    // Bind the updateProductTitle function to the change event of variation selects
    var selects = document.querySelectorAll('.variations select');
    selects.forEach(function(select) {
        select.addEventListener('change', updateProductTitle);
    });
});