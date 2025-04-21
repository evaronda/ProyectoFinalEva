<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$sql = $con->prepare("SELECT id, nombre, precio FROM productos WHERE activo=1");
$sql->execute();
$result = $sql->fetchAll(PDO::FETCH_ASSOC);

//session_destroy();


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MODA & MORE</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <link href="css/estilos.css" rel="stylesheet">
</head>


<body>

    <header>
        <div class="navbar navbar-expand-lg navbar-dark bg-dark ">
            <div class="container">
                <a href="#" class="navbar-brand ">
                    <strong>MODA & MORE</strong>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHeader"
                    aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarHeader">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        < class="nav-item">
                            <a href="#" class="nav-link active">Catálogo</a>

                    </ul>

                    <a href="checkout.php" class="btn btn-primary">
                        Carrito<span id="num_cart" class="badge bg-secondary"><?php echo $num_cart ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
    <div class="container py-4">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach($result as $row) { 
                $id = $row['id'];
                $imagen = "imagenes/productos/" . $id . "/principal.jpg";
                if (!file_exists($imagen)) {
                    $imagen = "imagenes/no-photo.jpg";
                }
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo $imagen; ?>" class="card-img-top" alt="<?php echo $row['nombre']; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $row['nombre']; ?></h5>
                        <p class="card-text mb-4"><?php echo number_format($row['precio'], 2 ,'.', ','); ?>€</p>
                        <div class="mt-auto d-flex justify-content-between">
                            <a href="detalles.php?id=<?php echo $row['id'];?>&token=<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN);?>" class="btn btn-primary btn-sm">Detalles</a>
                            <button class="btn btn-outline-success btn-sm" type="button" onclick="addProducto(<?php echo $row['id']; ?>, '<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>')">Agregar</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>

        <script>
        function addProducto(id, token) {
             let url = 'clases/carrito.php'
             let formData = new FormData()
             formData.append('id', id)
             formData.append('token', token)

             fetch(url,{
                method: 'POST',
                body: formData,
                mode: 'cors'
             }).then(response => response.json())
             .then(data => {
                if(data.ok) {
                    let elemento = document.getElementById("num_cart")
                    elemento.innerHTML = data.numero
                }
             })

        }
    </script>

</body>

</html>