<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/pedidos_listpick.css">
    <link rel="stylesheet" type="text/css" href="css/pedido_card.css">
</head>
<body>
<?php
    require 'config/header_barra_pedidos.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Selección de Tareas de Picking</h4></div>
        <div id="pedidos-lista-picking"></div>
        <div id="div_boton_mas">
        <button type="button" name="lista_carga_mas" id="lista_carga_mas" class="btn btn-primary form-control" onclick="load_more()">Ver más</button>
        <input name="lista_bloque" type="hidden" value="1">
        </div>
    </main>

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
    const spinner = document.getElementById("spinner");
    global_bloque = 1;
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
        console.log("pnbloque="+pnbloque);

        let search_text =  document.getElementById("search-input").value ;
        
        if (search_text.trim().length==0) {
            var formData = new FormData();
            formData.append("search-text","");
            formData.append("search-options","todos");
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
            formData.append("lista",'pedidos-aprobados');
        }else{
            var formData = new FormData(document.getElementById('form-search'));
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
        formData.append("lista", 'pedidos-aprobados');

        fetch('api/ap_pedidos.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            toggle_barra_search();
            document.getElementById("pedidos-lista-picking").innerHTML = responseJson.html_lista;
            spinner.setAttribute('hidden', '');
        });
        delete formData;
    };


    /*************************************************************************************
    *                              PEDIDO PICK TAREA
    * Convierte el pedido seleccionado en tarea de Picking y lo asigna al usuario
    * actual.
    **************************************************************************************/
    function pedido_picktarea(numero_ped,cliente_ped){
        console.log('numero_ped:'+numero_ped);
        var pedido   = document.getElementById(numero_ped);
        var cliente  = pedido.children[1].children[1].textContent
        Swal.fire({title: '¿Asignar la tarea de Picking del Pedido Nro. '+numero_ped,
                                text: "Cliente: "+cliente,
                                icon: 'question',
                    showCancelButton: true,
                  confirmButtonColor: '#339933',
                   cancelButtonColor: '#cc3300',
                   confirmButtonText: 'Si',
                    cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.value){
                            var myAsyncFunction = async () => {
                                // const response = await fetch('api/ap_picking.php',{method:'POST',body:'numero_ped='+numero_ped,headers: { 'Content-type': 'application/x-www-form-urlencoded'}});
                                const response = await fetch('api/ap_picking.php',{method:'POST',body:'idpedido='+numero_ped+'&accion=picking-insert',headers: { 'Content-type': 'application/x-www-form-urlencoded'}});
                                var data = await response.json();
                                return data;
                            };

                            myAsyncFunction()
                            .then((data) => {
                                if(data.response == 'success'){
                                    pedido.classList.add('removed-pedido');
                                    Swal.fire({icon: 'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                                    setTimeout(function(){pedido.remove();}, 1500);
                                }else{
                                    Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
                                }
                            })
                            .catch((err) => {
                                console.log("rejected", err.message);
                            });
                        }
                    });
    }

    /*************************************************************************************
    *                              PEDIDO PRODUCT LIST
    * Muestra los productos que contiene el pedido.
    **************************************************************************************/    
    function pedido_productlist(idpedido){
        spinner.removeAttribute('hidden');
        document.getElementById("idpedido2").value = idpedido;
        document.getElementById("ProductosLista").innerHTML = "";
        const myModal = new bootstrap.Modal(document.getElementById("ProductListModal"),{});

        var url = new URL(location.origin + '/wms/api/ap_pedidos.php');
        url.searchParams.append('idpedido', idpedido);
        url.searchParams.append('accion', 'pedido_productlist');
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
            .then((response) => response.json())
            .then((responseJson) => {
                spinner.setAttribute('hidden','');
                myModal.show();
                document.getElementById("ProductosLista").innerHTML = responseJson.html_productos;
        });        
        delete myModal,url;
    }
</script>