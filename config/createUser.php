<?php 
    require 'conn.php';
    include 'functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $name = test_data($_POST['name']);
        $mail = test_data($_POST['mail']);
        $passwd = test_data($_POST['passwd']);
        $rol = test_data($_POST['rol']);

        // Verificamos que el correo no esté registrado
        $stmtMail = $pdo -> prepare("SELECT correo FROM usuarios WHERE correo =?");
        $stmtMail -> execute([$mail]);
        $resMail = $stmtMail -> fetch();

        if ($resMail){
            header("Location: /fabrica-harinas/modulos/usuariosform.php?error=El correo ya ha sido registrado.");
            exit;
        } 

        $hashed_pwd = password_hash($passwd, PASSWORD_DEFAULT);
        
        $stmt = $pdo -> prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)");
        
        if ($stmt -> execute([$name, $mail, $hashed_pwd, $rol])){
            header("Location: /fabrica-harinas/modulos/usuariosform.php?success=Usuario registrado.");
            exit;
        } else{
            header("Location: /fabrica-harinas/modulos/usuariosform.php?error=Ocurrió un error en el registro.");
            exit;
        }
    } else{
        header('Location: /fabrica-harinas/modulos/usuariosform.php');
    }