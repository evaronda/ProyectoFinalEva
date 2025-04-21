<?php

require '../config/config.php';

if(isset($_POST['id'])) {

    $id = $_POST['id'];
    $token = $_POST['token'];

    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

    if ($token == $token_tmp) {

        $talla = isset($_POST['talla']) ? $_POST['talla'] : 'N/A';

        if(isset($_SESSION['carrito']['productos'][$id])) {
            if (is_array($_SESSION['carrito']['productos'][$id])) {
                $_SESSION['carrito']['productos'][$id]['cantidad'] += 1;
            } else {
                $_SESSION['carrito']['productos'][$id] = [
                    'cantidad' => 2,
                    'talla' => $talla
                ];
            }
        } else {
            $_SESSION['carrito']['productos'][$id] = [
                'cantidad' => 1,
                'talla' => $talla
            ];
        }
        

        $datos['numero'] = count($_SESSION['carrito']['productos']);
        $datos['ok'] = true;
        


    } else {
        $datos['ok'] = false;
    }

} else {
    $datos['ok'] = false;
}

echo json_encode($datos);

?>