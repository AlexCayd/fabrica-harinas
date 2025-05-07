<?php include_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fábrica de Harinas Elizondo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    //Validamos si existe sesión actica para permitir o denegar el acceso a la página
    session_start();
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        header("location: " . BASE_URL . "menu.php");
    }
    ?>
</head>

<body>
    <!-- ALERTAS -->
    <?php
    if (isset($_SESSION['error'])) {
        echo '  <script>
                    Swal.fire({
                            icon: "error",
                            title: "Oops!",
                            text: "' . $_SESSION['error'] . '",
                            });
                    </script>';
        unset($_SESSION['error']);
    }

    ?>
    <main class="login">
        <div class="login__contenedor contenedor">
            <div class="login__contenido">
                <h1 class="login__titulo">Inicia sesión</h1>
                <h3 class="login__subtitulo">¡Bienvenido de vuelta al portal de la Fábrica de Harinas Elizondo!</h3>
                <!-- <p class="alerta__login">¡No tienes permitido acceder!</p>
                -->
                <form class="login__login" id="form" action="<?= BASE_URL ?>config/login.php" method="post">
                    <div class="login__campo">
                        <label for="mail" class="login__label">Correo</label>
                        <input type="email" name="mail" id="correo" class="login__input" required placeholder="correo@ejemplo.com">
                    </div>

                    <div class="login__campo password">
                        <label for="password" class="login__label">Contraseña</label>
                        <input type="password" id="passwd" name="passwd" class="login__input" required placeholder="">
                        <button class="toggle-btn" id="viewPasswd" onclick="togglePassword()">
                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="login__submit">
                        <input class="login__btn" type="submit" value="Iniciar sesión">
                    </div>
                </form>
            </div>
            <div class="login__imagen">
                <img src="img/pan-login.jpg" alt="Imagen de login" class="login__img">
            </div>
        </div>
    </main>
    <script>
        const viewPasswd = document.getElementById('viewPasswd');
        const passwdField = document.getElementById('passwd');

        viewPasswd.addEventListener("click", function (event) {
            event.preventDefault()
        });


        function togglePassword() {
            if (passwdField.type === "password") {
                passwdField.type = "text";
                document.querySelector('.toggle-btn').innerHTML = '<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/><path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>';
            } else {
                passwdField.type = "password";
                document.querySelector('.toggle-btn').innerHTML = '<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>';
            }
        }
    </script>
</body>

</html>