<?php 
    require '../conn.php';
    include '../functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        session_start();
        $name = test_data($_POST['name']);
        $mail = test_data($_POST['mail']);
        $passwd = test_data($_POST['passwd']);
        $rol = test_data($_POST['rol']);

        // Verificamos que el correo no esté registrado
        $stmtMail = $pdo -> prepare("SELECT correo FROM usuarios WHERE correo = ?");
        $stmtMail -> execute([$mail]);
        $resMail = $stmtMail -> fetch();

        if ($resMail){
            $_SESSION['error'] = 'El correo ya ha sido registrado.';
            header("Location: /fabrica-harinas/modulos/usuariosform.php");
            exit;
        } 
        
        $hashed_pwd = password_hash($passwd, PASSWORD_DEFAULT);
        
        $stmt = $pdo -> prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)");
        
        if ($stmt -> execute([$name, $mail, $hashed_pwd, $rol])){
            $_SESSION['exito'] = 'Usuario registrado.';
            header("Location: /fabrica-harinas/modulos/usuarios.php");
            exit;
        } else{
            $_SESSION['error'] = 'Ocurrió un error en el registro del usuario.';
            header("Location: /fabrica-harinas/modulos/usuariosform.php");
            exit;
        }
    } else{
        header('Location: /fabrica-harinas/modulos/usuariosform.php');
    }