document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="password"]').forEach((input) => {
        const container = ensurePasswordToggleContainer(input);
        if (!container || container.querySelector('.password-toggle-btn')) {
            return;
        }

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

function ensurePasswordToggleContainer(input) {
    const floating = input.closest('.form-floating');
    if (floating) {
        floating.classList.add('password-field-wrap');

        return floating;
    }

    const parent = input.parentElement;
    if (!parent) {
        return null;
    }

    if (parent.classList.contains('password-input-wrap')) {
        parent.classList.add('password-field-wrap');

        return parent;
    }

    const isInputOnlyContainer = parent.children.length === 1
        && parent.querySelector(':scope > input[type="password"]') === input;

    if (isInputOnlyContainer) {
        parent.classList.add('password-field-wrap', 'password-input-wrap');

        return parent;
    }

    const wrap = document.createElement('div');
    wrap.className = 'password-input-wrap password-field-wrap';
    parent.insertBefore(wrap, input);
    wrap.appendChild(input);

    return wrap;
}
