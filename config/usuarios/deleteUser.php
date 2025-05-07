<?php 
    include_once "../../includes/config.php";
    require '../conn.php';
    session_start();
    if  (isset($_GET['id'])){
        $id = intval($_GET['id']);

        $stmt = $pdo -> prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
        $stmt -> execute([$id]);

        $query = "DELETE FROM Usuarios WHERE ID_usuario = $id";
       
        $_SESSION['exito'] = 'Usuario eliminado con éxito.';
    } 

    header('Location: ' . BASE_URL . 'modulos/usuarios.php');
       
   