<?php
require '../config/validar_permisos.php';
// Incluir el archivo de configuración de la base de datos
require '../config/conn.php';

// Inicializar variables de búsqueda
$busqueda = '';
$orderBy = 'clave';

// Procesar parámetros de búsqueda si existen
if (isset($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
}
if (isset($_GET['ordenar'])) {
    $orderBy = $_GET['ordenar'];
}

// Construir la consulta SQL con filtros si es necesario
$sql_equipos = "SELECT e.*, u.nombre as nombre_responsable 
                FROM Equipos_Laboratorio e
                LEFT JOIN Usuarios u ON e.id_responsable = u.id_usuario";

// Añadir condición de búsqueda si hay texto de búsqueda
if (!empty($busqueda)) {
    $sql_equipos .= " WHERE e.clave LIKE :busqueda1 
                    OR e.marca LIKE :busqueda2 
                    OR e.modelo LIKE :busqueda3";
}

// Añadir orden
$sql_equipos .= " ORDER BY e." . $orderBy;

// Preparar y ejecutar la consulta
$stmt_equipos = $pdo->prepare($sql_equipos);

if (!empty($busqueda)) {
    $busquedaParam = '%' . $busqueda . '%';
    $stmt_equipos->bindParam(':busqueda1', $busquedaParam);
    $stmt_equipos->bindParam(':busqueda2', $busquedaParam);
    $stmt_equipos->bindParam(':busqueda3', $busquedaParam);
}

$stmt_equipos->execute();
$equipos = $stmt_equipos->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener los parámetros correspondientes a cada equipo
$sql_parametros = "SELECT id_equipo, 
                  GROUP_CONCAT(CONCAT(nombre_parametro, ': ', lim_Inferior, '-', lim_Superior) SEPARATOR ', ') as valores_referencia
                  FROM Parametros 
                  WHERE tipo = 'Internacional'
                  GROUP BY id_equipo";

$stmt_parametros = $pdo->query($sql_parametros);
$parametros = [];
while ($row = $stmt_parametros->fetch(PDO::FETCH_ASSOC)) {
    $parametros[$row['id_equipo']] = $row['valores_referencia'];
}

// Consulta para obtener responsables (para el formulario de agregar o editar)
$sql_responsables = "SELECT id_usuario, nombre FROM Usuarios 
                    WHERE rol IN ('Gerencia de Control de Calidad', 'Laboratorio')
                    ORDER BY nombre";
$responsables = $pdo->query($sql_responsables)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Equipos de Laboratorio</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--<style>
    /*   /* Estilos para corregir el desbordamiento de la tabla */
        .contenedor__modulo {
            overflow-x: auto; /* Permite desplazamiento horizontal */
            max-width: 100%; /* Asegura que no sea más ancho que su contenedor */
        }
        
        .tabla {
            width: 100%; /* Tabla ocupa todo el ancho disponible */
            table-layout: fixed; /* Ayuda a controlar el ancho de columnas */
        }
        
        /* Ajustar anchos de columnas específicas */
        .tabla th, .tabla td {
            word-wrap: break-word; /* Permite que el texto se rompa */
            overflow-wrap: break-word;
            max-width: 200px; /* Ancho máximo para celdas con mucho texto */
        }
        
        /* Columnas con textos cortos */
        .tabla th:nth-child(1), .tabla td:nth-child(1), /* Clave */
        .tabla th:nth-child(2), .tabla td:nth-child(2), /* Marca */
        .tabla th:nth-child(3), .tabla td:nth-child(3), /* Modelo */
        .tabla th:nth-child(4), .tabla td:nth-child(4), /* Serie */
        .tabla th:nth-child(6), .tabla td:nth-child(6), /* Desc. corta */
        .tabla th:nth-child(7), .tabla td:nth-child(7), /* Garantía */
        .tabla th:nth-child(9), .tabla td:nth-child(9), /* Encargado */
        .tabla th:nth-child(10), .tabla td:nth-child(10), /* Ubicación */
        .tabla th:nth-child(11), .tabla td:nth-child(11) { /* Acciones */
            width: auto; /* Ancho automático para textos cortos */
        }
        
        /* Columnas con textos largos */
        .tabla th:nth-child(5), .tabla td:nth-child(5), /* Desc. larga */
        .tabla th:nth-child(8), .tabla td:nth-child(8) { /* Valores de referencia */
            width: 20%; /* Asignar más espacio a textos largos */
        }
        
        /* Estilo para columna de acciones */
        .tabla__botones {
            white-space: nowrap; /* Evita que los botones se separen */
            width: 80px !important; /* Ancho fijo pequeño */
        }
    */
    </style>
    -->
</head>
<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="../menu.php" class="atras">Ir atrás</a>
            <h2 class="heading">Equipos de Laboratorio</h2>

            <form action="" method="GET" class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" name="busqueda" class="buscador__input" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Clave, marca o modelo">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Ordenar por</h4>
                    <select name="ordenar" class="ordenar__select">
                        <option value="clave" <?php echo $orderBy == 'clave' ? 'selected' : ''; ?>>Clave de equipo</option>
                        <option value="marca" <?php echo $orderBy == 'marca' ? 'selected' : ''; ?>>Marca</option>
                        <option value="modelo" <?php echo $orderBy == 'modelo' ? 'selected' : ''; ?>>Modelo</option>
                    </select>
                </div>

                <button style="font-size: 18px; font-weight: bold; border: none;" type="submit" class="botones__buscar">Buscar</button>
                <a href="laboratoriosform.php" class="botones__crear">Agregar equipo</a>
            </form>

            <div class="tabla-container">
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
                        <?php if(count($equipos) > 0): ?>
                            <?php foreach ($equipos as $equipo): ?>
                            <tr class="tabla__fila">
                                <td><?php echo htmlspecialchars($equipo['clave']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['marca']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['modelo']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['serie']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['desc_larga']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['desc_corta']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['garantia']); ?></td>
                                <td><?php echo isset($parametros[$equipo['id_equipo']]) ? htmlspecialchars($parametros[$equipo['id_equipo']]) : ''; ?></td>
                                <td><?php echo htmlspecialchars($equipo['nombre_responsable']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['ubicacion']); ?></td>
                                <td class="tabla__botones">
                                    <a href="laboratoriosform.php?id=<?php echo $equipo['id_equipo']; ?>">
                                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                                    </a>
                                    <a href="../config/procesar_equipo.php?id=<?php echo $equipo['id_equipo']; ?>&accion=eliminar" onclick="return confirm('¿Está seguro de eliminar este equipo?');">
                                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align: center;">No se encontraron equipos</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
</html>