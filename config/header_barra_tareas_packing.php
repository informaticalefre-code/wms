    <div id="panel_superior">
        <?php
            require_once 'barra_search_filter.html';
        ?>
        <div id="barra-cont-form-search">
            <form class="barra-form-search" method="post" id="form-search" onsubmit="barra_buscar()">
                <input type="search" name="search-text" id="search-input" class="w-75" autocomplete="off" placeholder="¿Qué quieres buscar?"/>
                <button class="btn" type="submit"><i class="icon-search2" style="font-size:40px;color:white;"></i></button>
                <div id="search-options" style="color:white;">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio1" value="pedido" checked onchange="activa_search_focus()">
                        <label class="form-check-label text-light"  for="inlineRadio1">Pedido</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio2" value="embalador" onchange="activa_search_focus()">
                        <label class="form-check-label text-light" for="inlineRadio2">Embalador</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio3" value="tarea" onchange="activa_search_focus()">
                        <label class="form-check-label text-light" for="inlineRadio3">Tarea</label>
                    </div>
                </div>
            </form>            
        </div>
    </div>