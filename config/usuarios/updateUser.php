<?php 
    include_once "../../includes/config.php";
    require '../conn.php';
    include '../functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        session_start();
        $id = $_POST['id'];
        $name = test_data($_POST['name']);
        $mail = test_data($_POST['mail']);
        $passwd = test_data($_POST['passwd']);
        $rol = test_data($_POST['rol']);
        
        // Validar correo duplicado
        $stmtMail = $pdo -> prepare("SELECT correo FROM Usuarios WHERE correo = ? AND id_usuario != ? ");
        $stmtMail -> execute([$mail, $id]);
        $resMail = $stmtMail -> fetch();
        
        if ($resMail){
            $_SESSION['error'] = 'El correo ya ha sido registrado.';
            header("Location: " . BASE_URL . "modulos/usuariosform.php?id=". $id );
            exit;
        } 
        
        if(empty($passwd)){
            $stmt = $pdo -> prepare("UPDATE Usuarios set nombre = ?, correo = ?, rol = ? WHERE id_usuario = ?");
            $stmt -> execute([$name, $mail, $rol, $id]);
        } else {
            $hashed_pwd = password_hash($passwd, PASSWORD_DEFAULT);
            $stmt = $pdo -> prepare("UPDATE Usuarios set nombre = ?, correo = ?, contrasena = ?, rol = ? WHERE id_usuario = ?");
            $stmt -> execute([$name, $mail, $hashed_pwd, $rol, $id]);
        }
        $_SESSION['exito'] = 'Datos actualizados.';
        header("Location: " . BASE_URL . "modulos/usuarios.php");
        exit;
    } else{
        header('Location: ' . BASE_URL . 'modulos/usuarios.php');
        exit;
    }