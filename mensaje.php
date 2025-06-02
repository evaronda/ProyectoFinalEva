<?php
$estado = $_GET['estado'] ?? 'indefinido';
$mensaje = '';

switch ($estado) {
    case 'aprobado':
        $mensaje = "Gracias por su compra. Su pago ha sido procesado con éxito.";
        break;
    case 'cancelado':
        $mensaje = "El pago fue cancelado. No se ha realizado ninguna transacción.";
        break;
    default:
        $mensaje = "Hubo un problema con el pago.";
        break;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estado del pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5 text-center">
        <h1><?php echo $mensaje; ?></h1>
        <a href="catalogo.php" class="btn btn-primary mt-3">Volver al catálogo</a>
    </div>
</body>

</html>