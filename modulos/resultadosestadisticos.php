<?php require '../config/validar_permisos.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Reportes Estadísticos</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <main  class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <a href="estadisticos.php" class="atras">Ir atrás</a>
            <h2 class="heading">Resultados de estadísticos</h2>

            <div class="resultados">
                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Total de parámetros analizados</h2>
                    <h2 class="resultados__numero">15</h2>
                </div>

                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Parámetros aprobados</h2>
                    <h2 class="resultados__numero">10</h2>
                </div>

                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Parámetros no aprobados</h2>
                    <h2 class="resultados__numero">10</h2>
                </div>

                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Certificados generados</h2>
                    <h2 class="resultados__numero">6</h2>
                </div>
            </div>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>
</body>
</html>