<?php
require '../config/validar_permisos.php';
include '../config/conn.php';
session_start();
// Consulta para recuperar a todos los clientes
$estado = $_GET['estado'] ?? 'activo';

$sql = "SELECT id_cliente, nombre, correo_contacto, estado FROM Clientes WHERE estado = '$estado'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Clientes</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php' ?>


        <div class="contenedor__modulo">
            <h2 class="heading">Clientes</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" class="buscador__input" id="searchBar" placeholder="Nombre del cliente">
                </div>
                <div class="ordenar">
                    <h4 class="ordenar__label">Estado</h4>
                    <select name="estado" id="ordenarPor" class="ordenar__select">
                        <option value="activo" <?php if ($estado == 'activo') echo 'selected'; ?>>Activo</option>
                        <option value="inactivo" <?php if ($estado == 'inactivo') echo 'selected'; ?>>Inactivo</option>
                    </select>
                </div>
                <h2 class="botones__buscar">Buscar</h2>
                <a href="clientesform.php" class="botones__crear">Agregar cliente</a>
            </div>

            <table class="tabla">
                <thead>
                    <tr class="tabla__encabezado">
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr class="tabla__fila">
                            <td> <?php echo $cliente['nombre']; ?></td>
                            <td><?php echo $cliente['correo_contacto']; ?></td>
                            <td><?php echo $cliente['estado']; ?></td>
                            <td class="tabla__botones">
                                <a href="clientes_editar.php?id=<?php echo $cliente['id_cliente']; ?>">
                                    <img src="../img/edit.svg" alt="Editar" class="tabla__boton">
                                </a>

                                <a href="clientes/baja_cliente.php?id=<?php echo $cliente['id_cliente']; ?>"
                                    class="eliminar-cliente">
                                    <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // // Script para seleccionar un estado
        document.addEventListener("DOMContentLoaded", function() {
            const selectEstado = document.getElementById("ordenarPor");

            selectEstado.addEventListener("change", function() {
                // alert("Opción seleccionada: " + this.value);
                const estado = this.value;
                // Cambiar la URL 
                const nuevaUrl = new URL(window.location);
                nuevaUrl.searchParams.set("estado", estado);
                history.pushState({}, "", nuevaUrl);

                location.reload();
            });
        });

        // Script para mandar mensaje de confirmacion cuando se quiere eliminar a un cliente
        document.addEventListener('DOMContentLoaded', function() {
            const enlacesEliminar = document.querySelectorAll('.eliminar-cliente');

            enlacesEliminar.forEach(function(enlace) {
                enlace.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¡Esta acción no se puede deshacer!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                });
            });
        });

        // Buscar por nombre
        const buscador = document.getElementById('searchBar');
        const filasUsuarios = document.querySelectorAll('.tabla__fila');

        buscador.addEventListener('input', () => {
            busquedaUsuario = buscador.value.toLowerCase();

            filasUsuarios.forEach((fila) => {
                const contenidoFila = fila.textContent.toLocaleLowerCase();

                if (contenidoFila.includes(busquedaUsuario)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    </script>

        <!-- Alerta si es que se actualiza un cliente con éxito -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <script>
            <?php if ($_SESSION['mensaje'] == 'exito'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Cliente editado correctamente',
                    showConfirmButton: false,
                    timer: 1500
                });
            <?php elseif ($_SESSION['mensaje'] == 'error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error al editar el cliente',
                    text: 'Ocurrió un problema en el servidor',
                    showConfirmButton: true
                });
            <?php endif; ?>
        </script>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

</body>

</html>