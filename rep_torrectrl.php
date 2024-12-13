<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <meta http-equiv="Cache-Control" content="no-store">
</head>
<body>
<?php
    require 'config/barra.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h3 class='titulos_categorias'>Consultas Torre de</h3></div>
        <div id="consulta" class="accordion" id="accordionPanelsStayOpenExample">
            <div class="accordion-item">
                <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                    Tareas de Picking y Packing por Persona
                </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                    <div>
                        Muestra un cuadro con los preparadores y las respectivas tareas de Picking consolidadas por ellos en un rango de fechas determinado. La fecha es aquella en la que los preparadores consolidan el Pedido en las pistas.<br>
                    </div>
                    <div class="container-rep-fechas" style="display: flex; gap: 20px; justify-content: center; align-items: center; flex-wrap: nowrap; flex-direction: row;">
                        <div class="input-group" style="width:15rem;">
                            <span class="input-group-text">desde</span>
                            <input id="rep-desde" class="form-control" type="date" name="desde" autocomplete="off">
                        </div>
                        <div class="input-group" style="width:15rem;">
                            <span class="input-group-text">hasta</span>
                            <input id="rep-hasta" class="form-control" type="date" name="hasta" autocomplete="off">
                        </div>                        
                    </div>
                    <div>
                        Para generar el reporte haz clic en el siguiente botón 
                    </div>
                    <button type="button" id="rep-estadisticas-prep" class="btn btn-success btn-sm" onclick="rep_estadisticas_prep()">Generar&nbsp<i class="icon-download3"></i></button>
                    <!-- <a class="btn btn-success btn-sm" href="reportes/reporte_etiquetas.php" role="button">Generar&nbsp<i class="icon-download3"></i></a> -->
                </div>
            </div>
        </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                    Pedidos clasificados por Estatus
                </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                    <div>
                        Muestra un cuadro con los pedidos clasificados por estatus, en conjunto al aprobador, fecha de aprobación y número de pedido.<br>
                        <br>
                        <br>
                    </div>
                    <div>
                        Para generar el reporte haz clic en el siguiente botón 
                    </div>
                    <button type="button" id="rep-pedidos-prep" class="btn btn-success btn-sm" onclick="rep_pedidos_estatus()">Generar&nbsp<i class="icon-download3"></i></button>
                    <!-- <a class="btn btn-success btn-sm" href="reportes/reporte_etiquetas.php" role="button">Generar&nbsp<i class="icon-download3"></i></a> -->
                </div>
    </main>
<?php
    require 'config/footer.html';
?>
<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/index.php");
    }

    function rep_estadisticas_prep(){
        let fecha1 = document.getElementById("rep-desde").value;
        let fecha2 = document.getElementById("rep-hasta").value;
        console.log(fecha1);
        console.log(fecha2);
        let url = new URL(location.origin + '/wms/reportes/estadisticas_almacen.php');
        url.searchParams.append('desde', fecha1);
        url.searchParams.append('hasta', fecha2);
        console.log(url.href);
        location.href = url.href;
    }

    function rep_pedidos_estatus(){
        let fecha1 = document.getElementById("rep-desde").value;
        let fecha2 = document.getElementById("rep-hasta").value;
        console.log(fecha1);
        console.log(fecha2);
        let url = new URL(location.origin + '/wms/reportes/pedidos_por_estatus.php');
        url.searchParams.append('desde', fecha1);
        url.searchParams.append('hasta', fecha2);
        console.log(url.href);
        location.href = url.href;
    }
</script>