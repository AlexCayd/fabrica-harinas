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
            <?php 
                $rol = $_SESSION['rol'];
                
                // Definir módulos y los roles que tienen acceso
                $modulos = [
                    'usuarios' => [
                        'url' => '/fabrica-harinas/modulos/usuarios.php',
                        'img' => 'img/usuarios.svg',
                        'texto' => 'Usuarios',
                        'roles' => ['TI']
                    ],
                    'laboratorios' => [
                        'url' => '/fabrica-harinas/modulos/laboratorios.php',
                        'img' => 'img/laboratorios.svg',
                        'texto' => 'Equipos de Laboratorio',
                        'roles' => ['Laboratorio', 'TI', 'Gerencia de Control de Calidad']
                    ],
                    'clientes' => [
                        'url' => '/fabrica-harinas/modulos/clientes.php',
                        'img' => 'img/clientes.svg',
                        'texto' => 'Clientes',
                        'roles' => ['Laboratorio', 'TI', 'Gerencia de Control de Calidad']
                    ],
                    'analisiscalidad' => [
                        'url' => '/fabrica-harinas/modulos/analisiscalidad.php',
                        'img' => 'img/quality.svg',
                        'texto' => 'Análisis de Calidad',
                        'roles' => ['Laboratorio', 'TI', 'Gerencia de Control de Calidad']
                    ],
                    'historico' => [
                        'url' => '/fabrica-harinas/modulos/historico.php',
                        'img' => 'img/historico.svg',
                        'texto' => 'Certificados',
                        'roles' => ['Laboratorio', 'TI', 'Gerencia de Control de Calidad']
                    ],
                    'estadisticos' => [
                        'url' => '/fabrica-harinas/modulos/estadisticos.php',
                        'img' => 'img/stats.svg',
                        'texto' => 'Reportes estadísticos',
                        'roles' => ['Gerencia de Aseguramiento de Calidad', 'TI', 'Gerente de Planta', 'Director de Operaciones']
                    ],
                ];
                
                // Recorrer módulos y mostrar solo los que coincidan con el rol
                foreach ($modulos as $modulo) {
                    if (in_array($rol, $modulo['roles'])) {
                        echo '
                            <a href="' . $modulo['url'] . '" class="menu__card">
                                <img src="' . $modulo['img'] . '" alt="' . $modulo['texto'] . '" class="menu__icono">
                                <h2 class="menu__texto">' . $modulo['texto'] . '</h2>
                            </a>';
                    }
                }
                ?>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </main>
</body>
</html>