<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script
        src="https://www.paypal.com/sdk/js?client-id=AZClHD376iqJAP9y0UgJ-2yrPs-fEDv8eFnD05TXdtdW106h9y13ve6IAAudr-gk2SM704iL_47TFBH9&currency=EUR">

        </script>
</head>

<body>

    <div id="paypal-button-container"></div>

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
                            value: 100
                        }
                    }]

                });

            },

            onApprove: function (data, actions) {
                actions.order.capture().then(function (detalles) {
                    window.location.href = "completado.html"

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