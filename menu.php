<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Menú Principal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/menu.css">

    <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Solo inicia la sesión si no está activa
        }  
    
    ?>
    
</head>
<body>
    <main class="contenedor hoja">
        <header class="header">
            <h2 class="header__logo">
                F.H. Elizondo
            </h2>

            <nav class="header__nav">
                <a href="#" class="header__btn">
                    <img class="header__icono" src="img/home.svg" alt="Home">
                    <p class="header__textoicono">Home</p>
                </a>

                <a href="/fabrica-harinas/config/logout.php" class="header__btn">
                    <img class="header__icono" src="img/exit.svg" alt="Home">
                    <p class="header__textoicono">Salir</p>
                </a>
            </nav>
        </header>

        <div class="menu">
            <h1 class="menu__titulo">Menú Principal</h1>

            <div class="menu__grid">
                <a href="/fabrica-harinas/modulos/usuarios.php" class="menu__card">
                    <img src="img/usuarios.svg" alt="Reportes" class="menu__icono">
                    <h2 class="menu__texto">Usuarios</h2>
                </a>

                <a href="modulos/laboratorios.html" class="menu__card">
                    <img src="img/laboratorios.svg" alt="Laboratorio" class="menu__icono">
                    <h2 class="menu__texto">Equipos de Laboratorio</h2>
                </a>

                <a href="modulos/analisiscalidad.html" class="menu__card">
                    <img src="img/quality.svg" alt="Análisis" class="menu__icono">
                    <h2 class="menu__texto">Análisis de Calidad</h2>
                </a>

                <a href="modulos/clientes.html" class="menu__card">
                    <img src="img/clientes.svg" alt="Clientes" class="menu__icono">
                    <h2 class="menu__texto">Clientes</h2>
                </a>
                
                <a href="modulos/historico.html" class="menu__card">
                    <img src="img/historico.svg" alt="Certificados" class="menu__icono">
                    <h2 class="menu__texto">Certificados</h2>
                </a>

                <a href="modulos/estadisticos.html" class="menu__card">
                    <img src="img/stats.svg" alt="Estadísticos" class="menu__icono">
                    <h2 class="menu__texto">Reportes estadísticos</h2>
                </a>
            </div>
        </div>

        <div class="footer">
            <p class="footer__texto">Fábrica de Harinas Elizondo. Todos los derechos reservados &copy; 2025.</p>
        </div>
    </main>
</body>
</html>