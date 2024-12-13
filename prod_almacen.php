<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/prod_almacen.css">
    <link rel="stylesheet" type="text/css" href="css/boton-back-top.css">
</head>
<body>
<?php
    require 'config/header_barra_prod.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <button onclick="topFunction()" id="myBtn" title="Sube al principio"><i class="icon-arrow-up"></i></button>
        <div><h4 class='titulos_categorias'>Productos</h4></div>
        <div id="productos-lista">
            <table id="productos-tabla" cellspacing="0">
                <thead>
                    <tr class="tabla-header">
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Unidad</th>
                        <th>Existencia</th>
                        <th>Ubicacion</th>
                        <th>Ref</th>
                        <th>Marca</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="productos-body">
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
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Existencia:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_existencia(999);" type="checkbox" id="existencia-todos" name="existencia-todos" checked>
                    <label class="form-check-label" for="existencia-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="existencia-con">Con Existencia</label>
                    <input class="form-check-input" onchange="filtro_existencia(0);" type="checkbox" id="existencia-con" name="existencia-con">
                </div>
                <div class="form-check form-switch">
                    <label class="form-check-label" for="existencia-sin">Sin Existencia</label>
                    <input class="form-check-input" onchange="filtro_existencia(1);" type="checkbox" id="existencia-sin" name="existencia-sin">
                </div>
                <hr>
                <div>
                    <button type="button" name="aplicar_fitro" id="aplicar_filtro" data-bs-dismiss="offcanvas" class="btn btn-primary" onclick="aplica_filtro();">Filtrar</button>
                </div>                
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="ProductoModal" tabindex="-1" aria-labelledby="ProductoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="post" id="producto-form">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="ProductoModalLabel">SKU&nbsp</h5>
                        <input id="info-idproducto" type="text" name="info-idproducto" value="error..." class="border-0 col-6" style="font-size:1.25rem;" disabled autocomplete="off">
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
                                    <label for="deposito1-pro" class="label-campo" style="text-align:right;">Existencia:</label>
                                    <input id="info-deposito1-pro" type="text" name="deposito1-pro" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>

                                <div class="container-campo">
                                    <label for="disponible" class="label-campo" style="text-align:right;">Disponible:</label>
                                    <input id="info-disponible" type="text" name="disponible" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                            </div>    
                            <div class="container-linea mb-1 p-0">
                                <div class="container-campo">
                                    <label for="deposito2-pro" class="label-campo" style="text-align:left;">Deposito 2:</label>
                                    <input id="info-deposito2-pro" type="text" name="deposito2-pro" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>

                                <div class="container-campo">
                                    <label for="deposito3-pro" class="label-campo" style="text-align:right;">Deposito 3:</label>
                                    <input id="info-deposito3-pro" type="text" name="deposito3-pro" style="width:clamp(4rem,6rem,7rem);" disabled autocomplete="off">
                                </div>
                            </div>    
                            <div class="container-linea p-0 mb-1">
                                <div class="container-campo">
                                    <label for="ubicacion" class="label-campo" style="text-align:right;">Ubicación:</label>
                                    <input id="info-ubicacion" type="text" name="ubicacion" autocomplete="off" style="width:10rem;" maxlength="20" autocomplete="off">
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
                    <div id="ProductoModalFoto" class="d-flex justify-content-center">
                        <picture>
                            <source id="foto-webp" type="image/webp" srcset="" alt="foto-producto">
                            <source id="foto-jpeg" type="image/jpeg" srcset="" alt="foto-producto">
                            <img class="img-fluid" src="">
                        </picture>
                    </div>
                    <div class="modal-footer">
                        <button type="button" form="producto-form" onclick="submit_edit_producto(event)" class="btn btn-primary">Guardar</button> 
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
    const myModal = new bootstrap.Modal(document.getElementById("ProductoModal"),{focus:true});    
    var global_bloque = 1;
    var global_ubicaciones = 0;
    var ProductoModal = document.getElementById('ProductoModal');
    //carga_productos(global_bloque);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_productos(global_bloque);
    };

    /*************************************************************************************
    *                              CARGA PEDIDOS
    **************************************************************************************/
    function carga_productos(pnbloque){
        spinner.removeAttribute('hidden');
        let search_text =  document.getElementById("search-input").value ;
        let formData = {};
        if (search_text.trim().length==0) {
            formData = new FormData();
            formData.append("search-text","");
            formData.append("search-options","todos");
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
            formData.append("lista",'productos-lista');
        }else{
            formData = new FormData(document.getElementById('form-search'));
            formData.append("accion","barra-search");
            formData.append("bloque",global_bloque);
            formData.append("lista",'productos-lista');
        }

        let filter = new FormData(document.getElementById('form-filter'));
        for (let pair of filter.entries()) {
            formData.append(pair[0], pair[1]);
        }

        fetch('api/ap_productos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if (pnbloque == 1) {
                document.getElementById("productos-body").innerHTML = responseJson.html_lista;
            }else{
                document.getElementById("productos-body").insertAdjacentHTML("beforeend",responseJson.html_lista);
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
        carga_productos(global_bloque);
        toggle_barra_search();
    };


    /*************************************************************************************
    *                              EDIT PRODUCTO
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function edit_productos(idproducto){
        document.getElementById("producto-form").reset();
        
        document.getElementById("info-idproducto").value = idproducto;
        document.getElementById("info-nombre").textContent = document.getElementById(idproducto).children[1].innerText;
        document.getElementById("info-unidad").value = document.getElementById(idproducto).children[2].innerText;
        let existencia ;
        let deposito1_pro ;

        /* HACEMOS FETCH AL PRODUCTO */
        let url = new URL(location.origin + '/wms/api/ap_productos.php');
        url.searchParams.append('idproducto', idproducto);
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                document.getElementById("info-empaque").value     = data.empaque;
                document.getElementById("info-bulto").value       = data.bulto;
                document.getElementById("info-deposito1-pro").value  = data.deposito1_pro;
                document.getElementById("info-deposito2-pro").value  = data.deposito2_pro;
                document.getElementById("info-deposito3-pro").value  = data.deposito3_pro;
                document.getElementById("info-codigobarra").value = data.codigobarra;
                document.getElementById("info-ubicacion").value   = data.ubicacion;
                document.getElementById("info-ref").value         = data.referencia;
                deposito1_pro = +data.deposito1_pro;
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Error buscando producto',text: data.error_msj});
            }
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'Error buscando datos de producto', text:err.message});
        });

        /* Ahora buscamos la cantidad de ese producto que se encuentra anclada */
        async function AsyncAnclados(){
            let url = new URL(location.origin + '/wms/api/ap_picking.php');
            url.searchParams.append('idproducto', idproducto);
            let response = await fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}});
            let data = await response.json();
            document.getElementById("info-disponible").value  = deposito1_pro - +data.anclados;
            // anclados = data.anclados;
            // response = data = null;
        };

        AsyncAnclados();

        /* Buscamos ubicaciones adicionales */
        url = new URL(location.origin + '/wms/api/ap_productos.php');
        url.searchParams.append('idproducto', idproducto);
        url.searchParams.append('accion', 'html-ubicaciones');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                global_ubicaciones = data.cant_ubica;
                document.getElementById("ubicaciones-prod").innerHTML = data.html;
            }
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'Error buscando datos de producto', text:err.message});
        });

        document.getElementById("foto-webp").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.webp";
        document.getElementById("foto-jpeg").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.jpg";
        idproducto = existencia = url = null;
        myModal.show();
        document.getElementById("info-codigobarra").focus();
    }

    /**************************************************************************
    * SUBMIT guardar los campos editados
    ***************************************************************************/
    function submit_edit_producto(event){
        event.preventDefault();
        spinner.removeAttribute('hidden');
        
        let url = new URL(location.origin + '/wms/api/ap_productos.php');
        let formData = new FormData(document.getElementById('producto-form'));
        formData.append("accion","producto-ubicacion");
        formData.append("idproducto",document.getElementById('info-idproducto').value);

        fetch(url,{method:'POST',body:formData})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                myModal.hide();
                spinner.setAttribute('hidden', '');
                setInterval(() => {modal.hide();},1500 * 1);
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
            }else{
                spinner.setAttribute('hidden', '');
                Swal.fire({icon: data.error_tpo, title:'Datos No guardados', text:data.error_msj});
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Error al guardar', text:err.message});
        });
        formData = url = null;
    }

    /**************************************************************************
    *                           ADD MORE PLACES
    * Agrega filas de ubicaciones
    ***************************************************************************/
    function add_more_places(){
        let html = '';
        global_ubicaciones = global_ubicaciones + 1;
        html  = '<div id="ubicacion-'+global_ubicaciones+'" class="p-0 mb-1" style="display:flex; gap:10px;justify-content:center;">';
        html += '<div style="width:clamp(100px, 35%, 160px);">';
        html += '<select class="form-select" aria-label="Número de almacén" name="almacen2[]">';
        html += '<option selected value="">Almacén...</option>';
        html += '<option value="01">01</option>';
        html += '<option value="02">02</option>';
        html += '</select>';
        html += '</div>';
        html += '<div style="width:clamp(100px, 40%,160px);">';
        // html += '<label for="ubicacion" style="text-align:right;">Ubicación:</label>';
        html += '<input id="info-ubicacion2" class="form-control" type="text" name="ubicacion2[]" autocomplete="off" placeholder="Ubicación">';
        html += '</div>';
        html += '<div style="width:clamp(100px, 25%,120px);">';
        // html += '<label for="cantidad2" style="text-align:right;">Cantidad:&nbsp</label>';
        html += '<input id="info-cantidad2" class="form-control" type="text" name="cantidad2[]" autocomplete="off" style="text-align:right;" placeholder="Cantidad">';
        html += '</div>';        
        html += '<button type="button" onclick="del_places('+global_ubicaciones+');" name="add_places" id="add_places" style="border:none;background-color:white;width:40px;"><i style="color:red;" class="icon-minus-circle"></i></button>';
        // html += '<input id="info-activado2" class="form-control" type="hidden" name="activado2[]" value="1">';
        html += '<input id="info-accion2" class="form-control" type="hidden" name="accion2[]" value="INSERTADO">';
        html += '</div>';
        document.getElementById("ubicaciones-prod").insertAdjacentHTML("beforeend",html);
        html = null;
    }


    /**************************************************************************
    *                           DEL PLACES
    * Elimina Filas de ubicaciones
    ***************************************************************************/
    function del_places(linea){
        //document.getElementById("ubicacion-"+linea).remove();
        let tipo_registro = document.getElementById("ubicacion-"+linea).querySelector('#info-accion2');
        if (tipo_registro.value == 'SINCAMBIOS' || tipo_registro.value == 'MODIFICADO'){
            tipo_registro.value = 'ELIMINADO';
            document.getElementById("ubicacion-"+linea).style.display = 'none';
        }else{
            document.getElementById("ubicacion-"+linea).remove();
        }
        tipo_registro = null;
    }


    /**************************************************************************
    * Se ejecuta al mostrarse una ventana modal
    ***************************************************************************/
    ProductoModal.addEventListener('shown.bs.modal', function () {
        document.getElementById("info-codigobarra").focus();
    })

    /**************************************************************************
    * Se ejecuta al esconderse una ventana modal
    ***************************************************************************/
    ProductoModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById("foto-webp").srcset = "";
        document.getElementById("foto-jpeg").srcset = "";
        document.getElementById("ubicaciones-prod").innerHTML = "";
    })

    /*************************************************************************************
    *                          FILTRO EXISTENCIA
    **************************************************************************************/
    function filtro_existencia(opcion){
        if (opcion == 999){
            document.getElementById("existencia-con").checked  = false;
            document.getElementById("existencia-sin").checked = false;
        }else if (opcion == 0){
            document.getElementById("existencia-todos").checked = false;
            document.getElementById("existencia-sin").checked = false;
        }else{
            document.getElementById("existencia-todos").checked = false;
            document.getElementById("existencia-con").checked = false;
        }
        opcion = null;
    };   
    
    /*************************************************************************************
    *                             APLICA FILTRO
    **************************************************************************************/
    function aplica_filtro(){
        global_bloque = 1;
        carga_productos(global_bloque);
    };
    

    /**************************************************************************
    *                      CHANGE DETALLE UBICACIONES
    * Cambia el valor del input "accion" para determinar que esa linea
    * tuvo cambios y si se pulsa guardar se debe hacer UPDATE a ese registro
    ***************************************************************************/
    function change_detalle_ubicaciones(linea){
        let accion = document.getElementById("ubicacion-"+linea).querySelector('#info-accion2');
        /* Este if se coloca para que no haga un update cuando es insertado un nuevo producto
           es necesario colocar en el nuevo registro la cantidad requerida en la tarea. Esto
           debe obviarse y que el programa traiga todo y no permita modificar datos*/
        if (accion.value == 'SINCAMBIOS'){
            accion.value = 'MODIFICADO';
        }
        accion = linea = null;
    };

</script>