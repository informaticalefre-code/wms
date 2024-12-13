<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-Store">
    <meta http-equiv="Expires" content="0">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/picking.css">
</head>
<body>
<?php
    require 'config/barra_save.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div id="picking-header" class="mb-2" style="max-width:1200px">
            <form method="post" id="picking-header-form">
                <section>
                    <div class="d-flex flex-row justify-content-between">
                        <span id="picking-idpedido" style="font-size:100%;" class="badge bg-primary"></span>
                        <input type="text" name="pick_idpicking" id="pick_idpicking" autocomplete="off" readonly>
                        <p id="picking-fecha" class="mb-0"></p>
                    </div>
                    <p id="picking-cliente" class="mb-0"></p>
                    <p id="picking-vendedor" class="mb-0"></p>
                </section>
                <div class="form-group">
                    <label for="pick_observacion" style="font-size:inherit;">Observación</label>
                    <textarea class="form-control p-1" name="pick_observacion" id="pick_observacion" maxlength="100" style="font-size:inherit; padding:1%;" autocomplete="off"></textarea>
                </div>
            </form>
        </div>
        <div id="picking-productos" class="picking-lista-productos" style="max-width:1200px"></div>
    </main>

    <div class="modal fade" id="PickingModal" tabindex="-1" aria-labelledby="PickingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <!-- <form method="post" id="picking-producto-form" onsubmit="submit_pick_producto(event)" autocomplete="off">     -->
                <form method="post" id="picking-producto-form" autocomplete="off">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="PickingModalLabel">SKU&nbsp</h5>
                        <input id="info-idproducto" type="text" value="error..." class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2" id="PickingModalBody">
                        <div id="Picking-Producto-Modal">
                            <div id="info-nombre" style="width:100%;border-bottom: 1px solid darkgray"></div>
                            <div id="PickingModalBody-Info" class="mb-2">
                                <div>
                                    <label for="ubicacion">Ubicacion:</label>
                                    <input id="info-ubicacion" class="pickingModal-info-input" type="text" name="ubicacion" style="width:8rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="unidad">Unidad:</label>
                                    <input id="info-unidad" class="pickingModal-info-input" type="text" name="unidad" style="width:5rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="bulto">Bulto:</label>
                                    <input type="text" id="info-bulto" class="pickingModal-info-input" type="text" name="bulto" style="width:3rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="requerido">Requerido:</label>
                                    <input id="info-requerido" class="pickingModal-info-input" type="text" name="requerido" style="width:3rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="disponible">Disponible:</label>
                                    <input id="info-disponible" class="pickingModal-info-input" type="text" name="disponible" style="width:3rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="referencia">Ref:</label>
                                    <input id="info-referencia" class="pickingModal-info-input" type="text" name="referencia" style="width:5rem;" readonly autocomplete="off">
                                </div>                                
                            </div>
                            <div class="number-input mt-1 mb-2">
                                <button type="button" onclick="document.getElementById('info-cantidad').stepDown()" class="minus">-</button>
                                <input  id="info-cantidad" class="cant_requerido" name="cant_requerido" inputmode="numeric" type="number" min="0"  autocomplete="off">
                                <button type="button" onclick="document.getElementById('info-cantidad').stepUp()" class="plus">+</button>
                            </div>
                            <div id="PickingModalBody-Foto"></div>                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="picking-producto-form" class="btn btn-primary" onclick="submit_pick_producto(event)">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ContainerModal" tabindex="-1" aria-labelledby="ContainerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" id="picking-container-form" autocomplete="off">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="ContainerModalLabel">Cerrar Tarea &nbsp</h5>
                        <input id="picc_idpicking" name="picc_idpicking" type="text" value="error..." class="border-0" style="font-size:1.25rem;width:5rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2" id="PickingModalBody">
                        <div class="mb-2">
                            <label for="pista" class="form-label">Pista</label>
                            <input name="pista" type="number" class="form-control" id="pick_pista" min="1" max="7">
                        </div>
                        <div id="paletas-lista-div">
                            <div id="paletas-lista-title">
                                <h6 style="margin-bottom:0;">Ubicación del Consolidado:</h6>
                                <button type="button" onclick="add_more_paletas();" name="add_palets" id="add_palets" style="border:none;background-color:white;font-size:25px;"><i style="color:green;" class="icon-plus-circle"></i></button>
                            </div>
                            <div class="form-group mb-1">
                                <!-- <label for="reci_idcliente">Ubicación del Consolidado</label> -->
                                <input name="picc_bin[]" id="picc_bin" class="form-control paletas" Placeholder="Contenedor..." required autofocus>
                            </div>
                            <div class="form-group mb-1">
                                <input name="picc_bin[]" id="picc_bin" class="form-control paletas" Placeholder="Contenedor...">
                            </div>
                            <div class="form-group mb-1">
                                <input name="picc_bin[]" id="picc_bin" class="form-control paletas" Placeholder="Contenedor...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- <button type="submit" form="picking-container-form" class="btn btn-primary">Guardar</button> -->
                        <button type="button" form="picking-container-form" class="btn btn-primary" onclick="submit_close_tarea(event)">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
    require 'config/footer.html';
?>

<!-- Contiene las funciones JS solo para uso de esta página-->
<!-- <script type="text/javascript" src="js/picking.min.js"></script> -->
<script type="text/javascript" src="js/picking.js"></script>


<script type = "text/JavaScript">
    const spinner = document.getElementById("spinner");
    var myModalEl = document.getElementById('PickingModal');

    /********************************************************************************/
    var global_picking_productos;
    carga_tarea_picking();
    var barcode = '';
    var interval;

    /* Función que direcciona cuando se pulsa el botón de atrás (no confundir con el del navegador)*/
    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/picking_tareas.php");        
    }

    /*************************************************************************************
    * Función que abre la forma modal para colocar la cantidad que existe de un producto *
    **************************************************************************************/
    function pick_producto(idproducto){
        let cantidad  = document.getElementById("picking-"+idproducto).querySelector('#picd_cantidad').value;
        let requerido = +document.getElementById("picking-"+idproducto).querySelector('#picd_requerido').value;
        document.getElementById("picking-producto-form").reset();
        document.getElementById("info-nombre").textContent = document.getElementById("picking-nombre-pro-"+idproducto).textContent;
        document.getElementById("info-idproducto").value   = idproducto;
        document.getElementById("info-bulto").value        = document.getElementById("picking-"+idproducto).querySelector('#picking-bulto').textContent;
        document.getElementById("info-requerido").value    = requerido;
        document.getElementById("info-referencia").value   = document.getElementById("picking-"+idproducto).querySelector('#picking-ref').value;

        console.log("cantidad:"+cantidad);
        if (cantidad == "" || cantidad == null){
            document.getElementById("info-cantidad").value = requerido;    
        }else{
            document.getElementById("info-cantidad").value = cantidad;
        }

        let existencia = set_producto_form_values(idproducto);
        let foto       = document.getElementById("foto-"+idproducto).innerHTML;
        let ModalFoto  = document.getElementById("PickingModalBody-Foto");
        // foto = foto.replace("fotos-100","fotos-300");
        // foto = foto.replace("-lefre-th.","-lefre-sm.");
        ModalFoto.innerHTML = foto;
        let myModal = new bootstrap.Modal(myModalEl,{});

        async function AsyncAnclados(){
            let url = new URL(location.origin + '/wms/api/ap_picking.php');
            url.searchParams.append('idproducto', idproducto);
            const response = await fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}});
            let data = await response.json();
            document.getElementById("info-disponible").value = existencia - data.anclados;
            delete url, response, data;
        };

        AsyncAnclados();
        spinner.setAttribute('hidden', '');
        delete existencia, foto, ModalFoto;
        myModal.show();
    }

    /**************************************************************************
    * Abre la ventana modal con los datos referenciales del producto 
    * que se procede anclar al pedido
    ***************************************************************************/
    function carga_tarea_picking(){
        spinner.removeAttribute('hidden');

        // Tomamos el parametro del ID Picking a cargar
        const search_parametro = new URLSearchParams(window.location.search);
        const idpicking = search_parametro.get('id_picking');
        // Hacemos un fetch api mandando el Nro de la Tarea de Picking.
        let url = new URL(location.origin + '/wms/api/ap_picking.php');
        url.searchParams.append('idpicking', idpicking);
        url.searchParams.append('accion', 'picking-tarea');

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("pick_idpicking").value         = responseJson.idpicking;
            document.getElementById("pick_pista").value             = responseJson.pista;
            document.getElementById("picking-idpedido").innerText   = responseJson.idpedido;
            document.getElementById("picking-fecha").innerText      = responseJson.fecha;
            document.getElementById("picking-cliente").innerText    = responseJson.cliente;
            document.getElementById("picking-vendedor").innerText   = responseJson.vendedor;
            document.getElementById("pick_observacion").value       = responseJson.observacion;
            document.getElementById("picking-productos").innerHTML  = responseJson.html_lista;
            global_picking_productos = responseJson.picking_productos;
            spinner.setAttribute('hidden', '');
            
            // Ahora vamos a buscar la pista donde se colocará el consolidado.
            let url_pista = new URL(location.origin + '/wms/api/ap_picking_pista.php');
            url_pista.searchParams.append('idvendedor', responseJson.idvendedor);

            /* Si la pista es igual a cero quiere decir que no se ha cerrado el picking.
               y le asignamos pista según la zona del vendedor. Si tiene un valor distinto
               a cero entonces dejamos el que trae de la tabla Tpicking_pistas.*/
            if (responseJson.pista == 0){
                fetch(url_pista,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
                .then((response) => response.text())
                .then((responseText) => {
                    document.getElementById("pick_pista").value = responseText;
                });
                delete url_pista;
            }
            delete response, responseJson;
        });

        // Ahora buscamos las paletas donde se encuentra el pedido consolidado.
        url.searchParams.set('accion', 'picking-bins');

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            let paletas = JSON.parse(responseJson.paletas);
            let paletas_inputs = document.getElementsByClassName("paletas");
            for(let i = 0; i < paletas.length; i++){ 
                paletas_inputs[i].value = paletas[i];
            }
            delete paletas, paletas_inputs;
        });
        delete search_parametro, idpicking, url;
    }

    
    /**************************************************************************
    * Asigna los correspondientes valores según el producto seleccionado a los
    * inputs de la ventana modal de detalle de picking del producto.
    ***************************************************************************/
    function set_producto_form_values(idproducto){
        // global_picking_productos: array global(codigo_producto,codigo_barra, ubicacion, existencia,requerido, cantidad, unidad, cantverif)
        var existencia = 0;
        for(var i in global_picking_productos){
            if (Object.values(global_picking_productos[i]).indexOf(idproducto) >= 0){
                existencia = Object.values(global_picking_productos[i])[3];
                document.getElementById("info-ubicacion").value = Object.values(global_picking_productos[i])[2];
                document.getElementById("info-unidad").value    = Object.values(global_picking_productos[i])[6];
            }
        }
        return (existencia);
    }


    /**************************************************************************
    * SUBMIT en Anclaje de Productos al Pedido.
    ***************************************************************************/
    function submit_pick_producto(event) {
        console.log("submit-pick-producto");
        event.preventDefault();
        spinner.removeAttribute('hidden');
        idpicking  = document.getElementById("pick_idpicking").value;
        idproducto = document.getElementById("info-idproducto").value;
        requerido  = +document.getElementById("info-requerido").value;
        cantidad   = +document.getElementById("info-cantidad").value;
        ubicacion  = document.getElementById("info-ubicacion").value;
        data = {idproducto:idproducto, accion:"picking-producto", requerido:requerido, cantidad:cantidad,ubicacion:ubicacion};

        if (cantidad <= requerido){
            let url = new URL(location.origin + '/wms/api/ap_picking.php');
            url.searchParams.append('idpicking', idpicking);

            let x = fetch(url,{method:'PATCH',body:JSON.stringify(data),headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((data) => {
                // variable myModalEl se crea al inicio del script de javascript
                const modal     = bootstrap.Modal.getOrCreateInstance(myModalEl);
                if(data.response == 'success'){
                    spinner.setAttribute('hidden', '');
                    setInterval(() => {modal.hide();},1500 * 1);
                    Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                    document.getElementById("picking-"+idproducto).querySelector('#picd_cantidad').value = cantidad;
                    document.getElementById("picking-"+idproducto).querySelector('#semaforo').className  = data.semaforo;
                }else{
                    spinner.setAttribute('hidden', '');
                    Swal.fire({icon: data.error_tpo, title:'Datos No guardados', text:data.error_msj});
                }
                delete modal;
                delete response, data;
            }).catch((err) => {
                spinner.setAttribute('hidden', '');
                Swal.fire({icon: 'error', title:'Error al guardar', text:err.message});

            });
            delete url, x;
        }else{
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Datos No guardados', text:'La cantidad no puede superar lo requerido'});
        }
        delete idpicking, idproducto, requerido, cantidad, ubicacion, data;
    }


    /*********************************************************************************
    * Esta función abre el formulario donde se colocan los container, las cajas o 
    * los pales donde se coloca el pedido consolidado.
    **********************************************************************************/
    function guardar(){
        document.getElementById("picc_idpicking").value = document.getElementById("pick_idpicking").value;
        var myModal = new bootstrap.Modal(document.getElementById("ContainerModal"),{});
        myModal.show();
    }


    /**************************************************************************
    * SUBMIT en Anclaje de Productos al Pedido.
    ***************************************************************************/
    function submit_close_tarea(event) {
        event.preventDefault();
        spinner.removeAttribute('hidden');
        let idpicking = document.getElementById("pick_idpicking").value;
        let formData  = new FormData(document.getElementById('picking-container-form'));
        formData.append("accion","picking-consolida");
        formData.append("idpicking",idpicking);
        formData.append("observacion",document.getElementById("pick_observacion").value);
        // var url = new URL(location.origin + '/wms/api/ap_picking.php');
        // url.searchParams.append('idpicking', idpicking);
        fetch('api/ap_picking.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((data) => {
            spinner.setAttribute('hidden', '');
            if(data.response == 'success'){
                let myModalCo = document.getElementById('ContainerModal'); // relatedTarget
                let modal     = bootstrap.Modal.getOrCreateInstance(myModalCo);
                modal.hide();
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                delete response, data, myModalCo ,modal;
                setInterval(() => {
                    window.location.replace(location.origin + "/wms/picking_tareas.php");
                }, 1000 * 1);
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
            }
            delete response, data;
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Error al guardar', text:err.message});
        });
        delete idpicking, formData;
    }

    /**************************************************************************
    *                       ADD MORE PALETAS
    * Agrega campos para colocar las Paletas a las cuales se anclará el Pedido
    ***************************************************************************/
    function add_more_paletas(){
        let html = '';
        html  = '<div class="form-group mb-1">';
        html += '<input name="picc_bin[]" id="picc_bin" class="form-control paletas" Placeholder="Contenedor...">';
        html += '</div>';
        document.getElementById("paletas-lista-div").insertAdjacentHTML("beforeend",html);
        html = null;
    }
</script>