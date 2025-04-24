<?php 
session_start(); 
include '../../config/conn.php';
include '../../config/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// if($_SERVER['REQUEST_METHOD'] == 'POST'){

$id = $_GET['id'];
    // Primero eliminar los parámetros asociados al cliente
    $sql_delete_parametros = "DELETE FROM Parametros WHERE id_cliente = $id";
    $stmt_delete_parametros = $pdo->prepare($sql_delete_parametros);
    $stmt_delete_parametros->execute();

    // Luego eliminar el cliente
    $sql = "DELETE FROM Clientes WHERE id_cliente = $id";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = 'exito';
        $_SESSION['texto'] = 'Cliente eliminado correctamente.';
    } else {
        $_SESSION['mensaje'] = 'error';
        $_SESSION['texto'] = 'No se pudo eliminar el cliente.';
    }

    header("Location: /fabrica-harinas/modulos/clientes.php");

// }else{
//     echo $sql;
//     echo "<script> alert('algo fallo ff'); </script>";
//     exit;
// }



?>