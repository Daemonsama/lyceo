document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="password"]').forEach((input) => {
        const container = input.closest('.form-floating') ?? input.parentElement;
        if (!container || container.querySelector('.password-toggle-btn')) {
            return;
        }

        container.classList.add('password-field-wrap');

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-toggle-btn';
        button.setAttribute('aria-label', 'Afficher le mot de passe');
        button.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';

        button.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            button.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });

        container.appendChild(button);
    });
});
