<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/inv_detalle.css">
    <link rel="stylesheet" type="text/css" href="css/boton-back-top.css">
</head>
<body>
<?php
    require 'config/header_barra_invdetalle.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <button onclick="topFunction()" id="myBtn" title="Sube al principio"><i class="icon-arrow-up"></i></button>
        <div style="display:flex;">
            <h4 id="titulo-pantalla" class='titulos_categorias'>Toma Física de Inventario Nro.</h4>
        </div>
        <div id="detalle-lista">
            <table id="detalle-tabla" cellspacing="0">
                <thead>
                    <tr class="tabla-header">
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Existencia</th>
                        <th style="width: clamp(75px,85px,100px);">conteo 1</th>
                        <th style="width: clamp(75px,85px,100px);">conteo 2</th>
                        <th>Resultado</th>
                        <th style="width: clamp(75px,85px,100px);">Conteo 3</th>
                        <th>Contador3</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="detalle-body">
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
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Estatus (Solo conteo 1 y 2):</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_conteo(999);" type="checkbox" id="detalle-todos" name="detalle-todos" checked>
                    <label class="form-check-label" for="detalle-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="detalle-pendientes">Pendientes</label>
                    <input class="form-check-input" onchange="filtro_conteo(0);" type="checkbox" id="detalle-pendientes" name="detalle-pendientes">
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="detalle-culminados">Culminados</label>
                    <input class="form-check-input" onchange="filtro_conteo(1);" type="checkbox" id="detalle-culminados" name="detalle-culminados">
                </div>
                <hr>
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Con Diferencia entre conteo 1 y 2:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_difconteo(999);" type="checkbox" id="difconteo-todos" name="difconteo-todos" checked>
                    <label class="form-check-label" for="difconteo-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="difconteo-diferencia">Con Diferencias</label>
                    <input class="form-check-input" onchange="filtro_difconteo(0);" type="checkbox" id="difconteo-diferencia" name="difconteo-diferencia">
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="difconteo-iguales">Sin Diferencia o iguales</label>
                    <input class="form-check-input" onchange="filtro_difconteo(1);" type="checkbox" id="difconteo-iguales" name="difconteo-iguales">
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
                <form method="post" id="detalle-form" onsubmit="submit_edit_detalle(event)">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="ConteoModalLabel">SKU&nbsp</h5>
                        <input id="frm_idproducto" type="text" name="frm_idproducto" class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2">
                        <div id="inventario-producto-detalle">
                            <div id="frm_nombre" style="width:100%;"></div>
                            <div class="container-fluid p-0 m-0">
                                <input id="frm_idinventario" type="hidden" name="frm_idinventario" autocomplete="off">
                                <div class="container-linea m-0 mb-1 p-0">
                                    <div class="container-campo">
                                        <label class="label-campo" for="frm_conteo1" style="text-align:right;">Conteo 1:</label>
                                        <input type="text" id="frm_conteo1" inputmode="decimal" type="text" name="frm_conteo1" style="width:clamp(4rem,6rem,7rem);" autocomplete="off" readonly>
                                    </div>
                                    <div class="container-campo select-usr">
                                        <!-- <label class="label-campo" for="frm_username1" style="text-align:right;">Contador 1:</label> -->
                                        <select id="frm_username1" name="frm_username1" class="form-select contadores" placeholder="Contador..." aria-label="Contador o usuario asignado">
                                            <option value="">sin Contador</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="container-linea mb-1 p-0">
                                    <div class="container-campo">
                                        <label class="label-campo" for="frm_conteo2" style="text-align:right;">Conteo 2:</label>
                                        <input type="text" id="frm_conteo2" inputmode="decimal" type="text" name="frm_conteo2" style="width:clamp(4rem,6rem,7rem);" autocomplete="off" readonly>
                                    </div>
                                    <div class="container-campo select-usr">
                                        <!-- <label class="label-campo" for="frm_username2" style="text-align:right;">Contador 2:</label> -->
                                        <select id="frm_username2" name="frm_username2" class="form-select contadores" placeholder="Contador..." aria-label="Contador o usuario asignado">
                                            <option value="">sin Contador</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="container-linea p-0 mb-1">
                                    <div class="container-campo">
                                        <label class="label-campo" for="frm_conteo3" style="text-align:right;">Conteo 3:</label>
                                        <input type="text" id="frm_conteo3" inputmode="decimal" type="text" name="frm_conteo3" style="width:clamp(4rem,6rem,7rem);" autocomplete="off" readonly>
                                    </div>
                                    <div class="container-campo select-usr">
                                        <!-- <label class="label-campo" for="frm_username3" style="text-align:right;">Contador 3:</label> -->
                                        <select id="frm_username3" name="frm_username3" class="form-select contadores" placeholder="Contador..." aria-label="Contador o usuario asignado">
                                            <option value="">sin Contador</option>
                                        </select>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex flex-nowrap">
                                    <h6>Ubicaciones Alternas</h6>
                                </div>
                                <div id="ubicaciones-alternas" class="p-0">
                                </div>
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
                        <button type="submit" form="detalle-form" class="btn btn-primary">Guardar</button> 
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
    const search_url = new URLSearchParams(window.location.search);
    const global_idinventario = search_url.get('id');
    const spinner = document.getElementById("spinner");
    const myModal = new bootstrap.Modal(document.getElementById("ConteoModal"),{focus:true});
    var global_bloque = 1;
    var ConteoModal = document.getElementById('ConteoModal');
    document.getElementById("titulo-pantalla").innerText = 'Toma Física de Inventario Nro.'+global_idinventario;
    carga_contadores(global_idinventario);
    carga_detalle(global_bloque);

    
    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/inv_procesos.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_detalle(global_bloque);
    };

    /*************************************************************************************
    *                              CARGA DETALLE
    * Carga el detalle del inventario identificado como parametro en el URL.
    **************************************************************************************/
    function carga_detalle(pnbloque){
        spinner.removeAttribute('hidden');
        console.log("pnbloque="+pnbloque);

        let idinventario = global_idinventario;
        let search_text  =  document.getElementById("search-input").value ;

        let formData = {};
        formData = new FormData(document.getElementById('form-search'));
        formData.append("idinventario",idinventario);
        formData.append("accion","inventario-detalle");
        formData.append("bloque",global_bloque);

        let filter = new FormData(document.getElementById('form-filter'));
        for (let pair of filter.entries()) {
            formData.append(pair[0], pair[1]);
        }

        fetch('api/ap_inventarios.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if (pnbloque == 1) {
                document.getElementById("detalle-body").innerHTML = responseJson.html;
            }else{
                document.getElementById("detalle-body").insertAdjacentHTML("beforeend",responseJson.html);
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se pudo cargar lista', text:err.message});
        });
        idinventario = search_text = formData = null;
    };


    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        event.preventDefault();
        global_bloque = 1;
        carga_detalle(global_bloque);
        toggle_barra_search();
    };


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
    * Indica aquellos productos que tengan el conteo 1 y 2 culminado.
    **************************************************************************************/
    function filtro_conteo(opcion){
        if (opcion == 999){
            document.getElementById("detalle-pendientes").checked  = false;
            document.getElementById("detalle-culminados").checked = false;
        }else if (opcion == 0){
            document.getElementById("detalle-todos").checked = false;
            document.getElementById("detalle-culminados").checked = false;
        }else{
            document.getElementById("detalle-todos").checked = false;
            document.getElementById("detalle-pendientes").checked = false;
        }
        opcion = null;
    };   

    /*************************************************************************************
    *                          FILTRO DIFCONTEOS
    * Indica aquellos productos que tengan o no diferencia entre el conteo 1 y 2.
    **************************************************************************************/
    function filtro_difconteo(opcion){
        if (opcion == 999){
            document.getElementById("difconteo-diferencia").checked = false;
            document.getElementById("difconteo-iguales").checked = false;
        }else if (opcion == 0){
            document.getElementById("difconteo-todos").checked = false;
            document.getElementById("difconteo-iguales").checked = false;
        }else{
            document.getElementById("difconteo-todos").checked = false;
            document.getElementById("difconteo-diferencia").checked = false;
        }
        opcion = null;
    };   


    /*************************************************************************************
    *                             APLICA FILTRO
    **************************************************************************************/
    function aplica_filtro(){
        global_bloque = 1;
        carga_detalle(global_bloque);
    };


    /*************************************************************************************
    *                              EDIT DETALLE
    * Se ejecuta al pulsar el botón Editar de la lista de productos
    **************************************************************************************/
    function edit_detalle(idinventario, idproducto){
        document.getElementById("detalle-form").reset();
        document.getElementById("frm_idinventario").value = idinventario;
        document.getElementById("frm_idproducto").value   = idproducto;
        document.getElementById("frm_nombre").textContent = document.getElementById(idproducto).children[1].innerText;

        // HACEMOS FETCH AL PRODUCTO 
        let url = new URL(location.origin + '/wms/api/ap_inventarios.php');
        url.searchParams.append('id', idinventario);
        url.searchParams.append('idproducto', idproducto);
        url.searchParams.append('accion', 'producto-detalle');
        fetch(url,{method:'GET', headers:{'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            document.getElementById("frm_conteo1").value = data.invd_conteo1;
            document.getElementById("frm_conteo2").value = data.invd_conteo2;
            document.getElementById("frm_conteo3").value = data.invd_conteo3;
            document.getElementById("frm_username1").value = data.invd_username1;
            document.getElementById("frm_username2").value = data.invd_username2;
            if (data.invd_username3 == null){
                document.getElementById("frm_username3").value = "";
            }else{
                document.getElementById("frm_username3").value = data.invd_username3;
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
        .then((data)=>{
            let html = genera_html_alternos(data);
            document.getElementById("ubicaciones-alternas").insertAdjacentHTML("beforeend",html);
        }).catch((err) => {
             Swal.fire({icon: 'error', title:'Error buscando ubicaciones alternas', text:err.message});
        });

        document.getElementById("foto-webp").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.webp";
        document.getElementById("foto-jpeg").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.jpg";
        idproducto = url = null;
        myModal.show();
    }


    /**************************************************************************
    *                         GENERA HTML ALTERNOS
    * Genera lista html de ubicaciones alternas
    ***************************************************************************/
    function genera_html_alternos(data){
        let html = '';
        if (data.length > 0){
            for (let i = 0; i < data.length; i++) {
                console.log("I="+i);
                html += '<div style="display:flex; flex-wrap:nowrap; justify-content:center; gap:5px;">';
                html += '<input class="form-control" style="width:clamp(15px,5rem,10rem);" type="text" name="alternos-almacen[]" value="'+data[i].prou_almacen+'" disabled>';
                html += '<input class="form-control" style="width:clamp(15px,10rem,15rem);" type="text" name="alternos-ubicacion[]" value="'+data[i].prou_ubicacion+'" disabled>';
                html += '</div>'
            }
        }else{
            html = '<div id="container-msj-error" class="alert alert-danger" style="padding: 0.1rem 1rem;" role="alert">no hay ubicaciones alternas</div>';
        }
        return (html);
    }

    
    /*************************************************************************************
    *                           CARGA CONTADORES
    **************************************************************************************/
    async function carga_contadores(idinventario){
        let url = new URL(location.origin + '/wms/api/ap_inventarios.php');
        url.searchParams.append('id', idinventario);
        url.searchParams.append('accion', 'contadores');

        let html_select1 = document.getElementById("frm_username1");
        let html_select2 = document.getElementById("frm_username2");
        let html_select3 = document.getElementById("frm_username3");

        await fetch(url,{method:'GET', headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            for (const element of responseJson) {
                html_select1.insertAdjacentHTML('beforeend','<option value=\"'+element+'\">'+element+'</option>');
                html_select2.insertAdjacentHTML('beforeend','<option value=\"'+element+'\">'+element+'</option>');
                html_select3.insertAdjacentHTML('beforeend','<option value=\"'+element+'\">'+element+'</option>');
            }
        });
        idinventario = html_select1 = html_select2 = html_select3 = url = null;
    };
    

    /**************************************************************************
    * SUBMIT en Anclaje de Productos al Pedido.
    ***************************************************************************/
    function submit_edit_detalle(event) {
        event.preventDefault();
        spinner.removeAttribute('hidden');

        let formData = new FormData(document.getElementById('detalle-form'));
        let datos = Object.fromEntries(formData);
       
        let url = new URL(location.origin + '/wms/api/ap_inventarios.php');
        url.searchParams.append('idinventario', formData.get("frm_idinventario"));
        url.searchParams.append('idproducto', formData.get("frm_idproducto"));

        fetch(url,{method:'PATCH',body:JSON.stringify(datos),headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            // variable myModalEl se crea al inicio del script de javascript
            if(data.response == 'success'){
                spinner.setAttribute('hidden', '');
                myModal.hide();
                actualiza_lista(datos);
                setInterval(() => {null},1500 * 1);
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
            }else{
                spinner.setAttribute('hidden', '');
                Swal.fire({icon: data.error_tpo, title:'Datos No guardados', text:data.error_msj});
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Datos No guardados', text:err.message});
        });

        formData = url = null;
    }

    /**************************************************************************
    *                         ACTUALIZA LISTA
    * Actualiza los datos de la lista luego de una actualización de base
    * de datos existosa.
    ***************************************************************************/
    function actualiza_lista(datos){
        let producto = document.getElementById(datos.frm_idproducto);
        let conteo1 = datos.frm_conteo1;
        let conteo2 = datos.frm_conteo2;

        producto.querySelector('#table-conteo1').innerText = conteo1;
        producto.querySelector('#table-conteo2').innerText = conteo2;
        producto.querySelector('#table-conteo3').innerText = datos.frm_conteo3;
        producto.querySelector('#table-username3').innerText = datos.frm_username3;
        if (conteo1 && conteo2 && conteo1 == conteo2){
            producto.querySelector('#table-resultado').innerHTML ='<i style="color:green;" class="icon-checkmark">';
        }else if(conteo1 && conteo2 && conteo1 !== conteo2){
            producto.querySelector('#table-resultado').innerHTML = '<i style="color:red;" class="icon-cross">';
        }else{
            producto.querySelector('#table-resultado').innerHTML = '&nbsp';
        };
        
        producto = conteo1 = conteo2 = null;

    };
</script>
