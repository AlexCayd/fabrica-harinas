<?php 
    require 'conn.php';
    include 'functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){

        $id = $_POST['id'];
        $name = test_data($_POST['name']);
        $mail = test_data($_POST['mail']);
        $passwd = test_data($_POST['passwd']);
        $rol = test_data($_POST['rol']);
        
        // Validar correo duplicado
        $stmtMail = $pdo -> prepare("SELECT correo FROM usuarios WHERE correo = ? AND id_usuario != ? ");
        $stmtMail -> execute([$mail, $id]);
        $resMail = $stmtMail -> fetch();
        
        if ($resMail){
            header("Location: /fabrica-harinas/modulos/usuariosform.php?id=". $id ."&error=El correo ya ha sido registrado.");
            exit;
        } 

        if(empty($passwd)){
            $stmt = $pdo -> prepare("UPDATE usuarios set nombre = ?, correo = ?, rol = ? WHERE id_usuario = ?");
            $stmt -> execute([$name, $mail, $rol, $id]);
        } else {
            $hashed_pwd = password_hash($passwd, PASSWORD_DEFAULT);
            $stmt = $pdo -> prepare("UPDATE usuarios set nombre = ?, correo = ?, contrasena = ?, rol = ? WHERE id_usuario = ?");
            $stmt -> execute([$name, $mail, $hashed_pwd, $rol, $id]);
        }
        header("Location: /fabrica-harinas/modulos/usuariosform.php?id=$id&success=Usuario actualizado.");
        exit;
    } else{
        header('Location: /fabrica-harinas/modulos/usuariosform.php');
        exit;
    }