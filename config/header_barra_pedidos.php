    <div id="panel_superior">
        <?php
            require_once 'barra_search_filter.html';
        ?>        
        <div id="barra-cont-form-search">
            <form class="barra-form-search" method="post" id="form-search" onsubmit="barra_buscar()">
                <input type="search" name="search-text" id="search-input" class="w-75" autocomplete="off" placeholder="¿Qué quieres buscar?"/>
                <button class="btn" type="submit"> <img class="nav-botones" src="img/svg/search.svg" width="30"></button>
                <div id="search-options" style="color:white;">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio1" value="pedido" checked  onchange="activa_search_focus()">
                        <label class="form-check-label" for="inlineRadio1">Pedido</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio2" value="cliente" onchange="activa_search_focus()">
                        <label class="form-check-label" for="inlineRadio2">Cliente</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio3" value="vendedor" onchange="activa_search_focus()">
                        <label class="form-check-label" for="inlineRadio3">Vendedor</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search-options" id="inlineRadio4" value="fecha" onchange="activa_search_focus()">
                        <label class="form-check-label" for="inlineRadio4">Fecha</label>
                    </div>
                </div>
            </form>            
        </div>
    </div>