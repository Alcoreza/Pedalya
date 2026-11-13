const profileForm = document.querySelector('[data-profile-form]');

const profileName = profileForm?.querySelector('[name="name"]');
const initials = profileForm?.querySelector('[data-profile-initials]');
const updateInitials = () => {
    const words = profileName?.value.trim().split(/\s+/).filter(Boolean) ?? [];
    if (initials) initials.textContent = words.length
        ? (Array.from(words[0])[0] + (words.length > 1 ? Array.from(words.at(-1))[0] : '')).toLocaleUpperCase()
        : '?';
};
updateInitials();
profileName?.addEventListener('input', updateInitials);
const picture = profileForm?.querySelector('[data-profile-picture]');
if (picture) {
    picture.addEventListener('error', () => picture.remove());
    if (picture.complete && !picture.naturalWidth) picture.remove();
}

profileForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    let valid = true;

    profileForm.querySelectorAll('[required]').forEach((field) => {
        const error = profileForm.querySelector('[data-profile-error="' + field.name + '"]');
        const wrapper = field.closest('.profile-field');
        let message = field.value.trim() ? '' : field.previousElementSibling.textContent + ' is required.';

        if (field.type === 'email' && field.value.trim() && !field.validity.valid) {
            message = 'Enter a valid email address.';
        }

        error.textContent = message;
        wrapper.classList.toggle('is-invalid', Boolean(message));
        valid = valid && !message;
    });

    const message = profileForm.querySelector('[data-profile-message]');
    message.textContent = valid
        ? 'Your changes are saved in this frontend preview.'
        : 'Please check the highlighted fields.';
    message.classList.toggle('profile-status-success', valid);
});
