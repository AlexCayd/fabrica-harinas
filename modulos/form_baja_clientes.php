<?php

include '../config/conn.php';
include '../config/functions.php';
$id = $_GET['id'];
$sql = "SELECT * FROM Clientes WHERE id_cliente = $id";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$result = $stmt->fetch();

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
            <h2 class="header__logo">
                F.H. Elizondo
            </h2>

            <nav class="header__nav">
                <a href="../menu.html" class="header__btn">
                    <img class="header__icono" src="../img/home.svg" alt="Home">
                    <p class="header__textoicono">Home</p>
                </a>

                <a href="../index.html" class="header__btn">
                    <img class="header__icono" src="../img/exit.svg" alt="Home">
                    <p class="header__textoicono">Salir</p>
                </a>
            </nav>
        </header>

        <div class="contenedor__modulo">
            <a href="clientes.php" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar Cliente</h2>
            <form action="clientes/baja_cliente.php?id=<?php echo $result['id_cliente']?>" class="formulario" method="post">
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre</label>
                    <input type="text" name="nombre" class="formulario__input" placeholder="Nombre" value="<?php echo $result['nombre']; ?>" disabled>

                    <div class="formulario__campo">
                        <label for="rol" class="formulario__label">Estado</label>
                        <select name="categoria" id="categoria" class="formulario__select" value="<?php echo $result['estado']; ?>">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <input type="submit" class="formulario__submit" value="Baja de cliente">
            </form>
        </div>
    </main>
</body>

</html>