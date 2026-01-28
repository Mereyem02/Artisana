document.addEventListener('DOMContentLoaded', function() {
    const page = document.getElementById('product-page');
    if (!page) return;

    const mainImg = document.querySelector('.product-gallery-main img');
    const thumbs = document.querySelectorAll('.thumb-img[data-image-src]');
    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (mainImg) {
                mainImg.src = thumb.dataset.imageSrc;
            }
        });
    });

    const quantityInput = document.getElementById('quantity');
    const maxStock = parseInt(page.dataset.maxStock || (quantityInput ? quantityInput.max : '0'), 10) || null;
    const adjust = (delta) => {
        if (!quantityInput) return;
        const current = parseInt(quantityInput.value, 10) || 1;
        const min = parseInt(quantityInput.min || '1', 10) || 1;
        const max = maxStock || parseInt(quantityInput.max || '0', 10) || Infinity;
        const next = Math.min(Math.max(current + delta, min), max);
        quantityInput.value = next;
    };

    console.log('Product show script loaded. Found', document.querySelectorAll('.quantity-btn[data-quantity-action]').length, 'quantity buttons');
    
    document.querySelectorAll('.quantity-btn[data-quantity-action]').forEach((btn) => {
        const action = btn.dataset.quantityAction;
        console.log('Attaching listener to button with action:', action);
        btn.addEventListener('click', () => {
            console.log('Button clicked:', action);
            if (action === 'inc') adjust(1);
            if (action === 'dec') adjust(-1);
        });
    });
});
