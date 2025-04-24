<?php
require '../config/conn.php';

$orden_permitido = ['clave', 'marca', 'modelo', 'tipo_equipo', 'fecha_adquisicion', 'estado'];
$orden = isset($_GET['orden']) && in_array($_GET['orden'], $orden_permitido) ? $_GET['orden'] : 'clave';

// Construir la consulta SQL con filtros si es necesario
$sql_equipos = "SELECT e.*, u.nombre as nombre_responsable 
                FROM Equipos_Laboratorio e
                LEFT JOIN Usuarios u ON e.id_responsable = u.id_usuario
                ORDER BY e." . $orden;

$stmt_equipos = $pdo->prepare($sql_equipos);

$stmt_equipos->execute();
$equipos = $stmt_equipos->fetchAll(PDO::FETCH_ASSOC);

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <style>
        .tabla-container {
            overflow-x: auto;
            max-width: 100%;
        }
        
        .tabla {
            width: 100%;
            table-layout: fixed;
        }
        
        .tabla th, .tabla td {
            word-wrap: break-word;
            overflow-wrap: break-word;
            padding: 8px;
        }
        
        .tabla__botones {
            white-space: nowrap;
            width: 80px !important;
        }
        
        .estado-activo {
            color: green;
            font-weight: bold;
        }
        
        .estado-inactivo {
            color: orange;
            font-weight: bold;
        }
        
        .estado-baja {
            color: red;
            font-weight: bold;
        }
    </style>
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
                    <input id="searchBar" type="text" name="busqueda" class="buscador__input" value="" placeholder="Clave, marca o modelo">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Ordenar por</h4>
                    <select id="ordenarPor" name="ordenar" class="ordenar__select">
                        <option value="clave">Clave de equipo</option>
                        <option value="marca" >Marca</option>
                        <option value="modelo">Modelo</option>
                        <option value="tipo_equipo">Tipo de equipo</option>
                        <option value="fecha_adquisicion">Fecha de adquisición</option>
                        <option value="estado">Estado</option>
                    </select>
                </div>

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
                            <th>Tipo de equipo</th>
                            <th>Descripción corta</th>
                            <th>Encargado</th>
                            <th>Fecha adquisición</th>
                            <th>Garantía</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
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
                                <td><?php echo htmlspecialchars($equipo['tipo_equipo']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['desc_corta']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['nombre_responsable']); ?></td>
                                <td><?php echo !empty($equipo['fecha_adquisicion']) ? date('d/m/Y', strtotime($equipo['fecha_adquisicion'])) : ''; ?></td>
                                <td><?php echo htmlspecialchars($equipo['garantia']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['ubicacion']); ?></td>
                                <td class="<?php 
                                    echo $equipo['estado'] == 'Activo' ? 'estado-activo' : 
                                         ($equipo['estado'] == 'Inactivo' ? 'estado-inactivo' : 'estado-baja'); 
                                ?>">
                                    <?php echo htmlspecialchars($equipo['estado']); ?>
                                </td>
                                <td class="tabla__botones">
                                    <a href="laboratoriosform.php?id=<?php echo $equipo['id_equipo']; ?>">
                                        <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                                    </a>
                                    <a href="javascript:void(0);" onclick="deleteEquipo(<?php echo $equipo['id_equipo']; ?>, '<?php echo htmlspecialchars($equipo['clave']); ?>')">
                                        <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" style="text-align: center;">No se encontraron equipos</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>

    <script>
     // Filtros por rol
     const filtro_rol = document.getElementById('ordenarPor');

    filtro_rol.addEventListener('change', () => {
        const seleccion = filtro_rol.value;
        if (seleccion === "") {
            window.location.href = "laboratorios.php";
        } else {
            window.location.href = "?orden=" + seleccion;
        }
    });

     // Buscar por nombre
    const buscador = document.getElementById('searchBar');
    const filasEquipos = document.querySelectorAll('.tabla__fila');

    buscador.addEventListener('input', () => {
        busquedaEquipo = buscador.value.toLowerCase();
       
        filasEquipos.forEach((fila) => {
            const contenidoFila = fila.textContent.toLocaleLowerCase();

            if(contenidoFila.includes(busquedaEquipo)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });

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
                    mensaje = 'El equipo ha sido registrado correctamente.';
                    break;
                case 'update':
                    mensaje = 'El equipo ha sido actualizado correctamente.';
                    break;
                case 'delete':
                    mensaje = 'El equipo ha sido eliminado correctamente.';
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

    function deleteEquipo(id, clave) {
        Swal.fire({
            title: '¿Estás seguro?',
            html: `Estás a punto de eliminar el equipo <b>${clave}</b>. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../config/procesar_equipo.php?id=${id}&accion=eliminar`;
            }
        });
    }

    </script>
</body>
</html>