<?php require '../config/validar_permisos.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Clientes</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <main  class="contenedor hoja">
        <?php include '../includes/header.php' ?>


        <div class="contenedor__modulo">
            <a href="clientes.html" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar Cliente</h2>
            <form action="clientes/agregar_cliente.php" class="formulario" method="post">
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre</label>
                    <input type="text" name="nombre" class="formulario__input" placeholder="Nombre">
                </div>

                <div class="formulario__campo">
                    <label for="certificado" class="formulario__label"> Requiere certificado </label>
                    <select name="certificado" id="categoria" class="formulario__select">
                        <option value="1"> Si </option>
                        <option value="0"> No </option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="email" class="formulario__input" placeholder="Correo electrónico">
                </div>

                <div class="formulario__campo">
                    <label for="rfc" class="formulario__label"> RFC </label>
                    <input type="text" name="rfc" class="formulario__input" placeholder="RFC">
                </div>

                <div class="formulario__campo">
                    <label for="puesto" class="formulario__label"> Puesto </label>
                    <input type="text" name="puesto" class="formulario__input" placeholder="Puesto">
                </div>

                <div class="formulario__campo">
                    <label for="numero-telefonico" class="formulario__label"> Numero telefónico </label>
                    <input type="text" name="numero-telefonico" class="formulario__input" placeholder="Numero telefonico">
                </div>

                <div class="formulario__campo">
                    <label for="direccion-fiscal" class="formulario__label"> Direccion fiscal </label>
                    <input type="text" name="direccion-fiscal" class="formulario__input" placeholder="Direccion fiscal">
                </div>

                <div class="formulario__campo">
                    <label for="rol" class="formulario__label">Estado</label>
                    <select name="categoria" id="categoria" class="formulario__select">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="parametros" class="formulario__label"> Parametros </label>
                    <select name="parametros" id="categoria" class="formulario__select">
                        <option value="Internacionales"> Internacionales </option>
                        <option value="Personalizados"> Personalizados </option>
                    </select>
                </div>

                <input type="submit" class="formulario__submit" value="Agregar cliente">
            </form>
        </div>
    <?php include '../includes/footer.php' ?>
    </main>
</body>
</html>