<?php 
    require 'conn.php';

    if  (isset($_GET['id'])){
        $id = intval($_GET['id']);

        $stmt = $pdo -> prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt -> execute([$id]);

        $query = "DELETE FROM usuarios WHERE ID_usuario = $id";
       
    } 

    header('Location: /fabrica-harinas/modulos/usuariosform.php');
       
   