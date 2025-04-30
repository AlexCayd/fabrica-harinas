<?php

require '../config/validar_permisos.php';
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

        .tabla__fila {
            transition: background-color 0.3s ease;
        }

        .tabla__fila:hover {
            background-color: #f5f5f5;
        }

        /* Clase para el ícono de ojo (opcional) */
        .ver-detalles {
            cursor: pointer;
            color: black;
            margin-right: 10px;
        }

        /* Animaciones para SweetAlert */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translate3d(0, -100%, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes fadeOutUp {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
                transform: translate3d(0, -100%, 0);
            }
        }

        .animate__animated {
            animation-duration: 0.5s;
            animation-fill-mode: both;
        }

        .animate__fadeInDown {
            animation-name: fadeInDown;
        }

        .animate__fadeOutUp {
            animation-name: fadeOutUp;
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
                                <tr class="tabla__fila"
                                data-id="<?php echo $equipo['id_equipo']; ?>"
                                data-clave="<?php echo htmlspecialchars($equipo['clave']); ?>"
                                data-marca="<?php echo htmlspecialchars($equipo['marca']); ?>"
                                data-modelo="<?php echo htmlspecialchars($equipo['modelo']); ?>"
                                data-serie="<?php echo htmlspecialchars($equipo['serie']); ?>"
                                data-tipo_equipo="<?php echo htmlspecialchars($equipo['tipo_equipo']); ?>"
                                data-desc_larga="<?php echo htmlspecialchars($equipo['desc_larga']); ?>"  
                                data-desc_corta="<?php echo htmlspecialchars($equipo['desc_corta']); ?>"
                                data-proveedor="<?php echo htmlspecialchars($equipo['proveedor']); ?>"
                                data-encargado="<?php echo htmlspecialchars($equipo['nombre_responsable']); ?>"
                                data-fecha_adquisicion="<?php echo !empty($equipo['fecha_adquisicion']) ? date('d/m/Y', strtotime($equipo['fecha_adquisicion'])) : ''; ?>"
                                data-fecha_vencimiento="<?php echo !empty($equipo['vencimiento_garantia']) ? date('d/m/Y', strtotime($equipo['vencimiento_garantia'])) : ''; ?>"
                                data-garantia="<?php echo htmlspecialchars($equipo['garantia']); ?>"
                                data-ubicacion="<?php echo htmlspecialchars($equipo['ubicacion']); ?>"
                                data-estado="<?php echo htmlspecialchars($equipo['estado']); ?>"                                                           
                                style="cursor: pointer;"
                            >

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

    // Añadir funcionalidad para ver detalles al hacer clic en la fila
    document.addEventListener('DOMContentLoaded', function() {
        const filas = document.querySelectorAll('.tabla__fila');
        
        filas.forEach(fila => {
            // Añadir listener a toda la fila excepto a los botones
            fila.addEventListener('click', function(e) {
                // Verificar que no se hizo clic en un botón o en una imagen
                if (!e.target.closest('.tabla__botones') && !e.target.closest('.tabla__boton')) {
                    const clave = this.dataset.clave;
                    const marca = this.dataset.marca;
                    const modelo = this.dataset.modelo;
                    const serie = this.dataset.serie;
                    const tipoEquipo = this.dataset.tipo_equipo;
                    const descCorta = this.dataset.desc_corta;
                    const descLarga = this.dataset.desc_larga;
                    const proveedor = this.dataset.proveedor;
                    const encargado = this.dataset.encargado;
                    const fechaAdquisicion = this.dataset.fecha_adquisicion;
                    const fechaVencimiento = this.dataset.fecha_vencimiento;
                    const garantia = this.dataset.garantia;
                    const ubicacion = this.dataset.ubicacion;
                    const estado = this.dataset.estado;                    
                    
                    // Crear contenido HTML para el SweetAlert
                    const detallesHTML = `
                        <div class="detalles-equipo">
                            <h3 style="text-align: center; margin-bottom: 20px; color: #4c3325;">Detalles del Equipo: ${clave}</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: left;">
                                <div style="font-weight: bold;">Clave:</div><div>${clave}</div>
                                <div style="font-weight: bold;">Tipo de Equipo:</div><div>${tipoEquipo}</div>
                                <div style="font-weight: bold;">Marca:</div><div>${marca}</div>
                                <div style="font-weight: bold;">Modelo:</div><div>${modelo}</div>
                                <div style="font-weight: bold;">Número de Serie:</div><div>${serie}</div>
                                <div style="font-weight: bold;">Descripción Larga:</div><div>${descLarga}</div>
                                <div style="font-weight: bold;">Descripción Corta:</div><div>${descCorta}</div>
                                <div style="font-weight: bold;">Proveedor:</div><div>${proveedor}</div>
                                <div style="font-weight: bold;">Encargado:</div><div>${encargado}</div>
                                <div style="font-weight: bold;">Fecha de Adquisición:</div><div>${fechaAdquisicion}</div>
                                <div style="font-weight: bold;">Fecha de Vencimiento:</div><div>${fechaVencimiento}</div>
                                <div style="font-weight: bold;">Garantía:</div><div>${garantia}</div>
                                <div style="font-weight: bold;">Ubicación:</div><div>${ubicacion}</div>
                                <div style="font-weight: bold;">Estado:</div><div>${estado}</div>
                            </div>
                        </div>
                    `;
                    
                    // Mostrar SweetAlert con los detalles
                    Swal.fire({
                        html: detallesHTML,
                        width: '600px',
                        confirmButtonText: 'Cerrar',
                        confirmButtonColor: '#4c3325',
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>