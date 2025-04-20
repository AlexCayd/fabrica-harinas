<?php
include '../config/conn.php';

// Consulta para recuperar a todos los clientes
$estado = $_GET['estado'] ?? 'activo';
$busqueda = $_GET['busqueda'];

$sql = "SELECT id_cliente, nombre, correo_contacto, estado FROM Clientes WHERE estado = '$estado'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay búsqueda, no aplicamos el filtro LIKE
// if (empty($busqueda)) {
//     $sql = "SELECT id_cliente, nombre, correo_contacto, estado FROM Clientes WHERE estado = '$estado'";
//     $stmt = $pdo->prepare($sql);
//     $stmt->execute([$estado]);
// } else {
//     // Si hay búsqueda, usamos LIKE
//     $sql = "SELECT id_cliente, nombre, correo_contacto, estado FROM Clientes WHERE estado = ? AND nombre LIKE ?";
//     $stmt = $pdo->prepare($sql);
//     $stmt->execute([$estado, "%$busqueda%"]);
// }


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Clientes</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>

<body>
    <main class="contenedor hoja">
        <header class="header">
            <h2 class="header__logo">F.H. Elizondo</h2>
            <nav class="header__nav">
                <a href="../menu.php" class="header__btn">
                    <img class="header__icono" src="../img/home.svg" alt="Home">
                    <p class="header__textoicono">Home</p>
                </a>
                <a href="../index.php" class="header__btn">
                    <img class="header__icono" src="../img/exit.svg" alt="Salir">
                    <p class="header__textoicono">Salir</p>
                </a>
            </nav>
        </header>

        <div class="contenedor__modulo">
            <h2 class="heading">Clientes</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" class="buscador__input" id="busqueda-nombre" placeholder="Nombre del cliente">
                </div>
                <div class="ordenar">
                    <h4 class="ordenar__label">Estado</h4>
                    <select name="estado" id="estado" class="ordenar__select">
                        <option value="activo" <?php if ($estado == 'activo') echo 'selected'; ?>>Activo</option>
                        <option value="inactivo" <?php if ($estado == 'inactivo') echo 'selected'; ?>>Inactivo</option>
                    </select>
                </div>
                <h2 class="botones__buscar">Buscar</h2>
                <a href="clientesform.html" class="botones__crear">Agregar cliente</a>
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
                                    onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?');">
                                    <img src="../img/delete.svg" alt="Eliminar" class="tabla__boton">
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Script para actualizar las tablas
        document.addEventListener("DOMContentLoaded", function() {
            const selectEstado = document.getElementById("estado");
            const inputNombre = document.getElementById("busqueda-nombre");

            inputNombre.addEventListener("change", function() {

                const nombre = this.value;

                const nuevaUrl = new URL(window.location);
                nuevaUrl.searchParams.set("busqueda", nombre);
                history.pushState({}, "", nuevaUrl);

                location.reload();

            });

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
    </script>

</body>

</html>