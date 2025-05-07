<?php 
include_once '../includes/config.php';
require '../config/validar_permisos.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Certificados</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <main  class="contenedor hoja">
        <?php include '../includes/header.php'; ?>
        
        <div class="contenedor__modulo">
            <a href="historico.html" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar Certificado</h2>
            <form action="historico.html" class="formulario">
                <div class="formulario__campo">
                    <label for="lote_produccion" class="formulario__label">Lote de producción</label>
                    <input type="text" class="formulario__input" placeholder="Lote de producción">
                </div>
                
                <div class="formulario__campo">
                    <label for="orden_compra" class="formulario__label">Número de compra</label>
                    <input type="number" class="formulario__input" placeholder="Número de compra">
                </div>
                
                <div class="formulario__campo">
                    <label for="cantidad_solicitada" class="formulario__label">Cantidad solicitada (kg)</label>
                    <input type="number" class="formulario__input" placeholder="Cantidad solicitada">
                </div>
                
                <div class="formulario__campo">
                    <label for="cantidad_entregada" class="formulario__label">Cantidad entregada (kg)</label>
                    <input type="number" class="formulario__input" placeholder="Cantidad entregada">
                </div>
                
                <div class="formulario__campo">
                    <label for="resultados" class="formulario__label">Resultados del análisis</label>
                    <select name="categoria" id="categoria" class="formulario__select">
                        <option value="aprobado">Aprobado</option>
                        <option value="desaprobado">Desaprobado</option>
                    </select>
                </div>
                
                <div class="formulario__campo">
                    <label for="comparacion" class="formulario__label">Comparación con valores de referencia</label>
                    <select name="categoria" id="categoria" class="formulario__select">
                        <option value="dentro_rango">Dentro de rango</option>
                        <option value="fuera_rango">Fuera de rango</option>
                    </select>
                </div>
                
                <input type="submit" class="formulario__submit" value="Agregar certificado">
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
</html>