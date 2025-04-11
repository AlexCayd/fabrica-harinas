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
            <a href="clientes.php" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar Cliente</h2>
            <form action="clientes/editar_cliente.php?id=<?php echo $result['id_cliente']; ?>" class="formulario" method="post">
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre</label>
                    <input type="text" name="nombre" class="formulario__input" placeholder="Nombre" value="<?php echo $result['nombre']; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="certificado" class="formulario__label"> Requiere certificado </label>
                    <select name="certificado" id="categoria" class="formulario__select">
                        <option value="1" <?= $result['req_certificado'] == 1 ? 'selected' : '' ?>>Si</option>
                        <option value="0" <?= $result['req_certificado'] == 0 ? 'selected' : '' ?>>No</option>
                    </select>

                </div>

                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="email" class="formulario__input" placeholder="Correo electrónico" value="<?php echo $result['correo_contacto']; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="rfc" class="formulario__label"> RFC </label>
                    <input type="text" name="rfc" class="formulario__input" placeholder="RFC" value="<?php echo $result['rfc']; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="puesto" class="formulario__label"> Puesto </label>
                    <input type="text" name="puesto" class="formulario__input" placeholder="Puesto" value="<?php echo $result['puesto_contacto']; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="numero-telefonico" class="formulario__label"> Numero telefónico </label>
                    <input type="text" name="numero-telefonico" class="formulario__input" placeholder="Numero telefonico" value="<?php echo $result['telefono_contacto']; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="direccion-fiscal" class="formulario__label"> Direccion fiscal </label>
                    <input type="text" name="direccion-fiscal" class="formulario__input" placeholder="Direccion Fiscal" value="<?php echo $result['direccion_fiscal']; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="parametros" class="formulario__label"> Parametros </label>
                    <select name="parametros" id="categoria" class="formulario__select">
                        <option value="Internacionales" <?= $result['parametros'] == 'Internacionales' ? 'selected' : '' ?>>Internacionales</option>
                        <option value="Personalizados" <?= $result['parametros'] == 'Personalizados' ? 'selected' : '' ?>>Personalizados</option>
                    </select>
                </div>

                <div class="formulario__campo">
                        <label for="rol" class="formulario__label">Estado</label>
                        <select name="estado" id="categoria" class="formulario__select" value="<?php echo $result['estado']; ?>">
                            <option value="activo" <?= $result['estado'] == 'Activo' ? 'selected': ''; ?>> Activo</option>
                            <option value="inactivo" <?= $result['estado'] == 'Inactivo' ? 'selected': ''; ?>>Inactivo</option>
                        </select>
                    </div>

                <input type="submit" class="formulario__submit" value="Editar cliente">
            </form>
        </div>
    </main>
</body>

</html>