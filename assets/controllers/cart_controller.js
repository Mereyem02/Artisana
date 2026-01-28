import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['count', 'badge'];
    static values = {
        updateUrl: String
    }

    connect() {
        console.log('Cart controller connected');
        this.updateCartCount();
        
        this.intervalId = setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.updateCartCount();
            }
        }, 30000);
    }

    disconnect() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    }

    async updateCartCount() {
        try {
            const response = await fetch('/cart/count');
            const data = await response.json();
            
            if (this.hasCountTarget) {
                this.countTargets.forEach(target => {
                    target.textContent = data.count;
                });
            }

            if (this.hasBadgeTarget) {
                this.badgeTargets.forEach(badge => {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            }
        } catch (error) {
            console.error('Error updating cart count:', error);
        }
    }

    async addToCart(event) {
        event.preventDefault();
        console.log('Add to cart triggered');
        
        const form = event.currentTarget;
        const url = form.action;
        const formData = new FormData(form);

        try {
            console.log('Sending request to:', url);
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('Response status:', response.status, 'ok:', response.ok);
            
            let data = {};
            try {
                data = await response.json();
                console.log('Response JSON:', data);
            } catch (e) {
                console.log('Response is not JSON');
            }

            if (response.ok) {
                await this.updateCartCount();
                this.showNotification(data.message || 'Produit ajouté au panier', 'success');
                
                setTimeout(() => {
                    console.log('Redirecting to /cart');
                    window.location.href = '/cart';
                }, 1500);
            } else {
                this.showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Erreur lors de l\'ajout au panier', 'error');
        }
    }

    async removeFromCart(event) {
        event.preventDefault();
        
        const button = event.currentTarget;
        const url = button.dataset.url || button.closest('form')?.action;

        if (typeof AppNotifications === 'undefined') {
            if (!confirm('Retirer cet article du panier ?')) return;
        } else {
            return AppNotifications.confirm(
                'Retirer cet article ?',
                'L\'article sera supprimé de votre panier.',
                'warning',
                'Oui, retirer',
                () => this._performRemoveFromCart(button, url)
            );
        }

        await this._performRemoveFromCart(button, url);
    }

    async _performRemoveFromCart(button, url) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const cartItem = button.closest('[data-cart-item]');
                if (cartItem) {
                    cartItem.remove();
                }
                await this.updateCartCount();
                this.showNotification('Produit retiré du panier', 'success');
                
                const remainingItems = document.querySelectorAll('[data-cart-item]');
                if (remainingItems.length === 0) {
                    window.location.reload();
                }
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            this.showNotification('Erreur lors de la suppression', 'error');
        }
    }

    async updateQuantity(event) {
        const input = event.currentTarget;
        const productId = input.dataset.productId;
        const quantity = parseInt(input.value);

        if (quantity < 1) {
            return;
        }

        try {
            const response = await fetch(`/cart/update/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `quantity=${quantity}`
            });

            const data = await response.json();
            
            if (data.success) {
                await this.updateCartCount();
                const totalElement = document.querySelector('[data-cart-total]');
                if (totalElement && data.total !== undefined) {
                    totalElement.textContent = new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'EUR'
                    }).format(data.total);
                }
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showNotification('Erreur lors de la mise à jour', 'error');
        }
    }

    showNotification(message, type = 'info') {
        if (typeof AppNotifications !== 'undefined') {
            if (type === 'success') {
                AppNotifications.success(message);
            } else if (type === 'error') {
                AppNotifications.error('Erreur', message);
            } else {
                AppNotifications.toast.fire({
                    icon: 'info',
                    title: message
                });
            }
            return;
        }

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            'bg-blue-500'
        } text-white`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}
