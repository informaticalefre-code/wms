<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/picking_master.css">
</head>
<body>
<?php
    require 'config/header_barra_tareas.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Tareas de Picking</h4></div>
        <div id="picking-lista">
            <table id="picking-tabla" cellspacing="0">
                <thead>
                    <tr class="tabla-header" style="text-align:center;">
                        <th>Tarea</th>
                        <th>Fecha</th>
                        <th>Pedido</th>
                        <th>Prioridad</th>
                        <th>Preparador</th>
                        <th>Estatus</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="tareas-body">
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
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Estatus:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_status(999);" type="checkbox" id="status-todos" name="status-todos">
                    <label class="form-check-label" for="status-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input"  onchange="filtro_status(0);" type="checkbox" id="status-anulado" name="status-anulado">
                    <label class="form-check-label" for="status-anulado">Anulados</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input"  onchange="filtro_status(1);" type="checkbox" id="status-proceso" name="status-proceso" checked>
                    <label class="form-check-label" for="status-proceso">En Proceso</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_status(3);"type="checkbox" id="status-consolidado" name="status-consolidado" checked>
                    <label class="form-check-label" for="status-consolidado">Consolidados</label>
                </div>            
                <div class="form-check form-switch">
                    <input class="form-check-input"  onchange="filtro_status(5);" type="checkbox" id="status-culminado" name="status-culminado">
                    <label class="form-check-label" for="status-culminado">Culminados</label>
                </div>
                <hr>
                <div class="text-sm-left lh-base font-normal text-none text-decoration-none text-reset">Por Prioridad:</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_prioridad(999);" type="checkbox" id="prioridad-todos" name="prioridad-todos" checked>
                    <label class="form-check-label" for="prioridad-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_prioridad(0);" type="checkbox" id="prioridad-normal" name="prioridad-normal">
                    <label class="form-check-label" for="prioridad-normal">Normal</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_prioridad(1);" type="checkbox" id="prioridad-urgente" name="prioridad-urgente">
                    <label class="form-check-label" for="prioridad-urgente">Urgente</label>
                </div>
                <hr>
                <div>
                    <button type="button" name="aplicar_fitro" id="aplicar_filtro" data-bs-dismiss="offcanvas" class="btn btn-primary" onclick="aplica_filtro();">Filtrar</button>
                </div>                
            </form>
        </div>
    </div>
<?php
    require 'config/footer.html';
?>
<script type="text/javascript" src="js/toggle_barra_search.js"></script>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    var global_bloque = 1;
    carga_tareas_picking(global_bloque);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_tareas_picking(global_bloque);
    };

    /*************************************************************************************
    *                              CARGA PEDIDOS
    **************************************************************************************/
    function carga_tareas_picking(pnbloque){
        spinner.removeAttribute('hidden');

        let search_text = document.getElementById("search-input").value;
        let formData = {};

        if (search_text.trim().length==0) {
            formData = new FormData();
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
            formData.append("search-text","");
            formData.append("search-options","todos");
        }else{
            formData = new FormData(document.getElementById('form-search'));
            formData.append("accion","barra-search");
            formData.append("bloque",pnbloque);
        }

        let filter = new FormData(document.getElementById('form-filter'));
        for (var pair of filter.entries()) {
            formData.append(pair[0], pair[1]);
        }

        fetch('api/ap_pickingmaster.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            console.log("bloque (dentro de response): "+pnbloque);
            if (pnbloque == 1) {
                document.getElementById("tareas-body").innerHTML = responseJson.html_lista;
            }else{
                document.getElementById("tareas-body").insertAdjacentHTML("beforeend",responseJson.html_lista);
            }
            spinner.setAttribute('hidden', '');
        });
        search_text = formData = filter = null;
    };

    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        event.preventDefault();
        global_bloque = 1;
        carga_tareas_picking(global_bloque);
        toggle_barra_search();
    };

    /*************************************************************************************
    *                             FILTRO STATUS
    **************************************************************************************/
    function filtro_status(opcion){
        if (opcion == 999){
            document.getElementById("status-anulado").checked     = false;
            document.getElementById("status-proceso").checked     = false;
            document.getElementById("status-consolidado").checked = false;
            document.getElementById("status-culminado").checked   = false;
        }else{
            document.getElementById("status-todos").checked = false;
        }
        opcion = null;
    };

    /*************************************************************************************
    *                             FILTRO PRIORIDAD
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
        carga_tareas_picking(global_bloque);
    };
    
 </script>