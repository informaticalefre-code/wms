<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
</head>
<body>
<?php
    require 'config/header_barra_prod.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h3 class='titulos_categorias'>Consultas Almacén</h3></div>
        <div id="consulta" class="accordion" id="accordionPanelsStayOpenExample">
            <div class="accordion-item">
                <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                    Archivo .CSV para Excel de Etiquetas
                </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                    Se genera un <strong>archivo .CSV para ser importado en Excel</strong>. Esta data es para ser utilizada en el archivo Excel que imprime las etiquetas de identificación de productos en los racks de almacén.<br>
                    Para descargar el archivo hacer click en el siguiente boton 
                    <a class="btn btn-success btn-sm" href="reportes/csv_etiquetas_rack.php" role="button">Descargar&nbsp<i class="icon-download3"></i></a>
                </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                    Archivo .CSV Para productos sin ubicacion ni codigo de barras
                </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                    Se genera un <strong>archivo .CSV para ser importado en Excel</strong>. Esta data es para identificar cuales productos no tienen ubicaciones o no poseen codigo de barra.<br>
                    Para descargar el archivo hacer click en el siguiente boton 
                    <a class="btn btn-success btn-sm" href="reportes/csv_etiquetas_rack_sin_codigo.php" role="button">Descargar&nbsp<i class="icon-download3"></i></a>
                </div>
                </div>
            </div> 
            <div class="accordion-item">
                <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                    Consulta 3 (en desarrollo)
                </button>
                </h2>
                <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingThree">
                <div class="accordion-body">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Obcaecati quos quisquam rerum id amet reiciendis laborum, neque eius. Sequi quibusdam veniam distinctio quia vel asperiores rem reiciendis molestias fugiat! Qui!
                </div>
                </div>
            </div>
        </div>
    </main>
<?php
    require 'config/footer.html';
?>
<script type="text/javascript" src="js/toggle_barra_search.js"></script>
<script type = "text/JavaScript">
    const spinner = document.getElementById("spinner");
    global_bloque = 1;
    //carga_productos(global_bloque);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }
</script>