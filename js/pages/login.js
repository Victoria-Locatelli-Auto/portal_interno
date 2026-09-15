function switchTab(formId, button, title) {
    document.querySelectorAll('.login-form').forEach((form) => {
        form.classList.remove('active');
    });

    document.querySelectorAll('.tab-btn').forEach((tabButton) => {
        tabButton.classList.remove('active');
    });

    const selectedForm = document.getElementById(formId);

    if (selectedForm) {
        selectedForm.classList.add('active');
    }

    if (button) {
        button.classList.add('active');
    }

    const cardTitle = document.getElementById('card-title');

    if (cardTitle) {
        cardTitle.textContent = title;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-btn');

    tabButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const target = this.getAttribute('data-target');
            const title = this.getAttribute('data-title');

            document.body.classList.toggle('show-register', target === 'register-form');
            switchTab(target, this, title);
        });
    });

    if (window.loginPageState?.showRegisterTab) {
        document.body.classList.add('show-register');
        const registerButton = document.querySelector('[data-target="register-form"]');

        if (registerButton) {
            switchTab('register-form', registerButton, 'Primeiro Acesso');
        }
    }

    if (window.loginPageState?.showLoginTab) {
        document.body.classList.remove('show-register');
        const loginButton = document.querySelector('[data-target="login-form"]');

        if (loginButton) {
            switchTab('login-form', loginButton, 'Login');
        }
    }
});
