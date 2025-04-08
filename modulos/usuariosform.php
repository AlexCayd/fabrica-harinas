<?php
    session_start();

    require '../config/conn.php';

    $update = isset($_GET['id']);

    $usuario = [
        'id_usuario' => '',
        'nombre' => '',
        'correo' => '',
        'rol' => ''
    ];

    if($update){
        $stmt = $pdo -> prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$_GET['id']]);
        $usuario = $stmt->fetch();
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
</head>
<style>
    .tooltip-container {
        position: relative;
    } 
    .info__tooltip {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 250px;
        background-color: #fff8dc;
        color: #333;
        border: 1px solid #ffa500;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-top: 5px;
        z-index: 10;
    }
</style>
<body>
    <main  class="contenedor hoja">
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
            <a href="usuarios.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $update?'Editar':'Agregar'; ?> Usuario</h2>
            <form action="<?= $update ? '/fabrica-harinas/config/updateUser.php' : '/fabrica-harinas/config/createUser.php'; ?>" method="post" class="formulario">
                
                <?php if ($update): ?>
                    <div class="formulario__campo">
                        <label for="id" class="formulario__label">Id usuario</label>
                        <input type="text" value="<?php echo $usuario['id_usuario']?>" name="id" class="formulario__input" disabled>
                    </div>
                <?php endif; ?>
                
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre</label>
                    <input type="text"  name="name" value="<?php echo $usuario['nombre']?>" class="formulario__input">
                </div>
                
                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="mail" value="<?php echo $usuario['correo']?>" class="formulario__input">
                </div>
                
                <div class="formulario__campo tooltip-container">
                    <label for="password" class="formulario__label">Contraseña</label>
                    <input type="password" name="passwd" id="passwd" class="formulario__input" placeholder="<?php echo $update?'Nueva contraseña (opcional)':'Contraseña'; ?>">
                    <div class="info__tooltip" id="tooltip">⚠️ Si llenas este campo, la contraseña se actualizará. Si lo dejas vacío, se mantendrá igual.</div>
                </div>
                
                <div class="formulario__campo">
                    <label for="rol" class="formulario__label">Rol</label>
                    <select name="rol" id="rol" class="formulario__select">
                        <option value="TI">Departamento de Tecnologías de la Información</option>
                        <option value="Gerencia de Control de Calidad">Gerencia de Control de Calidad</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Gerencia de Aseguramiento de Calidad">Gerencia de Aseguramiento de Calidad</option>
                        <option value="Gerente de Planta">Gerente de Planta</option>
                        <option value="Director de Operaciones">Director de Operaciones</option>
                    </select>
                    <!-- 'TI','Gerencia de Control de Calidad','Laboratorio','Gerencia de Aseguramiento de Calidad','Gerente de Planta','Director de Operaciones' -->
                </div>
                 <!-- Muestra errores en el registro -->
                 <?php if (isset($_GET['error'])): ?>
                    <p style="color:red;">⚠️ <?= htmlspecialchars($_GET['error']) ?></p>
                <?php elseif (isset($_GET['success'])): ?>
                    <p style="color:green;">✅ <?= htmlspecialchars($_GET['success']) ?></p>
                <?php endif; ?>             
                
                <input type="submit" class="formulario__submit" value="Agregar usuario">
            </form>
        </div>
    </main>
</body>
<script>
        const passwordInput = document.getElementById('passwd');
        const tooltip = document.getElementById('tooltip');

        passwordInput.addEventListener('input', () => {
            if (passwordInput.value.trim().length > 0) {
                tooltip.style.display = 'block';
            } else {
                tooltip.style.display = 'none';
            }
        });

        // Opcional: mostrar también al hacer hover
        passwordInput.addEventListener('mouseenter', () => {
            if (passwordInput.value.trim().length === 0) {
                tooltip.style.display = 'block';
            }
        });

        passwordInput.addEventListener('mouseleave', () => {
            if (passwordInput.value.trim().length === 0) {
                tooltip.style.display = 'none';
            }
        });
</script>

</script>
</html>