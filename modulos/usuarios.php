<?php
require '../config/validar_permisos.php';
$orden = $_GET['orden'] ?? ''; // por defecto vacío
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
<script>
    function deleteUser(id, name) {
        Swal.fire({
            title: `¿Estás seguro?`,
            html: `Estás a punto de eliminar el usuario <b>${name}</b>, no podrás revetir estos cambios.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirmar",
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../config/usuarios/deleteUser.php?id=${id}`;
            }
        });
    }

</script>

<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="../menu.php" class="atras">Ir atrás</a>
            <h2 class="heading">Usuarios</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" id="searchBar" class="buscador__input" placeholder="Busqueda">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Filtrar por rol:</h4>
                    <select name="orden" id="ordenarPor" class="ordenar__select">
                        <option value="" <?= (empty($orden)) ? 'selected' : '' ?>>
                            Mostrar todos</option>
                        <option value="TI" <?= ($orden == 'TI') ? 'selected' : ''; ?>>
                            Departamento de Tecnologías de la Información</option>
                        <option value="Gerencia de Control de Calidad" <?= ($orden == 'Gerencia de Control de Calidad') ? 'selected' : ''; ?>>
                            Gerencia de Control de Calidad</option>
                        <option value="Laboratorio" <?= ($orden == 'Laboratorio') ? 'selected' : ''; ?>>Laboratorio
                        </option>
                        <option value="Gerencia de Aseguramiento de Calidad" <?= ($orden == 'Gerencia de Aseguramiento de Calidad') ? 'selected' : ''; ?>>Gerencia de Aseguramiento de Calidad</option>
                        <option value="Gerente de Planta" <?= ($orden == 'Gerente de Planta') ? 'selected' : ''; ?>>Gerente
                            de Planta</option>
                        <option value="Director de Operaciones" <?= ($orden == 'Director de Operaciones') ? 'selected' : ''; ?>>Director de Operaciones</option>
                    </select>
                </div>

                <!-- <button class="botones__buscar" onclick="buscar()">Buscar</button> -->
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

                        $params = [];
                        $sql = "SELECT * FROM usuarios";

                        // Aplica filtro si hay valor de ordenamiento
                        if (!empty($orden)) {

                            $sql .= " WHERE rol = ?";
                            $params[] = $orden;
                        }

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
                            echo '<a href="#" onclick="deleteUser(' . $res['id_usuario'] . ', \'' . $res['nombre'] . '\'); return false;"><img src="../img/delete.svg" alt="Eliminar" class="tabla__boton"></a>';
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
     // Filtros por rol
     const filtro_rol = document.getElementById('ordenarPor');

    filtro_rol.addEventListener('change', () => {
        const seleccion = filtro_rol.value;
        if (seleccion === "") {
            window.location.href = "usuarios.php";
        } else {
            window.location.href = "?orden=" + seleccion;
        }
    });

     // Buscar por nombre
    const buscador = document.getElementById('searchBar');
    const filasUsuarios = document.querySelectorAll('.tabla__fila');

    buscador.addEventListener('input', () => {
        busquedaUsuario = buscador.value.toLowerCase();
       
        filasUsuarios.forEach((fila) => {
            const contenidoFila = fila.textContent.toLocaleLowerCase();

            if(contenidoFila.includes(busquedaUsuario)){
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
</script>
</html>