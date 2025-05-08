<?php
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';
require '../config/conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$id_inspeccion = $_GET['id'];

// Consulta para obtener datos del cliente
$sql = "SELECT 
            c.nombre_contacto AS nombre_cliente, 
            c.correo_contacto,
            c.nombre
        FROM Inspeccion i
        INNER JOIN Clientes c ON i.id_cliente = c.id_cliente
        INNER JOIN Certificados ce ON ce.id_inspeccion = i.id_inspeccion
        WHERE i.id_inspeccion = :id_inspeccion";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_inspeccion', $id_inspeccion, PDO::PARAM_INT);

if ($stmt->execute() && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $nombre_destinatario = $row['nombre_cliente'];
    $correo_destinatario = $row['correo_contacto'];
    $nombre_empresa = $row['nombre'];

    // Ruta del PDF ya guardado
    $nombre_archivo_pdf = 'Certificado_de_inspeccion_' . $nombre_empresa . '.pdf';
    $ruta_archivo_pdf = '../certificados/' . $nombre_archivo_pdf;

    if (file_exists($ruta_archivo_pdf)) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tu_correo@gmail.com';       // PONER EL CORREO REAL
            $mail->Password = 'tu_contraseña';             // Usa App Password en Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('tu_correo@gmail.com', 'Harinas Elizondo');
            $mail->addAddress($correo_destinatario, $nombre_destinatario);

            $mail->isHTML(true);
            $mail->Subject = 'Tu certificado de inspección';
            $mail->Body = "Estimado $nombre_destinatario,<br><br>Adjunto encontrarás tu certificado en PDF.<br><br>Saludos,<br>Harinas Elizondo";

            // Adjuntar archivo ya existente
            $mail->addAttachment($ruta_archivo_pdf, $nombre_archivo_pdf);

            $mail->send();

            $_SESSION['exito'] = 'Correo enviado con éxito.';
            header("Location: /fabrica-harinas/modulos/historico.php");
            exit;

        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "El archivo PDF no existe.";
        header("Location: /fabrica-harinas/modulos/historico.php");
        exit;
    }

} else {
    $_SESSION['error'] = "No se pudo obtener información del cliente.";
    header("Location: /fabrica-harinas/modulos/historico.php");
    exit;
}
?>
