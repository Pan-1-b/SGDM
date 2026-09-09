function initializePasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
        if (toggle.dataset.passwordToggleInitialized === 'true') {
            return;
        }

        toggle.dataset.passwordToggleInitialized = 'true';
        toggle.addEventListener('click', function () {
            const input = document.querySelector(toggle.dataset.passwordToggle);

            if (!input) {
                return;
            }

            const isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            toggle.setAttribute('aria-pressed', String(!isVisible));
            toggle.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');

            const icon = toggle.querySelector('.eye-icon');
            if (icon) {
                icon.classList.toggle('bi-eye', !isVisible);
                icon.classList.toggle('bi-eye-slash', isVisible);
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePasswordToggles);
} else {
    initializePasswordToggles();
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-json-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            try {
                const formData = new FormData(form);
                if (event.submitter && event.submitter.name) {
                    formData.append(event.submitter.name, event.submitter.value);
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                await Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Operación exitosa' : 'Ocurrió un error',
                    text: data.message
                });

                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch (error) {
                console.error(error);

                await Swal.fire({
                    icon: 'error',
                    title: 'Ocurrió un error',
                    text: 'Ocurrió un error al comunicarse con el servidor.'
                });
            }
        });
    });

    document.querySelectorAll('.js-delete-torneo-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const confirmation = await Swal.fire({
                icon: 'warning',
                title: '¿Cancelar torneo?',
                text: 'Esta acción cambiará el estado del torneo a cancelado.',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No'
            });

            if (!confirmation.isConfirmed) {
                return;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await response.json();

                await Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Operación exitosa' : 'Ocurrió un error',
                    text: data.message
                });

                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch (error) {
                console.error(error);

                await Swal.fire({
                    icon: 'error',
                    title: 'Ocurrió un error',
                    text: 'Ocurrió un error al comunicarse con el servidor.'
                });
            }
        });
    });
});