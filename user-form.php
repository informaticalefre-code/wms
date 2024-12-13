<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-Store">
    <meta http-equiv="Expires" content="0">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/user-form.css">
</head>
<body>
<?php
    require 'config/barra_save.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
            <div style="display:flex;flex-wrap:nowrap;width:100%;align-items:center;">
                <h4 class='titulos_categorias mb-0'>Edición de Usuario</h4>
                <!-- <input type="text" class="form-control bg-white border-0 p-0 m-0" style="max-width:200px;font-size:1.75rem;" id="input-idpicking" name="input-idpicking" readonly> -->
            </div>
            <div id="form-container">
                <form id="usuario-form" method="post" id="picking-form-data" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Usuario ID.</label>
                        <input type="text" name="username" id="username" class="mb-3 form-control" placeholder="Cuenta de usuario" readonly required autocomplete="off" maxlength = "20">
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="mb-3 form-control" placeholder="Nombres" required autocomplete="off" maxlength = "50">
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido</label>
                        <input type="text" name="apellido" id="apellido" class="mb-3 form-control" placeholder="Apellidos" required autocomplete="off" maxlength = "50">
                    </div>    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="mb-3 form-control" placeholder="email" required autocomplete="off" maxlength = "100">
                    </div>
                    <div class="form-group">
                        <label for="estatus">Estatus</label>
                        <select id="estatus" name="estatus" class="form-select mb-3" aria-label="Estatus de usuario">
                            <option value="null" selected>Seleccione...</option>
                            <option value="0">No activo</option>
                            <option value="1">Activo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role">Rol</label>
                        <select id="role" name="role" class="form-select mb-3" placeholder="Rol de usuario" aria-label="Rol de usuario">
                            <option value="" selected>Sin Rol</option>
                        </select>
                    </div>
                </form>                    
            </div>
    </main>
<?php
    require 'config/footer.html';
?>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    carga_roles();
    carga_usuario_data();

    /* Función que direcciona cuando se pulsa el botón de atrás (no confundir con el del navegador)*/
    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/user_lista.php");
    }

    /**************************************************************************
    * CARGA USUARIO DATA
    ***************************************************************************/
    function carga_usuario_data(){
        spinner.removeAttribute('hidden');
        document.getElementById("usuario-form").reset();
        // Tomamos el parametro del ID Picking a cargar
        let search_parametro = new URLSearchParams(window.location.search);
        let username = search_parametro.get('user');
        document.getElementById("username").value = username;
        // Hacemos un fetch api mandando el Nro de la Tarea de Picking.
        let url = new URL(location.origin + '/wms/api/ap_usuario.php');
        url.searchParams.append('user', username);
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if (responseJson.response == 'success'){
                document.getElementById("nombre").value   = responseJson.nombre;
                document.getElementById("apellido").value = responseJson.apellido;
                document.getElementById("email").value    = responseJson.email;
                document.getElementById("estatus").value  = responseJson.activo;
                document.getElementById("role").value     = responseJson.role;
            }else{
                Swal.fire({icon: responseJson.error_tpo,title: 'No se puede cargar datos de usuario',text: responseJson.error_msj});
            }
        // }).catch((err) => {
        //     spinner.setAttribute('hidden', '');
        //     Swal.fire({icon: 'error', title:'Error al cargar datos de usuario', text:err.message});
        });
        search_parametro = username = url = null;
        spinner.setAttribute('hidden', '');
    };


    /**************************************************************************
    *                           GUARDAR DATOS
    ***************************************************************************/
    function guardar(){
        spinner.removeAttribute('hidden');

        let formData = new FormData(document.getElementById('usuario-form'));
        let data = Object.fromEntries(formData);

        let url = new URL(location.origin + '/wms/api/ap_usuario.php');

        fetch(url,{method:'PUT',body:JSON.stringify(data),headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if(responseJson.response == 'success'){
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
            }else{
                Swal.fire({icon: responseJson.error_tpo, title:'Datos No guardados', text:responseJson.error_msj});
            }
        });
        url = formData = data = null;

    };


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
            document.getElementById("role").insertAdjacentHTML('beforeend',responseJson.html_roles);
            // document.getElementById("role").innerHTML = responseJson.html_roles;
        });
        url = null;
    };
</script>