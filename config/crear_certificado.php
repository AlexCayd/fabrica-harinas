<?php 

include 'conn.php';
include 'functions.php';
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_inspeccion = test_data($_POST['nombre']);
$fecha_emision = test_data($_POST['fecha_emision']);
$cantidad_solicitada = test_data($_POST['cantidad_solicitada']);
$cantidad_recibida = test_data($_POST['cantidad_recibida']);
$fecha_envio = test_data($_POST['fecha_envio']);
$fecha_caducidad = test_data($_POST['fecha_caducidad']);
$desviacion = test_data($_POST['desviacion']);

// Convertir las fechas a objetos DateTime
$emision = new DateTime($fecha_emision);
$caducidad = new DateTime($fecha_caducidad);
$envio = new DateTime($fecha_envio);

// Validar que la cantidad recibida no sea mayor a la cantidad solicitada
if($cantidad_recibida > $cantidad_solicitada){
    $_SESSION['error'] = "La cantidad recibida no puede ser mayor a la cantidad solicitada";
    header('Location: ../modulos/generar_certificadoform.php');
    exit;
}

if($fecha_envio > $emision){
    $_SESSION['error'] = "La fecha de envio no puede ser mayor a la fecha de emisión";
    header('Location: ../modulos/generar_certificadoform.php');
    exit;
}

// Validar que la desviación no sea negativa
if($desviacion < 0){
    $_SESSION['error'] = "La desviación no puede ser negativa";
    header('Location: ../modulos/generar_certificadoform.php');
    exit;
}
// Validar que la fecha de emisión no sea mayor a la fecha de caducidad
if($emision > $caducidad){
    $_SESSION['error'] = "La fecha de emisión no puede ser mayor a la fecha de caducidad";
    header('Location: ../modulos/generar_certificadoform.php');
    exit;
}



$sql = "INSERT INTO Certificados (id_inspeccion, fecha_emision, cantidad_solicitada, cantidad_recibida, fecha_envio, fecha_caducidad, desviacion)  
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(1, $id_inspeccion);
$stmt->bindParam(2, $fecha_emision);
$stmt->bindParam(3, $cantidad_solicitada);
$stmt->bindParam(4, $cantidad_recibida);
$stmt->bindParam(5, $fecha_envio);
$stmt->bindParam(6, $fecha_caducidad);
$stmt->bindParam(7, $desviacion);
if($stmt->execute()){
    $_SESSION['exito'] = "Certificado creado correctamente";
    header('Location: ../modulos/historico.php');
}else{
    $_SESSION['error'] = "Error al crear el certificado";
    header('Location: ../modulos/historico.php');
}


?>