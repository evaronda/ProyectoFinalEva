<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;
$lista_carrito = array();
$total = 0;

if ($productos != null) {
    foreach ($productos as $clave => $producto) {
        $sql = $con->prepare("SELECT id, nombre, precio, descuento FROM productos WHERE id=? and activo=1");
        $sql->execute([$clave]);
        $producto_db = $sql->fetch(PDO::FETCH_ASSOC);

        if ($producto_db) {
            $producto_db['cantidad'] = $producto['cantidad'];
            $producto_db['talla'] = $producto['talla'];  // Guardar la talla
            $lista_carrito[] = $producto_db;
        }
    }
} else {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MODA & MORE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/estilos.css" rel="stylesheet">
</head>

<body>

    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="#" class="navbar-brand"><strong>MODA & MORE</strong></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarHeader">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a href="#" class="nav-link active">Catálogo</a></li>
                    </ul>
                    <a href="carrito.php" class="btn btn-primary">
                        Carrito <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart ?? 0 ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container py-4">
            <div class="row">
                <div class="col-6">
                    <h4>Detalles de pago</h4>
                    <div id="paypal-button-container"></div>
                </div>
                <div class="col-6">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (empty($lista_carrito)) {
                                    echo '<tr><td colspan="2" class="text-center"><b>Lista vacía</b></td></tr>';
                                } else {
                                    foreach ($lista_carrito as $producto) {
                                        $_id = $producto['id'];
                                        $nombre = $producto['nombre'];
                                        $precio = $producto['precio'];
                                        $descuento = $producto['descuento'];
                                        $cantidad = $producto['cantidad'];
                                        $talla = $producto['talla']; // Obtener la talla
                                        $precio_desc = $precio - (($precio * $descuento) / 100);
                                        $subtotal = $cantidad * $precio_desc;
                                        $total += $subtotal;
                                        ?>
                                        <tr>
                                            <td><?php echo $nombre . ' - Talla: ' . $talla; ?></td>  <!-- Mostrar talla -->
                                            <td><?php echo number_format($subtotal, 2, '.', ',') . MONEDA; ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td colspan="2">
                                            <p class="h3 text-end" id="total">
                                                <?php echo number_format($total, 2, '.', ',') . MONEDA; ?></p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PayPal SDK -->
    <script
        src="https://www.paypal.com/sdk/js?client-id=<?php echo CLIENT_ID; ?>&currency=<?php echo CURRENCY; ?>"></script>

    <script>
        paypal.Buttons({
            style: {
                color: 'blue',
                shape: 'pill',
                label: 'pay'
            },
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo number_format($total, 2, '.', ''); ?>'
                        }
                    }]
                });
            },
            onApprove: function (data, actions) {
                let URL = 'clases/captura.php'
                actions.order.capture().then(function (detalles) {
                    let url = 'clases/captura.php'
                    return fetch(url, {
                        method: 'post',
                        headers: {
                            'content-type': 'application/json'
                        },
                        body: JSON.stringify({
                            detalles: detalles
                        })
                    })
                });
            },
            onCancel: function (data) {
                alert("Pago cancelado");
                console.log(data);
            }
        }).render('#paypal-button-container');
    </script>

</body>

</html>
