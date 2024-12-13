<?php
    require 'config/header_head.html';
?>
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/user_registro.css">
</head>
<body>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div id="login-container" class="mt-5">
            <div id="login-content">
                <div id="login-header">
                    <h2 class="text-center mb-3">Registro de Usuario</h2>
                    <hr style="border:0;width: 96%;background-color:#C8C8C8;height:2px;margin:0px 0px 8px 0px;">
                </div>
                <div id="login-body">
                    <form id="form-registro" method="post" class="login-form" onsubmit="submit_crea_usuario(event)" autocomplete="off">
                        <div class="form-group">
                            <label for="name">Escoge un nombre o alias para usar el programa</label>
                            <input type="text" name="name" id="name" class="mb-3 form-control" placeholder="Cuenta de usuario" required autocomplete="off" maxlength = "20">
                        </div>
                        <div class="form-group">
                            <input type="text" name="nombre" id="nombre" class="mb-3 form-control" placeholder="Nombres" required autocomplete="off" maxlength = "50">
                        </div>
                        <div class="form-group">
                            <input type="text" name="apellido" id="apellido" class="mb-3 form-control" placeholder="Apellidos" required autocomplete="off" maxlength = "50">
                        </div>    
                        <div class="form-group">
                            <input type="email" name="email" id="email" class="mb-3 form-control" placeholder="e-mail" required autocomplete="off" maxlength = "100">
                        </div>
                        <div class="form-group">
                            <select id="role" name="role" class="form-select mb-3" placeholder="Rol de usuario" aria-label="Rol de usuario" required>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" id="password" class="mb-3 form-control" placeholder="Escribe la contraseña" required autocomplete="off" maxlength = "20">
                        </div>
                        <div class="form-group">
                            <input type="password" name="password2" id="password2" class="mb-3 form-control" placeholder="Repite la contraseña" required autocomplete="off" maxlength = "20">
                        </div>                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-primary form-control"><strong>GUARDAR</strong></button>
                        </div>
                    <form>
                </div>
            </div>
            <div id="container-msj-error" class='alert alert-danger' role='alert'></div>
        </div>
    </main>
</body>    
</html>
<script type="application/javascript">
    "use strict";
    carga_roles();

    function submit_crea_usuario(event){
        event.preventDefault();
        if (valida_password()){
            spinner.removeAttribute('hidden');
            let formData = new FormData(document.getElementById('form-registro'));
            formData.append("accion","user-registro");
            fetch('api/ap_usuario.php',{method:'POST', body:formData})
            .then((response) => response.json())
            .then((data) => {
                spinner.setAttribute('hidden', '');
                if(data.response == 'success'){
                    Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 3000});
                    document.getElementById("form-registro").reset();
                    // window.location.replace(location.origin + "/wms/index.php");
                }else{
                    Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
                }
            });
            formData = null;
        };
    }

    function valida_password(){
        let resp = true;
        if (document.getElementById('password').value != document.getElementById('password2').value){
            Swal.fire({icon: 'warning',title: 'Error en contraseña',text: "contraseñas no coinciden"});
            document.getElementById('password').classList.add("is-invalid");
            document.getElementById('password2').classList.add("is-invalid");
            resp = false;
        }
        return (resp);
    }

    /*************************************************************************************
    *                            CARGA ROLES
    **************************************************************************************/
    function carga_roles(){
        spinner.removeAttribute('hidden');

        let url = new URL(location.origin + '/wms/api/ap_roles.php');
        url.searchParams.append('accion', 'user-roles');
        
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden','');
            document.getElementById("role").innerHTML = responseJson.html_roles;
        });
        url = null;
    };    
</script>