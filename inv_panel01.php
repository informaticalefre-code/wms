<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/inv_panel01.css">
</head>
<body>
<?php
    require 'config/barra.html';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <!-- <div><h4 class='titulos_categorias'>Panel de Inventario</h4></div> -->
        <div id="body-main">
            <div id="productos-section" class="section-card">
                <div id="title-total" class="title-section">General</div>
                <div id="total-sku" class="card-data">
                    <div class="card-data-body">
                        <div>
                            <h1 id="card-data-total" class="card-data-number"></h1>
                            <small class="sku-title">&nbspsku</small>
                        </div>
                        <div id="card-contados" class="card-data-contados">
                            <h2 id="card-data-contados" class="card-data-number">0</h2>
                            <p id="card-data-porcentaje" class="card-data-number2">0%</p>
                        </div>
                    </div>
                    <div class="card-data-footer">
                        <h4 class="card-data-title">Total General</h4>
                    </div>
                </div>
                <div id="total-con-ubicaciones" class="card-data">
                    <div class="card-data-body">
                        <div>
                            <h1 id="card-data-total" class="card-data-number"></h1>
                            <small class="sku-title">&nbspsku</small>
                        </div>
                        <div id="card-contados" class="card-data-contados">
                            <h2 id="card-data-contados" class="card-data-number">0</h2>
                            <p id="card-data-porcentaje" class="card-data-number2">0%</p>
                        </div>
                    </div>
                    <div class="card-data-footer">
                        <h4 class="card-data-title">Con Ubicaciones</h4>
                    </div>
                </div>
                <div id="total-sin-ubicaciones" class="card-data">
                    <div class="card-data-body">
                        <div>    
                            <h1 id="card-data-total" class="card-data-number"></h1>
                            <small class="sku-title">&nbspsku</small>
                        </div>
                        <div id="card-contados" class="card-data-contados">
                            <h2 id="card-data-contados" class="card-data-number">0</h2>
                            <p id="card-data-porcentaje" class="card-data-number2">0%</p>
                        </div>
                    </div>
                    <div class="card-data-footer">
                        <h4 class="card-data-title">Sin Ubicaciones</h4>
                    </div>
                </div>                
            </div>
            <div id="users-section" class="section-card">
                <div id="title-total" class="title-section">Usuarios Contadores</div>
            </div>
        </div>
    </main>
<?php
    require 'config/footer.html';
?>


<script type = "text/JavaScript">
    "use strict";
    const spinner = document.getElementById("spinner");
    let search_url   = new URLSearchParams(window.location.search);
    const gidinventario = search_url.get('id');

    carga_contadores(gidinventario);

    function barra_back_btn(){
        window.location.replace(location.origin + "/wms/inv_procesos.php");
    }


    var source = new EventSource("inv_panel01_SSE.php?id="+gidinventario);
    source.onmessage = function(event){
        // console.log("evento "+event.data);
        // console.log("evento "+event.data);
        let datos = JSON.parse(event.data);
        let productos = datos.productos;
        let contadores = datos.contadores;
        update_totales(productos);
        update_contadores(contadores);
        //console.log(productos);
        //console.log(contadores);
        //console.log("innerHTML"+document.getElementById("score").innerHTML);
        //document.getElementById("score").innerHTML= event.data;
        datos = productos = contadores = null;
    };

    /**************************************************************************
    *                         UPDATE CONTADORES
    ***************************************************************************/
    function update_contadores(data){
        if (data.length > 0){
            for (let i = 0; i < data.length; i++) {
                let contador = document.getElementById("contador-"+data[i].username);
                let porcentaje = data[i].contados / data[i].sku * 100;
                porcentaje = Math.floor(porcentaje);
                contador.querySelector('#card-data-sku').innerText = data[i].sku;
                contador.querySelector('#card-data-contados').innerText = data[i].contados;
                contador.querySelector('#card-data-porcentaje').innerText = porcentaje+"%";
            }
        }
    }

    /**************************************************************************
    *                         UPDATE TOTALES
    ***************************************************************************/
    function update_totales(data){
        // console.log(data);
        let total = document.getElementById("total-sku");
        let porcentaje = data.contados_total / data.sku_total * 100;
        porcentaje = Math.floor(porcentaje);
        total.querySelector('#card-data-total').innerText = data.sku_total;
        total.querySelector('#card-data-contados').innerText = data.contados_total;
        total.querySelector('#card-data-porcentaje').innerText = porcentaje+"%";

        total = document.getElementById("total-con-ubicaciones");
        porcentaje = data.contados_con_ubicacion / data.sku_con_ubicacion * 100;
        porcentaje = Math.floor(porcentaje);
        total.querySelector('#card-data-total').innerText = data.sku_con_ubicacion;
        total.querySelector('#card-data-contados').innerText = data.contados_con_ubicacion;
        total.querySelector('#card-data-porcentaje').innerText = porcentaje+"%";

        total = document.getElementById("total-sin-ubicaciones");
        porcentaje = data.contados_sin_ubicacion / data.sku_sin_ubicacion * 100;
        porcentaje = Math.floor(porcentaje);
        total.querySelector('#card-data-total').innerText = data.sku_sin_ubicacion;
        total.querySelector('#card-data-contados').innerText = data.contados_sin_ubicacion;
        total.querySelector('#card-data-porcentaje').innerText = porcentaje+"%";

        // console.log(data.sku_total);
        // console.log(data.sku_con_ubicacion);
        // console.log(data.sku_sin_ubicacion);
        // console.log(data.contados_total);
        // console.log(data.contados_con_ubicacion);
        // console.log(data.contados_sin_ubicacion);
    }


    /*************************************************************************************
    *                           CARGA CONTADORES
    **************************************************************************************/
    async function carga_contadores(idinventario){
        let url = new URL(location.origin + '/wms/api/ap_inventarios.php');
        url.searchParams.append('id', idinventario);
        url.searchParams.append('accion', 'contadores');

        let users_section = document.getElementById("users-section");
        fetch(url,{method:'GET', headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.json())
        .then((responseJson) => {
            for (const element of responseJson) {
                let html = '';
                html += '<div id="contador-'+element+'" class="card-data">';
                html += '<div class="card-data-body">';
                html += '<div>';
                html += '<h1 id="card-data-sku" class="card-data-number">0</h1>';
                html += '<small class="sku-title">&nbspsku</small>';
                html += '</div>';
                html += '<div id="card-contados" class="card-data-contados">';
                html += '<h2 id="card-data-contados" class="card-data-number">0</h2>';
                html += '<p id="card-data-porcentaje" class="card-data-number2">0%</p>';
                html += '</div>';
                html += '</div>';
                html += '<div class="card-data-footer">';
                html += '<h4 class="card-data-title">'+element+'</h4>';
                html += '</div>';
                html += '</div>';
                
                users_section.insertAdjacentHTML('beforeend',html);
            }
        });
        idinventario = url = null;
    };
</script>