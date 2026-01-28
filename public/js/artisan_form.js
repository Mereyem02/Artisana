document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('artisan-form');
    if (!form) return;

    const firstError = document.querySelector('.error-message:not([style*="display: none"]), .is-invalid');
    if (firstError) {
        setTimeout(() => {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const errorField = firstError.closest('.form-group')?.querySelector('input, textarea, select');
            if (errorField) {
                errorField.focus();
            }
        }, 100);
    }

    const inputs = form.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
                const errorDiv = this.closest('.form-group').querySelector('.error-message');
                if (errorDiv) errorDiv.style.display = 'none';
            }
        });
    });

    form.addEventListener('submit', function(e) {
        let isValid = true;
        let firstInvalidField = null;

        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
                if (!firstInvalidField) firstInvalidField = input;
            }
        });

        if (!isValid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            if (typeof window.AppNotifications !== 'undefined') {
                window.AppNotifications.toast('Veuillez corriger les erreurs dans le formulaire', 'error');
            }
            
            return false;
        }
    });

    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        let isValid = true;
        let errorMessage = '';

        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Ce champ est obligatoire';
        } else if (fieldName.includes('[nom]') && value) {
            if (value.length < 3) {
                isValid = false;
                errorMessage = 'Le nom doit contenir au moins 3 caractères';
            } else if (value.length > 150) {
                isValid = false;
                errorMessage = 'Le nom ne peut pas dépasser 150 caractères';
            }
        } else if (fieldName.includes('[bio]') && value) {
            if (value.length < 20) {
                isValid = false;
                errorMessage = 'La description doit contenir au moins 20 caractères';
            } else if (value.length > 1000) {
                isValid = false;
                errorMessage = 'La description ne peut pas dépasser 1000 caractères';
            }
        } else if (fieldName.includes('[telephone]') && value) {
            const phoneRegex = /^(\+212|0)[\s.-]?[5-7]([\s.-]?\d){8}$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Numéro de téléphone marocain invalide (ex: 0612345678)';
            }
        } else if (fieldName.includes('[email]') && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Adresse email invalide';
            }
        } else if (fieldName.includes('[competences]') && value) {
            if (value.length < 3) {
                isValid = false;
                errorMessage = 'Les compétences doivent contenir au moins 3 caractères';
            }
        } else if (field.type === 'file' && field.hasAttribute('required')) {
            if (!field.files || field.files.length === 0) {
                isValid = false;
                errorMessage = 'La photo de profil/logo est obligatoire';
            } else {
                const file = field.files[0];
                const maxSize = 2 * 1024 * 1024; 
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                
                if (file.size > maxSize) {
                    isValid = false;
                    errorMessage = 'Le fichier est trop volumineux (max 2Mo)';
                } else if (!allowedTypes.includes(file.type)) {
                    isValid = false;
                    errorMessage = 'Format non accepté. Utilisez JPEG, PNG ou WEBP';
                }
            }
        }

        const formGroup = field.closest('.form-group');
        let errorDiv = formGroup.querySelector('.error-message');
        
        if (!isValid) {
            field.classList.add('is-invalid');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                field.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = errorMessage;
            errorDiv.style.display = 'block';
        } else {
            field.classList.remove('is-invalid');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }

        return isValid;
    }
});
