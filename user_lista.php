<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/usuarios_lista.css">
    <link rel="stylesheet" type="text/css" href="css/boton-back-top.css">
</head>
<body>
<?php
    require 'config/header_barra_usuarios.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <button onclick="topFunction()" id="myBtn" title="Sube al principio"><i class="icon-arrow-up"></i></button>
        <div><h3 class='titulos_categorias'>Usuarios</h3></div>
        <div id="usuarios-lista">
            <table id="usuarios-tabla" cellspacing="0">
                <thead>
                    <tr class="tabla-header">
                        <th>Username</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Estatus</th>
                        <th>Rol</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="usuarios-body">
                </tbody>
            </table>
        </div>
        <div id="div_boton_mas" class="my-3">
            <button type="button" name="lista_carga_mas" id="lista_carga_mas" class="btn btn-primary form-control" onclick="load_more()">Ver más</button>
            <input name="lista_bloque" type="hidden" value="1">
        </div>
    </main>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasRightLabel">Filtros</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form method="post" id="form-filter" autocomplete="off">
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Rol:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('TODOS');" type="checkbox" id="rol-todos" name="rol-todos"  checked>
                    <label class="form-check-label" for="rol-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('APROBADOR');" type="checkbox" id="rol-aprobador" name="rol-aprobador">
                    <label class="form-check-label" for="rol-aprobador">Aprobador</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('PREPARADOR');" type="checkbox" id="rol-pedidos" name="rol-pedidos">
                    <label class="form-check-label" for="rol-pedidos">Pedidos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('PREPARADOR');" type="checkbox" id="rol-preparador" name="rol-preparador">
                    <label class="form-check-label" for="rol-preparador">Preparador</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('VERIFICADOR');" type="checkbox" id="rol-verificador" name="rol-verificador">
                    <label class="form-check-label" for="rol-preparador">Verificador</label>
                </div>                
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('EMBALADOR');" type="checkbox" id="rol-embalador" name="rol-embalador">
                    <label class="form-check-label" for="rol-embalador">Embalador</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('RECEPCION');" type="checkbox" id="rol-recepcion" name="rol-recepcion">
                    <label class="form-check-label" for="rol-recepcion">Recepción</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('LOGISTICA');" type="checkbox" id="rol-logistica" name="rol-logistica">
                    <label class="form-check-label" for="rol-logistica">Logística</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_roles('ADMIN');" type="checkbox" id="rol-admin" name="rol-admin">
                    <label class="form-check-label" for="rol-admin">Administrador</label>
                </div>
                <hr>
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Estatus:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_estatus(999);" type="checkbox" id="estatus-todos" name="estatus-todos" checked>
                    <label class="form-check-label" for="prioridad-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_estatus(0);" type="checkbox" id="estatus-noactivo" name="estatus-noactivo">
                    <label class="form-check-label" for="prioridad-noactivo">No Activo</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_estatus(1);" type="checkbox" id="estatus-activo" name="estatus-activo">
                    <label class="form-check-label" for="prioridad-activo">Activo</label>
                </div>
                <hr>
                <div>
                    <button type="button" name="aplicar_fitro" id="aplicar_filtro" data-bs-dismiss="offcanvas" class="btn btn-primary" onclick="aplica_filtro();">Filtrar</button>
                </div>                
            </form>
        </div>
    </div>


    <div class="modal fade" id="UsuarioModal" tabindex="-1" aria-labelledby="UsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="post" id="usuario-form">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="UsuarioModalLabel">SKU&nbsp</h5>
                        <input id="info-username" type="text" name="info-username" value="error..." class="border-0 col-6" style="font-size:1.25rem;" disabled autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2">
                        <div id="info-nombre" style="width:100%;"></div>
                        <div class="container-fluid p-0 m-0">
                            <div class="container-linea m-0 mb-1 p-0">
                                <div class="container-campo">
                                    <label class="label-campo" for="unidad" style="text-align:right;">Unidad:</label>
                                    <input type="text" id="info-unidad" type="text" name="unidad" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                                <div class="container-campo">
                                    <label class="label-campo" for="codigobarra" style="text-align:right;">Cod.Barra:</label>
                                    <input type="text" id="info-codigobarra" type="text" name="codigobarra" style="width:10rem;" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="container-linea mb-1 p-0">
                                <div class="container-campo">
                                    <label for="empaque" class="label-campo" style="text-align:right;">Empaque:</label>
                                    <input type="text" id="info-empaque" type="text" name="empaque" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                                <div class="container-campo">
                                    <label for="bulto" class="label-campo" style="text-align:right;">Bulto:</label>
                                    <input type="text" id="info-bulto" type="text" name="bulto" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                            </div>
                            <div class="container-linea mb-1 p-0">
                                <div class="container-campo">
                                    <label for="existencia" class="label-campo" style="text-align:right;">Existencia:</label>
                                    <input id="info-existencia" type="text" name="existencia" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                                <div class="container-campo">
                                    <label for="disponible" class="label-campo" style="text-align:right;">Disponible:</label>
                                    <input id="info-disponible" type="text" name="disponible" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                            </div>    
                            <div class="container-linea p-0 mb-1">
                                <div class="container-campo">
                                    <label for="ubicacion" class="label-campo" style="text-align:right;">Ubicación:</label>
                                    <input id="info-ubicacion" type="text" name="ubicacion" autocomplete="off" style="width:10rem;" autocomplete="off">
                                </div>
                                <div class="container-campo">
                                    <label for="ref" class="label-campo" style="text-align:right;">Ref:</label>
                                    <input id="info-ref" type="text" name="ref" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex flex-nowrap">
                            <h6>Ubicaciones Alternas</h6><button type="button" class="col-1" onclick="add_more_places();" name="add_places" id="add_places" style="border:none;background-color:white;width:40px;"><i style="color:green;" class="icon-plus-circle"></i></button>
                            </div>
                            <div id="ubicaciones-prod" class="p-0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" form="usuario-form" onclick="submit_edit_usuario(event)" class="btn btn-primary">Guardar</button> 
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
    require 'config/footer.html';
?>

<script type="text/javascript" src="js/toggle_barra_search.js"></script>
<!-- boton de back to top -->
<script type="text/javascript" src="js/boton-back-top.js"></script>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    
    var global_bloque = 1;
    carga_usuarios(global_bloque);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_usuarios(global_bloque);
    };


    /*************************************************************************************
    *                              CARGA USUARIOS
    **************************************************************************************/
    function carga_usuarios(pnbloque){
        spinner.removeAttribute('hidden');
        let search_text = document.getElementById("search-input").value;
        let formData = {};
        if (search_text.trim().length==0){
            formData = new FormData();
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
            formData.append("lista",'usuarios-lista');
            formData.append("search-text","");
            formData.append("search-options","todos");
        }else{
            formData = new FormData(document.getElementById('form-search'));
            formData.append("accion", "barra-search");
            formData.append("bloque", global_bloque);
            formData.append("lista", 'usuarios-lista');
        }

        let filter = new FormData(document.getElementById('form-filter'));
        let pair   = [];
        for (pair of filter.entries()) {
            formData.append(pair[0], pair[1]);
        }

        fetch('api/ap_usuario.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            if (global_bloque == 1){
                document.getElementById("usuarios-body").innerHTML = responseJson.html_lista;
            }else{
                document.getElementById("usuarios-body").insertAdjacentHTML("beforeend",responseJson.html_lista);
            }
            spinner.setAttribute('hidden', '');
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se puede cargar Tarea de Packing', text:err.message});
        });
        //delete element, css_styles, css_opacity,formData;
        search_text = formData = filter = pair = null;
    };


    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        console.log("barra buscar");
        event.preventDefault();

        global_bloque = 1;
        carga_usuarios(global_bloque);
        toggle_barra_search();
    };


    /*************************************************************************************
    *                             FILTRO STATUS
    **************************************************************************************/
    function filtro_roles(opcion){
        if (opcion == 'TODOS'){
            document.getElementById("status-aprobado").checked  = false;
            document.getElementById("status-picking").checked   = false;
            document.getElementById("status-packing").checked   = false;
            document.getElementById("status-pendiente").checked = false;
            document.getElementById("status-facturado").checked = false;
            document.getElementById("status-retenido").checked  = false;
            document.getElementById("status-anulado").checked   = false;
        }else{
            document.getElementById("status-todos").checked = false;
        }
        opcion = null;
    };

    
    /*************************************************************************************
    *                           FILTRO PRIORIDAD
    **************************************************************************************/
    function filtro_estatus(opcion){
        if (opcion == 999){
            document.getElementById("estatus-activo").checked  = false;
        }else{
            document.getElementById("estatus-todos").checked = false;
        }
        opcion = null;
    };
    
    /*************************************************************************************
    *                             APLICA FILTRO
    **************************************************************************************/
    function aplica_filtro(){
        global_bloque = 1;
        carga_usuarios(global_bloque);
    };
</script>