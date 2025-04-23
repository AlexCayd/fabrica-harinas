<?php 
session_start(); 
include '../../config/conn.php';
include '../../config/functions.php';

// if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $id_cliente = $_GET['id'];
    $estado = test_data($_POST['categoria']);

    $sql = "DELETE FROM Clientes WHERE id_cliente = $id_cliente";
    echo $sql;
    $stmt = $pdo -> prepare($sql);

    // Ejecutamos la consulta
    $stmt -> execute();
    $res = $stmt -> fetch();

    if($res){
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo eliminar el cliente.',
            }).then(() => {
                window.location.href = '/fabrica-harinas/modulos/clientes.php';
            });
        </script>";
    }else{
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Cliente eliminado correctamente.',
            }).then(() => {
                window.location.href = '/fabrica-harinas/modulos/clientes.php';
            });
        </script>";
    }

// }else{
//     echo $sql;
//     echo "<script> alert('algo fallo ff'); </script>";
//     exit;
// }



?>