<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/inv_procesos.css">
</head>
<body>
<?php
    require 'config/header_barra_filter.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div style="display:flex;justify-content:space-between;">
            <h4 class='titulos_categorias'>Procesos de Inventario</h4>
            <button id="btn-add" class="btn btn-success" onclick="add_new()">Nueva&nbsp<i class="icon-plus"></i></button>
        </div>
        <div id="inventarios-lista">
            <table id="inventarios-tabla" cellspacing="0">
                <thead>
                <tr class="tabla-header" style="text-align:center;">
                    <th>Id.</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estatus</th>
                    <th>Observación</th>
                    <th>Acción</th>
                </tr>
                </thead>
                <tbody id="inventarios-body">
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
                    <input class="form-check-input" onchange="filtro_status(999);" type="checkbox" id="status-todos" name="status-todos" checked>
                    <label class="form-check-label" for="status-todos">Todos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_status(0);" type="checkbox" id="status-anulado" name="status-anulado">
                    <label class="form-check-label" for="status-anulado">Anulados</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_status(1);" type="checkbox" id="status-proceso" name="status-proceso">
                    <label class="form-check-label" for="status-proceso">En Proceso</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" onchange="filtro_status(5);" type="checkbox" id="status-culminado" name="status-culminado">
                    <label class="form-check-label" for="status-culminado">Culminados</label>
                </div>
                <hr>
                <div>
                    <button type="button" name="aplicar_fitro" id="aplicar_filtro" data-bs-dismiss="offcanvas" class="btn btn-primary" onclick="aplica_filtro();">Filtrar</button>
                </div>                
            </form>
        </div>
    </div>

    <div class="modal fade" id="InventarioModal" tabindex="-1" aria-labelledby="InventarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="InventarioModalLabel">Crear Proceso Nuevo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="inventario-form" onsubmit="submit_new_process(event)">
                    <div class="modal-body py-2" id="InventarioModal-body">
                        <div>
                            <label for="input-observacion">Observación</label>
                            <textarea class="form-control" name="input-observacion" aria-label="With textarea" maxlength="50"></textarea>
                        </div>
                        <!-- <hr class="my-2"> -->
                        <br>
                        <div class="d-flex flex-nowrap mb-2">
                            <h6>Contadores</h6>
                            <button type="button" class="col-1" onclick="add_more_people();" name="add_places" id="add_places" style="border:none;background-color:white;width:40px;"><i style="color:green;" class="icon-plus-circle"></i></button>
                        </div>
                        <div id="container-selects">
                            <div id="contador-1" class="container-campo select-usr">
                                <select id="contador" name="contador[]" class="form-select contadores" placeholder="Contador..." aria-label="Contador o usuario asignado">
                                </select>
                                <button type="button" onclick="del_people(1);" name="add_places" id="add_places" style="border:none;background-color:white;width:40px;"><i style="color:red;" class="icon-minus-circle"></i></button>
                            </div>                                
                            <div id="contador-2" class="container-campo select-usr">
                                <select id="contador" name="contador[]" class="form-select contadores" placeholder="Contador..." aria-label="Contador o usuario asignado">
                                </select>
                                <button type="button" onclick="del_people(2);" name="add_places" id="add_places" style="border:none;background-color:white;width:40px;"><i style="color:red;" class="icon-minus-circle"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="inventario-form" class="btn btn-primary">Guardar</button> 
                    </div>
                </form>
            </div>
        </div>
    </div>    
<?php
    require_once 'bultos_modal.html';
    require 'config/footer.html';
?>
<script type="text/javascript" src="js/toggle_barra_search.js"></script>

<script type = "text/JavaScript">
    "use strict";
    var html_contadores = "";
    var global_bloque = 1;
    var global_cont   = 2; // Número de contadores.
    const spinner = document.getElementById("spinner");
    const myModal = new bootstrap.Modal(document.getElementById("InventarioModal"),{});
    carga_contadores();
    
    carga_procesos_inventario(global_bloque);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_tareas_packing(global_bloque);
    };

    /*************************************************************************************
    *                         PROCESOS DE INVENTARIO
    **************************************************************************************/
    function carga_procesos_inventario(pnbloque){
        spinner.removeAttribute('hidden');

        let formData = new FormData();
        formData.append("accion","lista-inventarios");
        formData.append("bloque",pnbloque);
        
        let filter   = new FormData(document.getElementById('form-filter'));
        for (let pair of filter.entries()) {
            formData.append(pair[0], pair[1]);
        }
        
        fetch('api/ap_inventarios.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            if (pnbloque == 1) {
                document.getElementById("inventarios-body").innerHTML = responseJson.html_lista;
            }else{
                document.getElementById("inventarios-body").insertAdjacentHTML("beforeend",responseJson.html_lista);
            }
            spinner.setAttribute('hidden', '');
        });
        formData = filter = pnbloque = null;
    };

    /*************************************************************************************
    *                              SUBMIT NEW PROCESS
    * Crea un nuevo proceso de Inventario, haciendo un snapshot de todos los productos
    * activos del Sistema Administrativo al Sistema de Gestión de Almacén.
    **************************************************************************************/
    function submit_new_process(event){
        event.preventDefault();
        spinner.removeAttribute('hidden');

        let formData = new FormData(document.getElementById('inventario-form'));
        formData.append("accion","new-inventario");

        fetch('api/ap_inventarios.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((data) => {
            spinner.setAttribute('hidden', '');
            if(data.response == 'success'){
                myModal.hide();
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
                location.reload();
            }else{
                Swal.fire({icon: data.error_tpo,title: 'Datos No guardados',text: data.error_msj});
            }
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon:'error', title:'Error al crear proceso.', text:err.message});
        });
        formData = null;
    };


    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        event.preventDefault();
        global_bloque = 1;
        carga_tareas_packing(global_bloque);
        toggle_barra_search();
    };    


    /*************************************************************************************
    *                             FILTRO STATUS
    **************************************************************************************/
    function filtro_status(opcion){
        if (opcion == 999){
            document.getElementById("status-anulado").checked   = false;
            document.getElementById("status-proceso").checked   = false;
            document.getElementById("status-culminado").checked = false;
        }else{
            document.getElementById("status-todos").checked = false;
        }
        opcion = null;
    };


    /*************************************************************************************
    *                             APLICA FILTRO
    **************************************************************************************/
    function aplica_filtro(){
        global_bloque   = 1;
        carga_procesos_inventario(global_bloque);
    };


    /*************************************************************************************
    *                             APLICA FILTRO
    **************************************************************************************/
    function add_new(){
        document.getElementById("inventario-form").reset();
        myModal.show();
    }


    /*************************************************************************************
    *                           CARGA CONTADORES
    * Función async await
    **************************************************************************************/
    async function carga_contadores(){
        let url = new URL(location.origin + '/wms/api/ap_preparadores.php');
        url.searchParams.append('accion', 'usuarios-almacen');
        let html = '<option value="">Seleccione...</option>';
        await fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            responseJson.forEach(function(elemento, indice) {
                html += '<option value="'+elemento+'">'+elemento+'</option>';
            });
            let contadores = document.getElementsByClassName("contadores");
            for(let i=0; i<contadores.length; i++){
                contadores[i].insertAdjacentHTML("beforeend",html);
            }
            html_contadores = html;
            contadores = null;
            html = null;
        });
        url = null;

    };    


    /*************************************************************************************
    *                           ADD MORE PEOPLE
    * Agrega contadores
    **************************************************************************************/
    function add_more_people(){
        let contadores = html_contadores;
        let html = '';
        html += '<div id="contador-'+global_cont+'" class="container-campo select-usr">';
        html += '<select id="contador" name="contador[]" class="form-select contadores" placeholder="Contador..." aria-label="Contador o usuario asignado">'
        html += contadores; //variable global
        html += '</select>';
        html += '<button type="button" onclick="del_people('+global_cont+');" name="add_places" id="add_places" style="border:none;background-color:white;width:40px;"><i style="color:red;" class="icon-minus-circle"></i></button>';
        html += '</div>';

        document.getElementById("container-selects").insertAdjacentHTML("beforeend",html);
        global_cont++;
        html = null;
    }


    /**************************************************************************
    *                           DEL PLACES
    * Elimina contadores
    ***************************************************************************/
    function del_people(linea){
        document.getElementById("contador-"+linea).remove();
    }
 </script>