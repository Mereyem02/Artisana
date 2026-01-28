(function () {
    const root = document.getElementById('cart-page');
    if (!root) {
        return;
    }

    const updateTemplate = root.dataset.updateUrlTemplate || '';
    const removeTemplate = root.dataset.removeUrlTemplate || '';

    const resolveUrl = (template, productId) => template.replace('__ID__', productId);

    window.updateQuantity = function updateQuantity(productId, newQuantity) {
        if (!updateTemplate || !removeTemplate) {
            return;
        }

        if (newQuantity < 1) {
            if (window.confirm('Retirer cet article du panier ?')) {
                fetch(resolveUrl(removeTemplate, productId), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data && data.success) {
                            window.location.reload();
                        }
                    });
            }
            return;
        }

        fetch(resolveUrl(updateTemplate, productId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `quantity=${encodeURIComponent(newQuantity)}`
        })
            .then((response) => response.json())
            .then((data) => {
                if (data && data.success) {
                    window.location.reload();
                }
            });
    };
})();
