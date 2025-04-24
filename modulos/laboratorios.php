<?php
require '../config/conn.php';

$busqueda = '';
$orderBy = 'clave';

if (isset($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
}
if (isset($_GET['ordenar'])) {
    $orderBy = $_GET['ordenar'];
}


//Omitir porque esto se puede hacer con frontend
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
            <h2 class="heading">Equipos de Laboratorio</h2>

            <!-- Verificar si hay mensajes de éxito o error -->
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="mensaje mensaje-exito">
                    <?php 
                    $accion = isset($_GET['action']) ? $_GET['action'] : '';
                    
                    if ($accion == 'insert') {
                        echo 'El equipo ha sido registrado correctamente.';
                    } elseif ($accion == 'update') {
                        echo 'El equipo ha sido actualizado correctamente.';
                    } elseif ($accion == 'delete') {
                        echo 'El equipo ha sido eliminado correctamente.';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                <div class="mensaje mensaje-error">
                    <?php echo isset($_GET['message']) ? urldecode($_GET['message']) : 'Ha ocurrido un error al procesar la solicitud.'; ?>
                </div>
            <?php endif; ?>

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
                        <option value="tipo_equipo" <?php echo $orderBy == 'tipo_equipo' ? 'selected' : ''; ?>>Tipo de equipo</option>
                        <option value="fecha_adquisicion" <?php echo $orderBy == 'fecha_adquisicion' ? 'selected' : ''; ?>>Fecha de adquisición</option>
                        <option value="estado" <?php echo $orderBy == 'estado' ? 'selected' : ''; ?>>Estado</option>
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
                                    <a href="../config/procesar_equipo.php?id=<?php echo $equipo['id_equipo']; ?>&accion=eliminar" onclick="return confirm('¿Está seguro de eliminar este equipo?');">
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
    </main>
</body>
</html>