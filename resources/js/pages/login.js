const passwordToggle = document.querySelector('[data-password-toggle]');
const passwordInput = document.querySelector('#password');

if (passwordToggle && passwordInput) {
    passwordToggle.addEventListener('click', () => {
        const isVisible = passwordInput.type === 'text';
        passwordInput.type = isVisible ? 'password' : 'text';
        passwordToggle.setAttribute('aria-pressed', String(!isVisible));
        passwordToggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
    });
}

const loginForm = document.querySelector('[data-login-form]');

if (loginForm) {
    const fields = [...loginForm.querySelectorAll('input[required]')];

    const setFieldError = (field) => {
        const error = loginForm.querySelector(`[data-error-for="${field.name}"]`);
        if (!error) return field.checkValidity();

        let message = '';
        if (field.validity.valueMissing) message = 'This field is required.';
        else if (field.validity.typeMismatch) message = 'Enter a valid email address.';
        else if (field.validity.tooShort) message = `Use at least ${field.minLength} characters.`;

        error.textContent = message;
        field.closest('.input-shell')?.classList.toggle('has-error', Boolean(message));
        return !message;
    };

    fields.forEach((field) => {
        field.addEventListener('blur', () => setFieldError(field));
        field.addEventListener('input', () => {
            if (field.closest('.input-shell')?.classList.contains('has-error')) setFieldError(field);
        });
    });

    loginForm.addEventListener('submit', (event) => {
        const isValid = fields.map(setFieldError).every(Boolean);
        if (!isValid) {
            event.preventDefault();
            fields.find((field) => !field.checkValidity())?.focus();
            return;
        }

    });
}
