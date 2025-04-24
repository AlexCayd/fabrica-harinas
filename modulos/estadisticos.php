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
            <a href="../menu.php" class="atras">Ir atrás</a>
            <h2 class="heading">Reporte Estadístico</h2>
            <form action="resultadosestadisticos.php" class="formulario">
                <div class="formulario__campo">
                    <label for="fecha_inicio" class="formulario__label">Fecha de inicio</label>
                    <input type="date" class="formulario__input">
                </div>

                <div class="formulario__campo">
                    <label for="fecha_fin" class="formulario__label">Fecha de fin</label>
                    <input type="date" class="formulario__input">
                </div>

                <input type="submit" class="formulario__submit" value="Generar estadísticos">
            </form>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>
</body>
</html>