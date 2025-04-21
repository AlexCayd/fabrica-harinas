<?php require '../config/validar_permisos.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Equipos de Laboratorio</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <h2 class="heading">Equipos de Laboratorio</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" class="buscador__input" placeholder="Clave de equipo">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Ordenar por</h4>
                    <select name="categoria" id="categoria" class="ordenar__select">
                        <option value="clave_equipo">Clave de equipo</option>
                        <option value="marca">Marca</option>
                        <option value="modelo">Modelo</option>
                    </select>
                </div>

                <h2 class="botones__buscar">Buscar</h2>
                <a href="laboratoriosform.html" class="botones__crear">Agregar equipo</a>
            </div>

            <table class="tabla">
            <thead>
                <tr class="tabla__encabezado">
                    <th>Clave</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Serie</th>
                    <th>Descripción larga</th>
                    <th>Descripción corta</th>
                    <th>Garantía</th>
                    <th>Valores de referencia</th>
                    <th>Encargado</th>
                    <th>Ubicación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr class="tabla__fila">
                    <td>EQ-001</td>
                    <td>Brabender</td>
                    <td>FMB-300</td>
                    <td>BRD12345</td>
                    <td>Equipo farinógrafo de precisión para análisis de absorción de agua en harinas.</td>
                    <td>Farinógrafo</td>
                    <td>24</td>
                    <td>Absorción: 58-62%, Estabilidad: 10-12 min</td>
                    <td>Laura Méndez</td>
                    <td>Laboratorio 1</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>

                <tr class="tabla__fila">
                    <td>EQ-001</td>
                    <td>Brabender</td>
                    <td>FMB-300</td>
                    <td>BRD12345</td>
                    <td>Equipo farinógrafo de precisión para análisis de absorción de agua en harinas.</td>
                    <td>Farinógrafo</td>
                    <td>24</td>
                    <td>Absorción: 58-62%, Estabilidad: 10-12 min</td>
                    <td>Laura Méndez</td>
                    <td>Laboratorio 1</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>

                <tr class="tabla__fila">
                    <td>EQ-001</td>
                    <td>Brabender</td>
                    <td>FMB-300</td>
                    <td>BRD12345</td>
                    <td>Equipo farinógrafo de precisión para análisis de absorción de agua en harinas.</td>
                    <td>Farinógrafo</td>
                    <td>24</td>
                    <td>Absorción: 58-62%, Estabilidad: 10-12 min</td>
                    <td>Laura Méndez</td>
                    <td>Laboratorio 1</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>

                <tr class="tabla__fila">
                    <td>EQ-001</td>
                    <td>Brabender</td>
                    <td>FMB-300</td>
                    <td>BRD12345</td>
                    <td>Equipo farinógrafo de precisión para análisis de absorción de agua en harinas.</td>
                    <td>Farinógrafo</td>
                    <td>24</td>
                    <td>Absorción: 58-62%, Estabilidad: 10-12 min</td>
                    <td>Laura Méndez</td>
                    <td>Laboratorio 1</td>
                    <td class="tabla__botones">
                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
</html>