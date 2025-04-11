<?php
// Incluir el archivo de configuración de la base de datos
require '../config/conn.php';

// Inicializar variables de búsqueda
$busqueda = '';
$filtro = '';

// Procesar parámetros de búsqueda si existen$
if (isset($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
}
if (isset($_GET['filtro']) && !empty($_GET['filtro'])) {
    $filtro = $_GET['filtro'];
}

// Construir la consulta SQL con filtros si es necesario
$sql_analisis = "SELECT i.id_inspeccion, i.lote, i.secuencia, i.fecha_inspeccion,
                e.id_equipo, e.clave as equipo_clave, e.tipo_equipo, e.marca, e.modelo,
                i.alveograma_p, i.alveograma_l, i.alveograma_w, i.alveograma_pl, i.alveograma_ie,
                c.nombre as cliente_nombre
                FROM Inspeccion i
                INNER JOIN Equipo_Inspeccion ei ON i.id_inspeccion = ei.id_inspeccion
                INNER JOIN Equipos_Laboratorio e ON ei.id_equipo = e.id_equipo
                INNER JOIN Clientes c ON i.id_cliente = c.id_cliente";

// Añadir condición de búsqueda si hay texto de búsqueda
if (!empty($busqueda)) {
    $sql_analisis .= " WHERE i.lote LIKE :busqueda";
}

// Añadir filtro por tipo de equipo si está seleccionado
if (!empty($filtro)) {
    if (!empty($busqueda)) {
        $sql_analisis .= " AND e.tipo_equipo = :filtro";
    } else {
        $sql_analisis .= " WHERE e.tipo_equipo = :filtro";
    }
}

// Añadir orden
$sql_analisis .= " ORDER BY i.fecha_inspeccion DESC";

// Preparar y ejecutar la consulta
$stmt_analisis = $pdo->prepare($sql_analisis);

if (!empty($busqueda)) {
    $busquedaParam = '%' . $busqueda . '%';
    $stmt_analisis->bindParam(':busqueda', $busquedaParam);
}

if (!empty($filtro)) {
    $stmt_analisis->bindParam(':filtro', $filtro);
}

$stmt_analisis->execute();
$analisis = $stmt_analisis->fetchAll(PDO::FETCH_ASSOC);

// Función auxiliar para mostrar los parámetros específicos según el tipo de equipo
function mostrarParametros($analisis, $tipo_equipo) {
    if ($tipo_equipo == 'Alveógrafo') {
        return "P: " . $analisis['alveograma_p'] . ", L: " . $analisis['alveograma_l'] . 
               ", W: " . $analisis['alveograma_w'] . ", P/L: " . $analisis['alveograma_pl'] . 
               ", Ie: " . $analisis['alveograma_ie'];
    } else {
        // Para Farinógrafo u otros equipos, mostrar otros parámetros relevantes
        // Por ahora dejamos esto vacío ya que el SQL no incluye estos parámetros
        return "No disponible";
    }
}

// Verificar si hay mensajes de éxito o error
$mensajeExito = '';
$mensajeError = '';

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $accion = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($accion == 'insert') {
        $mensajeExito = 'El análisis ha sido registrado correctamente.';
    } elseif ($accion == 'update') {
        $mensajeExito = 'El análisis ha sido actualizado correctamente.';
    } elseif ($accion == 'delete') {
        $mensajeExito = 'El análisis ha sido eliminado correctamente.';
    }
}

if (isset($_GET['error']) && $_GET['error'] == '1') {
    $mensajeError = isset($_GET['message']) ? urldecode($_GET['message']) : 'Ha ocurrido un error al procesar la solicitud.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Análisis de Calidad</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <main class="contenedor hoja">
        <header class="header">
            <h2 class="header__logo">
                F.H. Elizondo
            </h2>

            <nav class="header__nav">
                <a href="../menu.php" class="header__btn">
                    <img class="header__icono" src="../img/home.svg" alt="Home">
                    <p class="header__textoicono">Home</p>
                </a>

                <a href="../index.php" class="header__btn">
                    <img class="header__icono" src="../img/exit.svg" alt="Home">
                    <p class="header__textoicono">Salir</p>
                </a>
            </nav>
        </header>

        <div class="contenedor__modulo">
            <h2 class="heading">Análisis de Calidad</h2>
            
            <?php if (!empty($mensajeExito)): ?>
            <div class="mensaje mensaje-exito">
                <?php echo $mensajeExito; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($mensajeError)): ?>
            <div class="mensaje mensaje-error">
                <?php echo $mensajeError; ?>
            </div>
            <?php endif; ?>

            <form action="" method="GET" class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" name="busqueda" class="buscador__input" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Lote de producción">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Filtrar</h4>
                    <select name="filtro" class="ordenar__select">
                        <option value="">Todos los equipos</option>
                        <option value="Alveógrafo" <?php echo $filtro == 'Alveógrafo' ? 'selected' : ''; ?>>Alveógrafos</option>
                        <option value="Farinógrafo" <?php echo $filtro == 'Farinógrafo' ? 'selected' : ''; ?>>Farinógrafos</option>
                    </select>
                </div>

                <button style="font-size: 18px; font-weight: bold; border: none;" type="submit" class="botones__buscar">Buscar</button>
                <a href="analisiscalidadform.php" class="botones__crear">Agregar análisis</a>
            </form>

            <div class="tabla-container">
                <table class="tabla">
                    <thead>
                        <tr class="tabla__encabezado">
                            <th>Lote de producción</th>
                            <th>Secuencia</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Equipo</th>
                            <th>Tipo</th>
                            <th>Parámetros</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($analisis) > 0): ?>
                            <?php foreach ($analisis as $item): ?>
                            <tr class="tabla__fila">
                                <td><?php echo htmlspecialchars($item['lote']); ?></td>
                                <td><?php echo htmlspecialchars($item['secuencia']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['fecha_inspeccion'])); ?></td>
                                <td><?php echo htmlspecialchars($item['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($item['equipo_clave']); ?> (<?php echo htmlspecialchars($item['marca']); ?> <?php echo htmlspecialchars($item['modelo']); ?>)</td>
                                <td><?php echo htmlspecialchars($item['tipo_equipo']); ?></td>
                                <td><?php echo mostrarParametros($item, $item['tipo_equipo']); ?></td>
                                <td class="tabla__botones">
                                    <a href="analisiscalidadform.php?id=<?php echo $item['id_inspeccion']; ?>">
                                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                                    </a>
                                    <a href="../config/procesar_analisis.php?id=<?php echo $item['id_inspeccion']; ?>&accion=eliminar" onclick="return confirm('¿Está seguro de eliminar este análisis?');">
                                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">No se encontraron análisis</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>