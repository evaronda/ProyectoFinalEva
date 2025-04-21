<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$id = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($id == '' || $token == '') {
    echo 'Error al procesar la petición';
    exit;
} else {
    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

    if ($token == $token_tmp) {

        $sql = $con->prepare("SELECT count(id) FROM productos WHERE id=? and activo=1");
        $sql->execute([$id]);
        if ($sql->fetchColumn() > 0) {
            $sql = $con->prepare("SELECT nombre, descripcion, precio, descuento FROM productos WHERE id=? and activo=1 limit 1");
            $sql->execute([$id]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            $nombre = $row['nombre'];
            $descripcion = $row['descripcion'];
            $precio = $row['precio'];
            $descuento = $row['descuento'];
            $precio_desc = $precio - (($precio * $descuento) / 100);
            $dir_images = 'imagenes/productos/' . $id . '/';

            $rutaImg = $dir_images . 'principal.jpg';

            if (!file_exists($rutaImg)) {
                $rutaImg = 'imagenes/no-photo.jpg';
            }

            $imagenes = array();
            if (file_exists($dir_images)) {
                $dir = dir($dir_images);

                while (($archivo = $dir->read()) !== false) {
                    if ($archivo != 'principal.jpg' && (strpos($archivo, 'jpg') || strpos($archivo, 'jpeg'))) {
                        $imagenes[] = $dir_images . $archivo;
                    }
                }

                $dir->close();
            }

        }
    } else {
        echo 'Error al procesar la petición';
        exit;
    }
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
                    <a href="checkout.php" class="btn btn-primary">
                        Carrito <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-6 order-md-1">

                    <img src="<?php echo $rutaImg; ?>" class="d-block w-100" alt="Imagen principal">

                </div>

                <div class="col-md-6 order-md-2">
                    <h2><?php echo $nombre; ?></h2>

                    <?php if ($descuento > 0) { ?>
                        <p><del><?php echo number_format($precio, 2, '.', ',') . MONEDA; ?></del></p>
                        <h2>
                            <?php echo number_format($precio_desc, 2, '.', ',') . MONEDA; ?>
                            <small class="text-success"><?php echo $descuento; ?>% descuento</small>
                        </h2>
                    <?php } else { ?>
                        <h2><?php echo $precio . MONEDA; ?></h2>
                    <?php } ?>

                    <p class="lead"><?php echo $descripcion; ?></p>

                    <div class="d-grid gap-3 col-10 mx-auto">
                        <button class="btn btn-primary" type="button">Comprar ahora</button>
                        <button class="btn btn-outline-primary" type="button"
                            onclick="addProducto(<?php echo $id; ?>, '<?php echo $token_tmp; ?>')">Agregar al
                            carrito</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function addProducto(id, token) {
            let url = 'clases/carrito.php';
            let formData = new FormData();
            formData.append('id', id);
            formData.append('token', token);

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            }).then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        let elemento = document.getElementById("num_cart");
                        elemento.innerHTML = data.numero;
                    }
                });
        }
    </script>

</body>

</html>