<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Solo inicia la sesión si no está activa
    }  
        
    if (!isset($_SESSION['user_id'])){
        header('Location: /fabrica-harinas/index.php');
    } 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Menú Principal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <main class="contenedor hoja">
        
        <?php include 'includes/header.php'; ?>
        <div class="menu">
            <h1 class="menu__titulo">Menú Principal</h1>

            <div class="menu__grid">
                <a href="/fabrica-harinas/modulos/usuarios.php" class="menu__card">
                    <img src="img/usuarios.svg" alt="Reportes" class="menu__icono">
                    <h2 class="menu__texto">Usuarios</h2>
                </a>

                <a href="modulos/laboratorios.php" class="menu__card">
                    <img src="img/laboratorios.svg" alt="Laboratorio" class="menu__icono">
                    <h2 class="menu__texto">Equipos de Laboratorio</h2>
                </a>

                <a href="modulos/analisiscalidad.php" class="menu__card">
                    <img src="img/quality.svg" alt="Análisis" class="menu__icono">
                    <h2 class="menu__texto">Análisis de Calidad</h2>
                </a>

                <a href="modulos/clientes.php" class="menu__card">
                    <img src="img/clientes.svg" alt="Clientes" class="menu__icono">
                    <h2 class="menu__texto">Clientes</h2>
                </a>
                
                <a href="modulos/historico.php" class="menu__card">
                    <img src="img/historico.svg" alt="Certificados" class="menu__icono">
                    <h2 class="menu__texto">Certificados</h2>
                </a>

                <a href="modulos/estadisticos.php" class="menu__card">
                    <img src="img/stats.svg" alt="Estadísticos" class="menu__icono">
                    <h2 class="menu__texto">Reportes estadísticos</h2>
                </a>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </main>
</body>
</html>