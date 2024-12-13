<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/productos_reservados.css">
    <link rel="stylesheet" type="text/css" href="css/boton-back-top.css">
</head>
<body>
<?php
    require 'config/header_barra_prod.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <button onclick="topFunction()" id="myBtn" title="Sube al principio"><i class="icon-arrow-up"></i></button>
        <div><h4 class='titulos_categorias'>Productos Reservados</h4></div>
        <div id="reservados-lista">
            <table id="reservados-tabla" cellspacing="0">
                <thead>
                    <tr class="tabla-header">
                        <th>Código</th>
                        <th>Descripción</th>
                        <th style="text-align:right;">Existencia</th>
                        <th style="text-align:right;">Reservado</th>
                        <th style="text-align:right;">Disponible</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="reservados-body">
                </tbody>
            </table>            
        </div>
        <div id="div_boton_mas" class="my-3">
            <button type="button" name="lista_carga_mas" id="lista_carga_mas" class="btn btn-primary form-control" onclick="load_more();">Ver más</button>
            <input name="lista_bloque" type="hidden" value="1">
        </div>        
    </main>

    
    <div class="modal fade" id="ReservadoModal" tabindex="-1" aria-labelledby="ReservadoModalLabel" aria-hidden="true" >
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ReservadoModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ReservadoModalBody">
                    
                </div>
            </div>
        </div>
    </div>

<?php
    require 'config/footer.html';
?>

<script type="text/javascript" src="js/toggle_barra_search.js"></script>
<!-- boton de back to top -->
<script type="text/javascript" src="js/boton-back-top.js"></script>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    var global_bloque = 1;
    var ReservadoModal = document.getElementById('ReservadoModal');

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function load_more(){
        global_bloque = global_bloque + 1;
        carga_reservados(global_bloque);
    };


    /*************************************************************************************
    *                              CARGA RESERVADOS
    **************************************************************************************/
    function carga_reservados(pnbloque){
        spinner.removeAttribute('hidden');
        // console.log("pnbloque="+pnbloque);
        let search_text =  document.getElementById("search-input").value ;
        let formData = {};

        if (search_text.trim().length==0) {
            formData = new FormData();
            formData.append("search-text","");
            formData.append("search-options","todos");
            formData.append("accion","barra-search");
            formData.append("bloque",global_bloque);
            formData.append("lista",'reservados-lista');
        }else{
            formData = new FormData(document.getElementById('form-search'));
            formData.append("accion","barra-search");
            formData.append("bloque",global_bloque);
            formData.append("lista",'reservados-lista');
        }

        fetch('api/ap_productos_reservados.php',{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            if (global_bloque == 1){
                document.getElementById("reservados-body").innerHTML = responseJson.html;
            }else{
                document.getElementById("reservados-body").insertAdjacentHTML("beforeend",responseJson.html);
            }
            spinner.setAttribute('hidden', '');
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se pudo cargar lista', text:err.message});
        });
        pnbloque = search_text = formData = null;
    };


    /*************************************************************************************
    *                              BARRA BUSCAR
    * Se ejecuta al pulsar el botón buscar de la barra de opciones de la parte superior.
    **************************************************************************************/
    function barra_buscar(){
        event.preventDefault();
        global_bloque = 1;
        toggle_barra_search();
        carga_reservados(global_bloque);
    };

    
    /**************************************************************************
    *                     VER RESERVADOS PEDIDOS
    * Abre una ventana modal y muestra aquellos pedidos en los que fue
    * solicitado el producto seleccionado.
    ***************************************************************************/
    function ver_reservados_pedidos(pid_producto){
        spinner.removeAttribute('hidden');

        let url = new URL(location.origin + '/wms/api/ap_productos_reservados.php');
        url.searchParams.append('idproducto', pid_producto);
        url.searchParams.append('accion', 'pedidos-reservados');

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) =>response.json())
        .then((responseJson) => {
            document.getElementById("ReservadoModalLabel").textContent = 'SKU ' + pid_producto;
            document.getElementById("ReservadoModalBody").innerHTML = responseJson.html;
            let myModal = new bootstrap.Modal(ReservadoModal,{});
            spinner.setAttribute('hidden', '');
            myModal.show();
            myModal = null;
        });
        url = null;
    }
</script>