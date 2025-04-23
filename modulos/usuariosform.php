<?php
    require '../config/validar_permisos.php'; 

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

        if (empty($usuario)){
            $_SESSION['error'] = "ID no válido";
            header('Location: usuarios.php');
            exit;
        }
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
            <a href="usuarios.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $update?'Editar':'Agregar'; ?> Usuario</h2>
            <form id="formUsuario" action="<?= $update ? '/fabrica-harinas/config/usuarios/updateUser.php' : '/fabrica-harinas/config/usuarios/createUser.php'; ?>" method="post" class="formulario">
                
                <?php if ($update): ?>
                    <div class="formulario__campo">
                        <label for="id" class="formulario__label">Id usuario [lectura]</label>
                        <input type="text" value="<?php echo $usuario['id_usuario']?>" name="id" id="id" class="formulario__input" readonly>
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
                    <?php if ($update): ?>
                        <div class="info__tooltip" id="tooltip">⚠️ Si llenas este campo, la contraseña se actualizará. Si lo dejas vacío, se mantendrá igual.</div>
                    <?php endif; ?>
                </div>
                
                <div class="formulario__campo">
                    <label for="rol" class="formulario__label">Rol</label>
                    <select name="rol" id="rol" class="formulario__select">
                        <option value="" disabled selected>Seleccionar rol ...</option>
                        <?php  
                            $roles = [
                                "TI" => "Departamento de Tecnologías de la Información",
                                "Gerencia de Control de Calidad" => "Gerencia de Control de Calidad",
                                "Laboratorio" => "Laboratorio",
                                "Gerencia de Aseguramiento de Calidad" => "Gerencia de Aseguramiento de Calidad",
                                "Gerente de Planta" => "Gerente de Planta",
                                "Director de Operaciones" => "Director de Operaciones",
                            ];

                            foreach ($roles as $clave => $rol){
                                 echo '<option value="'. $clave .'"';
                                 echo ($usuario['rol'] == $clave)?' selected':'';
                                 echo '>'. $rol .'</option>';                           
                            }
                        ?>
                        
                    </select>
                </div>
                <<button type="button" id="btnSubmit" class="formulario__submit">
                    <?php echo $update ? 'Actualizar usuario' : 'Agregar usuario'; ?>
                </button>
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
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


        document.getElementById('btnSubmit').addEventListener('click', function() {
        const form = document.getElementById('formUsuario');
        const nombre = form.name.value.trim();
        const correo = form.mail.value.trim();
        const rol = form.rol.value;
        const passwd = form.passwd.value.trim();

        let resumen = `<b>Nombre:</b> ${nombre}<br><b>Correo:</b> ${correo}<br><b>Rol:</b> ${rol}`;
       
        if (<?php echo $update ? 'true' : 'false'; ?>){
            if (passwd) {
                resumen += `<br><b>Contraseña:</b> Se actualizará`;
            } else {
                resumen += `<br><b>Contraseña:</b> Sin cambios`;
            }
        } 

        Swal.fire({
            title: 'Confirma los datos antes de continuar',
            html: resumen,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
</html>