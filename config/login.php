<?php 
    require 'conn.php';
    
    include 'test_input.php';


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

        if ($res) {
            $_SESSION['user_id'] = $res['id_usuario'];
            $_SESSION['username'] = $res['nombre'];
            $_SESSION['rol'] = $res['rol'];
            header("Location: /fabrica-harinas/menu.php");
            exit;
        } else {
            // --AGREGAR NOTIFICACION CON JS--
            header("Location: /fabrica-harinas/index.php?error=Credenciales+incorrectas");
            exit;
        }
    } else {
        header("Location: /fabrica-harinas/index.php");
        exit;
    }