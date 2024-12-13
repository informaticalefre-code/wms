    /*********************************************************************
    * Listener que captura las lecturas del scanner del código de barra  *
    **********************************************************************/
    document.addEventListener('keydown', function(evt) {
        if (interval)
            clearInterval(interval);
        if (evt.code == 'Enter') {
            if (barcode)
                handleBarcode(barcode);
                barcode = '';
            return;
        }
        console.log("barcode:"+barcode);
        if (evt.key != 'Shift')
            barcode += evt.key;
            interval = setInterval(() => barcode = '', 500);
    });

    /**************************************************************************
    * Función que maneja el código de barras capturado en el Listener Keydown *
    ***************************************************************************/
    function handleBarcode(scanned_barcode){
        const ModalPicking   = document.getElementById("PickingModal").style.display;
        // const ModalContainer = document.getElementById("ContainerModal").style.display
        // console.log("scanned_barcode:"+scanned_barcode);

        if (ModalPicking == 'block'){
            /* Si la ventana modal de picking está abierta verificamos que el código escaneado
               coincida con el del producto */
            let idproducto = document.getElementById("info-idproducto").value;
            let encontrado = false;
            console.log("Id. Producto (en modal):"+idproducto);
            for(var i in global_picking_productos){
                if (Object.values(global_picking_productos[i]).indexOf(scanned_barcode) >= 0){
                    if (Object.values(global_picking_productos[i])[0] == idproducto){
                        document.getElementById("info-cantverif").value++;
                        encontrado = true;
                    }
                }
            }
            if (!encontrado){
                alert("Código No corresponde al producto");
            }
            delete idproducto, encontrado;
        }else{
            if (ModalPicking == "" || ModalPicking == "none"){
                let encontrado  = false;
                let idproducto ;
                for(var i in global_picking_productos){
                    if (Object.values(global_picking_productos[i]).indexOf(scanned_barcode) >= 0){
                        encontrado = true;
                        // Siempre el código del producto está en la posición 0
                        idproducto = Object.values(global_picking_productos[i])[0];
                    }
                }
    
                /* Si el código escaneado coincide con alguno del pedido entonces
                   procedemos abrir la ventana de anclaje */    
                if (encontrado){
                    pick_producto(idproducto);
                }else{
                    Swal.fire({icon: 'error',title: 'código no está en lista',showConfirmButton: false,timer: 2000 });
                }
                delete encontrado, idproducto;
            }
        }


        delete ModalPicking;
    }