<?php 

include '../../config/conn.php';
include '../../config/functions.php';


if($_SERVER['REQUEST_METHOD'] == 'POST'){

    //Obtener el id del cliente con ayuda de la URL
    $id = $_GET['id'];
    $certificado = test_data($_POST['certificado']);
    $nombre = test_data($_POST['nombre']);
    $correo = test_data($_POST['email']);
    $rfc = test_data($_POST['rfc']);
    $puesto = test_data($_POST['puesto']);
    $telefono = test_data($_POST['numero-telefonico']);
    $direccion_fiscal = test_data($_POST['direccion-fiscal']);
    $parametros = test_data($_POST['parametros']);

    $sql = "UPDATE Clientes SET req_certificado = $certificado, nombre = '$nombre', rfc = '$rfc', nombre_contacto = '$nombre', 
    puesto_contacto = '$puesto', correo_contacto = '$correo', telefono_contacto = '$telefono', 
    direccion_fiscal = '$direccion_fiscal', parametros = '$parametros' WHERE id_cliente = $id";

    $stmt = $pdo -> prepare($sql);
    $stmt -> execute();

    $res = $stmt -> fetch();

    if($res){
        header("Location: /fabrica-harinas/modulos/clientes.php?error=consulta");
        exit;
    }else{
        header("Location: /fabrica-harinas/modulos/clientes.php?success"); 
        exit;
    }
}else{
    header("Location: /fabrica-harinas/menu.php");
    exit;
}



?>