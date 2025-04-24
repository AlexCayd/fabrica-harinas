<?php 

include '../../config/conn.php';
include '../../config/functions.php';


if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $certificado = test_data($_POST['certificado']);
    $nombre = test_data($_POST['nombre']);
    $correo = test_data($_POST['email']);
    $rfc = test_data($_POST['rfc']);
    $puesto = test_data($_POST['puesto']);
    $telefono = test_data($_POST['numero-telefonico']);
    $direccion_fiscal = test_data($_POST['direccion-fiscal']);
    $parametros = test_data($_POST['parametros']);
    $estado = test_data($_POST['categoria']);

    $sql = "INSERT INTO Clientes (req_certificado, nombre, rfc, nombre_contacto, puesto_contacto, correo_contacto, telefono_contacto,
    direccion_fiscal, estado, parametros) VALUES ($certificado, '$nombre', '$rfc', '$nombre', '$puesto', 
    '$correo', '$telefono', '$direccion_fiscal', '$estado', '$parametros')";

    $sql = "INSERT INTO Parametros (nombre_parametro, lim_Inferior, lim_Superior) VALUES ('Tiempo de desarrollo', 5, 10), ('Estabilidad', 8, 12)";
    
    $stmt = $pdo -> prepare($sql);
    // Ejecutar la consulta
    $stmt -> execute();

    // Convertir a un arreglo asociativo
    $res = $stmt -> fetch();

    if($res){
        header("Location: /fabrica-harinas/modulos/clientes.php?error=consulta");
        exit;
    }else{
        header("Location: /fabrica-harinas/modulos/clientes.php?success"); 
    }

}else{

    header("Location: /fabrica-harinas/menu.php");
    exit;
}


?>