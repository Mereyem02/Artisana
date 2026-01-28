

const AppNotifications = {
    toast: Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        customClass: {
            popup: 'premium-toast'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    }),

    confirm: function (title, text, icon, confirmText, callback) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon || 'question',
            showCancelButton: true,
            confirmButtonColor: icon === 'warning' ? '#DC2626' : '#2563EB',
            cancelButtonColor: '#6B7280',
            confirmButtonText: confirmText || 'Continuer',
            cancelButtonText: 'Annuler',
            reverseButtons: true,
            customClass: {
                popup: 'premium-popup',
                title: 'premium-swal-title',
                htmlContainer: 'premium-swal-content'
            }
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    },

    error: function (title, message) {
        this.toast.fire({
            icon: 'error',
            title: title,
            text: message,
            background: '#FEF2F2',
            color: '#991B1B',
            iconColor: '#EF4444'
        });
    },

    success: function (title) {
        this.toast.fire({
            icon: 'success',
            title: title,
            background: '#F0FDF4',
            color: '#166534',
            iconColor: '#22C55E'
        });
    }
};

function initAppShortcuts() {
    const flashContainer = document.getElementById('flash-messages-data');
    if (flashContainer && flashContainer.dataset.messages) {
        try {
            const messages = JSON.parse(flashContainer.dataset.messages);
            messages.forEach(msg => {
                const label = msg.type;
                const icon = label === "error" ? "error" : (label === "warning" ? "warning" : (label === "info" ? "info" : "success"));
                AppNotifications.toast.fire({
                    icon: icon,
                    title: msg.message
                });
            });
            flashContainer.removeAttribute('data-messages');
        } catch (e) {
            console.error('Error parsing flash messages:', e);
        }
    }

    const errorContainers = document.querySelectorAll('.error-message');
    if (errorContainers.length > 0) {
        AppNotifications.error('Attention !', 'Certains champs contiennent des erreurs.');

        errorContainers.forEach((container, index) => {
            const errorText = container.textContent.trim();
            if (errorText.length > 0) {
                const group = container.closest('.form-group, .mb-3, .col-md-6, .col-md-12');
                let fieldName = 'Champ';
                if (group) {
                    const label = group.querySelector('label, .form-label');
                    if (label) fieldName = label.textContent.replace('*', '').trim();

                    const widgets = group.querySelectorAll('input, textarea, select');
                    widgets.forEach(w => w.classList.add('is-invalid'));
                }

                setTimeout(() => {
                    AppNotifications.error(fieldName, errorText);
                }, (index + 1) * 600);
            }
        });
    }

    document.querySelectorAll('input, textarea, select').forEach(function (input) {
        input.addEventListener('input', function () {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
            }
        });
    });

    document.querySelectorAll('form').forEach(form => {
        if (form.dataset.shortcutsInitialized) return;
        form.dataset.shortcutsInitialized = "true";

        if (form.getAttribute('novalidate') === 'true') return;

        form.setAttribute('novalidate', 'novalidate');

        form.addEventListener('submit', function (e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();

                const invalidFields = this.querySelectorAll(':invalid');

                AppNotifications.error('Attention !', 'Veuillez remplir correctement tous les champs obligatoires.');

                invalidFields.forEach((field, index) => {
                    field.classList.add('is-invalid');

                    const group = field.closest('.form-group, .mb-3, .col-md-6, .col-md-12');
                    let fieldName = 'Champ';
                    if (group) {
                        const label = group.querySelector('label, .form-label');
                        if (label) fieldName = label.textContent.replace('*', '').trim();
                    }

                    setTimeout(() => {
                        AppNotifications.error(fieldName, field.validationMessage || 'Ce champ est invalide');
                    }, (index + 1) * 600);

                    field.addEventListener('input', function () {
                        if (this.checkValidity()) {
                            this.classList.remove('is-invalid');
                        }
                    }, { once: true });
                });

                const firstInvalid = invalidFields[0];
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
                return;
            }

            const isDelete = this.classList.contains('js-confirm-delete');
            const isSubmit = this.classList.contains('js-confirm-submit');

            if ((isDelete || isSubmit) && !this.dataset.confirmed) {
                e.preventDefault();

                AppNotifications.confirm(
                    this.dataset.title || (isDelete ? 'Supprimer ?' : 'Soumettre ?'),
                    this.dataset.text || (isDelete ? "Cette action est irréversible !" : "Voulez-vous envoyer ces informations ?"),
                    this.dataset.icon || (isDelete ? 'warning' : 'question'),
                    this.dataset.confirmButton || (isDelete ? 'Oui, supprimer' : 'Oui, enregistrer'),
                    () => {
                        this.dataset.confirmed = "true";
                        this.submit();
                    }
                );
            }
        });
    });

    document.querySelectorAll('a.js-confirm').forEach(link => {
        if (link.dataset.shortcutsInitialized) return;
        link.dataset.shortcutsInitialized = "true";

        link.addEventListener('click', function (e) {
            if (this.dataset.confirmed) return;
            e.preventDefault();

            AppNotifications.confirm(
                this.dataset.title || 'Êtes-vous sûr ?',
                this.dataset.text || "Cette action est irréversible !",
                this.dataset.icon || 'warning',
                'Confirmer',
                () => {
                    window.location.href = this.getAttribute('href');
                }
            );
        });
    });
}

window.AppNotifications = AppNotifications;

document.addEventListener('DOMContentLoaded', initAppShortcuts);
if (window.Turbo) {
    document.addEventListener('turbo:load', initAppShortcuts);
} else {
    document.addEventListener('pjax:complete', initAppShortcuts);
}
