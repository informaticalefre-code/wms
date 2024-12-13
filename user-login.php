<?php
    require 'config/header_head.html';
?>
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/user_login.css">
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
                    <!-- <h5>Lefre somos todos</h5> -->
                </div>
                <div id="login-body">
                    <form id="form-login" method="post" class="login-form" onsubmit="verifica_login(event)">
                        <input type="text" name="username" id="username" class="form-control mb-3" placeholder="Usuario" required autofocus autocomplete="off">
                        <div class="input-group mb-3">
                            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
                            <button type="button" class="btn btn-light b-0" onclick="show_password()"><i id="btn-showpwd" class="icon-eye"></i></button>
                        </div>

                        <div class="form-group text-right">     
                            <a href="user_clave_solicitud.php">¿olvidaste tu contraseña?</a>
                        </div>   

                        <div class="form-group">     
                            <button type="submit" class="btn btn-primary form-control"><strong>Iniciar Sesión</strong></button>
                        </div>   
                    </form>
                </div>                   
            </div>
            <div id="container-msj-error" class='alert alert-danger' role='alert'></div>
        </div>
    </main>
</body>
</html>
<script type="application/javascript">

function verifica_login(){
    event.preventDefault();
    const formData = new FormData(document.getElementById('form-login'));

    fetch('api/ap_user_login.php',{method:'POST', body:formData})
    .then((response) => response.json())
    .then((data) => {
        if(data.message == 'Autorizado'){
            delete formData;
            document.location.href="/wms";
        }else{
            var container_error  = document.getElementById('container-msj-error');
            container_error.innerHTML = data.error_msj + " ("+data.error_nro+")";
            container_error.style.display = 'initial';
        }
    }).catch((err) => {
        console.log("rejected:---", err.message);
    });
};


function show_password(){
    let eleme_icon = document.getElementById('btn-showpwd');
    let input_show = document.getElementById('inputPassword');
    if (eleme_icon.className === 'icon-eye'){
        eleme_icon.className = 'icon-eye-blocked';
        input_show.type = "text";
    } else {
        eleme_icon.className = 'icon-eye';
        input_show.type = "password";
    }
    delete eleme_icon, input_show;
}

</script>
