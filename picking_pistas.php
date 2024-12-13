<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/picking_verifica.css">
</head>
<body>
<?php
    require 'config/barra.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Verificación Tareas de Picking</h4></div>
        <div id="pistas-container">
            <div class="pistas" id="pista-1">
                <div class="pista-title">Pista 1</div>
            </div>
            <div class="pistas" id="pista-2">
                <div class="pista-title">Pista 2</div>
            </div>
            <div class="pistas" id="pista-3">
                <div class="pista-title">Pista 3</div>
            </div>
            <div class="pistas" id="pista-4">
                <div class="pista-title">Pista 4</div>
            </div>
            <div class="pistas" id="pista-5">
                <div class="pista-title">Pista 5</div>
            </div>
            <div class="pistas" id="pista-6">
                <div class="pista-title">Pista 6</div>
            </div>
            <div class="pistas" id="pista-7">
                <div class="pista-title">Pista 7</div>
            </div>
        </div>
    </main>
<?php
    require 'config/footer.html';
?>

<script type = "text/JavaScript">
    var global_picking_productos;
    carga_pista_picking_all();

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }
    
    /**************************************************************************
    * Abre la ventana modal con los datos referenciales del producto 
    * que se procede anclar al pedido
    ***************************************************************************/
    function carga_pista_picking_all(){
        carga_picking_pista(1);
        carga_picking_pista(2);
        carga_picking_pista(3);
        carga_picking_pista(4);
        carga_picking_pista(5);
        carga_picking_pista(6);
        carga_picking_pista(7);
    }

    function carga_picking_pista(npista){
        spinner.removeAttribute('hidden');

        // Hacemos un fetch api mandando el Nro de Pista.
        var url = new URL(location.origin + '/wms/api/ap_picking_pista.php');
        url.searchParams.append('pista', npista);

        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            // document.getElementById("pista-"+npista).innerHTML = responseJson.html;
            document.getElementById("pista-"+npista).insertAdjacentHTML('beforeend',responseJson.html);
            spinner.setAttribute('hidden', '');
            delete npista, url;
        });
    }
</script>