<?php
    require 'config/header_head.html';
?>
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/user_clave_cambio.css">
</head>
<body>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div id="login-container">
            <div id="login-title">
                <img src="img/svg/lefre02_letras_blancas.svg">
            </div>
            <div id="login-content">
                <div id="login-header">
                    <h2 class="text-center mb-3">Cambio de Contraseña</h2>
                    <hr style="border:0;width: 96%;background-color:#C8C8C8;height:2px;margin:0px 0px 8px 0px;">
                    <p>Introduce una nueva contraseña.</p>
                </div>
                <div id="login-body">
                    <form id="form-password" method="post" class="login-form" onsubmit="submit_recupera_link(event)">
                        <div class="input-group mb-3">
                            <input type="password" name="user_password" id="user_password" class="form-control" placeholder="Escribe la contraseña" required autocomplete="off">
                            <button type="button" class="btn btn-light b-0" onclick="show_password()"><i id="btn-showpwd" class="icon-eye"></i></button>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" name="user_password2" id="user_password2" class="form-control" placeholder="Repite la contraseña" required autocomplete="off">
                            <!-- <button type="button" class="btn btn-light b-0" onclick="show_password(2)"><i id="btn-showpwd" class="icon-eye"></i></button> -->
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary form-control"><strong>Cambiar Contraseña</strong></button>
                        </div>
                    <form>
                    <p>Al cambiarla exitosamente volverás a la pantalla de identificación</p>
                </div>
            </div>
            <div id="container-msj-error" class='alert alert-danger' role='alert'></div>
        </div>
    </main>
</body>    
</html>
<script type="application/javascript">
    function submit_recupera_link(event){
        event.preventDefault();
        spinner.removeAttribute('hidden');
        if (valida_password()){
            const url_param = new URLSearchParams(window.location.search);
            const token = url_param.get('token');
            const password = document.getElementById('user_password').value;
            const data = {accion:"clave-cambiar",token:token,password:password};

            fetch('api/ap_usuario_pwd.php',{method:'PATCH', body:JSON.stringify(data),headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                spinner.setAttribute('hidden', '');
                if (responseJson.response == 'success'){
                    document.getElementById("form-password").reset();
                    Swal.fire({icon:'success', title:'Contraseña cambiada con éxito', showConfirmButton:false, timer:2500});
                    setTimeout(() => {window.location.replace(location.origin + "/wms/index.php")}, 4000);
                }else{
                    Swal.fire({icon: responseJson.error_tpo,title: 'no se ha cambiado clave',text: responseJson.error_msj});
                }
            });
            delete url_param, token, password, data;
        }            
    }


    function show_password(){
        let eleme_icon = document.getElementById('btn-showpwd');
        let input_show = document.getElementById('user_password');
        if (eleme_icon.className === 'icon-eye'){
            eleme_icon.className = 'icon-eye-blocked';
            input_show.type = "text";
        } else {
            eleme_icon.className = 'icon-eye';
            input_show.type = "password";
        }
        delete eleme_icon, input_show;
    }

    function valida_password(){
        let validacion = true;
        const password1 = document.getElementById('user_password').value;
        const password2 = document.getElementById('user_password2').value;
        if (password1 !== password2){
            Swal.fire({icon: 'warning',title: 'contraseña no valida',text: 'contraseñas no coinciden'});
            validacion = false;
        }
        return (validacion);
    }
</script>

                
