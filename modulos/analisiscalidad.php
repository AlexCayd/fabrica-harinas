<?php require '../config/validar_permisos.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Análisis de Calidad</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>
        
        <div class="contenedor__modulo">
            <h2 class="heading">Análisis de Calidad</h2>
            
            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" class="buscador__input" placeholder="Lote de producción">
                </div>
                
                <div class="ordenar">
                    <h4 class="ordenar__label">Filtrar</h4>
                    <select name="categoria" id="categoria" class="ordenar__select">
                        <option value="alveografos">Alveógrafos</option>
                        <option value="farinografos">Farinógrafos</option>
                    </select>
                </div>
                
                <h2 class="botones__buscar">Buscar</h2>
                <a href="analisiscalidadform.html" class="botones__crear">Agregar análisis</a>
            </div>
            
            <table class="tabla">
                <thead>
                    <tr class="tabla__encabezado">
                        <th>Lote de producción</th>
                        <th>Secuencia de inspección</th>
                        <th>Valor del parámetro</th>
                        <th>Equipo de laboratorio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="tabla__fila">
                        <td>BARBS12024</td>
                        <td>A</td>
                        <td>50</td>
                        <td>Equipo de laboratorio</td>
                        <td class="tabla__botones">
                            <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                            <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                        </td>
                    </tr>
                    
                    <tr class="tabla__fila">
                        <td>BARBS12024</td>
                        <td>A</td>
                        <td>50</td>
                        <td>Equipo de laboratorio</td>
                        <td class="tabla__botones">
                            <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                            <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                        </td>
                    </tr>
                    
                    <tr class="tabla__fila">
                        <td>BARBS12024</td>
                        <td>A</td>
                        <td>50</td>
                        <td>Equipo de laboratorio</td>
                        <td class="tabla__botones">
                            <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                            <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                        </td>
                    </tr>
                    
                    <tr class="tabla__fila">
                        <td>BARBS12024</td>
                        <td>A</td>
                        <td>50</td>
                        <td>Equipo de laboratorio</td>
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