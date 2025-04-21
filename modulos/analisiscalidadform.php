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
            <a href="analisiscalidad.html" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar análisis de calidad</h2>
            <form action="analisiscalidad.html" class="formulario">
                <div class="formulario__campo">
                    <label for="lote_produccion" class="formulario__label">Lote de Producción</label>
                    <input type="text" class="formulario__input" placeholder="Lote de producción">
                </div>
                
                <div class="formulario__campo">
                    <label for="secuencia" class="formulario__label">Secuencia de inspección</label>
                    <input type="text" class="formulario__input" placeholder="A">
                </div>
                
                <div class="formulario__campo">
                    <label for="valor_parametro" class="formulario__label">Valor del parámetro 1</label>
                    <input type="number" class="formulario__input" placeholder="50">
                </div>
                
                
                <div class="formulario__campo">
                    <label for="equipo_laboratorio" class="formulario__label">Rol</label>
                    <select name="categoria" id="categoria" class="formulario__select">
                        <option value="alveografos">Alveógrafos</option>
                        <option value="farinografos">Farinógrafos</option>
                    </select>
                </div>
                
                
                <input type="submit" class="formulario__submit" value="Agregar análisis de calidad">
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
</html>