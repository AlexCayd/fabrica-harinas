<?php
require '../config/validar_permisos.php';
require '../config/conn.php';

$busqueda = '';
$filtro = '';

if (isset($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
}
if (isset($_GET['filtro']) && !empty($_GET['filtro'])) {
    $filtro = $_GET['filtro'];
}

// Construir la consulta SQL con filtros - incluyendo tipo_equipo del cliente
$sql_analisis = "SELECT 
                i.id_inspeccion, i.lote, i.secuencia, i.fecha_inspeccion, i.clave,
                e.id_equipo, e.clave as equipo_clave, e.tipo_equipo as equipo_tipo, e.marca, e.modelo,
                c.id_cliente, c.nombre as cliente_nombre, c.tipo_equipo as cliente_tipo,
                COUNT(DISTINCT ri.id_resultado) as total_parametros,
                SUM(CASE WHEN ri.aprobado IS NULL OR ri.aprobado = 0 THEN 1 ELSE 0 END) as parametros_fallidos
                FROM Inspeccion i
                LEFT JOIN Clientes c ON i.id_cliente = c.id_cliente
                LEFT JOIN Resultado_Inspeccion ri ON i.id_inspeccion = ri.id_inspeccion
                LEFT JOIN Equipos_Laboratorio e ON i.id_equipo = e.id_equipo";

$where_added = false;
$params = [];

// Añadir filtro por búsqueda si existe
if (!empty($busqueda)) {
    $sql_analisis .= " WHERE i.lote LIKE :busqueda";
    $where_added = true;
    $params[':busqueda'] = "%$busqueda%";
}


// Añadir filtro por tipo de equipo si está seleccionado
if (!empty($filtro)) {
    if ($where_added) {
        $sql_analisis .= " AND (e.tipo_equipo = :filtro_equipo OR c.tipo_equipo = :filtro_cliente)";
    } else {
        $sql_analisis .= " WHERE (e.tipo_equipo = :filtro_equipo OR c.tipo_equipo = :filtro_cliente)";
        $where_added = true;
    }
    $params[':filtro_equipo'] = $filtro;
    $params[':filtro_cliente'] = $filtro;
}

$sql_analisis .= " GROUP BY i.id_inspeccion";
$sql_analisis .= " ORDER BY i.fecha_inspeccion DESC";

$stmt_analisis = $pdo->prepare($sql_analisis);

// Vincular parámetros si existen
foreach ($params as $key => $value) {
    $stmt_analisis->bindValue($key, $value);
}

$stmt_analisis->execute();
$analisis = $stmt_analisis->fetchAll(PDO::FETCH_ASSOC);

// Función para obtener parámetros de un análisis
function obtenerParametros($pdo, $id_inspeccion) {
    $sql_params = "SELECT ri.id_resultado, ri.nombre_parametro, ri.valor_obtenido, ri.aprobado
                    FROM Resultado_Inspeccion ri
                    WHERE ri.id_inspeccion = :id_inspeccion
                    ORDER BY ri.nombre_parametro";
    
    $stmt_params = $pdo->prepare($sql_params);
    $stmt_params->bindParam(':id_inspeccion', $id_inspeccion, PDO::PARAM_INT);
    $stmt_params->execute();
    
    return $stmt_params->fetchAll(PDO::FETCH_ASSOC);
}

// Función para mostrar parámetros
function mostrarParametros($pdo, $id_inspeccion, $tipo_equipo) {
    $parametros = obtenerParametros($pdo, $id_inspeccion);
    
    if (empty($parametros)) {
        return "No hay parámetros registrados";
    }
    
    $resultado = "";
    foreach ($parametros as $param) {
        // Eliminar prefijos para mostrar nombres limpios
        $nombre = str_replace(['Alveograma_', 'Farinograma_'], '', $param['nombre_parametro']);
        $clase = ($param['aprobado'] === null || $param['aprobado'] == 0) ? 'param-fallo' : 'param-ok';
        $resultado .= '<span class="' . $clase . '">' . $nombre . ': ' . $param['valor_obtenido'] . '</span>, ';
    }
    
    return rtrim($resultado, ', ');
}

// Función para determinar si un análisis está aprobado
function analisisAprobado($total_parametros, $parametros_fallidos, $tipo_equipo) {
    // Si no hay parámetros, considerar como aprobado (depende de tu lógica de negocio)
    if ($total_parametros == 0) {
        return [true, 0, 0];
    }
    
    // Definir el número máximo de parámetros según el tipo de equipo
    $max_parametros = ($tipo_equipo == 'Alveógrafo') ? 12 : 11;
    
    // Calcular parámetros correctos
    $parametros_correctos = $total_parametros - $parametros_fallidos;
    
    // Determinar si está aprobado (sin errores)
    $aprobado = ($parametros_fallidos == 0);
    
    return [$aprobado, $parametros_correctos, $max_parametros];
}

// Función para mostrar información de equipo/cliente
function mostrarOrigenInspeccion($item) {
    $info = '';
    
    // Si tiene cliente, mostrar la información del cliente
    if (!empty($item['cliente_nombre'])) {
        $info .= '<span class="origen-cliente">Cliente: ' . htmlspecialchars($item['cliente_nombre']) . '</span>';
    }
    
    // Si tiene equipo, mostrar la información del equipo
    if (!empty($item['equipo_clave'])) {
        if (!empty($info)) {
            $info .= '<br>';
        }
        $info .= '<span class="origen-equipo">Equipo: ' . htmlspecialchars($item['equipo_clave']) . ' (' . 
                htmlspecialchars($item['marca']) . ' ' . htmlspecialchars($item['modelo']) . ')</span>';
    }
    
    return $info ?: 'No especificado';
}

// Función para determinar el tipo de equipo de la inspección
function determinarTipoEquipo($item) {
    // Prioridad 1: Usar tipo de equipo de la tabla Equipos_Laboratorio si está disponible
    if (!empty($item['equipo_tipo'])) {
        return htmlspecialchars($item['equipo_tipo']);
    }
    
    // Prioridad 2: Usar tipo de equipo de la tabla Clientes si está disponible
    if (!empty($item['cliente_tipo'])) {
        return htmlspecialchars($item['cliente_tipo']);
    }
    
    // Si no hay información disponible
    return 'No especificado';
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
        .origen-cliente {
            color: #4c3325;
            font-weight: bold;
        }
        .origen-equipo {
            color: #666;
        }
    </style>
</head>
<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="../menu.php" class="atras">Ir atrás</a>
            <h2 class="heading">Análisis de Calidad</h2>

            <form action="" method="GET" class="controles">
            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input id="searchBar" type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" class="buscador__input" placeholder="Lote de producción">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Filtrar</h4>
                    <select id="filtroEquipo" name="filtro" class="ordenar__select" onchange="this.form.submit()">
                        <option value="" <?= empty($filtro) ? 'selected' : '' ?>>Todos los equipos</option>
                        <option value="Alveógrafo" <?= $filtro == 'Alveógrafo' ? 'selected' : '' ?>>Alveógrafos</option>
                        <option value="Farinógrafo" <?= $filtro == 'Farinógrafo' ? 'selected' : '' ?>>Farinógrafos</option>
                    </select>
                </div>

                <a href="analisiscalidadform.php" class="botones__crear">Agregar análisis</a>
            </div>
            </form>

            <div class="tabla-container">
                <table class="tabla">
                    <thead>
                        <tr class="tabla__encabezado">
                            <th>Lote de producción</th>
                            <th>Secuencia</th>
                            <th>Fecha</th>
                            <th>Origen</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Parámetros</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($analisis) > 0): ?>
                            <?php foreach ($analisis as $item): ?>
                                <?php 
                                    $tipo_equipo = determinarTipoEquipo($item);
                                    list($aprobado, $parametros_correctos, $max_parametros) = analisisAprobado(
                                        $item['total_parametros'], 
                                        $item['parametros_fallidos'],
                                        $tipo_equipo
                                    );
                                ?>
                            <tr class="tabla__fila">
                                <td><?= htmlspecialchars($item['lote']) ?></td>
                                <td><?= htmlspecialchars($item['secuencia']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($item['fecha_inspeccion'])) ?></td>
                                <td><?= mostrarOrigenInspeccion($item) ?></td>
                                <td><?= $tipo_equipo ?></td>
                                <td class="<?= $aprobado ? 'estado-aprobado' : 'estado-fallido' ?>">
                                    <?= $aprobado ? 'Aprobado' : 'Fallido' ?>
                                    <?php if($item['total_parametros'] > 0): ?>
                                        <small>(<?= $parametros_correctos ?>/<?= $max_parametros ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= mostrarParametros($pdo, $item['id_inspeccion'], $tipo_equipo) ?></td>
                                <td class="tabla__botones">
                                    <a href="analisiscalidadform.php?id=<?= $item['id_inspeccion'] ?>">
                                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                                    </a>
                                    <a href="javascript:void(0);" onclick="deleteAnalisis(<?= $item['id_inspeccion'] ?>, '<?= htmlspecialchars($item['lote']) ?>')">
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
        <?php include '../includes/footer.php'; ?>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Buscador en tiempo real
        const searchBar = document.getElementById('searchBar');
        const filas = document.querySelectorAll('.tabla tbody tr');
        
        function filtrarFilas() {
            const termino = searchBar.value.toLowerCase().trim();
            
            filas.forEach(fila => {
                const celdaLote = fila.querySelector('td:nth-child(1)');
                if (celdaLote) {
                    const textoLote = celdaLote.textContent.toLowerCase();
                    fila.style.display = textoLote.includes(termino) ? '' : 'none';
                }
            });
        }
        
        // Aplicar filtro al escribir (con pequeño retardo para mejor rendimiento)
        let timeout = null;
        searchBar.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(filtrarFilas, 300);
        });
        
        // Aplicar filtro inicial si hay un valor
        if (searchBar.value) {
            filtrarFilas();
        }
        
        // Manejar alertas y notificaciones
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
            }).then(() => {
                // Eliminar parámetros de éxito de la URL
                const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]success=1(&|$)/, '');
                window.history.replaceState({}, document.title, cleanUrl);
            });
        }

        if (error === '1') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message ? decodeURIComponent(message) : 'Ha ocurrido un error al procesar la solicitud.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                // Eliminar parámetros de error de la URL
                const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]error=1(&|$)/, '');
                window.history.replaceState({}, document.title, cleanUrl);
            });
        }
    });

    // Función para confirmar eliminación
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