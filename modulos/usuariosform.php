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

if ($update) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$_GET['id']]);
    $usuario = $stmt->fetch();

    if (empty($usuario)) {
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
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-top: 5px;
        z-index: 10;
    }
</style>

<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="usuarios.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $update ? 'Editar' : 'Agregar'; ?> Usuario</h2>
            <form id="formUsuario"
                action="<?= $update ? '/fabrica-harinas/config/usuarios/updateUser.php' : '/fabrica-harinas/config/usuarios/createUser.php'; ?>"
                method="post" class="formulario">

                <?php if ($update): ?>
                    <div class="formulario__campo">
                        <label for="id" class="formulario__label">Id usuario [lectura]</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['id_usuario']) ?>" name="id" id="id"
                            class="formulario__input" readonly>
                    </div>
                <?php endif; ?>

                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($usuario['nombre']) ?>" class="formulario__input">
                </div>

                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="mail" value="<?php echo htmlspecialchars($usuario['correo']) ?>" class="formulario__input">
                </div>

                <div class="formulario__campo tooltip-container ">
                    <label for="password" class="formulario__label">Contraseña
                    <?php if ($update): ?>
                    <span onmouseenter="tooltip.style.display = 'block'" 
                        onmouseleave="tooltip.style.display = 'none'" 
                        style="cursor: help;">❓</span>
                        <?php endif; ?>
                    </label>
                    <input type="password" name="passwd" id="passwd" class="formulario__input"
                        placeholder="<?php echo $update ? 'Nueva contraseña (opcional)' : 'Contraseña'; ?>">
                    <?php if ($update): ?>
                        <div class="info__tooltip" id="tooltip">⚠️ Si llenas este campo, la contraseña se actualizará. Si lo
                            dejas vacío, se mantendrá igual.</div>
                    <?php endif; ?>

                    <button class="toggle-btn" id="viewPasswd" onclick="togglePassword()">
                        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
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

                        foreach ($roles as $clave => $rol) {
                            echo '<option value="' . $clave . '"';
                            echo ($usuario['rol'] == $clave) ? ' selected' : '';
                            echo '>' . $rol . '</option>';
                        }
                        ?>

                    </select>
                </div>
                <button type="button" id="btnSubmit" class="formulario__submit">
                    <?php echo $update ? 'Actualizar usuario' : 'Agregar usuario'; ?>
                    </button>
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
<script>
    const passwordInput = document.getElementById('passwd');

    const viewPasswd = document.getElementById('viewPasswd');

    viewPasswd.addEventListener("click", function(event) {
        event.preventDefault()
    });


    function togglePassword() {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            document.querySelector('.toggle-btn').innerHTML = '<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/><path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>';
        } else {
            passwordInput.type = "password";
            document.querySelector('.toggle-btn').innerHTML = '<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>';
        }
    }

    document.getElementById('btnSubmit').addEventListener('click', function() {
        const form = document.getElementById('formUsuario');
        const nombre = form.name.value.trim();
        const correo = form.mail.value.trim();
        const rol = form.rol.value;
        const passwd = form.passwd.value.trim();

        if (nombre === "") {
            Swal.fire({
                icon: 'error',
                title: 'Campo vacío',
                text: 'Ingresa un nombre.',
            });
            return;
        }

        const correoRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
        if (!correoRegex.test(correo)) {
            Swal.fire({
                icon: 'error',
                title: 'Correo inválido',
                text: 'Ingresa un correo electrónico válido.',
            });
            return;
        }

        if (<?php echo $update ? 'false' : 'true'; ?>) {
            const contrasenaRegex = /^(?=.*\d).{6,}$/;
            if (!contrasenaRegex.test(passwd)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Contraseña no válida',
                    text: 'La contraseña debe tener al menos 6 caracteres y un número.',
                });
                return;
            }
        }

        if (rol === "") {
            Swal.fire({
                icon: 'error',
                title: 'Selecciona un rol',
                text: 'Selecciona un rol de la lista.',
            });
            return;
        }

        let resumen = `<b>Nombre:</b> ${nombre}<br><b>Correo:</b> ${correo}<br><b>Rol:</b> ${rol}`;

        if (<?php echo $update ? 'true' : 'false'; ?>) {
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