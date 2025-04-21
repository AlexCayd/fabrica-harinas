<?php require '../config/validar_permisos.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Usuarios</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <main  class="contenedor hoja">
        <?php include '../includes/header.php'; ?>


        <div class="contenedor__modulo">
            <a href="laboratorios.html" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar Equipo de Laboratorio</h2>
            <form action="laboratorios.html" class="formulario">
                <div class="formulario__campo">
                    <label for="clave" class="formulario__label">Clave del equipo</label>
                    <input type="text" class="formulario__input" placeholder="Clave del equipo">
                </div>
                
                <div class="formulario__campo">
                    <label for="apellido" class="formulario__label">Marca</label>
                    <input type="text" class="formulario__input" placeholder="Marca">
                </div>

                <div class="formulario__campo">
                    <label for="modelo" class="formulario__label">Modelo</label>
                    <input type="text" class="formulario__input" placeholder="Modelo">
                </div>

                <div class="formulario__campo">
                    <label for="serie" class="formulario__label">Serie</label>
                    <input type="text" class="formulario__input" placeholder="Serie">
                </div>

                <div class="formulario__campo">
                    <label for="descripcion_larga" class="formulario__label">Descripción larga</label>
                    <textarea name="descripcion_larga" id="descripcion_larga" class="formulario__input"></textarea>
                </div>

                <div class="formulario__campo">
                    <label for="descripcion_corta" class="formulario__label">Descripción corta</label>
                    <input type="text" class="formulario__input" placeholder="Descripción corta">
                </div>

                <div class="formulario__campo">
                    <label for="garantia" class="formulario__label">Garantía</label>
                    <input type="text" class="formulario__input" placeholder="Garantía">
                </div>

                <div class="formulario__campo">
                    <label for="valores_referencia" class="formulario__label">Valores de referencia</label>
                    <input type="text" class="formulario__input" placeholder="Valores de referencia">
                </div>

                <div class="formulario__campo">
                    <label for="encargado" class="formulario__label">Encargado del equipo</label>
                    <input type="text" class="formulario__input" placeholder="Encargado del equipo">
                </div>

                <div class="formulario__campo">
                    <label for="serie" class="formulario__label">Ubicación del equipo</label>
                    <input type="text" class="formulario__input" placeholder="Ubicación">
                </div>

                
                <div class="formulario__campo">
                    <label for="vigencia" class="formulario__label">Vigencia de la garantía</label>
                    <input type="date" class="formulario__input">
                </div>

                <div class="formulario__campo">
                    <label for="adquisicion" class="formulario__label">Fecha de adquisición</label>
                    <input type="date" class="formulario__input">
                </div>

                <input type="submit" class="formulario__submit" value="Agregar equipo">
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
</html>