<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/pedido_card.css">
    <link rel="stylesheet" type="text/css" href="css/picking_assign.css">
</head>
<body>
<?php
    require 'config/header_barra_pedidos.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Selección de Tareas de Picking</h4></div>
        <div id="contenedor">
            <aside id="panel-left"></aside>
            <aside id="panel-right"></aside>
        </div>
    </main>    

    <div class="modal fade" id="AssignModal" tabindex="-1" aria-labelledby="AssignModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="assign-pedido-form" onsubmit="submit_assign_form(event)">
                    <div class="modal-header py-2">
                        <h5 class="modal-title" id="AssignModalLabel">Pedido Nro.&nbsp</h5>
                        <input id="info-idpedido" type="text" name="info-idpedido" value="error..." class="border-0 col-6" style="font-size:1.25rem;" readonly autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-2" id="AssignModalBody">
                        <input id="info-cliente" class="form-control" type="text" name="info-cliente" value="<Error...>" aria-label="nombre cliente" autocomplete="off" readonly>
                        <br>
                        <select class="form-select" aria-label="Default select example">
                            <option selected>Selecciona un preparador</option>
                            <option value="jcfreites">jcfreites</option>
                            <option value="embalador01">embalador01</option>
                            <option value="preparador02">preparador02</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="Assign-producto-form" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
    require 'config/footer.html';
?>


<script type = "text/JavaScript">
    const spinner = document.getElementById("spinner");
    global_bloque = 1;
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
            document.getElementById("panel-left").insertAdjacentHTML("beforeend",responseJson.html_lista);
            spinner.setAttribute('hidden', '');
        });
        delete search_text, formData;
    };


    /*************************************************************************************
    *                             CARGA PREPARADORES
    **************************************************************************************/
    function carga_preparadores(preparador){
        spinner.removeAttribute('hidden');

        var url = new URL(location.origin + '/wms/api/ap_preparadores.php');
        url.searchParams.append('accion', 'assign-stats');
        url.searchParams.append('preparador', preparador);
        
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})    
        .then((response) => response.json())
        .then((responseJson) => {
            document.getElementById("panel-right").insertAdjacentHTML("beforeend",responseJson.html_prepa);
            spinner.setAttribute('hidden', '');
        });
        delete url;
    };


    /*************************************************************************************
    *                     PEDIDO PICKING TAREA
    **************************************************************************************/
    function pedido_picktarea(numero_ped,cliente_ped){
        document.getElementById("info-idpedido").value = numero_ped;
        document.getElementById("info-cliente").value = cliente_ped;
        var myModal = new bootstrap.Modal(document.getElementById("AssignModal"),{});
        myModal.show();
    }
</script>