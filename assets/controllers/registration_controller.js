import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['firstName', 'lastName', 'phone', 'email', 'password', 'passwordConfirm'];

    connect() {
        this.setupValidation();
    }

    setupValidation() {
        if (this.hasFirstNameTarget) {
            this.firstNameTarget.addEventListener('blur', () => this.validateFirstName());
        }
        if (this.hasLastNameTarget) {
            this.lastNameTarget.addEventListener('blur', () => this.validateLastName());
        }
        if (this.hasPhoneTarget) {
            this.phoneTarget.addEventListener('blur', () => this.validatePhone());
        }
        if (this.hasEmailTarget) {
            this.emailTarget.addEventListener('blur', () => this.validateEmail());
        }
        if (this.hasPasswordTarget) {
            this.passwordTarget.addEventListener('input', () => this.validatePassword());
        }
        if (this.hasPasswordConfirmTarget) {
            this.passwordConfirmTarget.addEventListener('input', () => this.validatePasswordConfirm());
        }
    }

    validateFirstName() {
        const value = this.firstNameTarget.value.trim();
        const errors = [];

        if (value.length === 0) {
            errors.push('Le prénom est obligatoire');
        } else if (value.length < 2) {
            errors.push('Le prénom doit contenir au moins 2 caractères');
        } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/u.test(value)) {
            errors.push('Le prénom ne peut contenir que des lettres');
        }

        this.showErrors(this.firstNameTarget, errors);
        return errors.length === 0;
    }

    validateLastName() {
        const value = this.lastNameTarget.value.trim();
        const errors = [];

        if (value.length === 0) {
            errors.push('Le nom est obligatoire');
        } else if (value.length < 2) {
            errors.push('Le nom doit contenir au moins 2 caractères');
        } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/u.test(value)) {
            errors.push('Le nom ne peut contenir que des lettres');
        }

        this.showErrors(this.lastNameTarget, errors);
        return errors.length === 0;
    }

    validatePhone() {
        const value = this.phoneTarget.value.trim();
        const errors = [];

        if (value.length === 0) {
            errors.push('Le numéro de téléphone est obligatoire');
        } else if (!/^(\+212|0)[5-7][0-9]{8}$/.test(value)) {
            errors.push('Format invalide. Ex: 0612345678 ou +212612345678');
        }

        this.showErrors(this.phoneTarget, errors);
        return errors.length === 0;
    }

    validateEmail() {
        const value = this.emailTarget.value.trim();
        const errors = [];

        if (value.length === 0) {
            errors.push('L\'email est obligatoire');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            errors.push('L\'adresse email n\'est pas valide');
        }

        this.showErrors(this.emailTarget, errors);
        return errors.length === 0;
    }

    validatePassword() {
        const value = this.passwordTarget.value;
        const errors = [];

        if (value.length === 0) {
            errors.push('Le mot de passe est obligatoire');
        } else {
            if (value.length < 8) {
                errors.push('Minimum 8 caractères');
            }
            if (!/[a-z]/.test(value)) {
                errors.push('Au moins une minuscule requise');
            }
            if (!/[A-Z]/.test(value)) {
                errors.push('Au moins une majuscule requise');
            }
            if (!/\d/.test(value)) {
                errors.push('Au moins un chiffre requis');
            }
        }

        this.showErrors(this.passwordTarget, errors);
        
        if (this.hasPasswordConfirmTarget && this.passwordConfirmTarget.value) {
            this.validatePasswordConfirm();
        }
        
        return errors.length === 0;
    }

    validatePasswordConfirm() {
        const password = this.passwordTarget.value;
        const confirm = this.passwordConfirmTarget.value;
        const errors = [];

        if (confirm.length === 0) {
            errors.push('La confirmation est obligatoire');
        } else if (password !== confirm) {
            errors.push('Les mots de passe ne correspondent pas');
        }

        this.showErrors(this.passwordConfirmTarget, errors);
        return errors.length === 0;
    }

    showErrors(input, errors) {
        const existingError = input.parentElement.querySelector('.client-error');
        if (existingError) {
            existingError.remove();
        }

        if (errors.length > 0) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            
            const errorDiv = document.createElement('span');
            errorDiv.className = 'form-error client-error';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + errors.join(', ');
            input.parentElement.appendChild(errorDiv);
        } else if (input.value.trim() !== '') {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-invalid', 'is-valid');
        }
    }

    validateForm(event) {
        let isValid = true;

        if (this.hasFirstNameTarget) isValid = this.validateFirstName() && isValid;
        if (this.hasLastNameTarget) isValid = this.validateLastName() && isValid;
        if (this.hasPhoneTarget) isValid = this.validatePhone() && isValid;
        if (this.hasEmailTarget) isValid = this.validateEmail() && isValid;
        if (this.hasPasswordTarget) isValid = this.validatePassword() && isValid;
        if (this.hasPasswordConfirmTarget) isValid = this.validatePasswordConfirm() && isValid;

        if (!isValid) {
            event.preventDefault();
            
            const firstError = this.element.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }

            this.showNotification('Veuillez corriger les erreurs avant de continuer', 'error');
        }

        return isValid;
    }

    showNotification(message, type = 'error') {
        const notification = document.createElement('div');
        notification.className = `cart-notification ${type}`;
        notification.style.cssText = 'position: fixed; top: 1rem; right: 1rem; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 9999;';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
}
