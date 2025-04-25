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
            <h2 class="heading">Parámetros internacionales</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" id="searchBar" class="buscador__input" placeholder="Busqueda">
                </div>

                

                <!-- <button class="botones__buscar" onclick="buscar()">Buscar</button> -->
                <a href="parametrosform.php" class="botones__crear">Agregar parámetro</a>
            </div>

            <table class="tabla">
                <thead>
                    <tr class="tabla__encabezado">
                        <th>Nombre</th>
                        <th>Farinógrafo</th>
                        <th>Límite inferior</th>
                        <th>Límite superior</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <tr class="tabla__fila">
                    <td>Absorción del agua</td>
                    <td>Farinógrafo</td>
                    <td>55</td>
                    <td>65</td>
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