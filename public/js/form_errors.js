
document.addEventListener('DOMContentLoaded', function() {
    scrollToFirstError();
    
    enhanceErrorVisibility();
});

if (window.Turbo) {
    document.addEventListener('turbo:load', function() {
        scrollToFirstError();
        enhanceErrorVisibility();
    });
}

function scrollToFirstError() {
    const firstError = document.querySelector(
        '.error-message:not([style*="display: none"]), ' +
        '.is-invalid, ' +
        '.form-error-message, ' +
        'ul.form-error-list, ' +
        '.alert-danger'
    );
    
    if (firstError) {
        setTimeout(() => {
            firstError.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            
            const errorField = firstError.closest('.form-group')?.querySelector('input, textarea, select') ||
                               firstError.parentElement?.querySelector('input, textarea, select');
            
            if (errorField && !errorField.disabled) {
                errorField.focus();
            }
            
            firstError.style.animation = 'pulse 0.5s ease-in-out 2';
        }, 150);
    }
}


function enhanceErrorVisibility() {
    const invalidFields = document.querySelectorAll('.is-invalid, input.form-control:invalid');
    
    invalidFields.forEach(field => {
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
            
            const errorDiv = this.closest('.form-group')?.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        });
        
        field.addEventListener('focus', function() {
            const errorDiv = this.closest('.form-group')?.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.style.fontWeight = '600';
            }
        });
        
        field.addEventListener('blur', function() {
            const errorDiv = this.closest('.form-group')?.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.style.fontWeight = '400';
            }
        });
    });
}

if (!document.getElementById('form-error-animations')) {
    const style = document.createElement('style');
    style.id = 'form-error-animations';
    style.textContent = `
        @keyframes pulse {
            0%, 100% { 
                transform: scale(1); 
                opacity: 1; 
            }
            50% { 
                transform: scale(1.02); 
                opacity: 0.9; 
            }
        }
        
        .error-message {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(-5px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
    `;
    document.head.appendChild(style);
}
