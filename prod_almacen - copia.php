<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/prod_almacen.css">
</head>
<body>
<?php
    require 'config/header_barra_prod.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h3 class='titulos_categorias'>Productos</h3></div>
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
                            <div class="container-fluid m-0 mb-1 p-0">
                                <label for="unidad" class="col-2" style="text-align:right;">Unidad:</label>
                                <input type="text" id="info-unidad" class="col-2" type="text" name="unidad" disabled autocomplete="off">
                                <label for="codigobarra" class="col-3" style="text-align:right;">Cod.Barra:</label>
                                <input type="text" id="info-codigobarra" class="col-4" type="text" name="codigobarra" autocomplete="off" autofocus>
                            </div>
                            <div class="container-fluid mb-1 p-0">
                                <label for="empaque" class="col-3" style="text-align:right;">Empaque:</label>
                                <input type="text" id="info-empaque" class="col-2" type="text" name="empaque" disabled autocomplete="off">
                                <label for="bulto" class="col-2" style="text-align:right;">Bulto:</label>
                                <input type="text" id="info-bulto" class="col-3" type="text" name="bulto" disabled autocomplete="off">
                            </div>
                            <div class="container-fluid mb-1 p-0">
                                <label for="existencia" class="col-3" style="text-align:right;">Existencia:</label>
                                <input id="info-existencia" class="col-2" type="text" name="existencia" disabled autocomplete="off">
                                <label for="disponible" class="col-2" style="text-align:right;">Disponible:</label>
                                <input id="info-disponible" class="col-3" type="text" name="disponible" disabled autocomplete="off">
                            </div>    
                            <div class="container-fluid p-0 mb-1">
                                <label for="ubicacion" class="col-2" style="text-align:right;">Ubicación:</label>
                                <input id="info-ubicacion" class="col-3" type="text" name="ubicacion" autocomplete="off" autocomplete="off">
                                <label for="ref" class="col-2" style="text-align:right;">Ref:</label>
                                <input id="info-ref" class="col-3" type="text" name="ref" disabled autocomplete="off">
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

<script type = "text/JavaScript">
    const spinner = document.getElementById("spinner");
    global_bloque = 1;
    global_ubicaciones = 0;
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
        console.log("pnbloque="+pnbloque);

        let search_text =  document.getElementById("search-input").value ;

        if (search_text.trim().length==0) {
            var formData = new FormData();
            formData.append("search-text","");
            formData.append("search-options","todos");
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
            formData.append("lista",'productos-lista');
        }else{
            var formData = new FormData(document.getElementById('form-search'));
            formData.append("accion","barra-search");
            formData.append("bloque",global_bloque);
            formData.append("lista",'productos-lista');
        }

        fetch('api/ap_productos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("productos-body").insertAdjacentHTML("beforeend",responseJson.html_lista);
            spinner.setAttribute('hidden', '');
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se pudo cargar lista', text:err.message});
        });
        //delete element, css_styles, css_opacity,formData;
        delete search_text, formData;
    };


    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        event.preventDefault();
        spinner.removeAttribute('hidden');

        global_bloque = 1;
        var formData = new FormData(document.getElementById('form-search'));
        formData.append("accion", "barra-search");
        formData.append("bloque", global_bloque);
        formData.append("lista", 'productos-lista');

        fetch('api/ap_productos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            toggle_barra_search();
            document.getElementById("productos-body").innerHTML = responseJson.html_lista;
            spinner.setAttribute('hidden', '');
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Error al buscar productos', text:err.message});
        });
        delete formData;
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

        /* HACEMOS FETCH AL PRODUCTO */
        url = new URL(location.origin + '/wms/api/ap_productos.php');
        url.searchParams.append('idproducto', idproducto);
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                document.getElementById("info-empaque").value     = data.empaque;
                document.getElementById("info-bulto").value       = data.bulto;
                document.getElementById("info-existencia").value  = data.existencia;
                document.getElementById("info-codigobarra").value = data.codigobarra;
                document.getElementById("info-ubicacion").value   = data.ubicacion;
                document.getElementById("info-ref").value         = data.referencia;
                existencia = data.existencia;
                delete response, data;
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Error buscando producto',text: data.error_msj});
            }
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'Error buscando datos de producto', text:err.message});
        });

        /* Ahora buscamos la cantidad de ese producto que se encuentra anclada */
        async function AsyncAnclados(){
            var url = new URL(location.origin + '/wms/api/ap_picking.php');
            url.searchParams.append('idproducto', idproducto);
            const response = await fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}});
            var data = await response.json();
            document.getElementById("info-disponible").value  = existencia - data.anclados;
            // anclados = data.anclados;
            delete response, data;
        };

        AsyncAnclados();

        /* Buscamos ubicaciones adicionales */
        url = new URL(location.origin + '/wms/api/ap_productos.php');
        url.searchParams.append('idproducto', idproducto);
        url.searchParams.append('accion', 'carga_ubicaciones');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                global_ubicaciones = data.cant_ubica;
                document.getElementById("ubicaciones-prod").innerHTML = data.html_ubica;
            }
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'Error buscando datos de producto', text:err.message});
        });

        document.getElementById("foto-webp").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.webp";
        document.getElementById("foto-jpeg").srcset = "http://192.168.1.8/biblioteca-fotos-web/fotos-300/"+idproducto+"-1-lefre-sm.jpg";
        delete idproducto, existencia, url;
        var myModal = new bootstrap.Modal(document.getElementById("ProductoModal"),{focus:true});
        myModal.show();
        document.getElementById("info-codigobarra").focus();
        delete myModal;
    }

    /**************************************************************************
    * SUBMIT para añadir productos a la caja activa
    ***************************************************************************/
    function submit_edit_producto(event){
        event.preventDefault();
        spinner.removeAttribute('hidden');
        
        var url = new URL(location.origin + '/wms/api/ap_productos.php');
        var formData = new FormData(document.getElementById('producto-form'));
        formData.append("accion","producto-ubicacion");
        formData.append("idproducto",document.getElementById('info-idproducto').value);

        fetch(url,{method:'POST',body:formData})
        .then((response) => response.json())
        .then((data) => {
            if(data.response == 'success'){
                const myModalEl = document.getElementById('ProductoModal'); // relatedTarget
                const modal     = bootstrap.Modal.getOrCreateInstance(myModalEl);
                modal.hide();
                spinner.setAttribute('hidden', '');
                setInterval(() => {modal.hide();},1500 * 1);
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                // delete idpicking, idproducto, requerido, cantidad, data, url, x, myModalEl, modal;
                delete myModalEl, modal;
            }else{
                spinner.setAttribute('hidden', '');
                Swal.fire({icon: data.error_tpo, title:'Datos No guardados', text:data.error_msj});
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Error al guardar', text:err.message});
        });
        delete formData, url;
    }

    /**************************************************************************
    *                           ADD MORE PLACES
    * Agrega filas de ubicaciones
    ***************************************************************************/
    function add_more_places(){
        global_ubicaciones = global_ubicaciones + 1;
        html  = '<div id="ubicacion-'+global_ubicaciones+'" class="p-0 mb-1" style="display:flex; gap:10px;justify-content:center;">';
        html += '<div style="width:clamp(100px, 35%, 160px);">';
        html += '<select class="form-select" aria-label="Default select example" name="almacen2[]">';
        html += '<option selected>Selecciona...</option>';
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
        html += '<input id="info-activado2" class="form-control" type="hidden" name="activado2[]" value="1">';
        html += '<input id="info-accion2" class="form-control" type="hidden" name="accion2[]" value="INSERTADO">';
        html += '</div>';
        document.getElementById("ubicaciones-prod").insertAdjacentHTML("beforeend",html);
        delete html;
    }

    /**************************************************************************
    *                           DEL PLACES
    * Elimina Filas de ubicaciones
    ***************************************************************************/
    function del_places(linea){
        //document.getElementById("ubicacion-"+linea).remove();
        document.getElementById("ubicacion-"+linea).querySelector('#info-accion2').value = 'ELIMINADO';
        document.getElementById("ubicacion-"+linea).style.display = 'none';
    }

    /**************************************************************************
    * Se ejecuta al mostrarse una ventana modal
    ***************************************************************************/
    ProductoModal.addEventListener('shown.bs.modal', function () {
        document.getElementById("info-codigobarra").focus();
        console.log("mostrando modal");
    })

    /**************************************************************************
    * Se ejecuta al esconderse una ventana modal
    ***************************************************************************/
    ProductoModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById("foto-webp").srcset = "";
        document.getElementById("foto-jpeg").srcset = "";
        document.getElementById("ubicaciones-prod").innerHTML = "";
        console.log("cerrando modal");
    })
</script>