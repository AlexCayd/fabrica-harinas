<?php

require '../includes/conn.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tu_correo@gmail.com';
    $mail->Password = 'tu_contraseña';

    // Configuración del correo
    $mail->setFrom('tucorreo@gmail.com', 'Nombre del Remitente');
    $mail->addAddress('destinatario@correo.com', 'Nombre del Destinatario');

    $mail->Subject = 'Tu certificado de inspección';
    $mail->Body = 'Adjunto encontrarás tu certificado en PDF.';

    // Adjuntar el PDF en memoria
    $mail->addStringAttachment($pdf_content, 'certificado.pdf');

    // Enviar
    $mail->send();
    echo 'Correo enviado con éxito.';

} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}
