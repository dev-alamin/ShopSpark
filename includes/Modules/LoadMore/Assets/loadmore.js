"use strict";

document.addEventListener('DOMContentLoaded', function () {
    const loadMoreButton = document.getElementById('load-more-products');
    const productContainer = document.querySelector('.products') || document.querySelector('.wc-block-product-template__responsive'); // Your product grid container
    const paginationLinks = document.querySelectorAll('.woocommerce-pagination .page-numbers'); // Pagination links

    let currentPage = getCurrentPageFromUrl(); // Get the initial page from the URL

    // Set the initial data-page attribute on the button
    if (loadMoreButton) {
        loadMoreButton.setAttribute('data-page', currentPage);
    }

    // Function to get the current page number from the URL
    function getCurrentPageFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        let page = 1; // Default to page 1
        const pageMatch = window.location.pathname.match(/\/page\/(\d+)/);
        if (pageMatch && pageMatch[1]) {
            page = parseInt(pageMatch[1]);
        }
        return page;
    }

    // Function to update the URL with the current page
    function updateUrlWithPage(page) {
        const newUrl = `${window.location.origin}/shop/page/${page}/`;
        history.pushState({ page: page }, '', newUrl); // Update URL to reflect current page
    }
    

    // Function to replace products with the new ones
    function replaceProductsWithNew(responseHtml) {
        // If the response is empty, stop the process (no more products)
        if (responseHtml.trim() === '' || responseHtml == null) {
            loadMoreButton.style.display = 'none'; // Hide the button when no more products are available
            return;
        }
        
        // Replace the existing products with the new ones
        productContainer.innerHTML = responseHtml;
    }

    // Function to load more products based on page
    function loadMoreProducts(page = currentPage) {
        currentPage = page;

        if (page !== currentPage) {
            productContainer.innerHTML = ''; // Clear the old products if going to a new page
        }

        const data = new FormData();
        data.append('action', 'load_more_products');
        data.append('page', currentPage);

        // Make the Fetch request
        fetch(loadmore_params.ajax_url, {
            method: 'POST',
            body: data,
        })
        .then(response => response.text())
        .then(responseHtml => {
            replaceProductsWithNew(responseHtml);
            loadMoreButton.setAttribute('data-page', currentPage); // Update the button's page number
            updateUrlWithPage(currentPage); // Update the URL to reflect the current page
        })
        .catch(error => {
            console.error('Error loading more products:', error);
        });
    }

    // Function to handle button click event
    function handleButtonClick() {
        currentPage++; // Increment the page number when the button is clicked
        loadMoreProducts(currentPage); // Load products for the next page
    }

    // Function to handle infinite scroll using IntersectionObserver
    function handleInfiniteScroll() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadMoreProducts(currentPage + 1); // Increment page number for infinite scroll
                }
            });
        }, {
            rootMargin: '100px', // Trigger before the end of the page
        });

        // Target the button itself (or any other element to trigger scroll-based load)
        if (loadMoreButton) {
            observer.observe(loadMoreButton);
        }
    }

    // Function to handle pagination link click event
    function handlePaginationLinkClick(event) {
        event.preventDefault(); // Prevent the default link behavior
    
        const pageLink = event.target;
        
        // Check if the clicked element is a valid pagination link (number or arrow)
        if (!pageLink.classList.contains('page-numbers')) {
            return; // Ignore clicks on non-pagination links
        }
    
        let page;
    
        // If it's a page number
        if (!isNaN(parseInt(pageLink.innerText))) {
            page = parseInt(pageLink.innerText);
        }
    
        // Handle the "Previous" and "Next" buttons
        if (pageLink.classList.contains('prev') || pageLink.classList.contains('next')) {
            if (pageLink.classList.contains('prev')) {
                page = currentPage - 1; // Go to the previous page
            } else if (pageLink.classList.contains('next')) {
                page = currentPage + 1; // Go to the next page
            }
        }
    
        // Ensure that the page is a valid number (positive and not beyond the max page)
        if (isNaN(page) || page < 1) {
            return; // Invalid page, do nothing
        }
    
        // Assuming you know the total number of pages (this can be set dynamically via your PHP)
        const maxPage = parseInt(loadmore_params.total_pages); // Total number of pages, passed from PHP
    
        if (page > maxPage) {
            return; // Don't load if the page exceeds the max pages
        }
    
        // Update the current page and trigger loading more products
        currentPage = page;
        loadMoreProducts(page);
    }
    


    // Attach the button click event listener
    if (loadMoreButton) {
        console.log('Load more button found');
        loadMoreButton.addEventListener('click', handleButtonClick);
    }

    // Enable infinite scroll functionality (optional)
    // handleInfiniteScroll();

    // Attach click event listeners to all pagination links
    paginationLinks.forEach(link => {
        link.addEventListener('click', handlePaginationLinkClick);
    });

});
