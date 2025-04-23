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
        if (isset($_SESSION['user_id']) && isset($_SESSION['username'])){
            header("location: /fabrica-harinas/menu.php");
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
                <form class="login__login" action="/fabrica-harinas/config/login.php" method="post">
                    <div class="login__campo">
                        <label for="mail" class="login__label">Correo</label>
                        <input type="email" name="mail" class="login__input" required placeholder="correo@ejemplo.com">
                    </div>

                    <div class="login__campo">
                        <label for="password" class="login__label">Contraseña</label>
                        <input type="password" name="passwd" class="login__input" required placeholder="">
                    </div>

                    <div class="login__submit">
                        <input class="login__btn" type="submit" value="Iniciar sesión">
                    </div>
                </form>
                <a href="menu.php" style="margin-top: 25px;">Atajo a menú</a>
            </div>
            <div class="login__imagen">
                <img src="img/pan-login.jpg" alt="Imagen de login" class="login__img">
            </div>
        </div>
    </main>
    <script src="app.js"></script>
</body>
</html>