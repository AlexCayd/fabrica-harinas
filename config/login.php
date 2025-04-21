<?php 
    require 'conn.php';
    
    include 'functions.php';


    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        session_start();

        // Datos del formulario
        $mail = test_data($_POST['mail']);
        $passwd = test_data($_POST['passwd']);

        // Consulta preparada a la db en base al correo 
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, contrasena, rol FROM usuarios WHERE correo = ?");
        $stmt->execute([$mail]);
        // Resultado de la consulta
        $res = $stmt->fetch();

        if ($res && password_verify($passwd, $res['contrasena']) ) {
            $_SESSION['user_id'] = $res['id_usuario'];
            $_SESSION['username'] = $res['nombre'];
            $_SESSION['rol'] = $res['rol'];
            $_SESSION['exito'] = 'Bienvenido ' . $res['nombre'] ;
            header("Location: /fabrica-harinas/menu.php");
            exit;
        } else {
            $_SESSION['error'] = 'Credenciales incorrectas';
            header("Location: /fabrica-harinas/index.php");
            exit;
        }
    } else {
        header("Location: /fabrica-harinas/index.php");
        exit;
    }