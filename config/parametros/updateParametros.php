<?php 
    include_once("../../config/config.php");
    require '../conn.php';
    include '../functions.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        session_start();
        $id = $_POST['id'];
        $origen = $_POST['origen'];
        $lim_inf = test_data($_POST['limite_inf']);
        $lim_sup = test_data($_POST['limite_sup']);
        
        $stmt = $pdo -> prepare("UPDATE Parametros set lim_Superior = ?, lim_Inferior = ? WHERE id_parametro = ?");
       
        if ( $stmt -> execute([$lim_sup, $lim_inf, $id])){
           $_SESSION['exito'] = 'Parámetro actualizado.';
        } else{
            $_SESSION['error'] = 'No se pudo actualizar el parámetro';
        }
        header("Location:" . BASE_URL . "modulos/parametrosform.php?id=" . $id . '&ow=' . $origen);
        exit;
        
    } else{
        header('Location: ' . BASE_URL . 'menu.php');
        exit;
    }