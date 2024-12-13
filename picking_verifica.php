<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
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
            <section>
                <div class="d-flex flex-row justify-content-between">
                    <span id="picking-idpedido" style="font-size:100%;" class="badge bg-primary"></span>
                    <p id="picking-fecha" class="mb-0"></p>
                </div>
                <p id="picking-cliente"  class="mb-0"></p>
                <p id="picking-vendedor" class="mb-0"></p>
                <div class="d-flex justify-content-between p-0">
                    <div>
                        <label for="pick_pista">Pista:</label>
                        <input id="pick_pista" class="col-2 border-0" name="pick_pista" readonly>
                    </div>
                    <p id="picking-preparador" class="mb-0"></p>
                </div>                    
                <p id="picking-palets" class="mb-0"></p>
            </section>
            <form method="post" id="picking-header-form" autocomplete="off">
                <input type="hidden" name="pick_idpicking" id="pick_idpicking">
                <div class="form-group">
                    <label for="pick_observacion" style="font-size:inherit;">Observación:</label>
                    <textarea class="form-control p-1" name="pick_observacion" id="pick_observacion" maxlength="100" style="font-size:inherit; padding:1%;"></textarea>
                </div>
            </form>
        </div>
        <div id="picking-productos" class="picking-lista-productos" style="max-width:1200px"></div>
    </main>

    <div class="modal fade" id="PickingModal" tabindex="-1" aria-labelledby="PickingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <!-- <form method="post" id="picking-producto-form" onsubmit="submit_pick_producto(event)" autocomplete="off"> -->
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
                                    <label for="unidad">Unidad:</label>
                                    <input id="info-unidad" class="pickingModal-info-input" type="text" name="unidad" style="width:5rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="requerido">Requerido:</label>
                                    <input id="info-requerido" class="pickingModal-info-input" type="text" name="requerido" style="width:3rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="info-cantidad">Anclado:</label>
                                    <input id="info-cantidad" class="pickingModal-info-input" type="text" name="info-cantidad" style="width:3rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="existencia" style="text-align:right;">Existencia:</label>
                                    <input id="info-existencia" type="text" name="existencia" style="width:clamp(4rem,6rem,7rem);" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="disponible" style="text-align:right;">Disponible:</label>
                                    <input id="info-disponible" type="text" name="disponible" style="width:clamp(4rem,6rem,7rem);" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="referencia">Ref:</label>
                                    <input id="info-referencia" class="pickingModal-info-input" type="text" name="referencia" style="width:clamp(6rem,7rem,10rem);" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="codigobarra">Cod.Barra:</label>
                                    <input id="info-codigobarra" class="pickingModal-info-input" type="text" name="codigobarra" style="width:10rem;" readonly autocomplete="off">
                                </div>
                            </div>
                            <div id="PickingModalBody-Foto"></div>
                            <div class="number-input mt-1 mb-2">
                                <button type="button" onclick="document.getElementById('info-cantverif').stepDown()" class="minus">-</button>
                                <input  id="info-cantverif" class="cant_requerido" name="info-cantverif" inputmode="numeric" type="number" min="0"  autocomplete="off">
                                <button type="button" onclick="document.getElementById('info-cantverif').stepUp()" class="plus">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="picking-producto-form" class="btn btn-primary" onclick="submit_pick_producto(event)">Guardar</button>
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
<script type="text/javascript" src="js/picking_verifica.js"></script>


<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    var myModalEl = document.getElementById('PickingModal');
    
    /********************************************************************************/
    var global_picking_productos;
    carga_tarea_picking();
    // const form = document.getElementById('picking-producto-form');
    // form.addEventListener('submit', logSubmit);
    var barcode = '';
    var interval;

    /* Función que direcciona cuando se pulsa el botón de atrás (no confundir con el del navegador)*/
    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/picking_pistas.php");
    }

    /*************************************************************************************
    * Función que abre la forma modal para colocar la cantidad que existe de un producto *
    **************************************************************************************/
    function pick_producto(idproducto){
        document.getElementById("picking-producto-form").reset();
        document.getElementById("info-nombre").textContent = document.getElementById("picking-nombre-pro-"+idproducto).textContent;
        document.getElementById("info-idproducto").value   = idproducto;
        document.getElementById("info-unidad").value       = document.getElementById("picking-"+idproducto).querySelector('#picd_unidad').value;
        document.getElementById("info-requerido").value    = document.getElementById("picking-"+idproducto).querySelector('#picd_requerido').textContent;
        document.getElementById("info-cantidad").value     = document.getElementById("picking-"+idproducto).querySelector('#picd_cantidad').value;
        document.getElementById("info-cantverif").value    = document.getElementById("picking-"+idproducto).querySelector('#picd_cantverif').value;
        document.getElementById("info-referencia").value   = document.getElementById("picking-"+idproducto).querySelector('#picking-ref').value;
        document.getElementById("info-codigobarra").value  = document.getElementById("picking-"+idproducto).querySelector('#codigobarra_pro').value;

        // Se toma la existencia del array que se crea al cargar la tarea.
        let existencia = global_picking_productos[global_picking_productos.findIndex((productos) => productos["id_producto"] == idproducto)]["existencia"]
        document.getElementById("info-existencia").value = existencia;
        
        async function AsyncAnclados(){
            let url = new URL(location.origin + '/wms/api/ap_picking.php');
            url.searchParams.append('idproducto', idproducto);
            const response = await fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}});
            let data = await response.json();

            document.getElementById("info-disponible").value = existencia - data.anclados;
            url = response = data = existencia = null;
        };

        AsyncAnclados();        

        let foto       = document.getElementById("foto-"+idproducto).innerHTML;
        let ModalFoto  = document.getElementById("PickingModalBody-Foto");
        // foto = foto.replace("fotos-100","fotos-300");
        // foto = foto.replace("-lefre-th.","-lefre-sm.");
        ModalFoto.innerHTML = foto;
        let myModal = new bootstrap.Modal(document.getElementById("PickingModal"),{});

        spinner.setAttribute('hidden', '');
        myModal.show();
        foto = ModalFoto = myModal = null;
    }

    /**************************************************************************
    * Abre la ventana modal con los datos referenciales del producto 
    * que se procede anclar al pedido
    ***************************************************************************/
    function carga_tarea_picking(){
        spinner.removeAttribute('hidden');
        
        // Tomamos el parametro del ID Picking a cargar
        let search_parametro = new URLSearchParams(window.location.search);
        let idpicking = search_parametro.get('id_picking');
        // Hacemos un fetch api mandando el Nro de la Tarea de Picking.
        let url = new URL(location.origin + '/wms/api/ap_picking.php');
        url.searchParams.append('idpicking', idpicking);
        url.searchParams.append('accion', 'picking-verifica');

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("pick_idpicking").value         = responseJson.idpicking;
            document.getElementById("pick_pista").value             = responseJson.pista;
            document.getElementById("picking-idpedido").innerText   = responseJson.idpedido;
            document.getElementById("picking-fecha").innerText      = responseJson.fecha;
            document.getElementById("picking-cliente").innerText    = responseJson.cliente;
            document.getElementById("picking-vendedor").innerText   = responseJson.vendedor;
            document.getElementById("picking-preparador").innerText = 'Preparador: '+responseJson.preparador;
            document.getElementById("picking-palets").innerText     = responseJson.paletas;
            document.getElementById("pick_observacion").value       = responseJson.observacion;
            document.getElementById("picking-productos").innerHTML  = responseJson.html_lista;
            global_picking_productos = responseJson.picking_productos;
            spinner.setAttribute('hidden', '');
        });

        // Ahora buscamos las paletas donde se encuentra el pedido consolidado.
        url.searchParams.set('accion', 'picking-bins');

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            let paletas = JSON.parse(responseJson.paletas);
            let paletas_txt = '';
            for(let i = 0; i < paletas.length; i++){ 
                paletas_txt = paletas_txt + paletas[i];
                if ( i < paletas.length-1){
                    paletas_txt = paletas_txt + ', ';
                }
            }
            document.getElementById("picking-palets").innerText = paletas_txt;
            paletas = paletas_txt = null;
            spinner.setAttribute('hidden', '');
        });
        search_parametro = idpicking = url = null;
    }


    /**************************************************************************
    * SUBMIT en Anclaje de Productos al Pedido.
    ***************************************************************************/
    function submit_pick_producto(event) {
        event.preventDefault();
        spinner.removeAttribute('hidden');
        /*Pasar esto a un objeto formdata*/
        let idpicking  = document.getElementById("pick_idpicking").value;
        let idproducto = document.getElementById("info-idproducto").value;
        let requerido  = +document.getElementById("info-requerido").value;
        let anclado    = +document.getElementById("info-cantidad").value;
        let cantverif  = +document.getElementById("info-cantverif").value;
        let data = {idproducto:idproducto, accion:"picking-cantverif", anclado:anclado,cantverif:cantverif};
        let url = new URL(location.origin + '/wms/api/ap_picking.php');
        url.searchParams.append('idpicking', idpicking);
        if (cantverif <= requerido){
            fetch(url,{method:'PATCH',body:JSON.stringify(data),headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((data) => {
                const modal = bootstrap.Modal.getOrCreateInstance(myModalEl);
                spinner.setAttribute('hidden', '');
                setInterval(() => {modal.hide();}, 1500 * 1);
                if(data.response == 'success'){
                    Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                    document.getElementById("picking-"+idproducto).querySelector('#picd_cantverif').value = cantverif;
                    document.getElementById("picking-"+idproducto).querySelector('#semaforo').className  = data.semaforo;
                    cantverif = null;
                }else{
                    Swal.fire({icon: data.error_tpo, title:'Datos No guardados', text:data.error_msj});
                }
                modal = null;
            }).catch((err) => {
                spinner.setAttribute('hidden', '');
                console.log("rejected:---", err.message);
            });
        }else{
            spinner.setAttribute('hidden', '');
            console.log("Este este mensaje***");
            Swal.fire({icon: 'error', title:'Datos No guardados', text:'La cantidad no puede superar lo requerido'});
        }
        idpicking = requerido = anclado = data = url = null;
    }


    /**************************************************************************
    * SUBMIT en Anclaje de Productos al Pedido.
    ***************************************************************************/
    function guardar(event) {
        event.preventDefault();
        Swal.fire({title:'¿Cerrar tarea de verificación de picking?',
                    icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#339933',
        cancelButtonColor: '#cc3300',
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
        }).then((result) => {
            if (result.value){
                spinner.removeAttribute('hidden');
                let formData = new FormData(document.getElementById('picking-header-form'));
                formData.append("accion","picking-close");
                fetch('api/ap_picking.php',{method:'POST',body:formData})
                .then((response) => response.json())
                .then((data) => {
                    spinner.setAttribute('hidden', '');
                    if(data.response == 'success'){
                        Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                        setTimeout(() => {window.location.replace(location.origin + "/wms/picking_pistas.php")}, 1750);
                    }else{
                        Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
                    }
                }).catch((err) => {
                    spinner.setAttribute('hidden', '');
                    console.log("rejected:---", err.message);
                });
                formData = null;
            }
        });
    }
</script>