import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const flashData = document.getElementById('flash-data');
        if (!flashData) return;

        const successMessages = JSON.parse(flashData.dataset.success || '[]');
        const errorMessages = JSON.parse(flashData.dataset.error || '[]');

        successMessages.forEach(message => {
            this.showAlert(message, 'success');
        });

        errorMessages.forEach(message => {
            this.showAlert(message, 'error');
        });
    }

    showAlert(message, type) {
        const bgColor = type === 'success' ? '#28a745' : '#dc3545';
        const icon = type === 'success' ? 'success' : 'error';

        Swal.fire({
            icon: icon,
            title: type === 'success' ? 'Succès!' : 'Erreur!',
            text: message,
            confirmButtonColor: bgColor,
            timer: 4000
        });
    }
}
