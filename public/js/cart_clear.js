(function () {
    const clearCartForm = document.getElementById('clear-cart-form');
    if (!clearCartForm) return;

    clearCartForm.addEventListener('submit', (e) => {
        e.preventDefault();

        if (typeof AppNotifications !== 'undefined') {
            AppNotifications.confirm(
                'Vider complètement le panier ?',
                'Êtes-vous sûr de vouloir supprimer tous les articles ? Cette action est irréversible.',
                'warning',
                'Oui, vider le panier',
                () => {
                    clearCartForm.submit();
                }
            );
        } else {
            if (confirm('Vider complètement le panier ?')) {
                clearCartForm.submit();
            }
        }
    });
})();
