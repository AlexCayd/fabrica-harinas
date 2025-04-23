<?php 
    require '../conn.php';
    session_start();
    if  (isset($_GET['id'])){
        $id = intval($_GET['id']);

        $stmt = $pdo -> prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt -> execute([$id]);

        $query = "DELETE FROM usuarios WHERE ID_usuario = $id";
       
        $_SESSION['exito'] = 'Usuario eliminado con éxito.';
    } 

    header('Location: /fabrica-harinas/modulos/usuarios.php');
       
   