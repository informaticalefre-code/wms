<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-Store">
    <meta http-equiv="Expires" content="0">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/picking_form.css">
</head>
<body>
<?php
    require 'config/barra_save.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <form method="post" id="picking-form-data" autocomplete="off">
            <div style="display:flex;flex-wrap:nowrap;width:100%;align-items:center;">
                <h3 class='titulos_categorias mb-0'>Tarea de Picking&nbsp</h3>
                <input type="text" class="form-control bg-white border-0 p-0 m-0" style="max-width:200px;font-size:1.75rem;" id="input-idpicking" name="input-idpicking" readonly>
            </div>
            <div id="picking-form">
                <div id="picking-master">
                    <input type="hidden" class="form-control" id="input-idpicking" name="input-idpicking" disabled>
                    <div class="mb-2">
                        <input type="text" class="form-control p-1" id="input-cliente" name="input-cliente" disabled>
                        <input type="text" class="form-control p-1" id="input-vendedor" name="input-vendedor" disabled>
                    </div>
                    <div class="row">
                        <div class="col mb-2">
                            <label for="input-idpedido" class="form-label mb-0">Nro. de Pedido</label>
                            <input type="text" class="form-control bg-primary text-white" style="font-weight:bold;" id="input-idpedido" name="input-idpedido" placeholder="Nro. de Pedido" readonly>
                        </div>
                        <div class="col mb-2">
                            <label for="input-fecha" class="form-label  mb-0">Asignado</label>
                            <input type="datetime-local" class="form-control" id="input-fecha" name="input-fecha" placeholder="fecha de tarea">
                        </div>
                        <div class="col mb-2">
                            <label for="input-preparador" class="form-label mb-0">Preparador</label>
                            <select class="form-select" id="input-preparador" name="input-preparador" aria-label="Nombre del preparador">
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-2">
                            <label for="input-status" class="form-label mb-0">Estatus</label>
                            <select class="form-select" id="input-status" name="input-status" aria-label="Estatus de la tarea de picking">
                                <option value="null" selected>Seleccione...</option>
                                <option value="0">Anulado</option>
                                <option value="1">En Proceso</option>
                                <option value="2">Pausado</option>
                                <option value="3">Consolidado</option>
                                <option value="5">Culminado</option>
                            </select>
                        </div>
                        <div class="col mb-2">
                            <label for="input-prioridad" class="form-label mb-0">Prioridad</label>
                            <select class="form-select" id="input-prioridad" name="input-prioridad" aria-label="Prioridad de la tarea Normal o Urgente">
                                <option value="null" selected>Seleccione...</option>
                                <option value="0">Normal</option>
                                <option value="1">Urgente</option>
                            </select>
                        </div>
                        <div class="col mb-2">
                            <label for="input-pista" class="form-label mb-0">Pista</label>
                            <select class="form-select" name="input-pista" id="input-pista" aria-label="Pista de consolidación">
                                <option value="0">Seleccione...</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <!-- <label for="input-observacion" class="form-label">Example textarea</label> -->
                        <textarea class="form-control" id="input-observacion" name="input-observacion" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col mb-2">
                            <label for="input-fecverif" class="form-label mb-0">Fecha Verifcación</label>
                            <input type="datetime-local" class="form-control" id="input-fecverif" name="input-fecverif" placeholder="Nro. de Pista">
                        </div>
                        <div class="col mb-2">
                            <label for="input-userverif" class="form-label mb-0">Verificador</label>
                            <select class="form-select" aria-label="Default select example" id="input-userverif" name="input-userverif">
                                <option hidden value="null" selected>Seleccione...</option>    
                                <option value="jcfreites">jcfreites</option>
                                <option value="usuario1">usuario1</option>
                                <option value="usuario2">usuario2</option>
                            </select>
                        </div>
                    </div>
                </div>
                <hr class="my-2">
                <div id="picking-detalle-header">
                    <h6 class="mb-0">Produtos Requeridos</h6>
                    <button type="button" class="btn btn-info btn-sm" onclick="check_pedido();" name="check_prod" id="check_prod">Valida contra Pedido&nbsp<i class="icon-search"></i></button>
                </div>
                <div class="detalle-row mb-0" style="display:flex; gap:5px; justify-content:center;">
                    <div class="picking-prod-status">
                        <span id="semaforo" class="semaforo"></span>
                    </div>                
                    <div style="text-align:center;">Id. Producto</div>
                    <div style="text-align:center;">Producto</div>
                    <div style="text-align:center;">Requerido</div>
                    <div style="text-align:center;">Anclado</div>
                    <div style="text-align:center;">Verificado</div>
                    <button type="button" style="border:none;background-color:white;width:40px;"><i style="color:white;font-size:1.2rem;" class="icon-minus-circle"></i></button>
                    <input id="detalle-accion" class="form-control" type="hidden">
                </div>
                <div id="picking-detalle">
                </div>
            </div>
        </form>
    </main>
<?php
    require 'config/footer.html';
?>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    spinner.removeAttribute('hidden');
    carga_preparadores();
    carga_picking_data();

    /* Función que direcciona cuando se pulsa el botón de atrás (no confundir con el del navegador)*/
    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/picking_master.php");
    }

    /**************************************************************************
    * CARGA PICKING DATA
    ***************************************************************************/
    function carga_picking_data(){
        // Tomamos el parametro del ID Picking a cargar
        let search_parametro = new URLSearchParams(window.location.search);
        let idpicking = search_parametro.get('idpicking');
        // Hacemos un fetch api mandando el Nro de la Tarea de Picking.
        let url = new URL(location.origin + '/wms/api/ap_picking_data.php');
        url.searchParams.append('idpicking', idpicking);
        url.searchParams.append('accion', 'picking-master');

        carga_picking_master(url);
        
        url.searchParams.set('accion', 'picking-detalle');
        
        carga_picking_detalle(url);

        search_parametro = idpicking = url = null;
    };

    /**************************************************************************
    *                         CARGA PICKING MASTER
    ***************************************************************************/
    function carga_picking_master(purl){
        fetch(purl,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("input-idpicking").value   = responseJson.idpicking;
            document.getElementById("input-cliente").value     = responseJson.cliente;
            document.getElementById("input-vendedor").value    = responseJson.vendedor;
            document.getElementById("input-fecha").value       = responseJson.fecha;
            document.getElementById("input-idpedido").value    = responseJson.idpedido;
            document.getElementById("input-status").value      = responseJson.estatus;
            document.getElementById("input-prioridad").value   = responseJson.prioridad;
            document.getElementById("input-pista").value       = responseJson.pista;
            document.getElementById("input-observacion").value = responseJson.observacion;
            document.getElementById("input-fecverif").value    = responseJson.fecverif;
            document.getElementById("input-userverif").value   = responseJson.userverif;
            document.getElementById("input-preparador").insertAdjacentHTML('afterbegin','<option value="'+responseJson.preparador+'" selected>'+responseJson.preparador+'</option>');
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'No se puede cargar Tarea de Packing', text:err.message});
        });
        purl = null;
    };

    /**************************************************************************
    *                         CARGA PICKING DETALLE
    ***************************************************************************/    
    function carga_picking_detalle(purl){
        fetch(purl,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("picking-detalle").innerHTML = responseJson.html_detalle;
            spinner.setAttribute('hidden', '');
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se puede cargar Detalle de Tarea de Packing'});
        });

        purl = null; 
    };

    /**************************************************************************
    *                           CHECK PEDIDO
    * Compara los productos cargados en la tarea de picking con los 
    * productos del pedido. Mostrando diferencias.
    ***************************************************************************/
    function check_pedido(){
        spinner.removeAttribute('hidden');
        let idpedido = document.getElementById("input-idpedido").value;
        let url = new URL(location.origin + '/wms/api/ap_picking_data.php');
        url.searchParams.append('idpedido', idpedido);
        url.searchParams.append('accion', 'picking-dif');

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("picking-detalle").innerHTML = responseJson.html_detalle;
            spinner.setAttribute('hidden', '');
        });
        idpedido = url = null;
    };


    /**************************************************************************
    *                           CHANGE DETALLE
    * Cambia el valor del input "accion" para determinar que esa linea
    * tuvo cambios y si se pulsa guardar se debe hacer UPDATE a ese registro
    ***************************************************************************/
    function change_detalle(linea){
        let accion = document.getElementById("detalle-"+linea).querySelector('#detalle-accion');
        /* Este if se coloca para que no haga un update cuando es insertado un nuevo producto
           es necesario colocar en el nuevo registro la cantidad requerida en la tarea. Esto
           debe obviarse y que el programa traiga todo y no permita modificar datos*/
        if (accion.value == 'SINCAMBIOS'){
            accion.value = 'MODIFICADO';
        }
        accion = linea = null;
    };


    /**************************************************************************
    *                           DEL DETALLE
    * Borra un producto de la tarea de picking
    ***************************************************************************/
    function borra_detalle(linea){
        let anclados = parseInt(document.getElementById("detalle-"+linea).querySelector('#input-cantidad').value);
        if (anclados > 0){
            Swal.fire({icon: 'warning', text:"No se puede eliminar si tiene productos anclados al pedido."});
        }else{
            document.getElementById("detalle-"+linea).querySelector('#detalle-accion').value = 'ELIMINADO';
            document.getElementById("detalle-"+linea).style.display = 'none';
        }
        anclados = null;
    };

    
    /**************************************************************************
    *                           GUARDAR TAREA
    ***************************************************************************/
    function guardar(){
        spinner.removeAttribute('hidden');
        let url = new URL(location.origin + '/wms/api/ap_picking_data.php');
        let formData = new FormData(document.getElementById('picking-form-data'));
        fetch(url,{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if(responseJson.response == 'success'){
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                setTimeout(() => { document.location.reload(); }, 1500);
            }else{
                Swal.fire({icon: responseJson.error_tpo, title:'Datos No guardados', text:responseJson.error_msj});
            }
        }).catch((err) => {
            Swal.fire({icon: 'error', title:'No se puede guardar Tarea de Packing', text:err.message});
        });
        url = formData = null;
    };


    /*************************************************************************************
    *                             CARGA PREPARADORES
    **************************************************************************************/
    function carga_preparadores(){
        let url  = new URL(location.origin + '/wms/api/ap_preparadores.php');
        url.searchParams.append('accion', 'preparadores');
        
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("input-preparador").insertAdjacentHTML('beforeend',responseJson.html_preparadores);
        });
        url = null;
    };
</script>