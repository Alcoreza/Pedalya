const registerForm = document.querySelector('[data-register-form]');

document.querySelectorAll('[data-register-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.dataset.registerPasswordToggle);
    if (!input) return;

    toggle.addEventListener('click', () => {
        const isVisible = input.type === 'text';
        input.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!isVisible));
        toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
    });
});

if (registerForm) {
    const fields = [...registerForm.querySelectorAll('input[required]')];
    const password = registerForm.querySelector('[data-register-password]');
    const confirmation = registerForm.querySelector('#password_confirmation');
    const meter = registerForm.querySelector('[data-password-meter]');

    const setFieldError = (field) => {
        const error = registerForm.querySelector(`[data-error-for="${field.name}"]`);
        let message = '';

        if (field.validity.valueMissing) {
            message = field.type === 'checkbox' ? 'Please accept the terms to continue.' : 'This field is required.';
        } else if (field.validity.typeMismatch) {
            message = 'Enter a valid email address.';
        } else if (field.validity.tooShort) {
            message = `Use at least ${field.minLength} characters.`;
        } else if (field === confirmation && password && field.value !== password.value) {
            message = 'Passwords do not match.';
        }

        if (error) error.textContent = message;
        field.closest('.register-input')?.classList.toggle('has-error', Boolean(message));
        return !message;
    };

    const updateStrength = () => {
        if (!password || !meter) return;
        const value = password.value;
        let strength = 0;
        if (value.length >= 8) strength += 1;
        if (/[A-Z]/.test(value) && /[a-z]/.test(value)) strength += 1;
        if (/\d/.test(value) && /[^A-Za-z0-9]/.test(value)) strength += 1;
        meter.dataset.strength = String(strength);
    };

    fields.forEach((field) => {
        field.addEventListener('blur', () => setFieldError(field));
        field.addEventListener('input', () => {
            if (field.closest('.register-input')?.classList.contains('has-error')) setFieldError(field);
            if (field === password) {
                updateStrength();
                if (confirmation?.value) setFieldError(confirmation);
            }
        });
        field.addEventListener('change', () => setFieldError(field));
    });

    registerForm.addEventListener('submit', (event) => {
        const results = fields.map(setFieldError);
        const isValid = results.every(Boolean);

        if (!isValid) {
            event.preventDefault();
            fields[results.findIndex((result) => !result)]?.focus();
            return;
        }
    });
}
