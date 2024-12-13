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
        // console.log("barcode:"+barcode);
        if (evt.key != 'Shift')
            barcode += evt.key;
            interval = setInterval(() => barcode = '', 500);
    });

    /**************************************************************************
    * Función que maneja el código de barras capturado en el Listener Keydown *
    ***************************************************************************/
    function handleBarcode(scanned_barcode){
        const ModalPicking   = document.getElementById("PackingModal").style.display;
        console.log("HandleBarcode ModalPicking="+ModalPicking);
        console.log("Scanned Barcode="+scanned_barcode);
        if (ModalPicking == 'block'){
            console.log("estoy dentro del IF");
            /* Si la ventana modal de picking está abierta verificamos que el código escaneado
               coincida con el del producto */
            let idproducto = document.getElementById("info-idproducto").value;
            let encontrado = false;
            for(var i in global_packing_productos){
                if (Object.values(global_packing_productos[i]).indexOf(scanned_barcode) >= 0){
                    if (Object.values(global_packing_productos[i])[0] == idproducto){
                        document.getElementById("info-cantidad").value++;
                        encontrado = true;
                        console.log("encontrado!!");
                    }
                }
            }
            if (!encontrado){
                alert("Código No corresponde al producto");
            }
            delete idproducto;
        }else{
            console.log("Else");
            if (ModalPicking == "" || ModalPicking == "none"){
                let encontrado  = false;
                let id_producto ;
                for(var i in global_packing_productos){
                    if (Object.values(global_packing_productos[i]).indexOf(scanned_barcode) >= 0){
                        encontrado = true;
                        // Siempre el código del producto está en la posición 0
                        id_producto = Object.values(global_packing_productos[i])[0];
                    }
                }
    
                /* Si el código escaneado coincide con alguno del pedido entonces
                   procedemos abrir la ventana de anclaje */    
                if (encontrado){
                    console.log("encontrado");
                    pack_producto(id_producto);
                }else{
                    Swal.fire({icon: 'error',title: 'código no está en lista',showConfirmButton: false,timer: 2000 });
                }
                delete encontrado, id_producto;
            }
        }
        delete ModalPicking;
    }