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
} else if (isset($_SESSION['exito'])) {
    echo '  <script>
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "' . $_SESSION['exito'] . ' ",
                        showConfirmButton: true,
                        timer: 1500
                        });
                    </script>';
    unset($_SESSION['exito']);
}
?>
<header class="header">
    <h2 class="header__logo">
        F.H. Elizondo
    </h2>
    <p><?php echo $_SESSION["username"] . ' [' . $_SESSION["rol"]  . ']'?></p>
    <nav class="header__nav">
        <a href="/fabrica-harinas/menu.php" class="header__btn">
            <img class="header__icono" src="/fabrica-harinas/img/home.svg" alt="Home">
            <p class="header__textoicono">Home</p>
        </a>

        <a href="/fabrica-harinas/config/logout.php" class="header__btn">
            <img class="header__icono" src="/fabrica-harinas/img/exit.svg" alt="Home">
            <p class="header__textoicono">Salir</p>
        </a>
    </nav>
</header>