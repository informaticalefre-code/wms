<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/pedido_card.css">
    <link rel="stylesheet" type="text/css" href="css/pedidos_assign.css">
</head>
<body>
<?php
    require 'config/header_barra_pedidos.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Asignación de Tareas de Picking</h4></div>
        <div id="contenedor">
            <aside id="panel-left">
                <div id="pedidos-lista-picking"></div>            
                <div id="div_boton_mas">
                    <button type="button" name="lista_carga_mas" id="lista_carga_mas" class="btn btn-primary form-control" onclick="load_more()">Ver más</button>
                    <input name="lista_bloque" type="hidden" value="1">
                </div>
            </aside>
            <aside id="panel-right"></aside>
        </div>
    </main>

    <div class="modal fade" id="AssignModal" tabindex="-1" aria-labelledby="AssignModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="assign-pedido-form" onsubmit="submit_assign_form(event)" autocomplete="off">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="AssignModalLabel">Pedido Nro.&nbsp</h5>
                        <input id="idpedido" type="text" name="idpedido" value="error..." class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2" id="AssignModalBody">
                        <label for="cliente">Cliente</label>
                        <input id="cliente" class="form-control" type="text" name="cliente" value="<Error...>" aria-label="nombre cliente" autocomplete="off" readonly>
                        <br>
                        <label for="preparador">Asignar Pedido al Preparador:</label>
                        <select name="preparador" id="preparador" class="form-select" aria-label="Default select example">
                        </select>
                        <br>
                        <!-- <div class="col mb-2"> -->
                            <label for="prioridad" class="form-label mb-0">Prioridad</label>
                            <select class="form-select" id="prioridad" name="prioridad" aria-label="Prioridad de la tarea Normal o Urgente">
                                <option value="0">Normal</option>
                                <option value="1">Urgente</option>
                            </select>
                        <!-- </div> -->
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="assign-pedido-form" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ProductListModal" tabindex="-1" aria-labelledby="AssignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="ProductListLabel">Pedido Nro.&nbsp</h5>
                    <input id="idpedido2" type="text" name="idpedido2" value="error..." class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2" id="ProductosLista">
                </div>
            </div>
        </div>
    </div>    
<?php
    require 'config/footer.html';
?>
<script type="text/javascript" src="js/toggle_barra_search.js"></script>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    const myModal = new bootstrap.Modal(document.getElementById("AssignModal"),{});
    var global_bloque = 1;
    carga_pedidos(global_bloque);
    carga_preparadores('todos');

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
        console.log("pnbloque="+pnbloque);

        let element = document.getElementById("barra-cont-form-search");
        let css_styles = getComputedStyle(element);
        let css_opacity = css_styles.getPropertyValue('opacity');
        let formData = {};

        if (css_opacity==0) {
            formData = new FormData();
            formData.append("search-text","");
            formData.append("search-options","todos");
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
            formData.append("lista",'pedidos-aprobados');
        }else{
            formData = new FormData(document.getElementById('form-search'));
            formData.append("accion","barra-search");
            formData.append("bloque",global_bloque);
            formData.append("lista",'pedidos-aprobados');
        }

        fetch('api/ap_pedidos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("pedidos-lista-picking").insertAdjacentHTML("beforeend",responseJson.html_lista);
            spinner.setAttribute('hidden', '');
        });
        element = css_styles = css_opacity = formData = null;
    };


    /*************************************************************************************
    *                             CARGA PREPARADORES
    **************************************************************************************/
    function carga_preparadores(preparador){
        spinner.removeAttribute('hidden');
        let url = new URL(location.origin + '/wms/api/ap_preparadores.php');
        url.searchParams.append('accion', 'assign-stats');
        url.searchParams.append('preparador', preparador);
        
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            let card_preparador = document.getElementById(preparador);
            if (preparador !== 'todos'){
                console.log("card_preparador:"+card_preparador);
                if (card_preparador != null){
                    card_preparador.remove();
                }
            }
            document.getElementById("panel-right").insertAdjacentHTML("beforeend",responseJson.html_prepa);
            spinner.setAttribute('hidden', '');
            card_preparador = null;
        });
        url = null;
    };


    /*************************************************************************************
    *                             PEDIDO PICKING TAREA
    **************************************************************************************/
    function pedido_picktarea(numero_ped){
        spinner.removeAttribute('hidden');
        document.getElementById("assign-pedido-form").reset();
        document.getElementById("idpedido").value = numero_ped;
        document.getElementById("cliente").value = document.getElementById(numero_ped).querySelector('#card_cliente').innerText;
        // console.log(document.getElementById(numero_ped));
        // console.log(document.getElementById(numero_ped).querySelector('#card_cliente'));
        // console.log(document.getElementById(numero_ped).querySelector('#card_cliente').innerText);

        let url = new URL(location.origin + '/wms/api/ap_preparadores.php');
        url.searchParams.append('accion', 'preparadores');
        
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden','');
            document.getElementById("preparador").innerHTML = responseJson.html_preparadores;
            //const myModal = new bootstrap.Modal(document.getElementById("AssignModal"),{});
            myModal.show();
            //myModal = null;
        });
        url = null;
    }


    /**************************************************************************
    * SUBMIT VENTANA MODAL DE ASIGNACION DE PEDIDOS
    ***************************************************************************/
    function submit_assign_form(event) {
        event.preventDefault();
        spinner.removeAttribute('hidden');
        let pedido     = document.getElementById("idpedido");
        let preparador = document.getElementById("preparador").value;
        let prioridad  = document.getElementById("prioridad").value;
        var htmlpedido = document.getElementById(pedido.value);
        fetch('api/ap_picking.php', {method:'POST', body:'idpedido='+idpedido.value+'&accion=picking-insert'+'&preparador='+preparador+'&prioridad='+prioridad, headers:{'Content-type':'application/x-www-form-urlencoded'}})
        .then((response) => response.json())
        .then((data) =>{
            spinner.setAttribute('hidden', '');
            if(data.response == 'success'){
                //let myModalEl = document.getElementById('AssignModal');
                //let modal     = bootstrap.Modal.getOrCreateInstance(myModalEl);
                carga_preparadores(document.getElementById("preparador").value);
                myModal.hide();
                htmlpedido.classList.add('removed-pedido');
                Swal.fire({icon:'success', title:'Datos Guardados', showConfirmButton:false, timer:1500});
                setTimeout(function(){htmlpedido.remove();}, 1500);
                //delete html_pedido, myModalEl, modal;
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
            }
        }).catch((err) => {
            console.log("rejected:---", err.message);
        });
        //delete pedido, preparador, prioridad;
    }


    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        event.preventDefault();
        spinner.removeAttribute('hidden');

        global_bloque = 1;
        let formData = new FormData(document.getElementById('form-search'));
        formData.append("accion", "barra-search");
        formData.append("bloque", global_bloque);
        formData.append("lista", 'pedidos-aprobados');

        fetch('api/ap_pedidos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            toggle_barra_search();
            document.getElementById("pedidos-lista-picking").innerHTML = responseJson.html_lista;
            spinner.setAttribute('hidden', '');
        });
        formData = null;
    };

    /*************************************************************************************
    *                              PEDIDO PRODUCT LIST
    * Muestra los productos que contiene el pedido.
    **************************************************************************************/    
    function pedido_productlist(idpedido){
        spinner.removeAttribute('hidden');
        document.getElementById("idpedido2").value = idpedido;
        document.getElementById("ProductosLista").innerHTML = "";

        let url = new URL(location.origin + '/wms/api/ap_pedidos.php');
        url.searchParams.append('idpedido', idpedido);
        url.searchParams.append('accion', 'pedido_productlist');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                let myModal2 = new bootstrap.Modal(document.getElementById("ProductListModal"),{});
                spinner.setAttribute('hidden','');
                myModal2.show();
                document.getElementById("ProductosLista").innerHTML = responseJson.html_productos;
                myModal2 = null;
        });        
        url = null;
    }
</script>