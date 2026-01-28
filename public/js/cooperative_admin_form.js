

function initCooperativeAdminForm() {
    const form = document.getElementById('cooperative-admin-form');
    if (!form) return;
    if (form.dataset.jsInitialized) return;
    form.dataset.jsInitialized = 'true';

    const fields = {
        nom: form.querySelector('#cooperative_nom'),
        description: form.querySelector('#cooperative_description'),
        adresse: form.querySelector('#cooperative_adresse'),
        telephone: form.querySelector('#cooperative_telephone'),
        email: form.querySelector('#cooperative_email'),
        ville: form.querySelector('#cooperative_ville'),
        region: form.querySelector('#cooperative_region'),
        siteWeb: form.querySelector('#cooperative_siteWeb')
    };

    Object.values(fields).forEach(field => {
        if (field) {
            field.removeAttribute('required');
            field.removeAttribute('pattern');
            field.removeAttribute('minlength');
            field.removeAttribute('maxlength');
        }
    });

    function validateField(field, fieldName) {
        const errorDiv = field.closest('.form-group')?.querySelector('.error-message');
        if (!errorDiv) return true;

        let isValid = true;
        let errorMessage = '';

        const value = field.value.trim();

        switch (fieldName) {
            case 'nom':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Le nom est requis';
                } else if (value.length < 3) {
                    isValid = false;
                    errorMessage = 'Le nom doit contenir au moins 3 caractères';
                } else if (value.length > 150) {
                    isValid = false;
                    errorMessage = 'Le nom ne doit pas dépasser 150 caractères';
                }
                break;

            case 'description':
                if (!value) {
                    isValid = false;
                    errorMessage = 'La description est requise';
                } else if (value.length < 20) {
                    isValid = false;
                    errorMessage = `La description doit contenir au moins 20 caractères (actuellement: ${value.length})`;
                } else if (value.length > 2000) {
                    isValid = false;
                    errorMessage = 'La description ne doit pas dépasser 2000 caractères';
                }
                break;

            case 'adresse':
                if (!value) {
                    isValid = false;
                    errorMessage = 'L\'adresse est requise';
                } else if (value.length < 10) {
                    isValid = false;
                    errorMessage = 'L\'adresse doit contenir au moins 10 caractères';
                } else if (value.length > 300) {
                    isValid = false;
                    errorMessage = 'L\'adresse ne doit pas dépasser 300 caractères';
                }
                break;

            case 'telephone':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Le téléphone est requis';
                } else {
                                                    const phoneRegex = /^(\+\d{1,3}\s?)?\d{9,15}$/;
                    if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                        isValid = false;
                        errorMessage = 'Format de téléphone invalide (ex: +212612345678 ou 0612345678)';
                    }
                }
                break;

            case 'email':
                if (!value) {
                    isValid = false;
                    errorMessage = 'L\'email est requis';
                } else {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Format d\'email invalide';
                    }
                }
                break;

            case 'ville':
                if (!value) {
                    isValid = false;
                    errorMessage = 'La ville est requise';
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'La ville doit contenir au moins 2 caractères';
                } else if (value.length > 100) {
                    isValid = false;
                    errorMessage = 'La ville ne doit pas dépasser 100 caractères';
                }
                break;

            case 'region':
                if (!value) {
                    isValid = false;
                    errorMessage = 'La région est requise';
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'La région doit contenir au moins 2 caractères';
                } else if (value.length > 100) {
                    isValid = false;
                    errorMessage = 'La région ne doit pas dépasser 100 caractères';
                }
                break;

            case 'siteWeb':
                if (value) {
                    const urlRegex = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
                    if (!urlRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Format d\'URL invalide (ex: https://example.com)';
                    }
                }
                break;
        }

        if (!isValid) {
            errorDiv.textContent = errorMessage;
            errorDiv.style.display = 'block';
            field.classList.add('is-invalid');
        } else {
            errorDiv.style.display = 'none';
            field.classList.remove('is-invalid');
        }

        return isValid;
    }

    Object.entries(fields).forEach(([fieldName, field]) => {
        if (field) {
            field.addEventListener('blur', () => {
                validateField(field, fieldName);
            });

            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    validateField(field, fieldName);
                }
            });
        }
    });

    form.addEventListener('submit', function (e) {
        let isFormValid = true;

        ['nom', 'description', 'adresse', 'telephone', 'email', 'ville', 'region'].forEach(fieldName => {
            const field = fields[fieldName];
            if (field) {
                const fieldValid = validateField(field, fieldName);
                if (!fieldValid) {
                    isFormValid = false;
                }
            }
        });

        if (fields.siteWeb && fields.siteWeb.value.trim()) {
            const siteValid = validateField(fields.siteWeb, 'siteWeb');
            if (!siteValid) {
                isFormValid = false;
            }
        }

        if (!isFormValid) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const firstError = form.querySelector('.error-message[style*="display: block"]');
            if (firstError) {
                const firstErrorField = firstError.closest('.form-group')?.querySelector('input, textarea');
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
            }

            if (window.AppNotifications) {
                window.AppNotifications.toast('Veuillez corriger les erreurs dans le formulaire', 'error');
            }

            return false;
        }
    });

    if (fields.description) {
        const descCounter = document.createElement('div');
        descCounter.className = 'text-sm text-gray-500 mt-1';
        descCounter.id = 'description-counter';
        fields.description.parentElement.appendChild(descCounter);

        function updateDescCounter() {
            const length = fields.description.value.length;
            descCounter.textContent = `${length}/2000 caractères`;
            descCounter.style.color = length > 2000 ? '#ef4444' : '#6b7280';
        }
        fields.description.addEventListener('input', updateDescCounter);
        updateDescCounter();
    }

    if (fields.adresse) {
        const addrCounter = document.createElement('div');
        addrCounter.className = 'text-sm text-gray-500 mt-1';
        addrCounter.id = 'adresse-counter';
        fields.adresse.parentElement.appendChild(addrCounter);

        function updateAddrCounter() {
            const length = fields.adresse.value.length;
            addrCounter.textContent = `${length}/300 caractères`;
            addrCounter.style.color = length > 300 ? '#ef4444' : '#6b7280';
        }
        fields.adresse.addEventListener('input', updateAddrCounter);
        updateAddrCounter();
    }
}

document.addEventListener('turbo:load', initCooperativeAdminForm);
document.addEventListener('DOMContentLoaded', initCooperativeAdminForm);
