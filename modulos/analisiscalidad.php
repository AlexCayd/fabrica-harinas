<?php
require '../config/conn.php';

$busqueda = '';
$filtro = '';

if (isset($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
}
if (isset($_GET['filtro']) && !empty($_GET['filtro'])) {
    $filtro = $_GET['filtro'];
}

// Construir la consulta SQL con filtros si es necesario
$sql_analisis = "SELECT 
                i.id_inspeccion, i.lote, i.secuencia, i.fecha_inspeccion, i.clave,
                e.id_equipo, e.clave as equipo_clave, e.tipo_equipo, e.marca, e.modelo,
                c.nombre as cliente_nombre,
                COUNT(DISTINCT ri.id_resultado) as total_parametros,
                SUM(CASE WHEN ri.aprobado = 0 THEN 1 ELSE 0 END) as parametros_fallidos
                FROM Inspeccion i
                INNER JOIN Equipo_Inspeccion ei ON i.id_inspeccion = ei.id_inspeccion
                INNER JOIN Equipos_Laboratorio e ON ei.id_equipo = e.id_equipo
                LEFT JOIN Clientes c ON i.id_cliente = c.id_cliente
                LEFT JOIN Resultado_Inspeccion ri ON i.id_inspeccion = ri.id_inspeccion";



// Añadir filtro por tipo de equipo si está seleccionado
if (!empty($filtro)) {
    if (!empty($busqueda)) {
        $sql_analisis .= " AND e.tipo_equipo = :filtro";
    } else {
        $sql_analisis .= " WHERE e.tipo_equipo = :filtro";
    }
}

$sql_analisis .= " GROUP BY i.id_inspeccion, ei.id_equipo";
$sql_analisis .= " ORDER BY i.fecha_inspeccion DESC";

$stmt_analisis = $pdo->prepare($sql_analisis);
$stmt_analisis->execute();
$analisis = $stmt_analisis->fetchAll(PDO::FETCH_ASSOC);

// Obtener los resultados de parámetros de un análisis específico
function obtenerParametros($pdo, $id_inspeccion) {
    $sql_params = "SELECT ri.nombre_parametro, ri.valor_obtenido, ri.aprobado
                  FROM Resultado_Inspeccion ri
                  WHERE ri.id_inspeccion = :id_inspeccion
                  ORDER BY ri.nombre_parametro";
    
    $stmt_params = $pdo->prepare($sql_params);
    $stmt_params->bindParam(':id_inspeccion', $id_inspeccion);
    $stmt_params->execute();
    
    return $stmt_params->fetchAll(PDO::FETCH_ASSOC);
}

// Mostrar los parámetros específicos según el tipo de equipo
function mostrarParametros($pdo, $id_inspeccion, $tipo_equipo) {
    $parametros = obtenerParametros($pdo, $id_inspeccion);
    
    if (empty($parametros)) {
        return "No hay parámetros registrados";
    }
    
    // Filtrar los parámetros según el tipo de equipo
    $parametros_filtrados = [];
    foreach ($parametros as $param) {
        $prefijo = '';
        if (strpos($param['nombre_parametro'], 'Alveograma_') === 0) {
            $prefijo = 'Alveograma_';
        } elseif (strpos($param['nombre_parametro'], 'Farinograma_') === 0) {
            $prefijo = 'Farinograma_';
        }
        
        // Incluir los parámetros correspondientes al tipo de equipo
        if (($tipo_equipo == 'Alveógrafo' && $prefijo == 'Alveograma_') || 
            ($tipo_equipo == 'Farinógrafo' && $prefijo == 'Farinograma_')) {
            $parametros_filtrados[] = $param;
        }
    }
    
    if (empty($parametros_filtrados)) {
        return "No hay parámetros para este tipo de equipo";
    }
    
    // Manejo de error
    $resultado = "";
    foreach ($parametros_filtrados as $param) {
        $nombre = str_replace(['Alveograma_', 'Farinograma_'], '', $param['nombre_parametro']);
        $clase = $param['aprobado'] ? 'param-ok' : 'param-fallo';
        $resultado .= '<span class="' . $clase . '">' . $nombre . ': ' . $param['valor_obtenido'] . '</span>, ';
    }
    
    return rtrim($resultado, ', ');
}

// Determinar si un análisis está aprobado
function analisisAprobado($total_parametros, $parametros_fallidos) {
    if ($total_parametros == 0) {
        return false; // No hay parámetros para evaluar
    }
    return $parametros_fallidos == 0;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        .param-ok {
            color: green;
            font-weight: normal;
        }
        .param-fallo {
            color: red;
            font-weight: bold;
        }
        .estado-aprobado {
            color: green;
            font-weight: bold;
        }
        .estado-fallido {
            color: red;
            font-weight: bold;
        }
    </style>
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

            <form action="" method="GET" class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input id="searchBar" type="text" name="busqueda" value="" class="buscador__input" placeholder="Lote de producción">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Filtrar</h4>
                    <select id="filtroEquipo" name="filtro" class="ordenar__select">
                        <option value="" <?php echo empty($filtro) ? 'selected' : ''; ?>>Todos los equipos</option>
                        <option value="Alveógrafo" <?php echo $filtro == 'Alveógrafo' ? 'selected' : ''; ?>>Alveógrafos</option>
                        <option value="Farinógrafo" <?php echo $filtro == 'Farinógrafo' ? 'selected' : ''; ?>>Farinógrafos</option>
                    </select>
                </div>

                <button type="submit" class="botones__buscar">Buscar</button>
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
                            <th>Estado</th>
                            <th>Parámetros</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($analisis) > 0): ?>
                            <?php foreach ($analisis as $item): ?>
                            <?php $aprobado = analisisAprobado($item['total_parametros'], $item['parametros_fallidos']); ?>
                            <tr class="tabla__fila">
                                <td><?php echo htmlspecialchars($item['lote']); ?></td>
                                <td><?php echo htmlspecialchars($item['secuencia']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['fecha_inspeccion'])); ?></td>
                                <td><?php echo htmlspecialchars($item['cliente_nombre'] ?? 'Sin cliente'); ?></td>
                                <td><?php echo htmlspecialchars($item['equipo_clave']); ?> (<?php echo htmlspecialchars($item['marca']); ?> <?php echo htmlspecialchars($item['modelo']); ?>)</td>
                                <td><?php echo htmlspecialchars($item['tipo_equipo']); ?></td>
                                <td class="<?php echo $aprobado ? 'estado-aprobado' : 'estado-fallido'; ?>">
                                    <?php echo $aprobado ? 'Aprobado' : 'Fallido'; ?>
                                </td>
                                <td><?php echo mostrarParametros($pdo, $item['id_inspeccion'], $item['tipo_equipo']); ?></td>
                                <td class="tabla__botones">
                                    <a href="analisiscalidadform.php?id=<?php echo $item['id_inspeccion']; ?>">
                                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                                    </a>
                                    <a href="javascript:void(0);" onclick="deleteAnalisis(<?php echo $item['id_inspeccion']; ?>, '<?php echo htmlspecialchars($item['lote']); ?>')">
                                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center;">No se encontraron análisis</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Filtro por tipo de equipo
        const filtroEquipo = document.getElementById('filtroEquipo');
        
        filtroEquipo.addEventListener('change', () => {
            const seleccion = filtroEquipo.value;
            const searchParams = new URLSearchParams(window.location.search);
            
            if (seleccion === "") {
                searchParams.delete('filtro');
            } else {
                searchParams.set('filtro', seleccion);
            }
            
            // Mantener el parámetro de búsqueda si existe
            const busqueda = document.getElementById('searchBar').value;
            if (busqueda) {
                searchParams.set('busqueda', busqueda);
            }
            
            window.location.href = "?" + searchParams.toString();
        });

        // Buscar por lote de producción (filtro dinámico)
        const buscador = document.getElementById('searchBar');
        
        function filtrarPorLote(lote) {
            document.getElementById('searchBar').value = lote;
            document.querySelector('form.controles').submit();
        }

        // Manejar alertas y notificaciones
        document.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            const action = urlParams.get('action');
            const message = urlParams.get('message');

            if (success === '1') {
                let mensaje = '';
                switch(action) {
                    case 'insert':
                        mensaje = 'El análisis ha sido registrado correctamente.';
                        break;
                    case 'update':
                        mensaje = 'El análisis ha sido actualizado correctamente.';
                        break;
                    case 'delete':
                        mensaje = 'El análisis ha sido eliminado correctamente.';
                        break;
                    default:
                        mensaje = 'Acción completada con éxito.';
                }

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: mensaje,
                    confirmButtonText: 'Aceptar'
                });
            }

            if (error === '1') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message ? decodeURIComponent(message) : 'Ha ocurrido un error al procesar la solicitud.',
                    confirmButtonText: 'Aceptar'
                });
            }
        });

        // Función para confirmar eliminación usando SweetAlert
        function deleteAnalisis(id, lote) {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Estás a punto de eliminar el análisis del lote <b>${lote}</b>. Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `../config/procesar_analisis.php?id=${id}&accion=eliminar`;
                }
            });
        }
    </script>
</body>
</html>