<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

$lista_carrito = array();

if ($productos != null) {
    foreach ($productos as $clave => $producto_info) {
        $cantidad = is_array($producto_info) ? $producto_info['cantidad'] : $producto_info;
        $talla = is_array($producto_info) && isset($producto_info['talla']) ? $producto_info['talla'] : 'N/A';

        $sql = $con->prepare("SELECT id, nombre, precio, descuento FROM productos WHERE id=? and activo=1");
        $sql->execute([$clave]);
        $producto = $sql->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $producto['cantidad'] = $cantidad;
            $producto['talla'] = $talla;
            $lista_carrito[] = $producto;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MODA & MORE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <link href="estilos.css" rel="stylesheet">
</head>

<body>

    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark ">
            <div class="container">
                <a href="catalogo.php" class="navbar-brand">
                    <strong>MODA & MORE</strong>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader"
                    aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarHeader">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="catalogoDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Catálogo
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="catalogoDropdown">
                                <li><a class="dropdown-item" href="catalogo.php?categoria=1">Ropa de Mujer</a></li>
                                <li><a class="dropdown-item" href="catalogo.php?categoria=2">Accesorios</a></li>
                                <li><a class="dropdown-item" href="catalogo.php?categoria=3">Hogar y vida</a></li>
                                <li><a class="dropdown-item" href="catalogo.php?categoria=4">Belleza y salud</a></li>
                            </ul>
                        </li>

                    </ul>

                    <a href="carrito.php" class="btn btn-primary">
                        Carrito<span id="num_cart" class="badge bg-secondary"><?php echo $num_cart ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>


    <main>
        <div class="container py-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($lista_carrito == null) { ?>
                            <tr>
                                <td colspan="6" class="text-center"><b>Carrito vacío</b></td>
                            </tr>
                        <?php } else { ?>
                            <?php
                            $total = 0;
                            foreach ($lista_carrito as $producto) {
                                $_id = $producto['id'];
                                $nombre = $producto['nombre'];
                                $precio = $producto['precio'];
                                $descuento = $producto['descuento'];
                                $cantidad = $producto['cantidad'];
                                $talla = $producto['talla'];
                                $precio_desc = $precio - (($precio * $descuento) / 100);
                                $subtotal = $cantidad * $precio_desc;
                                $total += $subtotal;
                                ?>
                                <tr>
                                    <td><?php echo $nombre; ?></td>
                                    <td><?php echo $talla; ?></td>
                                    <td><?php echo number_format($precio_desc, 2, '.', ',') . MONEDA; ?></td>
                                    <td>
                                        <input type="number" min="1" max="10" step="1" value="<?php echo $cantidad ?>" size="5"
                                            id="cantidad_<?php echo $_id; ?>"
                                            onchange="actualizaCantidad(this.value, <?php echo $_id; ?>)">
                                    </td>
                                    <td id="subtotal_<?php echo $_id; ?>" class="subtotal">
                                        <?php echo number_format($subtotal, 2, '.', ',') . MONEDA; ?>
                                    </td>
                                    <td>
                                        <a href="#" id="eliminar" class="btn btn-warning btn-sm"
                                            data-bs-id="<?php echo $_id; ?>" data-bs-toggle="modal"
                                            data-bs-target="#eliminaModal">Eliminar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="4"></td>
                                <td colspan="2">
                                    <p class="h3" id="total"><?php echo number_format($total, 2, '.', ',') . MONEDA; ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>


                <?php if ($lista_carrito != null) { ?>
                    <div class="row">
                        <div class="col-md-5 offset-md-7 d-grid gap-2">
                            <a href="pago.php" class="btn btn-primary btn-lg">Realizar pago</a>
                        </div>
                    </div>
                <?php } ?>

            </div>

        </div>
    </main>

    <div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminaModalLabel">Alerta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Desea eliminar el producto de la lista?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button id="btn-elimina" type="button" class="btn btn-danger" onclick="eliminar()">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>

    <script>
        let eliminaModal = document.getElementById('eliminaModal')
        eliminaModal.addEventListener('show.bs.modal', function (event) {
            let button = event.relatedTarget
            let id = button.getAttribute('data-bs-id')
            let buttonElimina = eliminaModal.querySelector('.modal-footer #btn-elimina')
            buttonElimina.value = id
        })

        function actualizaCantidad(cantidad, id) {
            let url = 'clases/actualizar_carrito.php'
            let formData = new FormData()
            formData.append('action', 'agregar')
            formData.append('id', id)
            formData.append('cantidad', cantidad)

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            }).then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        let divsubtotal = document.getElementById('subtotal_' + id)
                        divsubtotal.innerHTML = data.sub

                        let total = 0.00
                        let list = document.querySelectorAll('.subtotal')

                        list.forEach(item => {
                            let texto = item.textContent.trim()

                            texto = texto.replace('€', '').replace(/\s/g, '')

                            if (texto.includes('.') && texto.includes(',')) {
                                texto = texto.replace(/\./g, '')
                            }

                            texto = texto.replace(',', '.')

                            let valor = parseFloat(texto)
                            if (!isNaN(valor)) {
                                total += valor
                            }
                        })

                        let totalFormateado = new Intl.NumberFormat('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(total)

                        document.getElementById('total').innerHTML = totalFormateado + '<?php echo MONEDA; ?>'
                    }
                })
        }


        function eliminar() {
            let botonElimina = document.getElementById('btn-elimina')
            let id = botonElimina.value

            let url = 'clases/actualizar_carrito.php'
            let formData = new FormData()
            formData.append('action', 'eliminar')
            formData.append('id', id)

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            }).then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        location.reload()
                    }
                })
        }
    </script>

</body>

</html>