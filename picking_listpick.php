<?php
    require 'user-auth-frontend.php';
    require 'config/header_head.html';
?>
    <!-- Personales      ----------------------------------------------->
    <link rel="stylesheet" type="text/css" href="css/picking_tareas.css">
</head>
<body>
<?php
    require 'config/header_barra_pedidos.php';
?>
    <main id="main_screen">
        <div hidden id="spinner"></div>
        <div><h4 class='titulos_categorias'>Selección de Tareas de Packing</h4></div>
        <div id="pedidos-lista-picking"></div>
        <div id="div_boton_mas">
            <button type="button" name="lista_carga_mas" id="lista_carga_mas" class="btn btn-primary form-control" onclick="load_more()">Ver más</button>
        <input name="lista_bloque" type="hidden" value="1">
        </div>
    </main>
<?php
    require 'config/footer.html';
?>



<script type = "text/JavaScript">
    const spinner = document.getElementById("spinner");
    carga_tareas_picking();
    
    function carga_tareas_picking(){
        spinner.removeAttribute('hidden');
        var url = new URL(location.origin + 'api/ap_lista_picking.php');
        var txt_search = document.getElementById("search-input").value;
        var data = {accion:"LISTA CULMINADOS",txt_a_buscar: txt_search};

        var x = fetch(url,{method:'POST',body:JSON.stringify(data),headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.text())
        .then((responseText) => {
            var x = document.getElementById("pedidos-lista-picking");
            x.innerHTML = responseText;
            spinner.setAttribute('hidden', '');
        });
    }
    
    function carga_tareas_picking(){
        spinner.removeAttribute('hidden');
        txt_search = document.getElementById("search-input").value;
        data = {accion:"LISTA CULMINADOS",txt_a_buscar: txt_search};
        
        var x = fetch('api/ap_lista_picking.php',{method:'POST',body:JSON.stringify(data),headers: {'Content-type':'application/json; charset=UTF-8'}})
        .then((response) => response.text())
        .then((responseText) => {
            var x = document.getElementById("pedidos-lista-picking");
            x.innerHTML = responseText;
            spinner.setAttribute('hidden', '');
        });
    }

    function pedido_picktarea(numero_ped,cliente_ped){
        console.log('numero_ped:'+numero_ped);
        var pedido   = document.getElementById(numero_ped);
        var cliente  = pedido.children[1].children[1].textContent
        Swal.fire({title: '¿Asignar la tarea de Packing del Pedido Nro. '+numero_ped,
                                text: "Cliente: "+cliente,
                                icon: 'question',
                    showCancelButton: true,
                  confirmButtonColor: '#339933',
                   cancelButtonColor: '#cc3300',
                   confirmButtonText: 'Si',
                    cancelButtonText: 'No'
                    }).then((result) => {
                        if (result.value){
                            console.log('SI');
                        }
                    });
    }
</script>
