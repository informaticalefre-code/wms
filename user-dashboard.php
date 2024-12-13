<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Validación de Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, minimum-scale=1">
    <link rel="icon" type="image/jpg" sizes="16x16" href="img/favicon/favicon-16x16.jpg">
    <link rel="icon" type="image/jpg" sizes="32x32" href="img/favicon/favicon-32x32.jpg">
    <link rel="icon" type="image/jpg" sizes="96x96" href="img/favicon/favicon-96x96.jpg">
    <!-- Boostrap CSS    ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/user_dashboard.css">
</head>
<body>
    <main id="main_screen">
        <div id="login-container">
            <div id="login-title">
                <img src="img/svg/lefre02_letras_blancas.svg">
            </div>
            <div id="login-content">
                <div id="login-header">
                    <h2 class="text-center mb-3">Sistema de Gestión de Almacén</h2>
                </div>
                <div id="login-body">
                    <br>
                    <p>Validando usuario... por favor espera</p>
                    <br>
                    <p>Serás redireccionado a la página principal</p>
                    <br>
                </div>                   
            </div>
            <div id="container-msj-error" class='alert alert-danger' role='alert'></div>
        </div>
    </main>
</body>
</html>
<script type="application/javascript">
"use_strict";
verifica_token();

function verifica_token(){
    let search_parametro = new URLSearchParams(window.location.search);
    let url = new URL(location.origin + '/wms/api/ap_user_token.php');
    url.searchParams.append('token', search_parametro.get('token'));

    fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
    .then((response) => response.json())
    .then((responseJson) => {
        if(responseJson.response == 'success'){
            window.location.replace(location.origin+"/wms");
        }else{
            document.getElementById('container-msj-error').innerHTML = responseJson.error_msj + " ("+responseJson.error_nro+")";
            document.getElementById('container-msj-error').style.display = 'initial';
        }
    }).catch((err) => {
        document.getElementById('container-msj-error').innerHTML = responseJson.error_msj + " ("+responseJson.error_nro+")";
        document.getElementById('container-msj-error').style.display = 'initial';
    });
};

</script>