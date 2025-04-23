<?php require '../config/validar_permisos.php'; ?>

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
    <main class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <h2 class="heading">Certificados</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" class="buscador__input" placeholder="Lote de producción">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Resultados</h4>
                    <select name="categoria" id="categoria" class="ordenar__select">
                        <option value="aprobado">Aprobado</option>
                        <option value="desaprobado">Desaprobado</option>
                    </select>
                </div>

                <h2 class="botones__buscar">Buscar</h2>
                <a href="certificadosform.html" class="botones__crear">Agregar certificado</a>
            </div>

            <table class="tabla">
            <thead>
                <tr class="tabla__encabezado">
                    <th>Lote de producción</th>
                    <th>Número de orden</th>
                    <th>Cantidad solicitada (kg)</th>
                    <th>Cantidad entregada (kg)</th>
                    <th>Resultados del análisis</th>
                    <th>Comparación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr class="tabla__fila">
                    <td>BARBS1012</td>
                    <td>38</td>
                    <td>90</td>
                    <td>85</td>
                    <td>Aprobado</td>
                    <td>Dentro de rango</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>

                <tr class="tabla__fila">
                    <td>BARBS1012</td>
                    <td>38</td>
                    <td>90</td>
                    <td>85</td>
                    <td>Aprobado</td>
                    <td>Dentro de rango</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>

                <tr class="tabla__fila">
                    <td>BARBS1012</td>
                    <td>38</td>
                    <td>90</td>
                    <td>85</td>
                    <td>Aprobado</td>
                    <td>Dentro de rango</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>

                <tr class="tabla__fila">
                    <td>BARBS1012</td>
                    <td>38</td>
                    <td>90</td>
                    <td>85</td>
                    <td>Aprobado</td>
                    <td>Dentro de rango</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>
</body>
</html>