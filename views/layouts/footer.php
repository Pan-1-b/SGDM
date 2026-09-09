        <footer>

            <p>
                © <?= date('Y') ?> HERMES
            </p>

        </footer>

    </div>

<script src="<?= BASE_URL ?>/js/app.js"></script>



<script>


document.addEventListener('DOMContentLoaded', function () {

    const logoutLink = document.querySelector('#logout-link');

    if (!logoutLink) {
        return;
    }

    logoutLink.addEventListener('click', async function (e) {

        e.preventDefault();

        try {

            const response = await fetch(logoutLink.href, {
                method: 'GET'
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
                text: 'Ocurrió un error al cerrar la sesión.'
            });

        }

    });

});


</script>
</body>

</html>