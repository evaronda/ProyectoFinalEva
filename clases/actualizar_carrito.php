<?php

require '../config/config.php';
require '../config/database.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    if ($action == 'agregar') {
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
        $talla = isset($_POST['talla']) ? $_POST['talla'] : 'N/A'; //Aseguramos que la talla sea recibida

        //Validación básica de la talla y cantidad
        if ($cantidad <= 0 || !is_numeric($cantidad)) {
            $datos['ok'] = false;
            $datos['mensaje'] = 'Cantidad inválida';
        } else {
            $respuesta = agregar($id, $cantidad, $talla); //Pasamos la talla

            if ($respuesta > 0) {
                $datos['ok'] = true;
                $datos['sub'] = number_format($respuesta, 2, '.', ',') . MONEDA;
            } else {
                $datos['ok'] = false;
                $datos['mensaje'] = 'Error al agregar el producto';
            }
        }
    } else if ($action == 'eliminar') {
        $datos['ok'] = eliminar($id);
    } else {
        $datos['ok'] = false;
    }

} else {
    $datos['ok'] = false;
}

echo json_encode($datos);

function agregar($id, $cantidad, $talla)
{
    $res = 0;
    if ($id > 0 && $cantidad > 0 && is_numeric($cantidad)) {
        //Verificamos si el producto ya está en el carrito
        if (isset($_SESSION['carrito']['productos'][$id])) {
            $_SESSION['carrito']['productos'][$id]['cantidad'] += $cantidad; //Aumentamos la cantidad
        } else {
            $_SESSION['carrito']['productos'][$id] = [
                'cantidad' => $cantidad, //Guardamos la cantidad
                'talla' => $talla       //Guardamos la talla seleccionada
            ];
        }

        //Conexión a la base de datos
        $db = new Database();
        $con = $db->conectar();

        $sql = $con->prepare("SELECT count(id) FROM productos WHERE id=? and activo=1");
        $sql->execute([$id]);
        if ($sql->fetchColumn() > 0) {
            $sql = $con->prepare("SELECT precio, descuento FROM productos WHERE id=? and activo=1 limit 1");
            $sql->execute([$id]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            $precio = $row['precio'];
            $descuento = $row['descuento'];
            $precio_desc = $precio - (($precio * $descuento) / 100);
            $res = $cantidad * $precio_desc;
        }
    }
    return $res;
}

function eliminar($id)
{
    if ($id > 0) {
        if (isset($_SESSION['carrito']['productos'][$id])) {
            unset($_SESSION['carrito']['productos'][$id]);
            return true;
        }
    } else {
        return false;
    }
}

?>