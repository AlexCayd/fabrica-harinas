<?php 

include '../config/conn.php'; 
include '../fpdf186/fpdf.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtenemos el id de la inspección
$id_inspeccion = $_GET['id'];

// Preparamos y ejecutamos la consulta
$stmt = $pdo->prepare("SELECT * FROM Inspeccion WHERE id_inspeccion = ?");
$stmt->execute([$id_inspeccion]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificamos que haya resultados
if (count($resultado) > 0) {
    $inspeccion = $resultado[0];

    // Inicializamos FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);

    $pdf->Cell(40,10,'Certificado de Inspección');
    $pdf->Ln(10);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(40,10,'Lote: ' . $inspeccion['lote']);

    $pdf->Output();
} else {
    echo "No se encontró la inspección con ID $id_inspeccion.";
}
?>
