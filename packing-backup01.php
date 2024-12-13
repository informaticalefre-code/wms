<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/packing.css">
</head>
<body>
<?php
    require 'config/header_barra_tarea.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div id="panel-left">
            <div id="packing-header" class="mb-2" style="max-width:1200px">
                <form method="post" id="packing-header-form">
                    <section>
                        <div class="d-flex flex-row justify-content-between">
                            <span id="packing-idpedido" style="font-size:100%;" class="badge bg-primary"></span>
                            <input type="text" name="pack_idpacking" id="pack_idpacking" autocomplete="off" readonly>
                            <p id="packing-fecha" class="mb-0"></p>
                        </div>
                        <p id="packing-cliente"  class="mb-0"></p>
                        <p id="packing-vendedor" class="mb-0"></p>
                    </section>
                    <div class="form-group">
                        <label for="pack_observacion" style="font-size:inherit;">Observación</label>
                        <textarea class="form-control p-1" name="pack_observacion" id="pack_observacion" maxlength="100" style="font-size:inherit; padding:1%;" autocomplete="off"></textarea>
                    </div>
                </form>
            </div>
            <div id="packing-productos" class="packing-lista-productos" style="max-width:1200px"></div>
        </div>            
        <aside id="panel-right">
            <div id="control-boxes" class="d-flex justify-content-evenly">
                <button class="btn btn-success" style="height:45px;" onclick="add_box()">Nueva&nbsp<i class="icon-plus"></i></button>
                <button class="btn btn-info" style="height:45px;" onclick="auto_box()">Auto&nbsp<i class="icon-order-39"></i></button>
            </div>
            <div id="boxes">
            </div>
        </aside>
    </main>

    <div class="modal fade" id="PackingModal" tabindex="-1" aria-labelledby="PackingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <!-- <form method="post" id="packing-producto-form" onsubmit="submit_pack_producto(event)"> -->
                <form method="post" id="packing-producto-form" autocomplete="off">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="PackingModalLabel">SKU&nbsp</h5>
                        <input id="info-idproducto" type="text" name="info-idproducto" value="error..." class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2" id="PackingModalBody">
                        <div id="Packing-Producto-Modal">
                            <div id="info-nombre" style="width:100%;border-bottom: 1px solid darkgray"></div>
                            <div id="PackingModalBody-Info" class="mb-2">
                                <div>
                                    <label for="unidad">Unidad:</label>
                                    <input id="info-unidad" class="packingModal-info-input" type="text" name="unidad" style="width:5rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="requerido">Anclado:</label>
                                    <input id="info-requerido" class="packingModal-info-input" type="text" name="requerido" style="width:3rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="disponible">Por empacar:</label>
                                    <input id="info-disponible" class="pickingModal-info-input" type="text" name="disponible" style="width:3rem;" readonly autocomplete="off">
                                </div>
                                <div>
                                    <label for="bulto">ID.Bulto:</label>
                                    <input id="info-bulto" class="packingModal-info-input" type="text" name="bulto" style="width:3rem;" readonly autocomplete="off">
                                </div>
                            </div>
                            <div id="PackingModalBody-Foto"></div>
                            <div class="number-input mt-1 mb-2">
                                <button type="button" onclick="document.getElementById('info-cantidad').stepDown()" class="minus">-</button>
                                <input  id="info-cantidad" class="cant_requerido" name="cantidad" type="number" min="0"  autocomplete="off">
                                <button type="button" onclick="document.getElementById('info-cantidad').stepUp()" class="plus">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="packing-producto-form" class="btn btn-primary" onclick="submit_pack_producto(event)">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="BoxModal" tabindex="-1" aria-labelledby="BoxModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" id="Box-producto-form" onsubmit="submit_close_bulto(event)">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="BoxModalLabel">Bulto&nbsp</h5>
                        <input id="bulto-idbulto" type="text" name="bulto-idbulto" value="error..." class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2" id="BoxModalBody">
                        <div id="Box-Producto-Modal">
                            <div id="BoxModalBody-Info" class="mb-2">
                                <div class="modal-info-line">
                                    <label for="bulto-peso">Peso:</label>
                                    <input id="bulto-peso" class="packingModal-info-input form-control" type="text" pattern="[0-9]+([\.][0-9]+)" maxlength="7" name="bulto-peso" autocomplete="off">
                                </div>
                                <div class="modal-info-line">
                                    <label for="bulto-unidad">Unidad:</label>
                                    <!-- <input id="bulto-unidad" class="packingModal-info-input form-control" type="text" name="bulto-unidad" autocomplete="off" value="Kg"> -->
                                    <select class="form-select" id="bulto-unidad" name="bulto-unidad" aria-label="Kilos o Gramos">
                                        <option selected value="Kg">Kg</option>
                                        <option value="gr">gr</option>
                                    </select>
                                </div>
                                <div class="modal-info-line">
                                    <label for="bulto-status">Estatus:</label>
                                    <input id="bulto-status" class="packingModal-info-input form-control" type="text" name="bulto-status" disabled autocomplete="off">
                                </div>
                            </div>
                            <div id="BoxModalBody-List" class="mb-2">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="bulto-btn-del" class="btn  btn-outline-secondary" onclick="box_delete()">Eliminar Bulto&nbsp<i class="icon-remove"></i></button>
                        <button type="button" id="bulto-btn-print" class="btn btn-secondary" onclick="print_box_modal()" title="Imprime etiqueta">Etiqueta&nbsp<i class="icon-noun-open-bill"></i></button>
                        <button type="submit" id="bulto-btn-cerrar" form="Box-producto-form" class="btn btn-primary">Cerrar Bulto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
    require 'config/footer.html';
?>

<!-- Contiene las funciones JS solo para uso de esta página-->
<script type="text/javascript" src="js/packing.js"></script>
<!-- Browser Print  ------------------------------------>
<script type="text/javascript" src="js/BrowserPrint-2.0.0.75.min.js"></script>


<script type = "text/JavaScript">
    const spinner = document.getElementById("spinner");
    /* Este es un listener que se ejecuta luego que la ventana Modal Packing Modal
       se muestra en pantalla, y luego que se muestra podemos asignar el foco al
       elemento que queramos */
    var myModalEl = document.getElementById('PackingModal');
    myModalEl.addEventListener('shown.bs.modal', function (event) {
        document.getElementById("info-cantidad").focus();
    })

    /************* RELACIONADO CON LA LECTURA DEL CODIGO DE BARRA ***********************/
    var barcode = '';  
    var interval;
    /************* FIN RELACIONADO CON LA LECTURA DEL CODIGO DE BARRA ***********************/
    /* Solo puede existir 1 caja abierta y todo se inserta en esa caja */
    var global_packing_cajas = 0;  // Variable global que contiene el número de bultos
    var global_box_open      = 0 ; // Indica el número de la caja abierta actualmente 
    var global_packing_productos;  // Array que contiene los productos a empacar
    const search_parametro = new URLSearchParams(window.location.search);
    const idpacking = search_parametro.get('idpacking');
    document.getElementById("pack_idpacking").value = idpacking;
    carga_tarea_packing(idpacking);
    var global_selected_device = null; // Array de objetos con las impresoras Zebras del Equipo
    setup_bp() // Inicializa el Zebra Browser Print;
    delete search_parametro, idpacking;

    
  
    /* Función que direcciona cuando se pulsa el botón de atrás (no confundir con el del navegador)*/
    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/packing_pistas.php");
    }

    /**************************************************************************
    * Abre la ventana modal con los datos referenciales del producto 
    * que se procede anclar al pedido
    ***************************************************************************/
    async function carga_tarea_packing(idpacking){
        spinner.removeAttribute('hidden');
        // Tomamos el parametro del ID Picking a cargar
        // Hacemos un fetch api mandando el Nro de la Tarea de Picking.
        var url = new URL(location.origin + '/wms/api/ap_packing.php');
        url.searchParams.append('idpacking', idpacking);
        url.searchParams.append('accion', 'packing-tarea');

        await fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if (responseJson.response == 'success'){
                document.getElementById("pack_idpacking").value         = responseJson.idpacking;
                document.getElementById("packing-idpedido").innerText   = responseJson.idpedido;
                document.getElementById("packing-fecha").innerText      = responseJson.fecha;
                document.getElementById("packing-cliente").innerText    = responseJson.cliente;
                document.getElementById("packing-vendedor").innerText   = responseJson.vendedor;
                document.getElementById("packing-productos").innerHTML  = responseJson.html_lista;
                global_packing_cajas = responseJson.bultos;
                global_packing_productos = responseJson.packing_productos;
                global_box_open = responseJson.bulto_open;
                if (global_packing_cajas == 0) {
                    console.log("voy con el ADD BOX");
                    add_box(idpacking);
                }else{
                    document.getElementById("boxes").innerHTML = responseJson.html_bultos;
                }
            }else{
                document.getElementById("packing-productos").innerHTML = responseJson.html_lista;
            }
            delete search_parametro, idpacking, url;
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se puede cargar Tarea de Packing', text:err.message});
        });
    }


    /*************************************************************************************
    * Función que abre la forma modal para colocar la cantidad que existe de un producto *
    * Se activa al darle clic al producto en la lista de Productos a Empacar
    **************************************************************************************/
    function pack_producto(idproducto){
        document.getElementById("packing-producto-form").reset();
        const anclado  = Number(document.getElementById("packing-"+idproducto).querySelector('#pacd_requerido').value);
        var  embalado  = document.getElementById("packing-"+idproducto).querySelector('#pacd_cantidad').value;
        var disponible = 0;
        disponible = anclado - Number(embalado);
        document.getElementById("info-nombre").textContent = document.getElementById("packing-nombre-pro-"+idproducto).textContent;
        document.getElementById("info-idproducto").value = idproducto;
        document.getElementById("info-unidad").value     = document.getElementById("packing-"+idproducto).querySelector('#pacd_unidad').value
        document.getElementById("info-requerido").value  = anclado;
        //document.getElementById("info-cantidad").value = embalado;
        document.getElementById("info-disponible").value = disponible;
        document.getElementById("info-bulto").value      = global_box_open;
        // var existencia = set_producto_form_values(idproducto);
        var foto      = document.getElementById("foto-"+idproducto).innerHTML;
        var ModalFoto = document.getElementById("PackingModalBody-Foto");
        // foto = foto.replace("fotos-100","fotos-300");
        // foto = foto.replace("-lefre-th.","-lefre-sm.");
        ModalFoto.innerHTML = foto;
        var myModal = new bootstrap.Modal(myModalEl,{});

        spinner.setAttribute('hidden', '');
        delete existencia, foto, ModalFoto;
        myModal.show();
        delete anclado, embalado, disponible, myModal;
    }


    /*************************************************************************************
    * Función que abre la forma modal para ver los productos asociados a una caja
    **************************************************************************************/
    function pack_box(bulto){
        spinner.removeAttribute('hidden');
        document.getElementById("bulto-idbulto").value = bulto;
        const search_parametro = new URLSearchParams(window.location.search);
        const idpacking = search_parametro.get('idpacking');
        // Hacemos un fetch api mandando el Nro de la Tarea de Picking.
        var url = new URL(location.origin + '/wms/api/ap_packing.php');
        url.searchParams.append('idpacking', idpacking);
        url.searchParams.append('box',bulto);

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            responseJson.response == 'success'
            if (responseJson.response == 'success'){
                document.getElementById("bulto-peso").value            = responseJson.peso;
                document.getElementById("bulto-unidad").value          = responseJson.unidadpeso;
                document.getElementById("bulto-status").value          = responseJson.status == 0  ? 'ABIERTO' : 'CERRADO';
                document.getElementById("BoxModalBody-List").innerHTML = responseJson.html_prod;
                /* Para facilitar la consulta (recuerde que los nombres están en SQL Server y los datos de Packing en MariaDB*/
                insert_nombres_productos();
                if (responseJson.status == 1){
                    document.getElementById("bulto-btn-cerrar").disabled = true;
                    document.getElementById("bulto-btn-del").disabled = true;
                }else{
                    document.getElementById("bulto-btn-cerrar").disabled = false;
                    document.getElementById("bulto-btn-del").disabled = false;
                }
                var myModal = new bootstrap.Modal(document.getElementById("BoxModal"),{});
                myModal.show();
            }else{
                document.getElementById("packing-productos").innerHTML  = responseJson.html_prod;
            }
            delete search_parametro, idpacking, url;
        }).catch((err) => {
            console.log("rejected:---", err.message);
        });
    };


    /**************************************************************************
    * SUBMIT para añadir productos a la caja activa
    ***************************************************************************/
    function submit_pack_producto(event){
        event.preventDefault();
        // Verificamos si hay una caja activa para insertar productos
        if (global_box_open == 0){
            Swal.fire({icon:'warning', title:'No hay ninguna caja abierta', text:'debe abrir o crear una nueva caja'});
            return;
        }

        spinner.removeAttribute('hidden');
        const idpacking  = document.getElementById("pack_idpacking").value;
        const idproducto = document.getElementById("info-idproducto").value;

        var formData = new FormData(document.getElementById('packing-producto-form'));
        formData.append("accion","packing-productos-bultos");
        formData.append("idpacking",idpacking);

        fetch('api/ap_packing.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((data) => {
            spinner.setAttribute('hidden', '');
            if(data.response == 'success'){
                const packing_obj = document.getElementById("packing-"+idproducto);
                packing_obj.querySelector('#pacd_cantidad').value = data.cantidad;
                packing_obj.querySelector('#semaforo').className  = data.semaforo;
                if (data.embalaje == 'total'){
                    packing_obj.style.display = "none"; 
                }
                // variable myModalEl se crea al inicio del script de javascript
                const modal = bootstrap.Modal.getOrCreateInstance(myModalEl);
                modal.hide();
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                delete myModalEl, modal;
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
            }
            delete idpacking, formData;
        }).catch((err) => {
            console.log("rejected:---", err.message);
        });
    }

    
    /**************************************************************************
    * SUBMIT para cerrar Bultos
    ***************************************************************************/
    function submit_close_bulto(event){
        event.preventDefault();
        spinner.removeAttribute('hidden');

        const idpacking = document.getElementById("pack_idpacking").value;
        const caja      = document.getElementById("bulto-idbulto").value;
        let   formData  = new FormData(document.getElementById('Box-producto-form'));
        formData.append("idpacking",idpacking);
        formData.append("accion","close-box");

        fetch('api/ap_packing.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((data) => {
            spinner.setAttribute('hidden', '');
            if (data.response == 'success'){
                print_box(caja);
                global_box_open = 0 ; // Solo puede haber una caja abierta.....
                document.getElementById("box-id-"+caja).classList.toggle("box-open");
                document.getElementById("box-id-"+caja).classList.toggle("box-close");
                document.getElementById("box-btn-"+caja).querySelector('#icon-lock').classList.toggle("icon-unlocked");
                document.getElementById("box-btn-"+caja).querySelector('#icon-lock').classList.toggle("icon-lock");
                const myModalEl = document.getElementById('BoxModal');
                const modal     = bootstrap.Modal.getOrCreateInstance(myModalEl);
                modal.hide();
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                delete myModalEl, modal;
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
            }
            delete idpacking, caja, formData;
        }).catch((err) => {
            console.log("rejected:---", err.message);
        });
    }


    /**********************************************************************
    *                            BOX OPEN
    * Permite abrir un bulto para modificar o eliminar productos.
    **********************************************************************/
    function box_open(caja){
        if (caja == global_box_open){
            return;
        }

        if (global_box_open == 0){
            console.log("Se procede abrir caja:"+caja);
            const idpacking = document.getElementById("pack_idpacking").value;
            let   formData  = new FormData();
            formData.append("idpacking", idpacking);
            formData.append("idbulto", caja);
            formData.append("accion", "open-box");

            fetch('api/ap_packing.php',{method:'POST',body:formData})
            .then((response) => response.json())
            .then((data) => {
                spinner.setAttribute('hidden', '');
                if (data.response == 'success'){
                    global_box_open = caja ; // Solo puede haber una caja abierta.....
                    document.getElementById("box-id-"+caja).classList.toggle("box-open");
                    document.getElementById("box-id-"+caja).classList.toggle("box-close");
                    document.getElementById("box-btn-"+caja).querySelector('#icon-lock').classList.toggle("icon-unlocked");
                    document.getElementById("box-btn-"+caja).querySelector('#icon-lock').classList.toggle("icon-lock");
                }else{
                    Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
                }
            }).catch((err) => {
                console.log("rejected:---", err.message);
            });
            delete idpacking, formData;
        }else{
            Swal.fire({icon:'warning', title:'No se puede abrir bulto', text:"El bulto "+global_box_open+" se encuentra abierto"});    
        }
        delete caja;
    }
    
    /**************************************************************************
    * Añade una caja vacía al proceso de Packing
    ***************************************************************************/
    function add_box(idpacking){
        if (global_box_open == 0){
            spinner.removeAttribute('hidden');
            let formData = new FormData();
            formData.append("accion", "add_box");
            formData.append("idpacking", document.getElementById("pack_idpacking").value);
            formData.append("idbulto", global_packing_cajas+1);

            fetch('api/ap_packing.php',{method:'POST', body:formData})
            .then((response) => response.json())
            .then((data) => {
                spinner.setAttribute('hidden', '');
                if(data.response == 'success'){
                    global_packing_cajas++;
                    global_box_open = global_packing_cajas;
                    document.getElementById("boxes").insertAdjacentHTML('afterbegin', data.html);
                }else{
                    Swal.fire({icon: 'warning',title: 'No se puede abrir una Nueva Caja',text: data.error_msj});        
                }
            }).catch((err) => {
                spinner.setAttribute('hidden', '');
                Swal.fire({icon: 'error', title:'Error al crear caja', text:err.message});
        });
            delete formData;
        }else{
            Swal.fire({icon: 'warning',title: 'No se puede abrir una Nueva Caja',text: 'La caja Nro.'+global_box_open+' aún está abierta'});
        }
    }

    /**************************************************************************
    * Elimina un producto de la caja indicada
    * 1. Solo puede haber 1 caja abierta.
    ***************************************************************************/
    function box_del_row(bulto,index,idproducto){
        spinner.removeAttribute('hidden');
        let idpacking = document.getElementById("pack_idpacking").value;
        let data = {idpacking:idpacking, accion:"remove-box-producto", idbulto:bulto, idproducto:idproducto};
        let url = new URL(location.origin + '/wms/api/ap_packing.php');
        fetch(url, {method:'DELETE', body:JSON.stringify(data), headers:{'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            spinner.setAttribute('hidden', '');
            if(data.response == 'success'){
                const packing_obj      = document.getElementById("packing-"+idproducto);
                const box_cantidad     = document.getElementById("box-prod-"+index).querySelector('#box_cantidad').value;
                const detalle_cantidad = packing_obj.querySelector('#pacd_cantidad');
                document.getElementById("hr-"+index).remove();
                document.getElementById("box-prod-"+index).remove();
                detalle_cantidad.value = parseInt(detalle_cantidad.value) - parseInt(box_cantidad);
                packing_obj.style.display  = "flex"; 

                if (detalle_cantidad.value == 0){
                    document.getElementById("packing-"+idproducto).querySelector('#semaforo').className = 'semaforo semaforo-rojo';
                }else{
                    document.getElementById("packing-"+idproducto).querySelector('#semaforo').className = 'semaforo semaforo-amarillo';
                }   
                delete idpacking, data, url;
                delete box_cantidad, detalle_cantidad, semaforo;
            }else{
                Swal.fire({icon: data.error_tpo, title:'Datos No guardados', text:data.error_msj});
            }
            delete idpacking, data, url;
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Error al eliminar producto de caja', text:err.message});
        });
    }


    /**********************************************************************
    *                            BOX DELETE
    * Borra o elimina un bulto vacío. Si tiene productos manda error.
    **********************************************************************/
    function box_delete(){
        const idpacking = document.getElementById("pack_idpacking").value;
        const idbulto   = document.getElementById('bulto-idbulto').value;

        let data = {idpacking:idpacking, accion:"remove-box", idbulto:idbulto};
        let url = new URL(location.origin + '/wms/api/ap_packing.php');
        fetch(url, {method:'DELETE', body:JSON.stringify(data), headers:{'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((data) => {
            spinner.setAttribute('hidden', '');
            if(data.response == 'success'){
                global_packing_cajas--;  // Variable global que contiene el número de bultos
                global_box_open = 0 ; // Indica el número de la caja abierta actualmente 
                const myModalEl = document.getElementById('BoxModal');
                const modal     = bootstrap.Modal.getOrCreateInstance(myModalEl);
                modal.hide();
                document.getElementById("box-"+idbulto).remove();
                delete myModalEl, modal;
                delete idpacking, idbulto, data, url;
            }else{
                Swal.fire({icon: data.error_tpo, title:'No se puede eliminar bulto', text:data.error_msj});
            }
            delete idpacking, data, url;
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'Error al eliminar bulto', text:err.message});
        });
    }


    /**************************************************************************
    *                           PRINT BOX
    ***************************************************************************/
    function print_box(caja){
        console.log(global_selected_device);
        if (global_selected_device == null){
            Swal.fire({icon:'warning',title: 'Vuelva a intentar',text:"inicializando impresora...",showConfirmButton: false,timer: 2500});
        }else{
            const idpacking  = document.getElementById("pack_idpacking").value;
            let data = {idpacking:idpacking, accion:"print-bulto-label", idbulto:caja};
            let url = new URL(location.origin + '/wms/api/ap_label.php');
            fetch(url, {method:'POST', body:JSON.stringify(data), headers:{'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((data) => {
                if(data.response == 'success'){
                    global_selected_device.send(data.etiqueta, undefined, undefined);
                    delete url, data;
                }else{
                    Swal.fire({icon: data.error_tpo, title:'Error al imprimir', text:data.error_msj});
                }            
            });
        }
    }

    /**************************************************************************
    *                           PRINT BOX MODAL
    * Es el botón de imprimir que está dentro de la pantalla modal.
    ***************************************************************************/
    function print_box_modal(){
        let box = document.getElementById("bulto-idbulto").value;
        print_box(box);
        delete box;
    }    

    /**************************************************************************
    * SUBMIT en Anclaje de Productos al Pedido.
    ***************************************************************************/
    function close_tarea(event) {
        event.preventDefault();
        Swal.fire({title:'¿Cerrar tarea de packing?',
                    icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#339933',
        cancelButtonColor: '#cc3300',
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
        }).then((result) => {
            if (result.value){
                spinner.removeAttribute('hidden');
                var formData = new FormData(document.getElementById('packing-header-form'));
                formData.append("accion","packing-close");
                // formData.append("idpedido",document.getElementById("packing-idpedido").innerText);
                fetch('api/ap_packing.php',{method:'POST',body:formData})
                .then((response) => response.json())
                .then((data) => {
                    spinner.setAttribute('hidden', '');
                    if(data.response == 'success'){
                        Swal.fire({icon:'success', title:'Datos Guardados', showConfirmButton:false, timer:1500});
                        delete formData;
                        setTimeout(() => {window.location.replace(location.origin + "/wms/packing_pistas.php")}, 1750);
                    }else{
                        Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
                    }
                    delete formData;
                }).catch((err) => {
                    spinner.setAttribute('hidden', '');
                    Swal.fire({icon: 'error', title:'Error al cerrar tarea de packing', text:err.message});
                });
            }
        });
    }    

    /*********************************************************
    *               INSERT NOMBRES PRODUCTOS
    * Esta función es importante. Ayuda mucho al desempeño del Api
    * al buscar los productos de una caja o bulto. Ya que al buscar
    * los productos no tiene que buscar el nombre en la tabla de productos.
    * Recuerdese que la información necesaria está en 2 base de datos
    * diferentes.
    *********************************************************/ 
    function insert_nombres_productos(){
        /* buscamos los objetos códigos de productos*/
        const productos_box  = document.querySelectorAll('.box-prod');
        for (let i = 0; i < productos_box.length; i++) {
            const codigo = productos_box[i].querySelector('#box_idproducto').value;
            for(var j in global_packing_productos){
                if (Object.values(global_packing_productos[j]).indexOf(codigo) >= 0){
                    const producto = Object.values(global_packing_productos[j])[5];
                    productos_box[i].querySelector('#box_descripcion_ped').value = producto;
                    delete producto;
                }
            }
            delete codigo;            
        }
        delete productos_box;
    }

    /*********************************************************
    * SETUP BROWSER BROWSER
    *********************************************************/ 
    function setup_bp(){
        //Discover any other devices available to the application
        BrowserPrint.getLocalDevices(function(device_list){
            //const searchIndex = device_list.findIndex((zebra) => zebra.name=="\\\\192.168.1.100\\ZDesigner GK420t (EPL)");
            const searchIndex = device_list.findIndex((zebra) => zebra.name=="ZDesigner GC420t");
            global_selected_device = device_list[searchIndex];
            device_list = null;
            delete device_list, searchIndex;
        }, function(){alert("Error getting local devices")},"printer");
    }


    /*********************************************************
    *                    AUTO BOX
    * Genera bultos de manera automática como por ejemplo los
    * cuñetes o aquellos productos cuyas cantidades cumplan
    * con el bulto original.
    *********************************************************/
    function auto_box(){
        if (global_box_open == 0){
            spinner.removeAttribute('hidden');
            let formData = new FormData();
            formData.append("accion", "auto-box");
            formData.append("idpacking", document.getElementById("pack_idpacking").value);
            formData.append("bulto", global_box_open);
            fetch('api/ap_packing.php',{method:'POST', body:formData})
            .then((response) => response.json())
            .then((data) => {
                spinner.setAttribute('hidden','');
                if(data.response == 'success'){
                    console.log("Finoooooo");
                    global_packing_cajas = data.bulto;  // Variable global que contiene el número de bultos
                }else{
                    Swal.fire({icon: 'warning',title: 'Error al generar bultos automaticamente',text: data.error_msj});
                }
            }).catch((err) => {
                spinner.setAttribute('hidden', '');
                Swal.fire({icon: 'error', title:'Error en automatización', text:err.message});
        });
            delete formData;
        }else{
            Swal.fire({icon: 'warning',title: 'Verifique bultos abiertos',text: 'No se puede ejecutar la tarea de automatización si existen bultos abiertos'});
        }
    }
</script>

