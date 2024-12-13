<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/inv_conteos.css">
    <link rel="stylesheet" type="text/css" href="css/boton-back-top.css">
</head>
<body>
<?php
    require 'config/header_barra_prod.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <button onclick="topFunction()" id="myBtn" title="Sube al principio"><i class="icon-arrow-up"></i></button>
        <div><h4 class='titulos_categorias'>Conteo Físico de Inventario</h4></div>
        <div id="conteos-lista">
            <table id="conteos-tabla" cellspacing="0">
                <thead>
                    <tr class="tabla-header">
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Unidad</th>
                        <th>Ubicacion</th>                        
                        <th>Conteo</th>
                        <th>Marca</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="conteos-body">
                </tbody>
            </table>
        </div>
        <div id="div_boton_mas" class="my-3">
            <button type="button" name="lista_carga_mas" id="lista_carga_mas" class="btn btn-primary form-control" onclick="load_more();">Ver más</button>
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
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Estatus:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_conteo(999);" type="checkbox" id="conteo-todos" name="conteo-todos" checked>
                    <label class="form-check-label" for="conteo-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="conteo-pendientes">Pendientes</label>
                    <input class="form-check-input" onchange="filtro_conteo(0);" type="checkbox" id="conteo-pendientes" name="conteo-pendientes">
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="conteo-culminados">Culminados</label>
                    <input class="form-check-input" onchange="filtro_conteo(1);" type="checkbox" id="conteo-culminados" name="conteo-culminados">
                </div>
                <hr>
                <div>
                    <button type="button" name="aplicar_fitro" id="aplicar_filtro" data-bs-dismiss="offcanvas" class="btn btn-primary" onclick="aplica_filtro();">Filtrar</button>
                </div>                
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="ConteoModal" tabindex="-1" aria-labelledby="ConteoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="post" id="conteo-form" onsubmit="submit_edit_conteo(event)">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="ConteoModalLabel">SKU&nbsp</h5>
                        <input id="info-idproducto" type="text" name="info-idproducto" class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2">
                        <div id="inventario-producto-detalle">
                            <div id="info-nombre" style="width:100%;"></div>
                            <div class="container-fluid p-0 m-0">
                                <input id="info-idinventario" type="hidden" name="info-idinventario" autocomplete="off">
                                <input id="info-tpoconteo" type="hidden" name="info-tpoconteo" autocomplete="off">
                                <div class="container-linea m-0 mb-1 p-0">
                                    <div class="container-campo">
                                        <label class="label-campo" for="unidad" style="text-align:right;">Unidad:</label>
                                        <input type="text" id="info-unidad" type="text" name="unidad" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                    </div>
                                    <div class="container-campo">
                                        <label class="label-campo" for="codigobarra" style="text-align:right;">Cod.Barra:</label>
                                        <input type="text" id="info-codigobarra" type="text" name="codigobarra" style="width:10rem;" disabled autocomplete="off">
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
                                <div class="container-linea p-0 mb-1">
                                    <div class="container-campo">
                                        <label for="ubicacion" class="label-campo" style="text-align:right;">Ubicación:</label>
                                        <input id="info-ubicacion" type="text" name="ubicacion" autocomplete="off" style="width:10rem;" disabled maxlength="20" autocomplete="off">
                                    </div>
                                    <div class="container-campo">
                                        <label for="ref" class="label-campo" style="text-align:right;">Ref:</label>
                                        <input id="info-ref" type="text" name="ref" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex flex-nowrap">
                                    <h6>Ubicaciones Alternas</h6>
                                </div>
                                <div id="ubicaciones-alternas" class="p-0">
                                </div>
                            </div>
                            <div class="number-input mt-1 mb-2">
                                <button type="button" onclick="document.getElementById('info-conteo').stepDown()" class="minus">-</button>
                                <input  id="info-conteo" class="cantidad_conteo" name="info-conteo" inputmode="decimal" type="number" min="0" autocomplete="off">
                                <button type="button" onclick="document.getElementById('info-conteo').stepUp()" class="plus">+</button>
                            </div>                                                    
                        </div>
                        <div id="ConteoModalFoto" class="d-flex justify-content-center">
                            <picture>
                                <source id="foto-webp" type="image/webp" srcset="" alt="foto-producto">
                                <source id="foto-jpeg" type="image/jpeg" srcset="" alt="foto-producto">
                                <img class="img-fluid" src="">
                            </picture>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="conteo-form" class="btn btn-primary">Guardar</button> 
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
    const myModal = new bootstrap.Modal(document.getElementById("ConteoModal"),{focus:true});    
    var global_bloque = 1;
    var global_ubicaciones = 0;
    var ConteoModal = document.getElementById('ConteoModal');
    carga_conteos(global_bloque);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_conteos(global_bloque);
    };


    // var myModalEl = document.getElementById('PackingModal');
    // /* Este es un listener que se ejecuta luego que la ventana Modal Packing Modal
    //    se muestra en pantalla, y luego que se muestra podemos asignar el foco al
    //    elemento que queramos */
    // myModalEl.addEventListener('shown.bs.modal', function (event) {
    //     document.getElementById("info-cantidad").focus();
    // })    
    //var myModalEl = document.getElementById('PackingModal');
    /* Este es un listener que se ejecuta luego que la ventana Modal Packing Modal
       se muestra en pantalla, y luego que se muestra podemos asignar el foco al
       elemento que queramos */
       ConteoModal.addEventListener('shown.bs.modal', function (event) {
        document.getElementById("info-conteo").focus();
    })        
    
    /*************************************************************************************
    *                              CARGA PEDIDOS
    **************************************************************************************/
    function carga_conteos(pnbloque){
        spinner.removeAttribute('hidden');
        console.log("pnbloque="+pnbloque);

        let search_text =  document.getElementById("search-input").value ;

        let formData = {};
        if (search_text.trim().length==0) {
            formData = new FormData();
            formData.append("search-text","");
            formData.append("search-options","todos");
            formData.append("accion","carga-conteos");
            formData.append("bloque",pnbloque);
            // formData.append("lista",'conteos-lista');
        }else{
            formData = new FormData(document.getElementById('form-search'));
            formData.append("accion","carga-conteos");
            formData.append("bloque",global_bloque);
            // formData.append("lista",'conteos-lista');
        }

        let filter = new FormData(document.getElementById('form-filter'));
        for (let pair of filter.entries()) {
            formData.append(pair[0], pair[1]);
        }

        fetch('api/ap_conteos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if (pnbloque == 1) {
                document.getElementById("conteos-body").innerHTML = responseJson.html;
            }else{
                document.getElementById("conteos-body").insertAdjacentHTML("beforeend",responseJson.html);
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se pudo cargar lista', text:err.message});
        });
        search_text = formData = null;
    };


    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        event.preventDefault();
        global_bloque = 1;
        carga_conteos(global_bloque);
        toggle_barra_search();
    };


    /*************************************************************************************
    *                              EDIT CONTEOS
    * Se ejecuta al pulsar el botón Editar de la lista de productos
    **************************************************************************************/
    function edit_conteos(idinventario, idproducto){
        document.getElementById("conteo-form").reset();
        document.getElementById("info-idinventario").value = idinventario;
        document.getElementById("info-tpoconteo").value    = document.getElementById(idproducto).children[6].querySelector('#tpoconteo-'+idproducto).value;
        document.getElementById("info-idproducto").value   = idproducto;
        document.getElementById("info-nombre").textContent = document.getElementById(idproducto).children[1].innerText;
        //document.getElementById("info-conteo").value       = document.getElementById(idproducto).children[4].innerText;

        /* HACEMOS FETCH AL PRODUCTO */
        let url = new URL(location.origin + '/wms/api/ap_productos.php');
        url.searchParams.append('idproducto', idproducto);
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                document.getElementById("info-unidad").value      = data.unidad;
                document.getElementById("info-empaque").value     = data.empaque;
                document.getElementById("info-bulto").value       = data.bulto;
                document.getElementById("info-codigobarra").value = data.codigobarra;
                document.getElementById("info-ubicacion").value   = data.ubicacion;
                document.getElementById("info-ref").value         = data.referencia;
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Error buscando producto',text: data.error_msj});
            }
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'Error buscando datos de producto', text:err.message});
        });


        /* Buscamos ubicaciones adicionales */
        url = new URL(location.origin + '/wms/api/ap_productos.php');
        url.searchParams.append('idproducto', idproducto);
        url.searchParams.append('accion', 'ubicaciones-alternas');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            let html = genera_html_alternos(data);
            document.getElementById("ubicaciones-alternas").insertAdjacentHTML("beforeend",html);
        }).catch((err) => {
             Swal.fire({icon: 'error', title:'Error buscando ubicaciones alternas', text:err.message});
        });


        /* Buscamos el conteo de la tabla inventarios_detalle */
        url = new URL(location.origin + '/wms/api/ap_conteos.php');
        url.searchParams.append('id', idinventario);
        url.searchParams.append('idproducto', idproducto);
        url.searchParams.append('accion', 'producto-conteo');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            document.getElementById("info-conteo").value = data.invd_conteo;
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'Error buscando valor del campo cantidad', text:err.message});
        });

        document.getElementById("foto-webp").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.webp";
        document.getElementById("foto-jpeg").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.jpg";
        idproducto = url = null;
        myModal.show();
//        document.getElementById("info-codigobarra").focus();
    }


    /**************************************************************************
    *                         GENERA HTML ALTERNOS
    * Genera lista html de ubicaciones alternas
    ***************************************************************************/
    function genera_html_alternos(data){
        let html = '';
        if (data.length > 0){
            for (let i = 0; i < data.length; i++) {
                html += '<div style="display:flex; flex-wrap:nowrap; justify-content:center; gap:5px;">';
                html += '<input class="form-control" style="width:clamp(15px,5rem,10rem);" type="text" name="alternos-almacen[]" value="'+data[i].prou_almacen+'" readonly>';
                html += '<input class="form-control" style="width:clamp(15px,10rem,15rem);" type="text" name="alternos-ubicacion[]" value="'+data[i].prou_ubicacion+'" readonly>';
                html += '</div>'
            }
        }else{
            html = '<div id="container-msj-error" class="alert alert-danger" style="padding: 0.1rem 1rem;" role="alert">no hay ubicaciones alternas</div>';
        }
        return (html);
    }

    /**************************************************************************
    * SUBMIT guardar los campos editados
    ***************************************************************************/
    function submit_edit_conteo(event){
        event.preventDefault();
        spinner.removeAttribute('hidden');
        
        let url = new URL(location.origin + '/wms/api/ap_conteos.php');
        let formData = new FormData(document.getElementById('conteo-form'));
        formData.append("accion","save-conteo");
        let idproducto = formData.get("info-idproducto");
        let tabla_cant = document.getElementById(idproducto).children[4];
        fetch(url,{method:'POST',body:formData})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                tabla_cant.textContent = document.getElementById("info-conteo").value;
                spinner.setAttribute('hidden', '');
                myModal.hide();
                setInterval(() => {myModal.hide;},1500 * 1);
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
            }else{
                spinner.setAttribute('hidden', '');
                Swal.fire({icon: data.error_tpo, title:'Datos No guardados', text:data.error_msj});
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Error al guardar', text:err.message});
        });
        formData = url = idproducto = null;
    }

    // /**************************************************************************
    // * Se ejecuta al mostrarse una ventana modal
    // ***************************************************************************/
    // ConteoModal.addEventListener('shown.bs.modal', function () {
    //     document.getElementById("info-codigobarra").focus();
    //     console.log("mostrando modal");
    // })

    /**************************************************************************
    * Se ejecuta al esconderse una ventana modal
    ***************************************************************************/
    ConteoModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById("foto-webp").srcset = "";
        document.getElementById("foto-jpeg").srcset = "";
        document.getElementById("ubicaciones-alternas").innerHTML = "";
        console.log("cerrando modal");
    })

    /*************************************************************************************
    *                          FILTRO CONTEOS
    **************************************************************************************/
    function filtro_conteo(opcion){
        if (opcion == 999){
            document.getElementById("conteo-pendientes").checked  = false;
            document.getElementById("conteo-culminados").checked = false;
        }else if (opcion == 0){
            document.getElementById("conteo-todos").checked = false;
            document.getElementById("conteo-culminados").checked = false;
        }else{
            document.getElementById("conteo-todos").checked = false;
            document.getElementById("conteo-pendientes").checked = false;
        }
        opcion = null;
    };   
    
    /*************************************************************************************
    *                             APLICA FILTRO
    **************************************************************************************/
    function aplica_filtro(){
        global_bloque = 1;
        carga_conteos(global_bloque);
    };
    
</script>