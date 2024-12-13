<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/pedidos_lista.css">
    <link rel="stylesheet" type="text/css" href="css/boton-back-top.css">
</head>
<body>
<?php
    require 'config/header_barra_pedidos.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <button onclick="topFunction()" id="myBtn" title="Sube al principio"><i class="icon-arrow-up"></i></button>
        <div><h4 class='titulos_categorias'>Pedidos</h4></div>
        <div id="pedidos-lista">
            <table id="pedidos-tabla" cellspacing="0">
                <thead>
                <tr class="tabla-header">
                    <th>Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Monto</th>
                    <th>Tracking</th>
                    <th>Acción</th>
                </tr>
                </thead>
                <tbody id="pedidos-body">
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
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Estatus:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('TODOS');" type="checkbox" id="status-todos" name="status-todos"  checked>
                    <label class="form-check-label" for="status-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('NUEVOS');" type="checkbox" id="status-nuevo" name="status-nuevo">
                    <label class="form-check-label" for="status-nuevo">Nuevos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('APROBADOS');" type="checkbox" id="status-aprobado" name="status-aprobado">
                    <label class="form-check-label" for="status-aprobado">Aprobados</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('PICKING');" type="checkbox" id="status-picking" name="status-picking">
                    <label class="form-check-label" for="status-picking">En Picking</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('PACKING');" type="checkbox" id="status-packing" name="status-packing">
                    <label class="form-check-label" for="status-packing">En Packing</label>
                </div>            
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('PENDIENTE');" type="checkbox" id="status-pendiente" name="status-pendiente">
                    <label class="form-check-label" for="status-pendiente">Pendiente</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('FACTURADO');" type="checkbox" id="status-facturado" name="status-facturado">
                    <label class="form-check-label" for="status-facturado">Facturado</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('RETENIDO');" type="checkbox" id="status-retenido" name="status-retenido">
                    <label class="form-check-label" for="status-retenido">Retenido</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_pedidos('ANULADO');" type="checkbox" id="status-anulado" name="status-anulado">
                    <label class="form-check-label" for="status-anulado">Anulado</label>
                </div>
                <hr>
                <div>
                    <button type="button" name="aplicar_fitro" id="aplicar_filtro" data-bs-dismiss="offcanvas" class="btn btn-primary" onclick="aplica_filtro();">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="PedidosModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-xl-down">
            <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="PedidosModalLabel">ID. Cliente&nbsp</h5>
                <input id="ModalIdCliente" type="text" value="error..." class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-2" id="PedidosModalBody">
                <p id="ModalCliente"></p>
                <hr>
                <div id="PedidosModalStatus">
                    <div id="Pedido-ID">Pedido:
                        <span id="pedido-idpedido" style="font-size:100%;" class="badge bg-primary"></span>
                        <div>Monto:&nbsp<span id="pedido-monto"></span></div>
                        <!-- <input type="text" name="pedido-monto" id="pedido-monto" class="packingModal-info-input border-0" readonly autocomplete="off"> -->
                    </div>

                    <div id="Pedido-Status">
                        <label for="status-pedido">Acción:</label>
                        <select class="form-select" aria-label="status pedido" style="max-width: 150px;" name="status-pedido" id="status-pedido">
                            <option value=null>Seleccione...</option>
                            <option value="APROBAR">Aprobar</option>
                            <option value="RETENER">Retener</option>
                            <option value="ANULAR">Anular</option>
                        </select>
                        <button type="submit" id="bulto-btn-cerrar" class="btn btn-primary" onclick="cambiar_estatus()">Cambiar Estatus</button>
                    </div>
                </div>
                <hr>
                <div id="cliente_detalle">
                    <div id="DatosClienteModal" style="display:flex; flex-direction:column; width:clamp(350px,50%,400px);">
                        <div class="mb-3 d-flex flex-row">
                            <label for="factura_uno" class="col-form-label">Inicio:</label>
                            <input type="text" name="factura_uno" class="form-control" id="factura_uno" readonly>
                        </div>
                        <div class="mb-3 d-flex flex-row">
                            <label for="fact_avg" class="col-form-label">Promedio Facturado<small>&nbsp(6 meses)</small>:</label>
                            <input type="text" name="fact_avg" class="form-control" id="fact_avg" style="max-width:8rem;" readonly>
                        </div>
                        <div class="mb-3 d-flex flex-row">
                            <label for="cobros_sin_procesar" class="col-form-label">Cobranzas<small>&nbsp(Sin Procesar)</small>:</label>
                            <input type="text" name="cobros_sin_procesar" class="form-control" id="cobros_sin_procesar" style="max-width:8rem;"readonly>
                        </div>
                    </div>
                    <hr>
                    <div id="saldos_mensuales">
                        <div><strong>Saldos últimos 6 meses</strong></div>
                        <div id="MesesModal"> </div>
                    </div>
                    <hr>
                    <div id="facturas_pendientes" style="width: 100%;">
                        <div><strong>Facturas Pendientes por Pagar</strong></div>
                        <div id="PedidosModalFacturas"></div>
                    </div>                        
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>

<?php
    /* Pendiente tú (otro programador aparte de mí). Este require es solicitado
       hasta el momento en pedidos_lista.php y packing.php */
    require_once 'bultos_modal.html';
    require 'config/footer.html';
?>

<script type="text/javascript" src="js/toggle_barra_search.js"></script>
<!-- boton de back to top -->
<script type="text/javascript" src="js/boton-back-top.js"></script>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    
    var global_bloque = 1;
    carga_pedidos(global_bloque);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_pedidos(global_bloque);
    };

    /*************************************************************************************
    *                              CARGA PEDIDOS
    **************************************************************************************/
    function carga_pedidos(pnbloque){
        spinner.removeAttribute('hidden');
        console.log("en Carga Pedidos pnbloque="+pnbloque);

        let search_text = document.getElementById("search-input").value;
        let formData    = {};
        if (search_text.trim().length==0) {
            formData = new FormData();
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
            formData.append("lista",'pedidos-lista');
            formData.append("search-text","");
            formData.append("search-options","todos");
        }else{
            formData = new FormData(document.getElementById('form-search'));
            formData.append("accion","barra-search");
            formData.append("bloque",global_bloque);
            formData.append("lista",'pedidos-lista');
        }

        let filter = new FormData(document.getElementById('form-filter'));
        for (let pair of filter.entries()) {
            formData.append(pair[0], pair[1]);
        }


        fetch('api/ap_pedidos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            if (global_bloque == 1){
                document.getElementById("pedidos-body").innerHTML = responseJson.html_lista;
            }else{
                document.getElementById("pedidos-body").insertAdjacentHTML("beforeend",responseJson.html_lista);
            }
            spinner.setAttribute('hidden', '');
        });
        //delete element, css_styles, css_opacity,formData;
        search_text = formData = filter = null;
    };


    /*************************************************************************************
    *                              PEDIDO PRINT
    **************************************************************************************/
    function pedido_print(idpedido){
        spinner.removeAttribute('hidden');
        fetch('reportes/pedido_detallado.php',{method:'POST',body:'idpedido='+idpedido,headers: {'Content-type':'application/x-www-form-urlencoded'}})
        .then((response) => response.text())
        .then((responseText) => {
            spinner.setAttribute('hidden', '');
            window.open('/wms/tmp/'+responseText, '_blank'); // open the pdf in a new window/tab
        });
        idpedido = null;
    };

    
    /*************************************************************************************
    *                              PEDIDO CONSULTA
    * Abre la pantalla modal con los datos estadísticos del cliente indicado
    * Hace 3 solicitudes de API asincronamente.
    **************************************************************************************/
    function pedido_consulta(idpedido,idcliente){
        // let idpedido = pidpedido;
        // let idcliente = pidcliente
        spinner.removeAttribute('hidden');
        /* Limpiamos los valores de los div */
        document.getElementById("MesesModal").innerHTML  = "";
        document.getElementById("PedidosModalFacturas").innerHTML  = "";
        document.getElementById("status-pedido").value  = null;
        let myModal = new bootstrap.Modal(document.getElementById("PedidosModal"),{});
        myModal.show();

        let url = new URL(location.origin + '/wms/api/ap_cliente.php');
        url.searchParams.append('idcliente', idcliente);
        url.searchParams.append('accion', 'deuda_detalle');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                console.log("idpedido2="+idpedido);
                console.log("idcliente2="+idcliente);
                spinner.setAttribute('hidden','');
                document.getElementById("pedido-idpedido").innerText = idpedido;
                document.getElementById("ModalIdCliente").value      = idcliente;
                document.getElementById("pedido-monto").innerText    = document.getElementById(idpedido).children[4].innerText;
                document.getElementById("ModalCliente").innerText    = document.getElementById(idpedido).children[2].innerText;
                document.getElementById("PedidosModalFacturas").innerHTML  = responseJson.html_deuda;
        });

        url.searchParams.delete('accion');
        url.searchParams.append('accion', 'meses_anteriores');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                spinner.setAttribute('hidden', '');
                document.getElementById("MesesModal").innerHTML  = responseJson.html_meses;
                document.getElementById("fact_avg").value  = responseJson.fact_avg;
        });

        url.searchParams.delete('accion');
        url.searchParams.append('accion', 'factura_uno');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                spinner.setAttribute('hidden', '');
                document.getElementById("factura_uno").value  = responseJson.factura_uno;
        });

        url.searchParams.delete('accion');
        url.searchParams.append('accion', 'cobros_sin_procesar');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                spinner.setAttribute('hidden', '');
                document.getElementById("cobros_sin_procesar").value  = responseJson.monto;
        });        
        myModal = url = null;
    };


    /*************************************************************************************
    *                              PACKING BULTOS
    * Muestra los bultos en los que se ha distribuido los productos en Packing
    **************************************************************************************/
    function packing_bultos(idpedido){
        spinner.removeAttribute('hidden');
        document.getElementById("ModalIdPedido").value   = idpedido;
        document.getElementById("BultosTabla").innerHTML = "";
        document.getElementById("BultosTabla").innerHTML = "";
        let myModal = new bootstrap.Modal(document.getElementById("BultosModal"),{});
        myModal.show();

        let url = new URL(location.origin + '/wms/api/ap_packing.php');
        url.searchParams.append('idpedido', idpedido);
        url.searchParams.append('accion', 'packing-bultos');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                if (responseJson.response == 'success'){
                    //document.getElementById("ModalIdPacking").value  = responseJson.idpacking;
                    document.getElementById("FacturaNota").innerHTML = responseJson.nota;
                    document.getElementById("BultosTabla").innerHTML = responseJson.html_bultos;
                }else{
                    document.getElementById("FacturaNota").innerHTML = responseJson.error_msj;
                }
                spinner.setAttribute('hidden','');
        });
        myModal = url = null;
    }

    /*************************************************************************************
    *                              CHANGE STATUS
    **************************************************************************************/
    function cambiar_estatus(){
        let myModalEl = document.getElementById('PedidosModal');
        let modal     = bootstrap.Modal.getOrCreateInstance(myModalEl);
        let idpedido  = document.getElementById("pedido-idpedido").innerText;
        let accion    = document.getElementById("status-pedido").value;
        spinner.removeAttribute('hidden');
        let data = {accion:accion, id_pedido:idpedido};
        fetch('api/ap_pedidos.php',{method:'PUT',body:JSON.stringify(data),headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if(responseJson.response == 'success'){
                document.getElementById(idpedido).children[5].innerHTML = responseJson.html_status;
                Swal.fire({icon:'success', title:'Datos Guardados', showConfirmButton:false, timer:1500});
            }else{
                Swal.fire({icon:responseJson.error_tpo, title:'Datos No guardados', text:responseJson.error_msj});
            }
            idpedido = accion = data = null;
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon:'error', title:'No se pudo cargar lista', text:err.message});
        });
        modal.hide();
        myModalEl = modal = null;
    }

    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        console.log("barra buscar");
        event.preventDefault();

        global_bloque = 1;
        carga_pedidos(global_bloque);
        toggle_barra_search();
    };    

    /*************************************************************************************
    *                             FILTRO STATUS
    **************************************************************************************/
    function filtro_pedidos(opcion){
        if (opcion == 'TODOS'){
            document.getElementById("status-nuevo").checked  = false;
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
    *                             FILTRO STATUS
    **************************************************************************************/
    function filtro_prioridad(opcion){
        if (opcion == 999){
            document.getElementById("prioridad-normal").checked  = false;
            document.getElementById("prioridad-urgente").checked = false;
        }else{
            document.getElementById("prioridad-todos").checked = false;
        }
        opcion = null;
    };   

    /*************************************************************************************
    *                             APLICA FILTRO
    **************************************************************************************/
    function aplica_filtro(){
        global_bloque = 1;
        carga_pedidos(global_bloque);
    };
</script>