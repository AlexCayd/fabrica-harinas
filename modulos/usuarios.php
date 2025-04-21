<?php 
    // Validar permisos de TI
    if (session_status() == PHP_SESSION_NONE){ //Solo inicia sesión si no está activa
        session_start();
    }
    if (isset($_SESSION['rol']) && $_SESSION['rol'] != 'TI'){
        $_SESSION['error'] = 'No tienes permisos para esta sección. Comunícate con el Departamento de Tecnologías de la Información';
        header('location: ../menu.php');
        exit;
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Usuarios</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        function deleteUser(id, name){
            Swal.fire({
                title: `¿Estás seguro?`,
                text: `Estás a punto de eliminar el usuario ${name}, no podrás revetir estos cambios.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Confirmar",
                }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href =  `../config/deleteUser.php?id=${id}`;
                } 
            });
        }
    </script>
    <main class="contenedor hoja">
       <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <h2 class="heading">Usuarios</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" class="buscador__input" placeholder="Nombre del usuario">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Filtrar por rol:</h4>
                    <select name="orden" id="ordenarPor" class="ordenar__select">
                        <option  selected value="">Seleccionar rol ...</option>
                        <option value="TI">Departamento de Tecnologías de la Información</option>
                        <option value="Gerencia de Control de Calidad">Gerencia de Control de Calidad</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Gerencia de Aseguramiento de Calidad">Gerencia de Aseguramiento de Calidad</option>
                        <option value="Gerente de Planta">Gerente de Planta</option>
                        <option value="Director de Operaciones">Director de Operaciones</option>
                    </select>
                </div>

                <h2 class="botones__buscar">Buscar</h2>
                <a href="usuariosform.php" class="botones__crear">Agregar usuario</a>
            </div>

            <table class="tabla">
            <thead>
                <tr class="tabla__encabezado">
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr class="tabla__fila">

                    <?php 
                        require '../config/conn.php';

                        $orden = $_GET['orden'] ?? ''; // por defecto vacío
                        $params = [];
                        $sql = "SELECT * FROM usuarios";
                        
                        // Aplica filtro si hay valor de ordenamiento
                        if (!empty($orden)) {
                            $sql .= " WHERE rol = ?";
                            $params[] = $orden;
                        }
                        
                        $sql .= " ORDER BY nombre"; // Puedes cambiar el criterio
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        while ($res = $stmt->fetch()) {
                            echo '<tr class="tabla__fila">';
                            echo '<td>' . $res['id_usuario'] . '</td>';
                            echo '<td>' . $res['nombre'] . '</td>';
                            echo '<td>' . $res['correo'] . '</td>';
                            echo '<td>' . $res['rol'] . '</td>';
                            echo '<td class="tabla__botones">';
                            echo '<a href="usuariosform.php?id=' . $res['id_usuario'] . '"><img src="../img/edit.svg" alt="Editar" class="tabla__boton"></a>';
                            echo '<a href="#" onclick="deleteUser('. $res['id_usuario'] .', \'' . $res['nombre'] . '\'); return false;"><img src="../img/delete.svg" alt="Eliminar" class="tabla__boton"></a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                    ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
<script>
    document.getElementById('ordenarPor').addEventListener('change', function() {
        const seleccion = this.value;
        window.location.href = "?orden=" + encodeURIComponent(seleccion);
    });
</script>
</html>