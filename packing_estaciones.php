<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/packing_estaciones.css">
</head>
<body>
<?php
    require 'config/barra_save.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Mesas de Embalaje</h4></div>
        <form method="post" id="packing-estaciones-data" autocomplete="off">
            <div id="estaciones-container"></div>
        </form>
    </main>
<?php
    require 'config/footer.html';
?>

<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    carga_estaciones();

  
    /* Función que direcciona cuando se pulsa el botón de atrás (no confundir con el del navegador)*/
    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    /**************************************************************************
    * Cargamos las estaciones de trabajo
    ***************************************************************************/
    function carga_estaciones(){
        spinner.removeAttribute('hidden');
        // Tomamos el parametro del ID Picking a cargar
        // Hacemos un fetch api mandando el Nro de la Tarea de Picking.
        let url = new URL(location.origin + '/wms/api/ap_packing_estaciones.php');
        url.searchParams.append('accion', 'packing-estaciones');
        url.searchParams.append('id', 'todas');

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            document.getElementById("estaciones-container").innerHTML = responseJson.html_lista;
        }).catch((err) => {
            spinner.setAttribute('hidden', '');
            Swal.fire({icon: 'error', title:'No se puede cargar mesas de embalaje', text:err.message});
        });
        url = null;
    }

    /**************************************************************************
    *                           CHANGE DETALLE
    * Cambia el valor del input "accion" para determinar que esa linea
    * tuvo cambios y si se pulsa guardar se debe hacer UPDATE a ese registro
    ***************************************************************************/
    function change_record(registro){
        document.getElementById("estacion-"+registro).querySelector('#input-accion').value = 'MODIFICADO';
        registro = null;
    };


    /**************************************************************************
    *                           GUARDAR TAREA
    ***************************************************************************/
    function guardar(){
        spinner.removeAttribute('hidden');
        let url = new URL(location.origin + '/wms/api/ap_packing_estaciones.php');
        let formData = new FormData(document.getElementById('packing-estaciones-data'));
        fetch(url,{method:'POST',body:formData})
        .then((response) => response.json())
        .then((responseJson) => {
            spinner.setAttribute('hidden', '');
            if(responseJson.response == 'success'){
                Swal.fire({icon:'success',title: 'Datos Guardados',showConfirmButton: false,timer: 1500});
            }else{
                Swal.fire({icon: responseJson.error_tpo, title:'Datos No guardados', text:responseJson.error_msj});
            }
        });
        url = formData = null;
    };

</script>

