<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$id_categoria = $_GET['categoria'] ?? '';
$searchTerm = $_GET['search'] ?? '';

$sqlCategorias = $con->prepare("SELECT id, nombre FROM categorias");
$sqlCategorias->execute();
$categorias = $sqlCategorias->fetchAll(PDO::FETCH_ASSOC);

if ($id_categoria != '') {
    $sql = $con->prepare("SELECT id, nombre, precio, descuento FROM productos WHERE activo=1 AND id_categoria = ? AND nombre LIKE ?");
    $sql->execute([$id_categoria, "%$searchTerm%"]);
} else {
    $sql = $con->prepare("SELECT id, nombre, precio, descuento FROM productos WHERE activo=1 AND nombre LIKE ?");
    $sql->execute(["%$searchTerm%"]);
}
$result = $sql->fetchAll(PDO::FETCH_ASSOC);

$categoriaNombre = '';
if ($id_categoria != '') {
    foreach ($categorias as $cat) {
        if ($cat['id'] == $id_categoria) {
            $categoriaNombre = $cat['nombre'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - MODA & MORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="estilos.css" rel="stylesheet">
    <link rel="icon" type="image/jpg" href="imagenes/logo.jpg">

</head>

<body>
    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a href="catalogo.php" class="navbar-brand"><strong>MODA & MORE</strong></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarHeader">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active" href="#" id="catalogoDropdown" role="button"
                                data-bs-toggle="dropdown">
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
                    <form class="d-flex" method="GET" action="catalogo.php">
                        <input class="form-control me-2" type="search" placeholder="Buscar productos..." name="search"
                            value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button class="btn btn-outline-success" type="submit">Buscar</button>
                    </form>
                    <a href="checkout.php" class="btn btn-primary ms-3">
                        Carrito <span id="num_cart" class="badge bg-secondary"><?php echo $num_cart ?? 0 ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container py-4">
            <h2 class="mb-4 text-capitalize <?php echo $id_categoria ? 'highlight' : 'highlight-default'; ?>">
                <?php echo $id_categoria ? "Catálogo de " . htmlspecialchars($categoriaNombre) : "Todos los productos"; ?>
            </h2>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                <?php foreach ($result as $row) {
                    $id = $row['id'];
                    $imagen = "imagenes/productos/" . $id . "/principal.jpg";
                    if (!file_exists($imagen)) {
                        $imagen = "imagenes/no-photo.jpg";
                    }

                    $sqlCat = $con->prepare("SELECT id_categoria FROM productos WHERE id = ?");
                    $sqlCat->execute([$id]);
                    $id_cat_prod = $sqlCat->fetchColumn();

                    $tallas = [];
                    if ($id_cat_prod == 1) {
                        $sqlTallas = $con->prepare("SELECT talla FROM tallas WHERE id_producto = ?");
                        $sqlTallas->execute([$id]);
                        $tallas = $sqlTallas->fetchAll(PDO::FETCH_COLUMN);
                    }

                    $precio_desc = $row['precio'] - (($row['precio'] * $row['descuento']) / 100);
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo $imagen; ?>" class="card-img-top" alt="<?php echo $row['nombre']; ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo $row['nombre']; ?></h5>
                                <p class="card-text mb-2"><?php echo number_format($precio_desc, 2, '.', ','); ?>€</p>

                                <?php if (!empty($tallas)) { ?>
                                    <div class="mb-3">
                                        <div class="d-flex flex-wrap gap-2" id="tallas_<?php echo $id; ?>">
                                            <?php foreach ($tallas as $index => $t) { ?>
                                                <input type="radio" class="btn-check" name="talla_<?php echo $id; ?>"
                                                    id="talla_<?php echo $id . '_' . $index; ?>" value="<?php echo $t; ?>"
                                                    autocomplete="off" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-secondary rounded-circle text-center fw-bold"
                                                    style="width: 40px; height: 40px; line-height: 30px;"
                                                    for="talla_<?php echo $id . '_' . $index; ?>">
                                                    <?php echo $t; ?>
                                                </label>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="mt-auto d-flex justify-content-between">
                                    <a href="detalles.php?id=<?php echo $row['id']; ?>&token=<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>"
                                        class="btn btn-primary btn-sm">Detalles</a>
                                    <button class="btn btn-outline-success btn-sm" type="button"
                                        onclick="addProducto(<?php echo $row['id']; ?>, '<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>')">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function addProducto(id, token) {
            let tallaInput = document.querySelector('input[name="talla_' + id + '"]:checked');
            let talla = tallaInput ? tallaInput.value : null;
            let url = 'clases/carrito.php';
            let formData = new FormData();
            formData.append('id', id);
            formData.append('token', token);
            if (talla) formData.append('talla', talla);

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