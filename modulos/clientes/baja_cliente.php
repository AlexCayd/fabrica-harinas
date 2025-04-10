<?php 

include '../../config/conn.php';
include '../../config/functions.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $id_cliente = $_GET['id'];
    $estado = test_data($_POST['categoria']);

    $sql = "DELETE FROM Clientes WHERE id_cliente = $id_cliente";
    echo $sql;
    $stmt = $pdo -> prepare($sql);

    // Ejecutamos la consulta
    $stmt -> execute();
    $res = $stmt -> fetch();

    if($res){
        header("Location: /fabrica-harinas/modulos/clientes.php?status=error");
        exit;
    }else{
        header("Location: /fabrica-harinas/modulos/clientes.php?status=success");
        exit;
    }

}else{
    header("Location: /fabrica-harinas/menu.php");
    exit;
}



?>