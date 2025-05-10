<?php
// Iniciar sesión
session_start();

// Eliminar solo los datos de parámetros consultados
if (isset($_SESSION['parametros_consulta'])) {
    unset($_SESSION['parametros_consulta']);
}

// Redirigir de vuelta al formulario
// Si hay id de inspección, preservarla
$redirect_url = '../modulos/analisiscalidadform.php';
if (isset($_GET['id'])) {
    $redirect_url .= '?id=' . $_GET['id'];
}

// Mensaje de éxito
$_SESSION['exito'] = "Verificación cancelada. Ahora puede seleccionar otro cliente/equipo.";

header("Location: $redirect_url");
exit;