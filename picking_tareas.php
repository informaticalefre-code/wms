<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/picking_tareas.css">
</head>
<body>
<?php
    require 'config/barra.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Tareas de Picking Asignadas</h4></div>
        <div id="pedidos-lista-picking"></div>
    </main>
<?php
    require 'config/footer.html';
?>


<script type = "text/JavaScript">
    const spinner = document.getElementById("spinner");
    carga_asignados_picking();

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function carga_asignados_picking(){
        spinner.removeAttribute('hidden');
        let data = {accion:"lista-tareas-picking"};
        let url = new URL(location.origin + '/wms/api/ap_picking_tareas.php');
        url.search = new URLSearchParams(data).toString();
        fetch(url,{method:'GET',headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.text())
        .then((responseText) => {
            document.getElementById("pedidos-lista-picking").innerHTML = responseText;
            spinner.setAttribute('hidden', '');
            delete response, responseText;
        });
        delete data, url;
    }


    function picking_start(idpicking){
        let url = new URL(location.origin + '/wms/picking.php');
        url.searchParams.append('id_picking', idpicking);
        window.location.href = url;
    }
</script>
