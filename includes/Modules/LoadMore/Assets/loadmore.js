"use strict";

document.addEventListener('DOMContentLoaded', function () {
    const productContainer = document.querySelector('.products') || document.querySelector('.wc-block-product-template__responsive');
    const paginationContainer = document.querySelector('.woocommerce-pagination');

    let currentPage = getCurrentPageFromUrl();

    // Get current page from URL
    function getCurrentPageFromUrl() {
        const match = window.location.pathname.match(/\/page\/(\d+)/);
        return match ? parseInt(match[1]) : 1;
    }

    // Generate a page URL
    function generatePageUrl(pageNumber) {
        const baseUrl = loadmore_params.shop_url || window.location.origin + '/shop/';
        return pageNumber === 1 ? baseUrl : `${baseUrl}page/${pageNumber}/`;
    }
    // Update browser URL without reloading
    function updateUrlWithPage(page) {
        const newUrl = generatePageUrl(page);
        history.pushState({ page: page }, '', newUrl);
    }

    // Replace products with new HTML
    function replaceProducts(responseHtml) {
        if (responseHtml.trim() === '' || !productContainer) return;
        productContainer.innerHTML = responseHtml;
    }

    // Handle loading products via AJAX
    function loadProducts(page) {
        currentPage = page;

        const data = new FormData();
        data.append('action', 'load_more_products');
        data.append('page', currentPage);

        fetch(loadmore_params.ajax_url, {
            method: 'POST',
            body: data,
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                document.querySelector('.woocommerce-pagination').setAttribute('data-total-pages', response.data.total_pages);
                replaceProducts(response.data.content);
                updatePaginationUI(currentPage, response.data.total_pages);
                updateUrlWithPage(currentPage);
            } else {
                console.error('Failed to load products.');
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
    }

    // Update pagination UI (highlight current page, update next/prev href)
    function updatePaginationUI(newPage, maxPage) {
        if (!paginationContainer) return;

        // Update number links
        const currentActive = paginationContainer.querySelector('span.current');
        if (currentActive) {
            const pageNumber = currentActive.innerText;
            const newLink = document.createElement('a');
            newLink.className = 'page-numbers';
            newLink.href = generatePageUrl(parseInt(pageNumber));
            newLink.innerText = pageNumber;
            currentActive.parentNode.replaceChild(newLink, currentActive);
        }

        const newPageLink = [...paginationContainer.querySelectorAll('a.page-numbers')]
            .find(link => parseInt(link.innerText) === newPage);

        if (newPageLink) {
            const newSpan = document.createElement('span');
            newSpan.className = 'page-numbers current';
            newSpan.innerText = newPage;
            newPageLink.parentNode.replaceChild(newSpan, newPageLink);
        }

        // Update Prev/Next
        const prevButton = paginationContainer.querySelector('a.prev');
        const nextButton = paginationContainer.querySelector('a.next');

        if (prevButton) {
            if (newPage > 1) {
                prevButton.href = generatePageUrl(newPage - 1);
                prevButton.classList.remove('disabled');
            } else {
                prevButton.href = '#';
                prevButton.classList.add('disabled');
            }
        }

        if (nextButton) {
            if (newPage < maxPage) {
                nextButton.href = generatePageUrl(newPage + 1);
                nextButton.classList.remove('disabled');
            } else {
                nextButton.href = '#';
                nextButton.classList.add('disabled');
            }
        }
    }

    // Handle click on any pagination link
    function handlePaginationClick(event) {
        event.preventDefault();

        const target = event.target;
        if (!target.classList.contains('page-numbers')) return;

        let page;

        if (target.classList.contains('prev')) {
            page = currentPage - 1;
        } else if (target.classList.contains('next')) {
            page = currentPage + 1;
            let maxPage = document.querySelector('.woocommerce-pagination' ).getAttribute('data-total-pages');
            maxPage = parseInt(maxPage);
            // Check if maxPage is null or undefined
            
            if( (maxPage  < page ) && maxPage !== null) {
                return;
            }

        } else {
            page = parseInt(target.innerText);
        }

        if (!page || page < 1) return;

        loadProducts(page);
    }

    // Attach event listeners to all pagination links
    const pageLinks = document.querySelectorAll('.woocommerce-pagination');
    if (!pageLinks) return;
    pageLinks.forEach(link => {
        link.addEventListener('click', handlePaginationClick);
    });
});
