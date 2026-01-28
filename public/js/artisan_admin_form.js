

function initArtisanAdminForm() {
    const form = document.getElementById('artisan-admin-form');
    if (!form) return;

    if (form.dataset.jsInitialized) return;
    form.dataset.jsInitialized = 'true';

    const fields = {
        nom: form.querySelector('#artisan_nom'),
        email: form.querySelector('#artisan_email'),
        telephone: form.querySelector('#artisan_telephone'),
        bio: form.querySelector('#artisan_bio'),
        competences: form.querySelector('#artisan_competences')
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
        const errorDiv = field.closest('.form-group, .mb-4, .mb-6')?.querySelector('.error-message');
        if (!errorDiv) return true;

        let isValid = true;
        let errorMessage = '';

        const value = field.value.trim();

        switch (fieldName) {
            case 'nom':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Le nom est requis';
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Le nom doit contenir au moins 2 caractères';
                } else if (value.length > 100) {
                    isValid = false;
                    errorMessage = 'Le nom ne doit pas dépasser 100 caractères';
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

            case 'telephone':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Le téléphone est requis';
                } else {
                    const phoneRegex = /^(\+\d{1,3}\s?)?\d{9,15}$/;
                    if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                        isValid = false;
                        errorMessage = 'Format de téléphone invalide (ex: +33612345678 ou 0612345678)';
                    }
                }
                break;

            case 'bio':
                if (!value) {
                    isValid = false;
                    errorMessage = 'La biographie est requise';
                } else if (value.length < 20) {
                    isValid = false;
                    errorMessage = `La biographie doit contenir au moins 20 caractères (actuellement: ${value.length})`;
                } else if (value.length > 1000) {
                    isValid = false;
                    errorMessage = 'La biographie ne doit pas dépasser 1000 caractères';
                }
                break;

            case 'competences':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Les compétences sont requises';
                } else if (value.length < 10) {
                    isValid = false;
                    errorMessage = `Les compétences doivent contenir au moins 10 caractères (actuellement: ${value.length})`;
                } else if (value.length > 500) {
                    isValid = false;
                    errorMessage = 'Les compétences ne doivent pas dépasser 500 caractères';
                }
                break;
        }

        if (!isValid) {
            errorDiv.textContent = errorMessage;
            errorDiv.style.display = 'block';
            field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
            field.classList.remove('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
        } else {
            errorDiv.style.display = 'none';
            field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
            field.classList.add('border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500');
        }

        return isValid;
    }

    Object.entries(fields).forEach(([fieldName, field]) => {
        if (field) {
            field.addEventListener('blur', () => {
                validateField(field, fieldName);
            });

            field.addEventListener('input', () => {
                if (field.classList.contains('border-red-500')) {
                    validateField(field, fieldName);
                }
            });
        }
    });

    form.addEventListener('submit', function (e) {
        let isFormValid = true;

        Object.entries(fields).forEach(([fieldName, field]) => {
            if (field) {
                const fieldValid = validateField(field, fieldName);
                if (!fieldValid) {
                    isFormValid = false;
                }
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const firstError = form.querySelector('.error-message[style*="display: block"]');
            if (firstError) {
                const firstErrorField = firstError.closest('.form-group, .mb-4, .mb-6')?.querySelector('input, textarea');
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

    if (fields.bio) {
        const bioCounter = document.createElement('div');
        bioCounter.className = 'text-sm text-gray-500 mt-1';
        bioCounter.id = 'bio-counter';
        fields.bio.parentElement.appendChild(bioCounter);

        function updateBioCounter() {
            const length = fields.bio.value.length;
            bioCounter.textContent = `${length}/1000 caractères`;
            bioCounter.style.color = length > 1000 ? '#ef4444' : '#6b7280';
        }
        fields.bio.addEventListener('input', updateBioCounter);
        updateBioCounter();
    }

    if (fields.competences) {
        const compCounter = document.createElement('div');
        compCounter.className = 'text-sm text-gray-500 mt-1';
        compCounter.id = 'comp-counter';
        fields.competences.parentElement.appendChild(compCounter);

        function updateCompCounter() {
            const length = fields.competences.value.length;
            compCounter.textContent = `${length}/500 caractères`;
            compCounter.style.color = length > 500 ? '#ef4444' : '#6b7280';
        }
        fields.competences.addEventListener('input', updateCompCounter);
        updateCompCounter();
    }
}

document.addEventListener('turbo:load', initArtisanAdminForm);
document.addEventListener('DOMContentLoaded', initArtisanAdminForm);
