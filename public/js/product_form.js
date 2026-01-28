function initProductForm() {
    const form = document.querySelector('.product-form-container form');
    if (!form) return;

    if (form.dataset.jsInitialized) return;
    form.dataset.jsInitialized = 'true';

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

    const inputs = form.querySelectorAll('input:not([type="checkbox"]), textarea, select');

    inputs.forEach(input => {
        input.addEventListener('blur', function () {
            validateField(this);
        });

        input.addEventListener('input', function () {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
                const errorDiv = this.closest('.form-group')?.querySelector('.error-message');
                if (errorDiv) errorDiv.style.display = 'none';
            }
        });
    });

    form.addEventListener('submit', function (e) {
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

        if (field.offsetParent === null || field.disabled) {
            return true;
        }

        if (fieldName.includes('[titre]')) {
            if (!value) {
                isValid = false;
                errorMessage = 'Le titre est obligatoire';
            } else if (value.length < 3) {
                isValid = false;
                errorMessage = 'Le titre doit contenir au moins 3 caractères';
            } else if (value.length > 255) {
                isValid = false;
                errorMessage = 'Le titre ne peut pas dépasser 255 caractères';
            }
        } else if (fieldName.includes('[description]')) {
            if (!value) {
                isValid = false;
                errorMessage = 'La description est obligatoire';
            } else if (value.length < 10) {
                isValid = false;
                errorMessage = 'La description doit contenir au moins 10 caractères';
            } else if (value.length > 2000) {
                isValid = false;
                errorMessage = 'La description ne peut pas dépasser 2000 caractères';
            }
        } else if (fieldName.includes('[prix]')) {
            if (!value) {
                isValid = false;
                errorMessage = 'Le prix est obligatoire';
            } else {
                const priceValue = parseFloat(value);
                if (isNaN(priceValue) || priceValue <= 0) {
                    isValid = false;
                    errorMessage = 'Le prix doit être un nombre positif';
                } else if (!/^\d+([.,]\d{1,2})?$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Le prix doit avoir au maximum 2 décimales (ex: 99.99)';
                }
            }
        } else if (fieldName.includes('[stock]')) {
            if (value === '') {
                isValid = false;
                errorMessage = 'Le stock est obligatoire';
            } else {
                const stockValue = parseInt(value);
                if (isNaN(stockValue) || stockValue < 0) {
                    isValid = false;
                    errorMessage = 'Le stock doit être un nombre positif ou zéro';
                } else if (stockValue > 1000000) {
                    isValid = false;
                    errorMessage = 'Le stock ne peut pas dépasser 1 000 000';
                }
            }
        } else if (fieldName.includes('[dimensions]')) {
            if (value && value.length > 255) {
                isValid = false;
                errorMessage = 'Les dimensions ne peuvent pas dépasser 255 caractères';
            }
        } else if (field.type === 'file' && fieldName.includes('[photos]')) {
            if (field.files && field.files.length > 0) {
                const maxSize = 2 * 1024 * 1024; // 2MB
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

                for (let i = 0; i < field.files.length; i++) {
                    const file = field.files[i];

                    if (file.size > maxSize) {
                        isValid = false;
                        errorMessage = `Le fichier "${file.name}" est trop volumineux (max 2Mo)`;
                        break;
                    } else if (!allowedTypes.includes(file.type)) {
                        isValid = false;
                        errorMessage = `Le fichier "${file.name}" n'est pas au bon format. Utilisez JPEG, PNG ou WEBP`;
                        break;
                    }
                }
            }
        }

        const formGroup = field.closest('.form-group');
        if (!formGroup) return isValid;

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

    const descriptionField = form.querySelector('[name*="[description]"]');
    if (descriptionField) {
        const formGroup = descriptionField.closest('.form-group');
        const counterDiv = document.createElement('div');
        counterDiv.className = 'char-counter';
        counterDiv.style.fontSize = '0.875rem';
        counterDiv.style.color = '#6B7280';
        counterDiv.style.marginTop = '0.25rem';
        formGroup.appendChild(counterDiv);

        function updateCounter() {
            const length = descriptionField.value.length;
            counterDiv.textContent = `${length} / 2000 caractères`;
            if (length > 2000) {
                counterDiv.style.color = '#DC2626';
            } else {
                counterDiv.style.color = '#6B7280';
            }
        }

        descriptionField.addEventListener('input', updateCounter);
        updateCounter();
    }
}

document.addEventListener('turbo:load', initProductForm);
document.addEventListener('DOMContentLoaded', initProductForm);

let materiauxIndex = 0;

function ensureMateriauxSetup() {
    const list = document.getElementById('materiaux-list');
    if (list) {
        if (list.dataset.index) {
            const parsed = parseInt(list.dataset.index, 10);
            materiauxIndex = isNaN(parsed) ? document.querySelectorAll('.materiau-item').length : parsed;
        } else {
            materiauxIndex = document.querySelectorAll('.materiau-item').length;
        }
    } else {
        materiauxIndex = 0;
    }

    const btn = document.getElementById('add-materiau-btn');
    if (btn && !btn.dataset.jsInitialized) {
        btn.addEventListener('click', addMateriau);
        btn.dataset.jsInitialized = 'true';
    }
}

document.addEventListener('turbo:load', ensureMateriauxSetup);
document.addEventListener('DOMContentLoaded', ensureMateriauxSetup);

function addMateriau() {
    const list = document.getElementById('materiaux-list');
    if (!list) return;

    const prototype = list.dataset.prototype;
    const newForm = prototype.replace(/__name__/g, materiauxIndex);

    const div = document.createElement('div');
    div.className = 'materiau-item';
    div.innerHTML = newForm;

    list.appendChild(div);
    materiauxIndex++;
}
